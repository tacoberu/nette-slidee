<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Slidee;

use Latte\Engine;
use Latte\Macros;
use Latte\Compiler\Tag;
use Latte\Compiler\PrintContext;
use Latte\Compiler\Nodes\StatementNode;
use Latte\Compiler\Nodes\Php\Scalar\StringNode;
use Latte\Compiler\Nodes\Php\Expression\ArrayNode;
use Latte\Compiler\Nodes\Php\ArrayItemNode;
use Nette;
use Nette\Application\Routers;
use Nette\Application\UI\ITemplateFactory;
use Nette\Utils\Strings;


/**
 * Micro-framework router for templates using {url} macro.
 * @credits David Grudl (https://davidgrudl.com)
 * @author Martin Takáč <martin@takac.name>
 */
class TemplateRouter extends Routers\RouteList
{

	function __construct(string $path, string $cachePath, string $presenterName, ITemplateFactory $templateFactory)
	{
		$template = $templateFactory->createTemplate();

		if (is_file($cacheFile = $cachePath . '/routes.php')) {
			$routes = require $cacheFile;
		}
		else {
			$routes = $this->scanRoutes($template->getLatte(), $path);
			file_put_contents($cacheFile, '<?php return ' . var_export($routes, TRUE) . ';');
		}

		foreach ($routes as $mask => $file) {
			$this[] = new Routers\Route($mask, $presenterName . ':' . self::uniq($path, $file));
		}
	}



	private function scanRoutes(Engine $latte, $path): array
	{
		$routes = new \ArrayObject;

		// inicializace Latte 2
		if (version_compare(Engine::VERSION, '3', '<')) {
			$macroSet = new Macros\MacroSet($latte->getCompiler());
			$macroSet->addMacro('url', function ($node) use (&$routes, &$file) {
				$routes[$node->args] = (string) $file;
			}/*, NULL, NULL, $macroSet::ALLOWED_IN_HEAD*/);
			// Prohledat šablony.
			foreach (Nette\Utils\Finder::findFiles('*.latte')->from($path) as $file) {
				$latte->compile($file);
			}
		}
		// inicializace Latte 3
		else {
			$ext = new SlideExtension($routes);
			$latte->addExtension($ext);
			// Prohledat šablony.
			foreach (Nette\Utils\Finder::findFiles('*.latte')->from($path) as $file) {
				$ext->setCurrentFile($file); // abych věděl kde šablonu hledat
				$latte->compile($file);
			}
		}
		return (array) $routes;
	}



	/**
	 * Odstranění z cesty souboru již známou $path.
	 */
	private static function uniq(string $path, string $file): string
	{
		if ( ! Strings::startsWith($file, $path)) {
			return $file;
		}
		return self::camelCase(substr(substr($file, strlen($path) + 1), 0, -6)); // .latte
	}



	private static function camelCase(string $str): string
	{
		return lcfirst(strtr(Strings::capitalize(strtr($str, '-', ' ')), [' ' => '']));
	}

}



/**
 * {url contact-me}
 */
class SlideExtension extends \Latte\Extension
{
	private $routes;
	private $currentFile;


	function __construct($routes)
	{
		$this->routes = $routes;
	}



	function setCurrentFile($file)
	{
		$this->currentFile = $file;
	}


	function getTags(): array
	{
		return [
			'url' => function($tag) {
				return new UrlNode($tag, $this->routes, $this->currentFile);
			},
		];
	}
}



class UrlNode extends StatementNode
{
	public $subject;


	function __construct(Tag $tag, $routes, $currentFile)
	{
		$this->subject = $tag->parser->parseUnquotedStringOrExpression();
		$routes[self::format($this->subject)] = (string) $currentFile;
	}



	function print(PrintContext $context): string
	{
		return '';
	}



	function &getIterator(): \Generator
	{
		yield $this->subject;
	}



	private static function format($src): string
	{
		switch(true) {
			case $src instanceof StringNode:
				return $src->value;

			case $src instanceof ArrayItemNode:
				return self::format($src->value);

			case $src instanceof ArrayNode:
				$xs = [];
				foreach ($src as $x) {
					$xs[] = self::format($x);
				}
				return '[' . implode(', ', $xs) . ']';

			default:
				throw new \LogicException("Unsupported type of url.");
		}
	}
}
