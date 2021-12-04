<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\Category;
use Application\Model\Product;
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

  public function getCategoriesAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');
    $fkind = $this->params()->fromPost('fkind', -1);
    $fname = $this->params()->fromPost('fname', '');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => Category::PAGE_SIZE],
    ];

    if (in_array($sort['field'], ['name', 'updated_at'])) {
      $params['order'] = ["kind ASC", "{$sort['field']} {$sort['type']}"];
    }

    //For filter
    if ($fkind > -1) {
      $params['conditions']['kind'] = $fkind;
    }
    if (!empty($fname)) {
      $params['conditions']['flexible'] = [
        ['like' => ['name', "%{$fname}%"]]
      ];
    }

    $result = $this->dao->find($params);

    $categories = [];
    $count = 0;
    if (!empty($result)) {
      foreach ($result->data as $category) {
        switch ($category->kind) {
          case 1:
            $category->kindName = "Android";
            break;
          case 2:
            $category->kindName = "Phụ kiện điện thoại";
            break;
          default:
            $category->kindName = "iOS";
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
    $kind = $this->params()->fromPost('kind', -1);
    $name = $this->params()->fromPost('name', '');

    if ($kind == -1) {
      HusAjax::setMessage('Vui lòng chọn Lớp sản phẩm.');
      HusAjax::outData(false);
    }

    $data = ['kind' => intval($kind)];

    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid(trim($name))) {
      HusAjax::setMessage('Please input Name.');
      HusAjax::outData(false);
    }
    $data['name'] = trim($name);

    $result = $this->dao->save($data, intval($recordID));

    HusAjax::outData($result);
  }

  public function deleteAction()
  {
    $recordID = $this->params()->fromPost('idRecord', 0);

    // Check if category is existed
    $myCategory = $this->dao->find([], intval($recordID));

    if (empty($myCategory)) {
      HusAjax::setMessage("Loại sản phẩm này không tồn tại trong hệ thống.");
      HusAjax::outData(false);
    }

    // Check if category has children
    $daoProduct = Product::initDao();
    $products = $daoProduct->find(['category_id' => $myCategory->id]);
    if (!empty($products)) {
      HusAjax::setMessage("Loại sản phẩm này đang được sử dụng.");
      HusAjax::outData(false);
    }

    // Remove category
    $conditions = [
      'id' => $recordID
    ];
    $this->dao->remove($conditions);

    HusAjax::outData($myCategory);
  }
}
