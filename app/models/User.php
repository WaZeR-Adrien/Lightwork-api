<?php

class User extends \Kernel\Database
{
	/** @var string */
	private $id;

	/** @var string */
	private $username;

	/** @var string */
	private $email;

	/** @var string */
	private $password;


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
	public function getUsername()
	{
		return $this->username;
	}


	/**
	 * @param string username
	 */
	public function setUsername($username)
	{
		$this->username = username;
	}


	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}


	/**
	 * @param string email
	 */
	public function setEmail($email)
	{
		$this->email = email;
	}


	/**
	 * @return string
	 */
	public function getPassword()
	{
		return $this->password;
	}


	/**
	 * @param string password
	 */
	public function setPassword($password)
	{
		$this->password = password;
	}

}
