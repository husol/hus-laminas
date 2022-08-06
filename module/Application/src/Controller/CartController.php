<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\Order;
use Application\Model\Product;
use Application\Model\Transaction;
use Core\Hus\HusAjax;
use Laminas\Config\Factory;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class CartController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = Transaction::initDao();
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

  public function confirmAction()
  {
    $formData = $this->params()->fromPost('data', []);

    $cartProducts = [];
    $totalQuantity = 0;
    $totalAmount = 0;

    if (count($formData) > 0) {
      $daoProduct = Product::initDao();
      foreach ($formData['cartProducts'] as $product) {
        $myProduct = $daoProduct->find([], $product['id']);

        $totalQuantity += $product['quantity'];
        $totalAmount += $product['quantity'] * $myProduct->price;

        $cartProducts[] = [
          'id' => $myProduct->id,
          'quantity' => $product['quantity'],
          'price' => $myProduct->price,
        ];
      }
    }

    if (empty($cartProducts)) {
      HusAjax::setMessage("Không có sản phẩm nào trong giỏ hàng.");
      HusAjax::outData(false);
    }

    $view = new ViewModel([
      'cartProductStr' => json_encode($cartProducts),
      'totalQuantity' => $totalQuantity,
      'totalAmount' => $totalAmount,
    ]);

    //Render html
    $view->setTemplate('application/cart/form');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function saveAction()
  {
    $cartProductJSON = $this->params()->fromPost('cartProductStr', '');
    $contactInfo = $this->params()->fromPost('contactInfo', '');
    $deliverPlace = $this->params()->fromPost('deliverPlace', '');
    $notes = $this->params()->fromPost('notes', '');

    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid($contactInfo)) {
      HusAjax::setMessage('Vui lòng nhập Người nhận hàng.');
      HusAjax::outData(false);
    }

    if (!$validatorNotEmpty->isValid($deliverPlace)) {
      HusAjax::setMessage('Vui lòng nhập Địa chỉ giao hàng.');
      HusAjax::outData(false);
    }

    $cartProducts = json_decode($cartProductJSON);

    if ($cartProductJSON == '' || !$validatorNotEmpty->isValid($cartProducts)) {
      HusAjax::setMessage('Không có sản phầm nào trong giỏ hàng.');
      HusAjax::outData(false);
    }

    $data = [
      'user_id' => $this->getLoggedUser('id'),
      'contact_info' => $contactInfo,
      'deliver_place' => $deliverPlace
    ];

    if (trim($notes) != '') {
      $data['notes'] = trim($notes);
    }

    $myTransaction = $this->dao->save($data);

    $totalAmount = 0;
    $daoOrder = Order::initDao();
    foreach ($cartProducts as $product) {
      $data = [
        'transaction_id' => $myTransaction->id,
        'product_id' => $product->id,
        'quantity' => $product->quantity,
        'amount' => $product->quantity * $product->price,
      ];

      $totalAmount += $data['amount'];

      $daoOrder->save($data);
    }

    $this->dao->save(['amount' => $totalAmount], intval($myTransaction->id));

    HusAjax::outData($myTransaction);
  }

  public function paymentAction()
  {
    $id = $this->params('id');

    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');

    $paypal = new \stdClass();
    $paypal->url = $configHus['PAYPAL']['url'];
    $paypal->id = $configHus['PAYPAL']['id'];
    $paypal->currency = $configHus['PAYPAL']['currency'];
    $paypal->returnURL = $this->getBaseUrl() . '/cart/payment/success';
    $paypal->cancelURL = $this->getBaseUrl() . '/cart/payment/cancel';

    $daoOrder = Order::initDao();
    $orders = $daoOrder->find(['conditions' => ['transaction_id' => $id]]);

    $items = [];

    $daoProduct = Product::initDao();

    foreach ($orders as $order) {
      $myProduct = $daoProduct->find([], $order->product_id);
      $order->productName = $myProduct->name;

      $items[] = $order;
    }

    return new ViewModel([
      "paypal" => $paypal,
      "items" => $items
    ]);
  }

  public function successAction()
  {
    return new ViewModel();
  }

  public function cancelAction()
  {
    return new ViewModel();
  }
}
