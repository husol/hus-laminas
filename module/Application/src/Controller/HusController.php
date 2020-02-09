<?php

namespace Application\Controller;

use Laminas\Json\Json;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\View\Model\JsonModel;

class HusController extends AbstractActionController
{
  protected $dao = null;
  protected $container;
  protected $session;
  protected $renderer;

  public function __construct($container)
  {
    $this->container = $container;
    $this->session = $container->get('HusSessionContainer');
    $this->renderer = $container->get('Laminas\View\Renderer\PhpRenderer');
  }

  public function getBaseUrl()
  {
    $uri = $this->getRequest()->getUri();
    return sprintf('%s://%s', $uri->getScheme(), $uri->getHost());
  }

  public function getLoggedUserInfo($param = '')
  {
    $userInfo = $this->session->loggedUser->info;
    if (empty($param)) {
      return $userInfo;
    }

    return $userInfo->$param;
  }

  private function escapeValuesSQL($values)
  {
    $search = ["\x00", "\n", "\r", "\\", "'", "\"", "\x1a"];
    $replace = ["\\x00", "\\n", "\\r", "\\\\" ,"\'", "\\\"", "\\\x1a"];
    $result = [];
    foreach ($values as $value) {
      if (is_string($value)) {
        $result[] = "'" . str_replace($search, $replace, $value) . "'";
      } elseif (is_bool($value)) {
        $result[] = ($value === false) ? 0 : 1;
      } elseif (is_null($value)) {
        $result[] = 'NULL';
      } else {
        $result[] = $value;
      }
    }
    return implode(',', $result);
  }

  protected function buildInsertUpdateSQL($tablename, $dataObject = [], $fields = [], $exclude = false)
  {
    if (empty($tablename)) {
      return ['result' => false, 'message' => 'Table name must be inputted.'];
    }
    if (empty($fields)) {
      return ['result' => false, 'message' => 'Fields for updating/not updating must be inputted.'];
    }
    if (empty($dataObject)) {
      return ['result' => false, 'message' => 'No data updated.'];
    }

    $firstObj = reset($dataObject);

    if (is_array($firstObj)) {
      $keys = array_keys($firstObj);
      foreach ($dataObject as $obj) {
        $values[] = '(' . $this->escapeValuesSQL(array_values($obj)) . ')';
      }
      $values = implode(',', $values);
    } elseif (is_object($firstObj)) {
      $firstObj = get_object_vars($firstObj);
      $keys = array_keys($firstObj);

      $values = [];
      foreach ($dataObject as $obj) {
        $value = [];
        foreach ($keys as $key) {
          $value[] = $obj->$key;
        }
        $values[] = '(' . $this->escapeValuesSQL($value) . ')';
      }

      $values = implode(',', $values);
    } else {
      $keys = array_keys($dataObject);
      $values = '(' . $this->escapeValuesSQL(array_values($dataObject)) . ')';
    }

    if ($exclude) {
      $fields = array_diff($keys, $fields);
    }

    $keys = implode('`,`', $keys);

    $sql = "INSERT INTO $tablename (`$keys`) VALUES $values ON DUPLICATE KEY UPDATE ";

    $updateFields = [];
    foreach ($fields as $col) {
      $updateFields[] = "`$col` = VALUES(`$col`)";
    }

    $sql .= implode(', ', $updateFields);

    return array('result' => true, 'sql' => $sql);
  }

  public function insertUpdateAction()
  {
    $resp = ['status' => 'FAIL', 'message' => '', 'result' => ''];

    $request = $this->getRequest();

    if (!$request->isPost()) {
      $resp['message'] = 'Invalid request method.';
      return new JsonModel($resp);
    }

    $data = Json::decode($request->getContent());

    if (!isset($data->table)) {
      $resp['message'] = 'Missing "table" parameter.';
      return new JsonModel($resp);
    }

    $table = $data->table;
    unset($data->table);

    $fields = [];
    if (isset($data->fields)) {
      $fields = $data->fields;
      unset($data->fields);
    }

    $exclude = false;
    if (isset($data->exclude)) {
      $exclude = $data->exclude;
      unset($data->exclude);
    }

    //Access DAO
    $query = $this->buildInsertUpdateSQL($table, $data->data, $fields, $exclude);

    if ($query['result']) {
      $rs = $this->dao->query($query['sql']);
    } else {
      $resp['message'] = $query['message'];
      return new JsonModel($resp);
    }

    $resp['result'] = $rs;
    $resp['status'] = 'SUCCESS';

    return new JsonModel($resp);
  }
}
