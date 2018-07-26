Nette Slidee
============

Simple slide for Nette. Little thing for making web sites in the oldschool style.

Many credits to David Grudl and Nette Foundation (https://doc.nette.org).

The points is, you are not creating any complex abstractions as many software guys nowdays like to do.
It is more simply, one webpage is one template.

But when you need, you can add whatever: components, servises, and all those timey wimey stuff by useing DI container.


## Usage

	composer require tacoberu/nette-slidee
	mkdir app
	cp -r vendor/tacoberu/nette-slidee/skel/* app
	mkdir document_root
	mv app/index.php document_root/
	mkdir -m 0777 -p var/log
	mkdir -m 0777 temp
	php -S localhost:8001 -t document_root
