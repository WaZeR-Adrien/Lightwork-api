<?php

class PostCategory extends \Kernel\Database
{
	/** @var int */
	private $id;

	/** @var int */
	private $category_id;

	/** @var int */
	private $post_id;


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
	 * @return int
	 */
	public function getCategoryId()
	{
		return $this->category_id;
	}


	/**
	 * @params int category_id
	 */
	public function setCategoryId($category_id)
	{
		$this->category_id = category_id;
	}


	/**
	 * @return int
	 */
	public function getPostId()
	{
		return $this->post_id;
	}


	/**
	 * @params int post_id
	 */
	public function setPostId($post_id)
	{
		$this->post_id = post_id;
	}

}
