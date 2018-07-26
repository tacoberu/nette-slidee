<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Slidee;

use Latte;
use Nette\Application\UI;
use Nette\ComponentModel\IComponent;
use Nette\Utils\Strings;


/**
 * Obsahuje právě jednu render a action metodu.
 * @credits David Grudl (https://davidgrudl.com)
 * @author Martin Takáč <martin@takac.name>
 */
class PagePresenter extends UI\Presenter
{
	private string $pagesDir;

	function __construct(string $pagesDir)
	{
		$this->pagesDir = $pagesDir;
	}


	/**
	 * Template factory.
	 */
	function createTemplate($class = NULL, callable $latteFactory = NULL): UI\ITemplate
	{
		$template = $this->getTemplateFactory()->createTemplate($this);

		// Macro {url ...}
		// inicializace Latte 2
		$latte = $template->getLatte();
		if (version_compare($latte::VERSION, '3', '<')) {
			$macroSet = new Latte\Macros\MacroSet($latte->getCompiler());
			$macroSet->addMacro('url', function () {}/*, NULL, NULL, $macroSet::ALLOWED_IN_HEAD*/); // ignore
		}
		// inicializace Latte 3
		else {
			$latte->addExtension(new SlideExtension([]));
		}

		$params = $this->request->getParameters();
		$template->presenter = $this;
		$template->context = $this->context;

		$file = self::actionToFilename($params['action']);
		if ($file[0] !== '/') {
			$file = $this->getPagesDir() . DIRECTORY_SEPARATOR . $file;
			if ( ! Strings::endsWith($file, '.latte')) {
				$file .= '.latte';
			}
		}

		$template->setFile($file);

		if ($this->getHttpRequest()) {
			$url = $this->getHttpRequest()->getUrl();
			$template->baseUrl = rtrim($url->getBaseUrl(), '/');
			$template->basePath = rtrim($url->getBasePath(), '/');
		}

		return $template;
	}



	protected function createComponent(string $name): ?IComponent
	{
		if ( ! $this->context->hasService($name)) {
			return Null;
		}
		return $this->context->getService($name)
			->create($this, $name);
	}



	private function getPagesDir(): string
	{
		return $this->pagesDir;
	}



	private static function actionToFilename(string $str): string
	{
		return strtolower(Strings::replace($str, '~([A-Z])~', '-$1'));
	}

}
