<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\Asset;
use Application\Model\Category;
use Application\Model\Product;
use Core\Hus\HusAjax;
use Core\Hus\HusFile;
use Core\Paginator\Adapter\Offset;
use Core\Vls\VlsAjax;
use Core\Vls\VlsFile;
use Laminas\Json\Json;
use Laminas\Paginator\Paginator;
use Laminas\Validator\File\IsImage;
use Laminas\Validator\File\Size;
use Laminas\Validator\File\UploadFile;
use Laminas\Validator\NotEmpty;
use Laminas\Validator\ValidatorChain;
use Laminas\View\Model\ViewModel;

class ProductController extends HusController
{
  public function __construct($container)
  {
    parent::__construct($container);
    $this->dao = Product::initDao();
  }

  public function indexAction()
  {
    $daoCategory = Category::initDao();
    $categories = $daoCategory->find();

    return new ViewModel(['categories' => $categories]);
  }

  public function getProductsAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');
    $fcategory = $this->params()->fromPost('fcategory', 0);
    $fbranch = $this->params()->fromPost('fbranch', '');
    $fname = $this->params()->fromPost('fname', '');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => Product::PAGE_SIZE],
    ];

    if (in_array($sort['field'], ['name', 'updated_at'])) {
      $params['order'] = ["id DESC", "{$sort['field']} {$sort['type']}"];
    }

    //For filter
    if ($fcategory > 0) {
      $params['conditions']['category_id'] = $fcategory;
    }
    if (!empty($fbranch)) {
      $params['conditions']['flexible'] = [
        ['like' => ['branch', "%{$fbranch}%"]]
      ];
    }
    if (!empty($fname)) {
      $params['conditions']['flexible'] = [
        ['like' => ['name', "%{$fname}%"]]
      ];
    }

    $result = $this->dao->find($params);

    $products = [];
    $count = 0;
    if (!empty($result)) {
      $daoCategory = Category::initDao();
      foreach ($result->data as $product) {
        $myCategory = $daoCategory->find([], $product->category_id);
        $product->categoryName = $myCategory->name;

        $products[] = $product;
      }
      $count = $result->count;
    }

    //Pagination
    $offset = new Offset($products, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(Product::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['products' => $products, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('admin/product/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listProduct', $html);
    HusAjax::outData();
  }

  public function formAction()
  {
    $idRecord = $this->params()->fromPost('idRecord', 0);

    $view = new ViewModel();

    $daoCategory = Category::initDao();
    $categories = $daoCategory->find();

    $view->setVariable('categories', $categories);

    if ($idRecord > 0) {
      $myProduct = $this->dao->find([], intval($idRecord));
      $view->setVariable('myProduct', $myProduct);
    }

    //Render html
    $view->setTemplate('admin/product/form');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('commonDialog', $html);
    HusAjax::outData();
  }

  public function updateAction()
  {
    $recordID = $this->params()->fromPost('recordID', 0);
    $category = $this->params()->fromPost('category', 0);
    $branch = $this->params()->fromPost('branch', '');
    $name = $this->params()->fromPost('name', '');
    $price = $this->params()->fromPost('price', 0);
    $shortDescription = $this->params()->fromPost('shortDescription', '');
    $description = $this->params()->fromPost('description', '');
    $image = $this->params()->fromFiles('image');
    $image2 = $this->params()->fromFiles('image2');
    $image3 = $this->params()->fromFiles('image3');
    $isFeature = $this->params()->fromPost('isFeature', '');
    $status = $this->params()->fromPost('status', 0);

    if ($category == 0) {
      HusAjax::setMessage('Vui lòng chọn Loại sản phẩm.');
      HusAjax::outData(false);
    }

    $data = [
      'category_id' => intval($category),
      'is_feature' => $isFeature == 'on' ? 1 : 0,
      'status' => $status
    ];

    //Validate
    $validatorNotEmpty = new ValidatorChain();
    $validatorNotEmpty->attach(new NotEmpty());

    if (!$validatorNotEmpty->isValid(trim($name))) {
      HusAjax::setMessage('Vui lòng nhập Tên sản phẩm.');
      HusAjax::outData(false);
    }
    $data['name'] = trim($name);

    if (!$validatorNotEmpty->isValid(trim($branch))) {
      HusAjax::setMessage('Vui lòng nhập Hãng.');
      HusAjax::outData(false);
    }
    $data['branch'] = trim($branch);

    if (!empty($price)) {
      $data['price'] = convertAnyStringToNumber($price);
    }

    if (!empty(trim($shortDescription))) {
      $data['short_description'] = trim($shortDescription);
    }
    if (!empty(trim($description))) {
      $data['description'] = trim($description);
    }

    $myProduct = $this->dao->save($data, intval($recordID));

    $validatorFile = new ValidatorChain();
    $validatorFile->attach(new UploadFile());
    $validatorFile->attach(new Size('5MB'));
    $validatorFile->attach(new IsImage());

    if ($image['error'] || $image2['error'] || $image3['error']) {
      HusAjax::setMessage("Upload hình không thành công. Vui lòng chọn lại hình.");
      HusAjax::outData(false);
    }

    //Upload image
    if (!$validatorFile->isValid($image)) {
      HusAjax::setMessage("{$image['name']} must be image and less than 5MB.");
      HusAjax::outData(false);
    }

    // Delete old image if any
    if (!empty($myProduct->image)) {
      $publicDir = ROOT_DIR.DS.'public';
      @unlink($publicDir.DS.$myProduct->image);
    }

    $objID = $myProduct->id;
    $file = new HusFile($image);
    $result = $file->upload('products', $objID);

    if (!$result['status']) {
      HusAjax::setMessage("Error Upload File: " . $result['message']);
      HusAjax::outData(false);
    }

    $data = ['image' => $result['pathUrl']];

    //Upload image2
    if (!$validatorFile->isValid($image2)) {
      HusAjax::setMessage("{$image2['name']} must be image and less than 5MB.");
      HusAjax::outData(false);
    }

    // Delete old image2 if any
    if (!empty($myProduct->image2)) {
      $publicDir = ROOT_DIR.DS.'public';
      @unlink($publicDir.DS.$myProduct->image2);
    }

    $objID = $myProduct->id;
    $file = new HusFile($image2);

    $result = $file->upload('products', $objID);

    if (!$result['status']) {
      HusAjax::setMessage("Error Upload File: " . $result['message']);
      HusAjax::outData(false);
    }

    $data['image2'] = $result['pathUrl'];

    //Upload image3
    if (!$validatorFile->isValid($image3)) {
      HusAjax::setMessage("{$image3['name']} must be image and less than 5MB.");
      HusAjax::outData(false);
    }

    // Delete old image3 if any
    if (!empty($myProduct->image3)) {
      $publicDir = ROOT_DIR.DS.'public';
      @unlink($publicDir.DS.$myProduct->image3);
    }

    $objID = $myProduct->id;
    $file = new HusFile($image3);
    $result = $file->upload('products', $objID);

    if (!$result['status']) {
      HusAjax::setMessage("Error Upload File: " . $result['message']);
      HusAjax::outData(false);
    }

    $data['image3'] = $result['pathUrl'];

    $myProduct = $this->dao->save($data, $myProduct->id);

    HusAjax::outData($myProduct);
  }

  public function deleteAction()
  {
    $recordID = $this->params()->fromPost('idRecord', 0);

    //Check if category is existed
    $myCategory = $this->dao->find([], intval($recordID));

    if (empty($myCategory)) {
      HusAjax::setMessage("Loại sản phẩm này không tồn tại trong hệ thống.");
      HusAjax::outData(false);
    }

    //Remove category
    $conditions = [
      'id' => $recordID
    ];
    $this->dao->remove($conditions);

    HusAjax::outData($myCategory);
  }
}
