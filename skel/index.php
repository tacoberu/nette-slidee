<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @author     Martin Takáč <martin@takac.name>
 */

$container = require __dir__ . '/../app/bootstrap.php';
$container->getByType('Nette\Application\Application')
	->run();
