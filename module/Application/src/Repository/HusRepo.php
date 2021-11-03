<?php

namespace Application\Repository;

use Core\Hus\HusAjax;
use Core\Hus\HusLogger;

class HusRepo
{
  protected $container;
  protected $session;
  protected $config;
  protected $apiHost;
  protected $entity;

  public function __construct($container)
  {
    $this->container = $container;
    $this->session = $this->container->get('HusSessionContainer');
    $this->config = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
  }

  public function find($conditions = [], $id = 0)
  {
    $uri = "{$this->apiHost}/" . alphabetonly($this->entity);
    if ($id) {
      $uri .= "/$id";
    }
    $params = [
      'method' => 'GET',
      'token' => $this->session->loggedUser->token,
      'data' => $conditions
    ];

    $result = callAPI($this->apiHost, $uri, $params);

    if ($result['status'] != 'SUCCESS') {
      HusLogger::error($result, "ResponseServiceFailed_".alphabetonly($this->entity), 'find');
      HusAjax::setMessage($result['error']);
      return false;
    }

    $result = $result['result'];
    return $result;
  }

  public function save($data, $id = 0)
  {
    $userInfo = $this->session->loggedUser->info;

    if ($id) {
      $data['id'] = $id;
      $data['updated_by'] = $userInfo->id;
    } else {
      $data['created_by'] = $userInfo->id;
    }

    $params = [
      'token' => $this->session->loggedUser->token,
      'data' => $data
    ];

    $uri = "{$this->apiHost}/" . alphabetonly($this->entity) . '/save';
    $result = callAPI($this->apiHost, $uri, $params);

    if ($result['status'] != 'SUCCESS') {
      HusLogger::error($result, "ResponseServiceFailed_".alphabetonly($this->entity), 'save');
      HusAjax::setMessage($result['error']);
      return false;
    }

    $result = $result['result'];
    return $result;
  }

  public function remove($conditions, $hard = false)
  {
    $params = [
      'token' => $this->session->loggedUser->token,
      'data' => $conditions
    ];
    $params['data']['hard'] = $hard;

    $uri = "{$this->apiHost}/" . alphabetonly($this->entity) . '/remove';
    $result = callAPI($this->apiHost, $uri, $params);

    if ($result['status'] != 'SUCCESS') {
      HusLogger::error($result, "ResponseServiceFailed_".alphabetonly($this->entity), 'remove');
      HusAjax::setMessage($result['error']);
      return false;
    }

    return $result['result'];
  }
}
