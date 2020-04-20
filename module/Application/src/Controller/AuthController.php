<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Admin\Model\User;
use Core\Hus\HusAjax;
use Core\Hus\HusEmail;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class AuthController extends AbstractActionController
{
  private $husConfig;
  private $container;
  private $session;
  protected $renderer;
  private $dao;

  public function __construct($container)
  {
    $this->husConfig = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $this->container = $container;
    $this->session = $container->get('HusSessionContainer');
    $this->renderer = $container->get('Laminas\View\Renderer\PhpRenderer');
    $this->dao = User::initDao();
  }

  public function indexAction()
  {
    $this->layout("layout/layout_auth");
    return new ViewModel();
  }

  public function loginAction()
  {
    $formData = $this->params()->fromPost('data');

    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($formData['email'])) {
      HusAjax::setMessage('Email is invalid');
      HusAjax::outData(false);
    }

    $validatorPassword = new ValidatorChain();
    $validatorPassword->attach(new StringLength(['min' => 8, 'max' => 30]));

    if (!$validatorPassword->isValid($formData['password'])) {
      HusAjax::setMessage('Độ dài mật khẩu từ 8 đến 30 ký tự');
      HusAjax::outData(false);
    }

    //Start Login
    $email = $formData['email'];
    $password = hash('sha256', $formData['password']);

    $params = [
      'conditions' => [
        'email' => $email,
        'password' => $password
      ],
      'isFetchRow' => 1
    ];
    $myUser = $this->dao->find($params);

    if (empty($myUser)) {
      HusAjax::setMessage("Sai Email hoặc Mật khẩu.");
      HusAjax::outData(false);
    }

    if ($myUser->status == 'INACTIVE') {
      HusAjax::setMessage("Tài khoản của bạn chưa được kích hoạt.");
      HusAjax::outData(false);
    }

    $myUser= $this->dao->save(['last_login' => date('Y-m-d H:i:s')], $myUser->id);
    unset($myUser->password);

    //Store loggedUser session with Hotel Info
    $this->session->loggedUser = $myUser;

    HusAjax::outData();
  }

  public function myAccountFormAction()
  {
    $idRecord = $this->session->loggedUser->id;

    $view = new ViewModel();
    $myAccount = $this->dao->find([], $idRecord);

    $view->setVariable('myAccount', $myAccount);

    //Render html
    $view->setTemplate('application/auth/account_form');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function updateMyAccountAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);
    $fullName = $this->params()->fromPost('fullName', '');
    $currentPassword = $this->params()->fromPost('currentPassword', '');
    $password = $this->params()->fromPost('password', '');
    $confirmPassword = $this->params()->fromPost('confirmPassword', '');
    $mobile = $this->params()->fromPost('mobile', '');
    $address = $this->params()->fromPost('address', '');

    $data = [];
    //Validate
    $validatorEmpty = new ValidatorChain();
    $validatorEmpty->attach(new NotEmpty());

    if (!$validatorEmpty->isValid($fullName)) {
      HusAjax::setMessage('Vui lòng nhập Họ và tên.');
      HusAjax::outData(false);
    }
    $data['full_name'] = $fullName;

    if (!$validatorEmpty->isValid($currentPassword)) {
      HusAjax::setMessage('Vui lòng nhập mật khẩu hiện tại.');
      HusAjax::outData(false);
    }

    $validatorPassword = new ValidatorChain();
    $validatorPassword->attach(new StringLength(['min' => 8, 'max' => 30]));
    if (!empty($password) && !$validatorPassword->isValid($password)) {
      HusAjax::setMessage('Độ dài mật khẩu phải từ 8 đến 30 ký tự.');
      HusAjax::outData(false);
    }

    if ($password != $confirmPassword) {
      HusAjax::setMessage('Xác nhận mật khẩu không trùng khớp với Mật khẩu.');
      HusAjax::outData(false);
    }
    if (!empty($password)) {
      $data['password'] = hash('sha256', $password);;
    }

    if (!empty($mobile)) {
      $data['mobile'] = $mobile;
    }

    if (!empty($address)) {
      $data['address'] = $address;
    }

    //Validate if current Password is correct
    $myUser = $this->dao->find([], $idRecord);

    $currentPassword = hash('sha256', $currentPassword);
    if ($currentPassword != $myUser->password) {
      HusAjax::setMessage('Mật khẩu hiện tại không đúng.');
      HusAjax::outData(false);
    }

    $myUser = $this->dao->save($data, $idRecord);

    //Update loggedUser session
    unset($myUser->password);
    $this->session->loggedUser = $myUser;

    HusAjax::outData();
  }

  public function forgotpasswordformAction()
  {
    //Render html
    $view = new ViewModel();
    $view->setTemplate('application/auth/forgotpassword');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function forgotpasswordAction()
  {
    $formData = $this->params()->fromPost('data');

    //Validate
    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($formData['email'])) {
      HusAjax::setMessage('Invalid email address.');
      HusAjax::outData(false);
    }

    //Get token string for resetting password
    $result = $this->repo->forgotpasswordAPI($formData['email']);
    $tokenStr = $result->secretKey;

    //Send email with reset-password url
    $resetUrl = "{$this->getBaseUrl()}/auth/resetpassword?email={$formData['email']}&token=$tokenStr";

    $content = "Welcome to Hus Laminas.\n";
    $content .= "You have request to reset your password account. Please click the link below to reset it:\n";
    $content .= $resetUrl;

    $vlsEmail = new HusEmail();
    $vlsEmail->setFrom(['No-Reply' => 'noreply@husol.org']);
    $vlsEmail->setTo([
      $formData['email']
    ]);
    $vlsEmail->setSubject('Reset Your Password on Hus Laminas');

    $result = $vlsEmail->send($content);

    if ($result['status']) {
      HusAjax::outData(true);
    }

    HusAjax::setMessage($result['message']);
    HusAjax::outData(false);
  }

  public function resetpasswordAction()
  {
    $email = $this->params()->fromQuery('email');
    $tokenStr = $this->params()->fromQuery('token');

    $this->layout()->setTemplate('layout/layout_auth');
    $view = new ViewModel();
    $view->setVariables(['email' => $email, 'token' => $tokenStr]);

    return $view;
  }

  public function changepasswordAction()
  {
    $formData = $this->params()->fromPost('data');

    //Validate
    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($formData['email'])) {
      HusAjax::setMessage('Invalid email address.');
      HusAjax::outData(false);
    }

    $validatorPassword = new ValidatorChain();
    $validatorPassword->attach(new StringLength(['min' => 8, 'max' => 30]));

    if (!$validatorPassword->isValid($formData['newPassword'])) {
      HusAjax::setMessage('Password length must be between 8 and 30 characters');
      HusAjax::outData(false);
    }

    if (!$validatorPassword->isValid($formData['confirmPassword'])) {
      HusAjax::setMessage('Confirm Password length must be between 8 and 30 characters');
      HusAjax::outData(false);
    }

    if ($formData['newPassword'] != $formData['confirmPassword']) {
      HusAjax::setMessage('Confirm Password must be the same as New Password');
      HusAjax::outData(false);
    }

    //Change password
    $result = $this->repo->resetpasswordAPI($formData['email'], $formData['newPassword'], $formData['token']);
    if ($result === false) {
      HusAjax::outData(false);
    }

    HusAjax::outData(true);
  }

  public function logoutAction()
  {
    unset($this->session->loggedUser);
    $this->redirect()->toRoute('login');
  }

  public function error403Action()
  {
    $this->layout("error/403");
    return new ViewModel();
  }
}
