<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Slidee;

use Nette;
use Nette\DI\CompilerExtension;


/**
 * @author Martin Takáč <martin@takac.name>
 */
final class Extension extends CompilerExtension
{

	/**
	 * Default configuration.
	 * @var array
	 */
	private $defaults = [
		'pagesDir' => '%appDir%/pages',
		'tempDir' => '%tempDir%',
		'presenter' => 'Page',
		'bare' => False,
	];


	function loadConfiguration()
	{
		$config = $this->getConfig();
		$builder = $this->getContainerBuilder();
		$builder->parameters['pagesDir'] = $config['pagesDir'];
	}



	function beforeCompile()
	{
		$config = $this->getConfig($this->defaults);

		$builder = $this->getContainerBuilder();
		$builder->getDefinition('router')
			->setClass('Taco\Nette\Slidee\TemplateRouter', [
				$config['pagesDir'],
				$config['tempDir'],
				$config['presenter']
			]);

		if ($config['bare']) {
			$builder->getDefinition($builder->getByType(Nette\Application\IPresenterFactory::class))->addSetup(
				'setMapping',
				[['*' => 'Taco\Nette\Slidee\*Presenter']]
			);
		}
	}

}
