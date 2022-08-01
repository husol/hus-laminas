<?php
/**
 * Last modifier: khoaht
 * Last modified date: 13/09/22
 * Description: Use this class to implement file functions
 */

namespace Core\Hus;

use Laminas\Cache\Storage\Adapter\Redis;

class HusRedis
{
  protected $cache;

  public function __construct()
  {
    $dbConfig = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/config/database.config.php');
    if (!isset($dbConfig['redis'])) {
      throw new \Exception('Redis configuration not found');
    }

    $options = [
      'namespace' => 'hus',
      'database' => $dbConfig['redis']['database'],
      'server' => [
        'host' => $dbConfig['redis']['host'],
        'port' => $dbConfig['redis']['port'],
        'timeout' => $dbConfig['redis']['timeout']
      ],
      'password' => $dbConfig['redis']['password']
    ];

    $this->cache = new Redis($options);
  }

  public function getItem($key, &$success)
  {
    return $this->cache->getItem($key, $success);
  }

  public function addItem($key, $value)
  {
    return $this->cache->addItem($key, $value);
  }

  public function setItem($key, $value)
  {
    return $this->cache->setItem($key, $value);
  }

  // ttl in seconds
  public function addItemWithTTL($key, $value, $ttl)
  {
    if (!is_string($value)) {
      $value = json_encode($value);
    }

    $this->cache->getOptions()->setTtl($ttl);

    return $this->cache->addItem($key, $value);
  }

  // ttl in seconds
  public function setItemWithTTL($key, $value, $ttl)
  {
    if (!is_string($value)) {
      $value = json_encode($value);
    }

    $this->cache->getOptions()->setTtl($ttl);

    return $this->cache->setItem($key, $value);
  }
}
