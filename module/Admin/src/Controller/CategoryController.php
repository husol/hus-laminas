<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\Category;
use Application\Model\HusDao;
use Application\Model\User;
use Core\Dao\Dao;
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
    $parentCategories = $this->dao->find();

    return new ViewModel(['parentCategories' => $parentCategories]);
  }

  public function getCategoriesAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');
    $fparentCategory = $this->params()->fromPost('fparentCategory', 0);
    $fname = $this->params()->fromPost('fname', '');
    $fstatus = $this->params()->fromPost('fstatus', '');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => Category::PAGE_SIZE],
    ];

    if (in_array($sort['field'], ['name', 'updated_at'])) {
      $params['order'] = ["parent_id ASC", "{$sort['field']} {$sort['type']}"];
    }

    //For filter
    if ($fparentCategory > 0) {
      $params['conditions']['parent_id'] = $fparentCategory;
    }
    if (!empty($fname)) {
      $params['conditions']['flexible'] = [
        ['like' => ['name', "%{$fname}%"]]
      ];
    }
    if (!empty($fstatus)) {
      $params['conditions']['status'] = $fstatus;
    }

    $result = $this->dao->find($params);

    $categories = [];
    $count = 0;
    if (!empty($result)) {
      foreach ($result->data as $category) {
        $category->parentCategory = "";
        if ($category->parent_id > 0) {
          $myCategory = $this->dao->find([], $category->parent_id);
          $category->parentCategory = $myCategory->name;
        }

        $categories[] = $category;
      }
      $count = $result->count;
    }

    //Pagination
    $offset = new Offset($categories, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(Category::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['categories' => $categories, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('admin/category/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listCategory', $html);
    HusAjax::outData();
  }

  public function formAction()
  {
    $recordID = $this->params()->fromPost('idRecord', 0);

    $view = new ViewModel();

    $parentCategories = $this->dao->find();
    $view->setVariable('parentCategories', $parentCategories);

    if ($recordID > 0) {
      $myCategory = $this->dao->find([], intval($recordID));
      $view->setVariable('myCategory', $myCategory);
    }

    //Render html
    $view->setTemplate('admin/category/form');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function updateAction()
  {
    $recordID = $this->params()->fromPost('idRecord', 0);
    $parentCategory = $this->params()->fromPost('parentCategory', 0);
    $name = $this->params()->fromPost('name', '');
    $status = $this->params()->fromPost('status', '');

    $data = ['parent_id' => intval($parentCategory)];
    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid($name)) {
      HusAjax::setMessage('Please input Name.');
      HusAjax::outData(false);
    }
    $data['name'] = $name;

    if (!$validatorNotEmpty->isValid($status)) {
      HusAjax::setMessage('Invalid Status.');
      HusAjax::outData(false);
    }
    $data['status'] = $status;

    if ($recordID > 0) {
      $data['updated_by'] = $this->getLoggedUser('id');
    } else {
      $data['created_by'] = $this->getLoggedUser('id');
    }

    // Example for using DB transaction commit / rollback
    /*
    $trans = $this->dao->beginTransaction();

    try {
      $daoUser = User::initDao($trans);
      $result1 = $daoUser->save([
        "email" => "tester@husol.org",
        "full_name" => "Tester",
        "password" => "12345678",
        "role" => ROLE_STAFF,
        "created_by" => $this->getLoggedUser('id')
      ]);

      if ($result1 === false) {
        HusAjax::setMessage('Error create user.');
        HusAjax::outData(false);
      }

      $data["updated_b"] = $result1->id;
      $result = $this->dao->save($data, intval($recordID));
      if ($result === false) {
        HusAjax::setMessage('Error saving category.');
        HusAjax::outData(false);
      }

      $this->dao->commit();
    } catch (\Exception $e) {
      $this->dao->rollback();
    }
    */

    $result = $this->dao->save($data, intval($recordID));

    HusAjax::outData($result);
  }

  public function deleteAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);

    //Check if category is existed
    $myCategory = $this->dao->find([], intval($idRecord));

    if (empty($myCategory)) {
      HusAjax::setMessage("The category is not existed in system.");
      HusAjax::outData(false);
    }

    //Check if category has children
    $params['conditions'] = ['parent_id' => $myCategory->id];
    $categoryChildren = $this->dao->find($params);
    if (!empty($categoryChildren)) {
      HusAjax::setMessage("The category is already used as parent. Please delete its children firstly.");
      HusAjax::outData(false);
    }

    //Remove category
    $conditions = [
      'id' => $idRecord
    ];
    $this->dao->remove($conditions);

    HusAjax::outData($myCategory);
  }
}
