<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

use Application\Model\Contact;
use Core\Hus\HusAjax;
use Core\Hus\HusHelper;
use Laminas\Json\Json;
use Laminas\Validator\EmailAddress;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class ContactController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = Contact::initDao();
  }

  public function indexAction()
  {
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $captchaSiteKey = $configHus['CAPTCHA']['siteKey'];

    return new ViewModel(['captchaSiteKey' => $captchaSiteKey]);
  }

  public function saveAction()
  {
    $fullName = $this->params()->fromPost('fullName', '');
    $email = $this->params()->fromPost('email', '');
    $mobile = $this->params()->fromPost('mobile', '');
    $title = $this->params()->fromPost('title', '');
    $content = $this->params()->fromPost('content', '');
    $gRecaptchaResponse = $this->params()->fromPost('gRecaptchaResponse', '');

    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid($fullName)) {
      HusAjax::setMessage('Vui lòng nhập Họ và tên.');
      HusAjax::outData(false);
    }

    if (!$validatorNotEmpty->isValid($mobile)) {
      HusAjax::setMessage('Vui lòng nhập Số điện thoại.');
      HusAjax::outData(false);
    }

    if (!$validatorNotEmpty->isValid($title)) {
      HusAjax::setMessage('Vui lòng nhập Tiêu đề.');
      HusAjax::outData(false);
    }

    if (!$validatorNotEmpty->isValid($content)) {
      HusAjax::setMessage('Vui lòng nhập Nội dung.');
      HusAjax::outData(false);
    }

    $validatorEmail = new ValidatorChain();
    $validatorEmail->attach(new EmailAddress());

    if (!$validatorEmail->isValid($email)) {
      HusAjax::setMessage('Email chưa đúng định dạng.');
      HusAjax::outData(false);
    }

    //Verify gReCaptcha
    $husConfig = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $clientInfo = HusHelper::getClientInfoFromRequest($this->getRequest());
    $response = Json::decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret={$husConfig['CAPTCHA']['secretKey']}&response={$gRecaptchaResponse}&remoteip={$clientInfo['ip']}"));

    if (!($response->success && $response->action == 'contact' && $response->score >= 0.7)) {
      HusAjax::setMessage('Vui lòng xác thực Captcha thành công.');
      HusAjax::outData(false);
    }

    $data = [
      'full_name' => $fullName,
      'email' => $email,
      'mobile' => $mobile,
      'title' => $title,
      'content' => $content
    ];

    $myContact = $this->dao->save($data);

    HusAjax::outData($myContact);
  }
}
