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
use Application\Model\Transaction;
use Application\Model\User;
use Laminas\View\Model\ViewModel;

class HomeController extends HusController
{
    public function indexAction()
    {
      $daoUser = User::initDao();
      $daoCategory = Category::initDao();
      $daoProduct = Product::initDao();
      $daoTransaction = Transaction::initDao();

      $params = [
        'columns' => [
          'expressions' => ['total_user' => 'COUNT(id)']
        ],
        'conditions' => ['status' => 1],
        'isFetchRow' => 1
      ];
      $resultUser = $daoUser->find($params);

      $params = [
        'columns' => [
          'expressions' => ['total_category' => 'COUNT(id)']
        ],
        'isFetchRow' => 1
      ];
      $resultCategory = $daoCategory->find($params);

      $params = [
        'columns' => [
          'expressions' => ['total_product' => 'COUNT(id)']
        ],
        'conditions' => ['status' => 1],
        'isFetchRow' => 1
      ];
      $resultProduct = $daoProduct->find($params);

      $params = [
        'conditions' => [
          'flexible' => [
            ['expression' => ['status <> ?', 2]]
          ]
        ],
      ];
      $resultTransaction = $daoTransaction->find($params);

      $totalPendingTransaction = 0;
      $totalDeliveringTransaction = 0;
      $totalCompletedTransaction = 0;
      if (!empty($resultTransaction)) {
        foreach ($resultTransaction as $transaction) {
          switch ($transaction->status) {
            case 0:
              $totalPendingTransaction++;
              break;
            case 1:
              $totalDeliveringTransaction++;
              break;
            case 3:
              $totalCompletedTransaction++;
              break;
          }
        }
      }

      return new ViewModel([
        'totalActiveUser' => $resultUser->total_user,
        'totalCategory' => $resultCategory->total_category,
        'totalActiveProduct' => $resultProduct->total_product,
        'totalPendingTransaction' => $totalPendingTransaction,
        'totalDeliveringTransaction' => $totalDeliveringTransaction,
        'totalCompletedTransaction' => $totalCompletedTransaction,
      ]);
    }
}
