<?php

class Category extends \Kernel\Database
{
	/** @var int */
	private $id;

	/** @var string */
	private $label;

	/** @var float */
	private $test;


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @params int id
	 */
	public function setId($id)
	{
		$this->id = id;
	}


	/**
	 * @return string
	 */
	public function getLabel()
	{
		return $this->label;
	}


	/**
	 * @params string label
	 */
	public function setLabel($label)
	{
		$this->label = label;
	}


	/**
	 * @return float
	 */
	public function getTest()
	{
		return $this->test;
	}


	/**
	 * @params float test
	 */
	public function setTest($test)
	{
		$this->test = test;
	}

}
