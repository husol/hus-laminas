<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\User;
use Core\Hus\HusAjax;
use Core\Paginator\Adapter\Offset;
use Laminas\Paginator\Paginator;
use Laminas\View\Model\ViewModel;

class UserController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = User::initDao($this->container);
  }

  public function indexAction()
  {
    return new ViewModel();
  }

  public function getUsersAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => User::PAGE_SIZE]
    ];

    $result = $this->dao->find($params);

    $users = $result->data;
    $count = $result->count;

    //Pagination
    $offset = new Offset($users, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(User::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['users' => $users, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('application/user/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listUser', $html);
    HusAjax::outData();
  }
}
