<?php

/**
 * @see       https://github.com/laminas/laminas-mvc-skeleton for the canonical source repository
 * @copyright https://github.com/laminas/laminas-mvc-skeleton/blob/master/COPYRIGHT.md
 * @license   https://github.com/laminas/laminas-mvc-skeleton/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace Application;

use Core\Authentication\Auth;
use Core\Hus\HusTranslator;
use Core\Hus\HusAjax;
use Laminas\Mvc\MvcEvent;
use Laminas\Session\SessionManager;

class Module
{
  public function getConfig() : array
  {
      return include __DIR__ . '/../config/module.config.php';
  }

  public function onBootstrap(MvcEvent $event)
  {
    $application = $event->getApplication();
    $serviceManager = $application->getServiceManager();
    $eventManager = $application->getEventManager();

    // The following line instantiates the SessionManager and automatically
    // makes the SessionManager the 'default' one.
    $sessionManager = $serviceManager->get(SessionManager::class);
    $this->forgetInvalidSession($sessionManager);

    $eventManager->attach(MvcEvent::EVENT_DISPATCH_ERROR, [$this, 'onDispatchError'], 100);
    $eventManager->attach(MvcEvent::EVENT_DISPATCH, [$this, 'onDispatch'], 200);
  }

  public function onDispatchError(MvcEvent $e)
  {
    $viewModel = $e->getViewModel();
    $viewModel->setTemplate('layout/layout_error');
  }

  public function onDispatch(MvcEvent $e)
  {
    $app = $e->getApplication();
    $sm = $app->getServiceManager();
    $sessionContainer = $sm->get('HusSessionContainer');

    $routeMatch = $sm->get('Application')->getMvcEvent()->getRouteMatch();
    $routeParams = $routeMatch->getParams();

    $viewModel = $app->getMvcEvent()->getViewModel();
    $versionHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/version.php');
    $viewModel->appVersion = $versionHus['APP_VERSION'];
    $viewModel->dateVersion = $versionHus['DATE_VERSION'];
    $configHus = \Laminas\Config\Factory::fromFile(ROOT_DIR . '/module/Application/config/config.php');
    $viewModel->isLive = $configHus['IS_LIVE'];

    $moduleName = substr($routeParams['controller'], 0, strpos($routeParams['controller'], '\\'));

    $translator = HusTranslator::getInstance($moduleName);
    $viewModel->translator = $translator;

    if ($routeParams['controller'] != 'Application\Controller\IndexController') {
      $request = new \Laminas\Http\PhpEnvironment\Request();
      $httpXRequestWith = $request->getServer('HTTP_X_REQUESTED_WITH');

      //Check Authentication
      if (!isset($sessionContainer->loggedUser)) {
        if (!empty($httpXRequestWith) && $httpXRequestWith == 'XMLHttpRequest') {
          HusAjax::outData('expired_session');
        } else {
          header('Location: /');
          exit;
        }
      }

      //Check User's role
      $roles = [$sessionContainer->loggedUser->info->role];

      $auth = Auth::getInstance();
      if (!$auth->isAllowed($roles, $routeParams['controller'], $routeParams['action'])) {
        if (!empty($httpXRequestWith) && $httpXRequestWith == 'XMLHttpRequest') {
          HusAjax::setMessage('Permission denied');
          HusAjax::outData(false);
        } else {
          header('Location: /error/403');
          exit;
        }
      }
    }

    if (isset($sessionContainer->loggedUser)) {
      //Set loggedUserInfo to layout
      $viewModel->loggedUserInfo = $sessionContainer->loggedUser->info;
      $viewModel->loggedUserHotel = $sessionContainer->loggedUser->hotel;
      $currDateObj = new \DateTime("now", new \DateTimeZone($viewModel->loggedUserHotel->timezone));
      $viewModel->systemDateTime = $currDateObj->format('Y-m-d H:i:s');
    }
  }

  protected function forgetInvalidSession($sessionManager)
  {
    try {
      $sessionManager->start();
      return;
    } catch (\Exception $e) {
    }
    /**
     * Session validation failed: toast it and carry on.
     */
    // @codeCoverageIgnoreStart
    session_unset();
    // @codeCoverageIgnoreEnd
  }
}
