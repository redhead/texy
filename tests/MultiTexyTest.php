<?php

include_once "../Texy/Texy.php";

abstract class MultiTexyTest extends PHPUnit_Framework_TestCase
{

	/** @var Texy */
	private $texy;



	protected function setUp()
	{
		Texy::$advertisingNotice = FALSE;
		$this->texy = new Texy();
	}



	/**
	 * @dataProvider getValues
	 */
	public function test($input, $expected)
	{
		$output = trim($this->texy->process($input));
		$this->assertEquals($expected, $output);
	}



	protected function fromFiles($folder)
	{
		$values = array();

		$base = 'files/' . $folder . '/';
		$inputPattern = $base . 'input_*';
		foreach (glob($inputPattern) as $inputFile) {
			$input = file_get_contents($inputFile);

			$extectedFile = str_replace("input", "expected", $inputFile);
			$expected = file_get_contents($extectedFile);

			$input = str_replace("\r\n", "\n", $input);
			$expected = str_replace("\r\n", "\n", $expected);

			$values[] = array($input, $expected);
		}

		return $values;
	}



	abstract public function getValues();
}
