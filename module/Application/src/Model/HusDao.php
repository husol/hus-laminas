<?php

namespace Application\Model;

use Core\Dao\Dao;
use Laminas\Db\Sql\Expression;
use Laminas\Json\Json;

class HusDao extends Dao
{
  protected $table = null;
  protected $connTrans;

  public function update($table, $data = [], $where = [])
  {
    if(!is_array($data) || empty($data)) {
      return false;
    }

    $update = $this->sql->update($table);
    $update->set($data);
    if(!empty($where) && (is_array($where) || is_string($where))) {
      $update->where($where);
    }

    $sqlStr = $this->sql->getSqlStringForSqlObject($update);
    return $this->conn->query($sqlStr, $this->conn::QUERY_MODE_EXECUTE);
  }

  public function delete($table, $where = [])
  {
    if(!$table || !is_array($where) || empty($where)) {
      return false;
    }

    $delete = $this->sql->delete($table);
    $delete->where($where);

    $sqlStr = $this->sql->getSqlStringForSqlObject($delete);
    return $this->conn->query($sqlStr, $this->conn::QUERY_MODE_EXECUTE);
  }

  public function beginTransaction()
  {
    $this->connTrans = $this->conn->getDriver()->getConnection();
    $this->connTrans->beginTransaction();
  }

  public function commit()
  {
    $this->connTrans->commit();
  }

  public function rollback()
  {
    $this->connTrans->rollback();
  }
  /* START STANDARD FUNCTIONS: find, save, remove */

  public function find($params = [], $id = 0)
  {
    $husConfig = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');

    try {
      $sql = $this->sql->select();
      $sql->from($this->table);
      if (!in_array($this->table, $husConfig['HARD_DELETED_TABLES'])) {
        $sql->where->isNotNull('deleted_at');
      }

      if ($id) {
        $params['conditions']['id'] = $id;
      }

      if (isset($params['columns']) && isset($params['columns']['expressions'])) {
        foreach ($params['columns']['expressions'] as $alias => $expression) {
          $params['columns'][$alias] = new Expression($expression);
        }
        unset($params['columns']['expressions']);
      }

      //Check pagination
      $isPaging = false;
      if (isset($params['pagination'])) {
        $page = intval($params['pagination']['page']) <= 1 ? 1 : intval($params['pagination']['page']);
        $pageSize = $params['pagination']['pageSize'];
        $params['limit'] = $pageSize;
        $params['offset'] = ($page - 1) * $pageSize;
        $isPaging = true;
      }

      //Check flexible conditions
      if (isset($params['conditions']['flexible']) && is_array($params['conditions']['flexible'])) {
        foreach ($params['conditions']['flexible'] as $condition) {
          $operator = key($condition);
          if (is_string($operator)) {
            switch (strtolower($operator)) {
              case 'isnull':
                $sql->where->isNull($condition[$operator]);
                break;
              case 'isnotnull':
                $sql->where->isNotNull($condition[$operator]);
                break;
              case 'in':
                $sql->where->in($condition[$operator][0], $condition[$operator][1]);
                break;
              case 'notin':
                $sql->where->notIn($condition[$operator][0], $condition[$operator][1]);
                break;
              case 'like':
                $sql->where->like($condition[$operator][0], $condition[$operator][1]);
                break;
              case 'expression':
                $sql->where->expression($condition[$operator][0], $condition[$operator][1]);
                break;
              case 'between':
                $sql->where("{$condition[$operator][0]} BETWEEN {$condition[$operator][1]} AND {$condition[$operator][2]}");
                break;
              case 'complex':
                $sql->where("({$condition[$operator]})");
                break;
              default:
                $this->__logs("Hus_ModelMysql_{$this->table}.find", "Type of condition is not supported.");
                return false;
            }
          }
        }
        unset($params['conditions']['flexible']);
      }

      if (!empty($params['conditions'])) {
        $sql->where($params['conditions']);
      }

      $rs['count'] = true;
      if ($isPaging) {
        $sql->columns(['total_count' => new Expression('COUNT(*)')]);
        $rs['count'] = $this->query($sql)->fetch();
        $sql->columns(['*']);
      }

      if (isset($params['columns'])) {//Ex: array: ['id', 'name']
        $sql->columns($params['columns']);
      }

      if (isset($params['order'])) {//Ex: array: ['name ASC', 'age DESC']
        $sql->order($params['order']);
      }

      //Limit if any
      if (isset($params['limit'])) {
        $sql->limit(intval($params['limit']));
      }

      //Offset if any
      if (isset($params['offset'])) {
        $sql->offset(intval($params['offset']));
      }

      $rs['data'] = $this->query($sql)->fetchAll();

      if ($rs['count'] === false || $rs['data'] === false) {
        return false;
      }


      if ($id > 0 || isset($params['isFetchRow']) && intval($params['isFetchRow'])) {
        $result = $rs['data']->current();
      } elseif (isset($params['pagination'])) {
        $result = new \stdClass();
        $result->count = $rs['count']->total_count;
        $result->data = $rs['data']->toArray();
      } else {
        $result = $rs['data']->toArray();
      }

      return Json::decode(Json::encode($result));
    } catch (\Exception $e) {
      $this->__logs("Hus_ModelMysql_{$this->table}.find", $e->getMessage());
      return false;
    }
  }

  /**
   * @param $data
   * @param int $id
   * @return array|bool|int|null
   */
  public function save($data, $id = 0)
  {
    try {
      $insertBatch = $id == -1;

      if (is_object($data)) {
        $data = (array)$data;
      }
      if ($id > 0) {
        $this->update($this->table, $data, ['id' => $id]);
      } else {
        $id = $this->sqlInsert($this->table, $data)->getGeneratedValue();
        if (isset($data['id'])) {
          $id = $data['id'];
        }
      }

      if ($insertBatch) {
        return $id;
      }

      $sql = $this->sql->select();
      $sql->from($this->table);
      $sql->where(['id' => $id]);

      return $this->query($sql)->fetch();
    } catch (\Exception $e) {
      $this->__logs("Hus_ModelMysql_{$this->table}.save", $e->getMessage());
      return false;
    }
  }

  /**
   * @param $where
   * @param int $flag : -1: Hard deletion, 0: Soft deletion without updated_by, > 0: Update updatedBy
   * @return bool
   */
  public function remove($where, $flag = 0)
  {
    $husConfig = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');

    try {
      if (is_object($where)) {
        $where = (array)$where;
      }

      if (in_array($this->table, $husConfig['HARD_DELETED_TABLES']) || $flag == -1) {
        $this->delete($this->table, $where);
      } else {
        $data = ['deleted_at' => date('Y-m-d H:i:s')];

        if ($flag > 0) {
          $data['updated_by'] = $flag;
        }

        $this->update($this->table, $data, $where);
      }

      return true;
    } catch (\Exception $e) {
      $this->__logs("Hus_ModelMysql_{$this->table}.remove", $e->getMessage());
      return false;
    }
  }
  /* END STANDARD FUNCTIONS: find, count, save, remove */
}
