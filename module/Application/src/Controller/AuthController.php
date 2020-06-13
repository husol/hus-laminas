<?php
/**
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2016 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\User;
use Core\Hus\HusAjax;
use Core\Hus\HusEmail;
use Core\Hus\HusHelper;
use Laminas\Mvc\Controller\AbstractActionController;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\StringLength;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;
use Laminas\Json\Json;

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

    //Store loggedUser session
    $this->session->loggedUser = $myUser;

    HusAjax::outData($myUser);
  }

  public function registerAction()
  {
    $fullName = $this->params()->fromPost('fullName', '');
    $email = $this->params()->fromPost('email', '');
    $password = $this->params()->fromPost('password', '');
    $confirmPassword = $this->params()->fromPost('confirmPassword', '');
    $mobile = $this->params()->fromPost('mobile', '');
    $address = $this->params()->fromPost('address', '');
    $gRecaptchaResponse = $this->params()->fromPost('gRecaptchaResponse', '');

    $data = [
      'role' => 'CLIENT',
      'created_by' => 0
    ];
    //Validate
    $validatorEmpty = new ValidatorChain();
    $validatorEmpty->attach(new NotEmpty());

    if (!$validatorEmpty->isValid($fullName)) {
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

    $myUser = $this->dao->find($params);
    if (!empty($myUser)) {
      HusAjax::setMessage('Email đã được sử dụng trong hệ thống.');
      HusAjax::outData(false);
    }
    $data['email'] = $email;

    $validatorPassword = new ValidatorChain();
    $validatorPassword->attach(new StringLength(['min' => 8, 'max' => 30]));
    if (!$validatorPassword->isValid($password)) {
      HusAjax::setMessage('Độ dài Mật khẩu phải từ 8 đến 30 ký tự.');
      HusAjax::outData(false);
    }

    if ($password != $confirmPassword) {
      HusAjax::setMessage('Xác nhận mật khẩu không trùng khớp với Mật khẩu.');
      HusAjax::outData(false);
    }
    if (!empty($password)) {
      $data['password'] = hash('sha256', $password);;
    }

    //Verify gReCaptcha
    $clientInfo = HusHelper::getClientInfoFromRequest($this->getRequest());
    $response = Json::decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$this->husConfig['CAPTCHA']['secretKey']}&response={$gRecaptchaResponse}&remoteip={$clientInfo['ip']}"));

    if (!($response->success && $response->action == 'register' && $response->score >= 0.7)) {
      HusAjax::setMessage('Vui lòng xác thực Captcha thành công.');
      HusAjax::outData(false);
    }

    if (!empty($mobile)) {
      $data['mobile'] = $mobile;
    }

    if (!empty($address)) {
      $data['address'] = $address;
    }

    //Get token string for activate account
    $tokenStr = HusHelper::generateRandomString();

    $uri = $this->getRequest()->getUri();
    $baseUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

    //Send email with activate-account url
    $activateUrl = "{$baseUrl}/auth/activateAccount?email={$email}&token=$tokenStr";

    $content = "Welcome to Hus Laminas.\n";
    $content .= "You have registered your account. Please click the link below to activate it:\n";
    $content .= $activateUrl;

    $husEmail = new HusEmail($this->husConfig['SMTP_OPTIONS']);
    $husEmail->setFrom(['No-Reply' => 'noreply@husol.org']);
    $husEmail->setTo([$email]);
    $husEmail->setSubject('Activate your account on Hus Laminas');

    $result = $husEmail->send($content);

    if ($result['status']) {
      //Create new user
      $data['token'] = $tokenStr;
      $myUser = $this->dao->save($data);
      HusAjax::outData($myUser);
    }

    HusAjax::setMessage($result['message']);
    HusAjax::outData(false);
  }

  public function activateAccountAction()
  {
    $email = $this->params()->fromQuery('email');
    $token = $this->params()->fromQuery('token');

    $this->layout("layout/layout_auth");
    $result = ['status' => false, 'message' => ''];

    //Validate
    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($email)) {
      $result['message'] = 'Sai địa chỉ Email.';
      return new ViewModel(['result' => $result]);
    }

    //Check email and token
    $params = [
      'conditions' => [
        'email' => $email
      ],
      'isFetchRow' => 1
    ];
    $myUser = $this->dao->find($params);
    if (empty($myUser)) {
      $result['message'] = 'Email không tồn tại trong hệ thống.';
      return new ViewModel(['result' => $result]);
    }

    if ($myUser->token != $token) {
      $result['message'] = 'Hết hạn Token.';
      return new ViewModel(['result' => $result]);
    }

    //Change status
    $data = [
      'status' => 'ACTIVE',
      'token' => ''
    ];
    $myUser = $this->dao->save($data, $myUser->id);

    if (empty($myUser)) {
      $result['message'] = 'Error query of account activation.';
      return new ViewModel(['result' => $result]);
    }

    $result['status'] = true;
    return new ViewModel(['result' => $result]);
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
    $avatar = $this->params()->fromFiles('avatar');

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

  public function forgotPasswordFormAction()
  {
    //Render html
    $view = new ViewModel();
    $view->setTemplate('application/auth/forgot_password');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function forgotPasswordAction()
  {
    $email = $this->params()->fromPost('email');

    //Validate
    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($email)) {
      HusAjax::setMessage('Invalid email address.');
      HusAjax::outData(false);
    }

    //Check if email is existed in system
    $params = [
      'conditions' => [
        'email' => $email
      ],
      'isFetchRow' => 1
    ];
    $myUser = $this->dao->find($params);
    if (empty($myUser)) {
      HusAjax::setMessage("Email không tồn tại trong hệ thống.");
      HusAjax::outData(false);
    }

    //Get token string for resetting password
    $tokenStr = HusHelper::generateRandomString();

    $uri = $this->getRequest()->getUri();
    $baseUrl = sprintf('%s://%s', $uri->getScheme(), $uri->getHost());

    //Send email with reset-password url
    $resetUrl = "{$baseUrl}/auth/resetPassword?email={$email}&token=$tokenStr";

    $content = "Welcome to Hus Laminas.\n";
    $content .= "You have request to reset your password account. Please click the link below to reset it:\n";
    $content .= $resetUrl;

    $husEmail = new HusEmail($this->husConfig['SMTP_OPTIONS']);
    $husEmail->setFrom(['No-Reply' => 'noreply@husol.org']);
    $husEmail->setTo([$email]);
    $husEmail->setSubject('Reset Your Password on Hus Laminas');

    $result = $husEmail->send($content);

    if ($result['status']) {
      //Save tokenStr to DB
      $myUser = $this->dao->save(['token' => $tokenStr], $myUser->id);
      HusAjax::outData($myUser);
    }

    HusAjax::setMessage($result['message']);
    HusAjax::outData(false);
  }

  public function resetPasswordAction()
  {
    $email = $this->params()->fromQuery('email');
    $tokenStr = $this->params()->fromQuery('token');

    $this->layout("layout/layout_auth");

    return new ViewModel(['email' => $email, 'token' => $tokenStr]);
  }

  public function changePasswordAction()
  {
    $email = $this->params()->fromPost('email', '');
    $newPassword = $this->params()->fromPost('newPassword', '');
    $confirmPassword = $this->params()->fromPost('confirmPassword', '');
    $token = $this->params()->fromPost('token', '');

    //Validate
    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($email)) {
      HusAjax::setMessage('Sai địa chỉ Email.');
      HusAjax::outData(false);
    }

    $validatorPassword = new ValidatorChain();
    $validatorPassword->attach(new StringLength(['min' => 8, 'max' => 30]));

    if (!$validatorPassword->isValid($newPassword)) {
      HusAjax::setMessage('Mật khẩu phải có độ dài từ 8 đến 30 ký tự.');
      HusAjax::outData(false);
    }

    if ($newPassword != $confirmPassword) {
      HusAjax::setMessage('Nhập lại mật khẩu không trùng khớp với Mật khẩu mới.');
      HusAjax::outData(false);
    }

    //Check email and token
    $params = [
      'conditions' => [
        'email' => $email
      ],
      'isFetchRow' => 1
    ];
    $myUser = $this->dao->find($params);
    if (empty($myUser)) {
      HusAjax::setMessage('Email không tồn tại trong hệ thống.');
      HusAjax::outData(false);
    }

    if ($myUser->status == 'INACTIVE') {
      HusAjax::setMessage('Tài khoản chưa được kích hoạt.');
      HusAjax::outData(false);
    }

    if ($myUser->token != $token) {
      HusAjax::setMessage('Hết hạn Token.');
      HusAjax::outData(false);
    }

    //Change password
    $data = [
      'password' => hash('sha256', $newPassword),
      'token' => ''
    ];
    $myUser = $this->dao->save($data, $myUser->id);

    HusAjax::outData($myUser);
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
