<?php

class Post extends \Kernel\Database
{
	/** @var int */
	private $id;

	/** @var string */
	private $title;

	/** @var string */
	private $picture;

	/** @var string */
	private $content;

	/** @var int */
	private $date;

	/** @var int */
	private $user_id;


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
	public function getTitle()
	{
		return $this->title;
	}


	/**
	 * @params string title
	 */
	public function setTitle($title)
	{
		$this->title = title;
	}


	/**
	 * @return string
	 */
	public function getPicture()
	{
		return $this->picture;
	}


	/**
	 * @params string picture
	 */
	public function setPicture($picture)
	{
		$this->picture = picture;
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
	public function getDate()
	{
		return $this->date;
	}


	/**
	 * @params int date
	 */
	public function setDate($date)
	{
		$this->date = date;
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

}
