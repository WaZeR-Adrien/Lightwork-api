<?php

class Comment extends \Kernel\Database
{
	/** @var string */
	private $id;

	/** @var User */
	private $user;

	/** @var string */
	private $id_movie;

	/** @var string */
	private $content;


	/**
	 * @return string
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @param string id
	 */
	public function setId($id)
	{
		$this->id = id;
	}


	/**
	 * @return User
	 */
	public function getUser()
	{
		return $this->user;
	}


	/**
	 * @param User user
	 */
	public function setUser(User $user)
	{
		$this->user = user;
	}


	/**
	 * @return string
	 */
	public function getIdMovie()
	{
		return $this->id_movie;
	}


	/**
	 * @param string id_movie
	 */
	public function setIdMovie($id_movie)
	{
		$this->id_movie = id_movie;
	}


	/**
	 * @return string
	 */
	public function getContent()
	{
		return $this->content;
	}


	/**
	 * @param string content
	 */
	public function setContent($content)
	{
		$this->content = content;
	}

}
