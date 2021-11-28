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

class IndexController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
  }

  public function indexAction()
  {
    $daoProduct = Product::initDao();

    $params = [
      'conditions' => [
        'is_feature' => 1,
        'status' => 1
      ]
    ];
    $featuredProducts = $daoProduct->find($params);

    $params = [
      'conditions' => ['status' => 1],
      'order' => ['count_view DESC'],
      'limit' => 8
    ];
    $mostViewedProducts = $daoProduct->find($params);

    return new ViewModel([
      'featuredProducts' => $featuredProducts,
      'mostViewedProducts' => $mostViewedProducts
    ]);
  }

  public function loginForm()
  {

  }
}
