<?php

class User extends \Kernel\Database
{
	/** @var int */
	private $id;

	/** @var string */
	private $firstName;

	/** @var string */
	private $lastName;

	/** @var string */
	private $email;

	/** @var string */
	private $password;

	/** @var string */
	private $role;


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
	public function getFirstName()
	{
		return $this->firstName;
	}


	/**
	 * @params string firstName
	 */
	public function setFirstName($firstName)
	{
		$this->firstName = firstName;
	}


	/**
	 * @return string
	 */
	public function getLastName()
	{
		return $this->lastName;
	}


	/**
	 * @params string lastName
	 */
	public function setLastName($lastName)
	{
		$this->lastName = lastName;
	}


	/**
	 * @return string
	 */
	public function getEmail()
	{
		return $this->email;
	}


	/**
	 * @params string email
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
	 * @params string password
	 */
	public function setPassword($password)
	{
		$this->password = password;
	}


	/**
	 * @return string
	 */
	public function getRole()
	{
		return $this->role;
	}


	/**
	 * @params string role
	 */
	public function setRole($role)
	{
		$this->role = role;
	}

}
