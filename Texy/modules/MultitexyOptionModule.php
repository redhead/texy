<?php

class MultitexyOptionModule extends TexyModule
{

	public $options = false;



	public function __construct($texy)
	{
		$this->texy = $texy;

		$texy->addHandler('option', array($this, 'solve'));

		// [+] or [-]
		$texy->registerLinePattern(
				array($this, 'pattern'), '#(\[(\+|-)\])#', 'option'
		);
	}



	/**
	 * Callback for: [+] or [-].
	 *
	 * @param  TexyBlockParser $parser
	 * @param  array $matches regexp matches
	 * @return TexyHtml
	 */
	public function pattern($parser, $matches)
	{
		list(,, $type) = $matches;

		return $this->texy->invokeAroundHandlers('option', $parser, array($type));
	}



	/**
	 * Finish invocation.
	 *
	 * @param  TexyHandlerInvocation  handler invocation
	 * @param  string
	 * @return TexyHtml
	 */
	public function solve($invocation, $type)
	{
		$this->options = true;

		$el = TexyHtml::el('input');
		$el->attrs['type'] = 'checkbox';
		$el->attrs['data-option'] = ($type === '+' ? 'c' : 'w');
		$el->attrs['class'] = 'answer-option';

		return $el;
	}



	public function hasOptions()
	{
		return (bool) $this->options;
	}

}
