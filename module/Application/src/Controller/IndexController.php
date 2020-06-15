<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application\Controller;

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
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $captchaSiteKey = $configHus['CAPTCHA']['siteKey'];

    /*
    //Upload to S3 usage
    $images = $this->params()->fromFiles('images');

    //Validate
    $validatorEmpty = new ValidatorChain();
    $validatorEmpty->attach(new NotEmpty());

    $validatorFile = new ValidatorChain();
    $validatorFile->attach(new UploadFile());
    $validatorFile->attach(new Size('10MB'));
    $validatorFile->attach(new IsImage());

    $code = 123456;
    $objType = 'MENU';
    $objID = 1;
    $pathDir = "{$code}/". strtolower($objType);
    foreach ($images as $image) {
      if (!$validatorFile->isValid($image)) {
        HusAjax::setMessage("{$image['name']} must be image and less than 10MB.");
        HusAjax::outData(false);
      }

      $imageInfo = getimagesize($image['tmp_name']);

      $file = new HusFile($image);
      $result = $file->uploadToS3('images', $pathDir, '', $objID);

      if ($result['error']) {
        HusAjax::setMessage("Error Upload File: " . $result['info']);
        HusAjax::outData(false);
      }

      //Use $result['path']
      $data = [
        'obj_id' => $objID,
        'obj_type' => $objType,
        'name' => $image['name'],
        'path' => $result['path'],
        'size' => $image['size'],
        'info' => Json::encode($imageInfo)
      ];
      $daoAsset = Asset::initDao();
      $myAsset = $daoAsset->save($data);
    }
    //End upload to S3 usage
    */

    return new ViewModel(['captchaSiteKey' => $captchaSiteKey]);
  }
}
