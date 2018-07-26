<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @author     Martin Takáč <martin@takac.name>
 */

if (@!include __dir__ . '/../vendor/autoload.php') {
	die('Install Nette using `composer install`');
}

error_reporting(~E_USER_DEPRECATED);

// Configure application
$configurator = new Nette\Configurator;

// Enable Nette Debugger for error visualisation & logging
$configurator->enableTracy(__dir__ . '/../var/log');
$configurator->setDebugMode(True);  // debug mode MUST NOT be enabled on production server

// Create Dependency Injection container
$configurator->setTempDirectory(__dir__ . '/../temp');

$configurator->addConfig(__dir__ . '/config.neon');
$configurator->addConfig(__dir__ . '/config.local.neon');

$container = $configurator->createContainer();
return $container;
