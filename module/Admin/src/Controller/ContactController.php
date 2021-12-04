<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Admin\Controller;

use Application\Model\Contact;
use Core\Hus\HusAjax;
use Core\Paginator\Adapter\Offset;
use Laminas\Paginator\Paginator;
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
    return new ViewModel();
  }

  public function getContactsAction()
  {
    $page = $this->params()->fromPost('page', 0);
    $sort = $this->params()->fromPost('sort');
    $fullName = $this->params()->fromPost('ffullName', '');
    $femail = $this->params()->fromPost('femail', '');
    $fmobile = $this->params()->fromPost('fmobile', '');

    $params = [
      'pagination' => ['page' => $page, 'pageSize' => Contact::PAGE_SIZE],
    ];

    if (in_array($sort['field'], ['name', 'updated_at'])) {
      $params['order'] = ["status ASC", "{$sort['field']} {$sort['type']}"];
    }

    //For filter
    if (!empty($fullName)) {
      $params['conditions']['flexible'] = [
        ['like' => ['full_name', "%{$fullName}%"]]
      ];
    }
    if (!empty($femail)) {
      $params['conditions']['flexible'] = [
        ['like' => ['email', "%{$femail}%"]]
      ];
    }
    if (!empty($fmobile)) {
      $params['conditions']['flexible'] = [
        ['like' => ['mobile', "%{$fmobile}%"]]
      ];
    }


    $result = $this->dao->find($params);

    $contacts = [];
    $count = 0;
    if (!empty($result)) {
      $contacts = $result->data;
      $count = $result->count;
    }

    //Pagination
    $offset = new Offset($contacts, $count);
    $paginator = new Paginator($offset);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(Contact::PAGE_SIZE);

    //Render html
    $view = new ViewModel(['contacts' => $contacts, 'sort' => $sort, 'paginator' => $paginator]);
    $view->setTemplate('admin/contact/list');
    $html = $this->renderer->render($view);

    HusAjax::setHtml('listContact', $html);
    HusAjax::outData();
  }

  public function updateStatusAction()
  {
    $recordID = $this->params()->fromPost('recordID', 0);
    $status = $this->params()->fromPost('status', 0);

    if ($recordID == 0) {
      HusAjax::setMessage('Invalid record ID.');
      HusAjax::outData(false);
    }

    // Validate if contact existed
    $myContact = $this->dao->find([], $recordID);
    if (empty($myContact)) {
      HusAjax::setMessage('Người liên hệ này không tồn tại.');
      HusAjax::outData(false);
    }

    $data = ['status' => intval($status)];

    $result = $this->dao->save($data, $myContact->id);

    HusAjax::outData($result);
  }
}
