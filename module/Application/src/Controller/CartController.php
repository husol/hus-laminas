<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\Product;
use Core\Hus\HusAjax;
use Core\Hus\HusFile;
use Laminas\Json\Json;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class CartController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
  }

  public function indexAction()
  {
    $daoProduct = Product::initDao();

    $params = [
      'limit' => 3,
      'order' => ['created_at DESC', 'name ASC'],
      'conditions' => [
        'is_feature' => 1
      ]
    ];
    $relatedProducts = $daoProduct->find($params);

    return new ViewModel(['relatedProducts' => $relatedProducts]);
  }

  public function getListCartAction()
  {
    $sort = $this->params()->fromPost('sort', "");
    $cartProducts = $this->params()->fromPost('cart_products', []);

    $daoProduct = Product::initDao();

    $products = [];
    if (!empty($cartProducts)) {
      foreach ($cartProducts as $product) {
        $myProduct = $daoProduct->find(['status' => 1], $product['id']);

        $myProduct->quantity = $product['quantity'];
        $myProduct->amount = $myProduct->price * $myProduct->quantity;

        $products[] = $myProduct;
      }
    }

    //Render html
    $view = new ViewModel(['products' => $products]);
    $view->setTemplate('application/cart/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listCart', $html);
    HusAjax::outData();
  }
}
