<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\Contact;
use Application\Model\Order;
use Application\Model\Product;
use Application\Model\Transaction;
use Application\Model\User;
use Core\Hus\HusAjax;
use Core\Paginator\Adapter\Offset;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

class TransactionController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = Transaction::initDao();
  }

  public function indexAction()
  {
    return new ViewModel();
  }

  public function getTransactionsAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');
    $fid = $this->params()->fromPost('fid', '');
    $fmobile = $this->params()->fromPost('fmobile', '');
    $fcontactInfo = $this->params()->fromPost('fcontactInfo', '');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => Transaction::PAGE_SIZE],
    ];

    if (in_array($sort['field'], ['name', 'updated_at'])) {
      $params['order'] = ["status ASC", "{$sort['field']} {$sort['type']}"];
    }

    //For filter
    if (!empty($fid)) {
      $params['conditions'] = ['id' => $fid];
    }

    $daoUser = User::initDao();

    $userIDs = [];
    if (!empty($fmobile)) {
      $userParams = ['conditions' => [
        'flexible' => [
          ['like' => ['mobile', "%{$fmobile}%"]]
        ]
      ]
      ];

      $users = $daoUser->find($userParams);

      $userIDs = [-1];
      foreach ($users as $user) {
        $userIDs[] = $user->id;
      }
    }

    if (!empty($userIDs)) {
      $params['conditions']['flexible'] = [
        ['in' => ['user_id', $userIDs]]
      ];
    }

    if (!empty($fcontactInfo)) {
      $params['conditions']['flexible'] = [
        ['like' => ['contact_info', "%{$fcontactInfo}%"]]
      ];
    }

    $result = $this->dao->find($params);

    $transactions = [];
    $count = 0;
    if (!empty($result)) {
      foreach ($result->data as $transaction) {
        $myUser = $daoUser->find([], $transaction->user_id);
        $transaction->userFullName = $myUser->full_name;
        $transaction->userMobile = $myUser->mobile;

        switch ($transaction->payment_type) {
          case 1:
            $transaction->paymentTypeName = 'One Pay';
            break;
          case 2:
            $transaction->paymentTypeName = 'Paypal';
            break;
          default:
            $transaction->paymentTypeName = 'Cash';
        }

        switch ($transaction->status) {
          case 1:// PAID
            $transaction->colorClass = 'bg-green';
            break;
          case 2:// DELIVERING
            $transaction->colorClass = 'bg-blue';
            break;
          case 3:// CANCELLED
            $transaction->colorClass = 'bg-red';
            break;
          case 4:// COMPLETED
            $transaction->colorClass = '';
            break;
          default:// PENDING
            $transaction->colorClass = 'bg-orange';
        }

        $transactions[] = $transaction;
      }

      $count = $result->count;
    }

    //Pagination
    $offset = new Offset($transactions, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(Contact::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['transactions' => $transactions, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('admin/transaction/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listTransaction', $html);
    HusAjax::outData();
  }

  public function viewTransactionAction()
  {
    $recordID = $this->params()->fromPost('recordID', 0);

    if ($recordID == 0) {
      HusAjax::setMessage('Invalid record ID.');
      HusAjax::outData(false);
    }

    $myTransaction = $this->dao->find([], $recordID);
    if (empty($myTransaction)) {
      HusAjax::setMessage('Giao dịch không tồn tại.');
      HusAjax::outData(false);
    }

    $daoOrder = Order::initDao();

    $result = $daoOrder->find(['conditions' => ['transaction_id' => $myTransaction->id]]);
    $orders = [];
    if (!empty($result)) {
      $daoProduct = Product::initDao();

      foreach ($result as $order) {
        $myProduct = $daoProduct->find([], $order->product_id);
        $order->productName = $myProduct->name;

        $orders[] = $order;
      }
    }

    //Render html
    $view = new ViewModel(['orders' => $orders]);
    $view->setTemplate('admin/transaction/view');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function updateStatusAction()
  {
    $recordID = $this->params()->fromPost('recordID', 0);
    $status = $this->params()->fromPost('status', 0);

    if ($recordID == 0) {
      HusAjax::setMessage('Invalid record ID.');
      HusAjax::outData(false);
    }

    // Validate if contact existed
    $myContact = $this->dao->find([], $recordID);
    if (empty($myContact)) {
      HusAjax::setMessage('Người liên hệ này không tồn tại.');
      HusAjax::outData(false);
    }

    $data = ['status' => intval($status)];

    $result = $this->dao->save($data, intval($myContact->id));

    HusAjax::outData($result);
  }
}
