<?php

include_once "MultiTexyTestCase.php";

class ListsTest extends MultiTexyTestCase
{

	public function getValues()
	{
		return $this->fromFiles('lists');
	}

}
