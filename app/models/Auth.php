<?php

class Auth extends \Kernel\Database
{
	/** @var string */
	private $id;

	/** @var string */
	private $token;

	/** @var string */
	private $date;

	/** @var User */
	private $user;


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
	 * @return string
	 */
	public function getToken()
	{
		return $this->token;
	}


	/**
	 * @param string token
	 */
	public function setToken($token)
	{
		$this->token = token;
	}


	/**
	 * @return string
	 */
	public function getDate()
	{
		return $this->date;
	}


	/**
	 * @param string date
	 */
	public function setDate($date)
	{
		$this->date = date;
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

}
