<?php

namespace Application\Model;

class Category extends HusDao
{
  const PAGE_SIZE = 20;

  /**
   * @var $instance : The unique instance of cache storage
   */
  protected static $instance = null;

  /**
   * Dao constructor.
   */
  public function __construct()
  {
    $this->table = 'categories';
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
