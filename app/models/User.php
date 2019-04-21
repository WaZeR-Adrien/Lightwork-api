<?php

namespace Models;

/**
 * Class User
 * @package Models
 * @table user
 */
class User extends Entity
{
	/** @var int */
	private $id;

	/** @var string */
	private $username;

	/** @var string */
	private $email;

	/** @var string */
	private $password;


	/**
	 * @param int id
	 */
	public function __construct($id = NULL)
	{
		$this->id = $id;
	}


	/**
	 * @return int
	 */
	public function getId()
	{
		return $this->id;
	}


	/**
	 * @param int id
	 */
	public function setId($id)
	{
		$this->id = $id;
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
		$this->username = $username;
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
		$this->email = $email;
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
		$this->password = $password;
	}

    /**
     * Check if email and password correspond
     * @return array|bool
     */
    public static function check($email, $password)
    {
        $user = self::findFirst(['email' => $email]);
        if (password_verify($password, $user->password)) {
            return $user;
        }
        return false;
    }

}
