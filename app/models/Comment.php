<?php

class Comment extends \Kernel\Database
{
	/** @var int */
	private $id;

	/** @var string */
	private $content;

	/** @var int */
	private $user_id;

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
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}


	/**
	 * @params string content
	 */
	public function setContent($content)
	{
		$this->content = content;
	}


	/**
	 * @return int
	 */
	public function getUserId()
	{
		return $this->user_id;
	}


	/**
	 * @params int user_id
	 */
	public function setUserId($user_id)
	{
		$this->user_id = user_id;
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
