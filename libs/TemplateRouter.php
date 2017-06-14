<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Slidee;

use Latte\Engine;
use Latte\Macros;
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

	/**
	 * @param string $path
	 * @param string $cachePath
	 * @param string $presenterName
	 * @param ITemplateFactory $templateFactory
	 */
	function __construct($path, $cachePath, $presenterName, ITemplateFactory $templateFactory)
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
			// No, krapet dost zbastlené, ale za účelem prototypu...
			// Možná vlastní router?
			$this[] = new Routers\Route($mask, $presenterName . ':' . self::uniq($path, $file));
		}
	}



	/**
	 * @param string
	 */
	private function scanRoutes(Engine $latte, $path)
	{
		$routes = [];
		$macroSet = new Macros\MacroSet($latte->getCompiler());
		$macroSet->addMacro('url', function ($node) use (&$routes, &$file) {
			$routes[$node->args] = (string) $file;
		}/*, NULL, NULL, $macroSet::ALLOWED_IN_HEAD*/);

		// Prohledat šablony.
		foreach (Nette\Utils\Finder::findFiles('*.latte')->from($path) as $file) {
			$latte->compile($file);
		}

		return $routes;
	}



	/**
	 * Odstranění z cesty souboru již známou $path.
	 */
	private static function uniq($path, $file)
	{
		if ( ! Strings::startsWith($file, $path)) {
			return $file;
		}
		return substr(substr($file, strlen($path) + 1), 0, -6); // .latte
	}

}
