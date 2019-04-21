<?php

namespace Models;

/**
 * Class Auth
 * @package Models
 * @table auth
 */
class Auth extends Entity
{
	/** @var int */
	private $id;

	/** @var User */
	private $user;

	/** @var string */
	private $token;

	/** @var int */
	private $date;


	/**
	 * @param int id
	 */
	public function __construct($id = NULL)
	{
	    if (null != $id) { $this->id = $id; }
		$this->user = new User();
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
		$this->user = $user;
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
		$this->token = $token;
	}


	/**
	 * @return int
	 */
	public function getDate()
	{
		return $this->date;
	}


	/**
	 * @param int date
	 */
	public function setDate($date)
	{
		$this->date = $date;
	}

    /**
     * @param int $user_id
     * @param string $tokenToKeep
     * @return boolean
     */
    public static function disconnectAll($user_id, $tokenToKeep)
    {
        return self::exec('DELETE FROM '. self::getTable() .' WHERE user_id = ? AND token != ?', [
            $user_id, $tokenToKeep
        ]);
    }

}
