<?php

namespace Application\Model;

class User extends HusDao
{
  /**
   * @var $instance : The unique instance of cache storage
   */
  protected static $instance = null;

  /**
   * Policies constructor.
   */
  public function __construct()
  {
    $this->table = 'users';
    return $this;
  }

  public static function initDao()
  {
    if (null === self::$instance) {
      $thisClass = __CLASS__;
      self::$instance = new $thisClass();
    }
    $dao = self::$instance;

    return $dao->setConnection('main');
  }

  /* CUSTOM DAO FUNCTIONS */

}
