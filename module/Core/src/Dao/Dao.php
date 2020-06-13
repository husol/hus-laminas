<?php

namespace Core\Dao;

use Laminas\Db\Sql\Sql;
use Laminas\Db\Adapter\Driver\ResultInterface;
use Laminas\Db\ResultSet\ResultSet;
use Laminas\Json\Json;

abstract class Dao
{
  /**
   * @var Hus_Db_Connection
   */
  protected $conn;

  /**
   * @var Hus_Db_Dao
   */
  protected $dao;

  /**
   * @var Laminas\Db\Sql\Sql
   */
  protected $sql;

  /**
   * @var Hus_Db_Execute
   */
  protected $execute;


  /**
   * Database table prefix
   *
   * @var string
   * @since 2.0.3
   */
  protected $prefix = '';

  /**
   * The language content
   * @var string
   * @since 2.0.8
   */
  protected $lang;

  /**
   * @param null $db
   * @return Dao
   * @throws \Exception
   */
  public function setConnection($db = null)
  {
    $validator = new \Laminas\Validator\File\Exists(ROOT_DIR . '/config');
    if (!$validator->isValid(ROOT_DIR . '/config/database.config.php')) {
      throw new \Exception('Database configuration not found');
    }

    $dbConfig = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/config/database.config.php');
    if (!isset($dbConfig[$db])) {
      throw new \Exception('Database configuration not found');
    }

    $this->conn = new \Laminas\Db\Adapter\Adapter($dbConfig[$db]);
    $this->sql = new Sql($this->conn);

    return $this;
  }


  /**
   * @return Hus_Connection
   */
  public function getConnection()
  {
    return $this->conn;
  }

  /**
   * @param string $lang
   * @return Hus_Model_Dao
   * @since 2.0.8
   */
  public function setLang($lang)
  {
    $this->lang = $lang;
    return $this;
  }

  public function sql()
  {
    return new Sql($this->conn);
  }

  public function query($sql)
  {
    if (is_string($sql)) {
      $stmt = $this->conn->query($sql);
    } else {
      $stmt = $this->sql->prepareStatementForSqlObject($sql);
    }

    $this->execute = $stmt->execute();
    return $this;
  }

  public function procedure($callStr)
  {
    $stmt = $this->conn->createStatement();
    $stmt->prepare($callStr);
    $this->execute = $stmt->execute();

    return $this;
  }

  public function sqlDelete($table, $where = [])
  {
    if (!$table || !is_array($where) || empty($where)) {
      return false;
    }
    try {
      $delete = $this->sql->delete($table);
      if (is_array($where) && !empty($where)) {
        $delete->where($where);
      }

      $sqlStr = $this->sql->getSqlStringForSqlObject($delete);
      return $this->conn->query($sqlStr, $this->conn::QUERY_MODE_EXECUTE);
    } catch (\Exception $e) {
      $this->__logs("Hus_ModelMysql_{$table}.sqlDelete", $e->getMessage());
    }

    return false;
  }

  /**
   * @returns Laminas\Db\Sql\Insert instance
   */
  public function sqlInsert($table, $data = [])
  {
    if (!$table || !is_array($data) || empty($data)) {
      return false;
    }
    try {
      $insert = $this->sql->insert($table);
      $insert->values($data);
      $sqlStr = $this->sql->getSqlStringForSqlObject($insert);
      return $this->conn->query($sqlStr, $this->conn::QUERY_MODE_EXECUTE);
    } catch (\Exception $e) {
      $this->__logs("Hus_ModelMysql_{$table}.sqlInsert", $e->getMessage());
    }

    return false;
  }

  /**
   * Sql Update
   *
   * @param array $data
   * @param array $where
   * @return bool
   * @var String $table , Array $data, Array $where
   */
  public function sqlUpdate($table, $data = [], $where = [])
  {
    if (!$table || !is_array($data) || empty($data)) {
      return false;
    }
    try {
      $update = $this->sql->update($table);
      $update->set($data);
      if (is_array($where) && !empty($where)) {
        $update->where($where);
      }

      $sqlStr = $this->sql->getSqlStringForSqlObject($update);
      return $this->conn->query($sqlStr, $this->conn::QUERY_MODE_EXECUTE);
    } catch (\Exception $e) {
      $this->__logs("Hus_ModelMysql_{$table}.sqlUpdate", $e->getMessage());
    }

    return false;
  }

  public function fetch()
  {
    try {
      if ($this->execute instanceof ResultInterface && $this->execute->isQueryResult()) {
        $resultSet = new ResultSet(ResultSet::TYPE_ARRAYOBJECT);
        return $resultSet->initialize($this->execute)->current();
      }
    } catch (\Exception $e) {
    }

    return false;
  }

  public function fetchAll()
  {
    try {
      if ($this->execute instanceof ResultInterface && $this->execute->isQueryResult()) {
        $resultSet = new ResultSet(ResultSet::TYPE_ARRAYOBJECT);
        return $resultSet->initialize($this->execute)->buffer();
      }
    } catch (\Exception $e) {
    }

    return false;
  }

  /**
   * @param $strName
   * @param $logs
   */

  protected function __logs($strName, $logs)
  {
    $fn = sprintf('%s.%s.txt', $strName, date('Y.m.d'));
    $writer = new \Laminas\Log\Writer\Stream(ROOT_DIR . DS . 'logs' . DS . $fn);
    $formatter = new \Laminas\Log\Formatter\Simple('%message%');
    $writer->setFormatter($formatter);
    $logger = new \Laminas\Log\Logger();
    $logger->addWriter($writer);
    $logger->info(Json::encode($logs));
  }
}
