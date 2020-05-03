<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\Category;
use Core\Hus\HusAjax;
use Core\Paginator\Adapter\Offset;
use Laminas\Paginator\Paginator;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class CategoryController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = Category::initDao();
  }

  public function indexAction()
  {
    return new ViewModel();
  }

  public function getCategorysAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => Category::PAGE_SIZE]
    ];

    $result = $this->dao->find($params);

    $users = $result->data;
    $count = $result->count;

    //Pagination
    $offset = new Offset($users, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(Category::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['users' => $users, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('admin/user/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listCategory', $html);
    HusAjax::outData();
  }

  public function formAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);

    $view = new ViewModel();
    if ($idRecord > 0) {
      $myCategory = $this->dao->find([], intval($idRecord));
      $view->setVariable('myCategory', $myCategory);
    }

    //Render html
    $view->setTemplate('admin/user/form');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function updateAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);
    $name = $this->params()->fromPost('name', '');
    $image = $this->params()->fromPost('image', '');
    $status = $this->params()->fromPost('status', '');

    $data = [
      'type' => 'add',
      'userCode' => VlsHelper::decToHex($this->getLoggedUserInfo('id')),
      'idRvc' => $this->getLoggedUserInfo('rvcId')
    ];
    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid($name)) {
      HusAjax::setMessage('Please input Full Name.');
      HusAjax::outData(false);
    }
    $data['name'] = $name;

    //Save image
    if (!empty($image)) {

    }

    if (!$validatorNotEmpty->isValid($status)) {
      HusAjax::setMessage('Invalid Status.');
      HusAjax::outData(false);
    }
    $data['status'] = $status;

    if ($idRecord > 0) {
      $data['type'] = 'edit';
      $data['id'] = intval($idRecord);
    }

    $result = $this->repo->saveArea($data);

    HusAjax::outData($result);
  }

  public function deleteAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);

    //Remove my area
    $conditions = [
      'userCode' => VlsHelper::decToHex($this->getLoggedUserInfo('id')),
      'idRvc' => $this->getLoggedUserInfo('rvcId'),
      'id' => $idRecord
    ];
    $myArea = $this->repo->removeArea($conditions);
    HusAjax::outData([
      'name' => $myArea->name
    ]);
  }
}
