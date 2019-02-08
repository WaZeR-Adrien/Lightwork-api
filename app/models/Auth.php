<?php
namespace Models;
use Kernel\Database;

class Auth extends Database
{
   /**
    * Exact name of the table
    * @var string
    */
   protected static $_table = "auth";

   /**
    * @var string
    */
   protected $id;

   /**
    * @var string
    */
   protected $user_id;

   /**
    * @var string
    */
   protected $token;

   /**
    * @var string
    */
   protected $date;

    /**
     * @var User|null
     */
   private static $user = null;

   /**
    * @return int
    */
   public function getId()
   {
       return $this->id;
   }

   /**
    * @param int $id
    */
   public function setId($id)
   {
       $this->id = $id;
   }
   /**
    * @return int
    */
   public function getUserId()
   {
       return $this->user_id;
   }

   /**
    * @param int $user_id
    */
   public function setUserId($user_id)
   {
       $this->user_id = $user_id;
   }
   /**
    * @return int
    */
   public function getToken()
   {
       return $this->token;
   }

   /**
    * @param int $token
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
    * @param int $date
    */
   public function setDate($date)
   {
       $this->date = $date;
   }

    /**
     * @return array
     */
    public static function getUser($token)
    {
        // Cache condition
        if (null != self::$user) {
            return self::$user;
        }

        $auth = self::findFirst(['token' => $token]);

        self::$user = User::getById($auth->user_id);

        return self::$user;
    }

    /**
     * @param int $user_id
     * @param string $tokenToKeep
     * @return boolean
     */
    public static function disconnectAll($user_id, $tokenToKeep)
    {
        return self::exec('DELETE FROM '. self::$_table .' WHERE user_id = ? AND token != ?', [
            $user_id, $tokenToKeep
        ]);
    }
}