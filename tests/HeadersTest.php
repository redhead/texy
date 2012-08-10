<?php

include "MultiTexyTest.php";

class HeadersTest extends MultiTexyTest
{

	public function getValues()
	{
		$vals = array(
			array('# First-level', '<h1>First-level</h1>'),
			array('## Second-level', '<h2>Second-level</h2>'),
			array('### Third-level', '<h3>Third-level</h3>'),
			array('### Third-level ###', '<h3>Third-level</h3>'),
			array('## Second-level ##', '<h2>Second-level</h2>'),
			array("First-level\n===========", '<h1>First-level</h1>'),
			array("Second-level\n-------------", '<h2>Second-level</h2>')
		);
		$fromFiles = $this->fromFiles('headers');
		return array_merge($vals, $fromFiles);
	}

}
