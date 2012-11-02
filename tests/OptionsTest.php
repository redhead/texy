<?php

include_once "MultiTexyTestCase.php";

class OptionsTest extends MultiTexyTestCase
{

	public function getValues()
	{
		return $this->fromFiles('options');
	}

}
