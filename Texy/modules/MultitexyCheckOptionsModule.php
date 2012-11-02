<?php

class MultitexyCheckOptionsModule extends TexyModule
{

	private $optionModule;



	public function __construct($texy, $optionModule)
	{
		$this->texy = $texy;
		$this->optionModule = $optionModule;

		$texy->addHandler('afterParse', array($this, 'afterParse'));
	}



	/**
	 * @param Texy $texy
	 * @param TexyHtml $dom
	 */
	public function afterParse($texy, $dom)
	{
		if (!$this->optionModule->hasOptions()) return;
		
		$el = TexyHtml::el('button');
		$el->attrs['class'][] = 'check-answers';
		
		$dom->add($el);
	}

}
