<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Slidee;

use Latte;
use Nette\Application\UI;
use Nette\Utils\Strings;


/**
 * Obsahuje právě jednu render a action metodu.
 * @credits David Grudl (https://davidgrudl.com)
 * @author Martin Takáč <martin@takac.name>
 */
class PagePresenter extends UI\Presenter
{

	/**
	 * Changes current action. Only alphanumeric characters are allowed.
	 * @param  string
	 * @return void
	 */
	function changeAction($action)
	{
	}



	/**
	 * Template factory.
	 * @param  string
	 * @return Application\UI\ITemplate
	 */
	function createTemplate($class = NULL, callable $latteFactory = NULL)
	{
		$template = $this->getTemplateFactory()->createTemplate($this);

		// Macro {url ...}
		$latte = $template->getLatte();
		$macroSet = new Latte\Macros\MacroSet($latte->getCompiler());
		$macroSet->addMacro('url', function () {}/*, NULL, NULL, $macroSet::ALLOWED_IN_HEAD*/); // ignore

		$params = $this->request->getParameters();
		$template->presenter = $this;
		$template->context = $this->context;

		$file = $params['action'];
		if ($file{0} !== '/') {
			$file = $this->getPagesDir() . DIRECTORY_SEPARATOR . $file;
			if ( ! Strings::endsWith($file, '.latte')) {
				$file .= '.latte';
			}
		}

		$template->setFile($file);
		$params['action'] = 'default';
		$template->setParameters($params);

		if ($this->getHttpRequest()) {
			$url = $this->getHttpRequest()->getUrl();
			$template->baseUrl = rtrim($url->getBaseUrl(), '/');
			$template->basePath = rtrim($url->getBasePath(), '/');
		}

		return $template;
	}



	/**
	 * @return string
	 */
	private function getPagesDir()
	{
		return $this->context->parameters['pagesDir'];
	}

}
