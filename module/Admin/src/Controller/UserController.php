<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\User;
use Core\Hus\HusAjax;
use Core\Paginator\Adapter\Offset;
use Laminas\Paginator\Paginator;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class UserController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = User::initDao();
  }

  public function indexAction()
  {
    return new ViewModel();
  }

  public function getUsersAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');
    $ffullName = $this->params()->fromPost('ffullName', '');
    $fstatus = $this->params()->fromPost('fstatus', '');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => User::PAGE_SIZE]
    ];

    if (in_array($sort['field'], ['fullName', 'updatedAt'])) {
      $conditions['order'] = ["{$sort['field']} {$sort['type']}"];
    }

    //For filter
    if (!empty($ffullName)) {
      $params['conditions']['flexible'] = [
        ['like' => ['full_name', "%{$ffullName}%"]]
      ];
    }

    if ($fstatus != '') {
      $params['conditions']['status'] = $fstatus;
    }

    $result = $this->dao->find($params);

    $count = 0;
    $users = [];
    if (!empty($result)) {
      $count = $result->count;
      $users = $result->data;
    }

    //Pagination
    $offset = new Offset($users, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(User::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['users' => $users, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('admin/user/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listUser', $html);
    HusAjax::outData();
  }

  public function formAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);

    $view = new ViewModel();
    if ($idRecord > 0) {
      $myUser = $this->dao->find([], intval($idRecord));
      $view->setVariable('myUser', $myUser);
    }

    //Render html
    $view->setTemplate('admin/user/form');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function updateAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);
    $fullName = $this->params()->fromPost('fullName', '');
    $email = $this->params()->fromPost('email', '');
    $password = $this->params()->fromPost('password', '');
    $confirmPassword = $this->params()->fromPost('confirmPassword', '');
    $role = $this->params()->fromPost('role', '');
    $mobile = $this->params()->fromPost('mobile', '');
    $address = $this->params()->fromPost('address', '');
    $status = $this->params()->fromPost('status', '');

    $data = [];
    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid($fullName)) {
      HusAjax::setMessage('Vui lòng nhập Họ và tên.');
      HusAjax::outData(false);
    }
    $data['full_name'] = $fullName;

    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());
    if (!$validatorEmail->isValid($email)) {
      HusAjax::setMessage('Sai định dạng Email.');
      HusAjax::outData(false);
    }

    $params = [
      'conditions' => ['email' => $email],
      'isFetchRow' => 1
    ];
    if (intval($idRecord) > 0) {
      $params['conditions']['flexible'] = [['expression' => ['id <> ?', $idRecord]]];
    }

    $myUser = $this->dao->find($params);
    if (!empty($myUser)) {
      HusAjax::setMessage('Email đã được sử dụng trong hệ thống.');
      HusAjax::outData(false);
    }
    $data['email'] = $email;

    if ($idRecord == 0 && !$validatorNotEmpty->isValid($password)) {
      HusAjax::setMessage('Độ dài Mật khẩu phải từ 8 đến 30 ký tự.');
      HusAjax::outData(false);
    }

    $validatorPassword = new ValidatorChain();
    $validatorPassword->attach(new StringLength(['min' => 8, 'max' => 30]));
    if (!empty($password) && !$validatorPassword->isValid($password)) {
      HusAjax::setMessage('Độ dài mật khẩu phải từ 8 đến 30 ký tự.');
      HusAjax::outData(false);
    }

    if ($password != $confirmPassword) {
      HusAjax::setMessage('Nhập lại mật khẩu không trùng khớp.');
      HusAjax::outData(false);
    }

    if (!empty($password)) {
      $data['password'] = hash('sha256', $password);
    }

    if (!$validatorNotEmpty->isValid($role)) {
      HusAjax::setMessage('Sai Vai trò.');
      HusAjax::outData(false);
    }
    $data['role'] = $role;

    if (!empty($mobile)) {
      $data['mobile'] = $mobile;
    }
    if (!empty($address)) {
      $data['address'] = $address;
    }

    if (!$validatorNotEmpty->isValid($status)) {
      HusAjax::setMessage('Sai Trạng thái.');
      HusAjax::outData(false);
    }
    $data['status'] = $status;

    if (intval($idRecord)) {
      $data['updated_by'] = $this->getLoggedUser('id');
    } else {
      $data['created_by'] = $this->getLoggedUser('id');
    }

    $result = $this->dao->save($data, intval($idRecord));

    HusAjax::outData($result);
  }

  public function deleteAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);

    //Check if user is existed
    $myUser = $this->dao->find([], intval($idRecord));

    if (empty($myUser)) {
      HusAjax::setMessage("The user is not existed in system");
      HusAjax::outData(false);
    }

    //Remove user
    $conditions = [
      'id' => $idRecord
    ];
    $this->dao->remove($conditions);

    HusAjax::outData($myUser);
  }
}
