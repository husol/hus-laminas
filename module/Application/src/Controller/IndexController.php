<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Laminas\View\Model\ViewModel;

class IndexController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
  }

  public function indexAction()
  {
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $captchaSiteKey = $configHus['CAPTCHA']['siteKey'];

    return new ViewModel(['captchaSiteKey' => $captchaSiteKey]);
  }
}
