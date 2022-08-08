<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\Category;
use Application\Model\Product;
use Core\Hus\HusAjax;
use Core\Paginator\Adapter\Offset;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

class ProductController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = Product::initDao();
  }

  public function indexAction()
  {
    $daoCategory = Category::initDao();

    $tmpCategories = $daoCategory->find();

    $categories = [];
    foreach ($tmpCategories as $category) {
      switch ($category->kind) {
        case 0:
          $categories[$category->kind]['name'] = 'iOS';
          break;
        case 1:
          $categories[$category->kind]['name'] = 'Android';
          break;
        default:
          $categories[$category->kind]['name'] = 'Phụ kiện điện thoại';
      }

      $categories[$category->kind]['items'][] = $category;
    }

    return new ViewModel(['categories' => $categories]);
  }

  public function detailAction()
  {
    $slug = $this->params('slug');
    list($id, $fname) = explode("_", $slug);

    $daoProduct = Product::initDao();

    $myProduct = $daoProduct->find(['conditions' => ['status' => 1]], intval($id));

    $params = [
      'limit' => 6,
      'order' => ['created_at DESC', 'name ASC'],
      'conditions' => [
        'category_id' => $myProduct->category_id
      ]
    ];
    $relatedProducts = $daoProduct->find($params);

    // Increase count_view
    $this->dao->save(['count_view' => $myProduct->count_view + 1], intval($myProduct->id));

    $myProduct->slugName = $myProduct->id . "_" . codau2khongdau($myProduct->name, true);

    return new ViewModel([
      'myProduct' => $myProduct,
      'relatedProducts' => $relatedProducts
    ]);
  }

  public function getProductsAction()
  {
    $page = $this->params()->fromPost('page', 1);
    $categoryKind = $this->params()->fromPost('categoryKind', -1);
    $categoryID = $this->params()->fromPost('categoryID', 0);
    $keyword = $this->params()->fromPost('keyword', '');
    $fprice = $this->params()->fromPost('fprice', 0);

    $daoProduct = Product::initDao();
    $pageSize = 6;

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => $pageSize],
      'conditions' => ['status' => 1]
    ];

    $keyword = str_replace("+", " ", $keyword);

    if (!empty($keyword)) {
      $params['conditions']['flexible'] = [
        ['like' => ['name', "%$keyword%"]]
      ];
    }

    if ($fprice > 0) {
      $params['conditions']['flexible'][] = ['expression' => ['price < ?', $fprice]];
    }

    $products = [];

    if ($categoryID > 0) {
      $params['conditions']['category_id'] = $categoryID;
    }

    $categoryIDs = [];
    if ($categoryKind > -1) {
      $daoCategory = Category::initDao();
      $categoriesByKind = $daoCategory->find(['conditions' => ['kind' => $categoryKind]]);
      foreach ($categoriesByKind as $category) {
        $categoryIDs[] = $category->id;
      }
    }

    if (!empty($categoryIDs)) {
      $params['conditions']['flexible'][] = [
        'in' => ['category_id', $categoryIDs]
      ];
    }

    $productData = $daoProduct->find($params);

    if (!empty($productData)) {
      $products = $productData->data;
    }

    //Pagination
    $offset = new Offset($products, $productData->count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage($pageSize);

    //Render html
    $view = new ViewModel(['products' => $products, 'paginator' => $paginator]);
    $view->setTemplate('application/product/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listProduct', $html);
    HusAjax::outData();
  }
}
