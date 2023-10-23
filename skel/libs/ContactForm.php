<?php
/**
 * Copyright (c) since 2004 Martin Takáč (http://martin.takac.name)
 * @author     Martin Takáč <martin@takac.name>
 */

namespace App\Controls;

use Nette\Application\UI\Form;
use Nette\ComponentModel\IContainer;


class ContactFormFactory
{

	private ContactFormModel $model;


	function __construct(ContactFormModel $m)
	{
		$this->model = $m;
	}



	function create(IContainer $parent, string $name)
	{
		$form = new Form($parent, $name);
		$form->addText('surname', 'Příjmení')->setRequired();
		$form->addText('mail', 'Email');
		$form->addSubmit('send', 'Odeslat');
		$form->addSubmit('cancel', 'Cancel')->setValidationScope(array());

		$form->onSuccess[] = function(Form $form, array $values) {
			switch (True) {
				case isset($form['send']) && $form['send']->isSubmittedBy():
					$this->model->doPersist($values);
					$form->presenter->flashMessage("Odesláno");
					$form->presenter->redirect("this");
					return;
				case isset($form['cancel']) && $form['cancel']->isSubmittedBy():
					$form->presenter->redirect("index");
					return;
			}
		};
		return $form;
	}
}



class ContactFormModel
{
	function doPersist(array $values)
	{
		// @TODO
	}
}
