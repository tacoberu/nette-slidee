<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @license   https://opensource.org/licenses/MIT MIT
 */

namespace Taco\Nette\Slidee;

use Nette;
use Nette\Schema\Expect;
use Nette\DI\CompilerExtension;


/**
 * @author Martin Takáč <martin@takac.name>
 */
final class Extension extends CompilerExtension
{

	private string $appDir;
	private string $tempDir;


	function __construct(string $appDir, string $tempDir)
	{
		$this->appDir = $appDir;
		$this->tempDir = $tempDir;
	}



	function getConfigSchema(): Nette\Schema\Schema
	{
		return Expect::structure([
			'pagesDir' => Expect::string()->default($this->appDir . '/pages'),
			'tempDir' => Expect::string()->default($this->tempDir),
			'presenter' => Expect::string()->default('Page'),
			'bare' => Expect::bool()->default(True),
		]);
	}



	function loadConfiguration()
	{
		$builder = $this->getContainerBuilder();
		$builder->addDefinition('router')
			->setCreator(TemplateRouter::class, [
				$this->config->pagesDir,
				$this->config->tempDir,
				$this->config->presenter
			]);
		$builder->getDefinition($builder->getByType(Nette\Application\IPresenterFactory::class))->addSetup(
			'setMapping',
			[['*' => 'Taco\Nette\Slidee\*Presenter']]
		);
		$builder->addDefinition($this->prefix('slider'))
			->setCreator(PagePresenter::class, [
				$this->config->pagesDir
			]);
	}


}
