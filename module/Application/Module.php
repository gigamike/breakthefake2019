<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application;

use Zend\Mvc\ModuleRouteListener;
use Zend\Mvc\MvcEvent;
use Zend\Session\SessionManager;
use Zend\Session\Container;
use Zend\Http\Request as HttpRequest;
use Zend\Console\Request as ConsoleRequest;

class Module
{
  public function onBootstrap(MvcEvent $e)
  {
    date_default_timezone_set('Asia/Manila');
    ini_set('session.gc_maxlifetime', 86400);
    $eventManager        = $e->getApplication()->getEventManager();
    $moduleRouteListener = new ModuleRouteListener();
    $moduleRouteListener->attach($eventManager);

    $application = $e->getApplication();
    $sm = $application->getServiceManager();

    if ($e->getRequest() instanceof HttpRequest) {
      $application->getEventManager()->attach('dispatch', function($e) {
        $routeMatch = $e->getRouteMatch();
        $viewModel = $e->getViewModel();
        $viewModel->setVariable('route', $routeMatch->getMatchedRouteName());
        $viewModel->setVariable('controller', $routeMatch->getParam('controller'));
        $viewModel->setVariable('action', $routeMatch->getParam('action'));

        $controller = $e->getTarget();
        $action = $routeMatch->getParam('action');
        if (substr($routeMatch->getMatchedRouteName(), 0, 5) == 'admin') {
          $controller->layout('layout/admin-layout');
        }
        if (substr($routeMatch->getMatchedRouteName(), 0, 7) == 'chatbot') {
          $controller->layout('layout/chatbot-layout');
        }

        $application = $e->getApplication();
        $sm = $application->getServiceManager();

        $config = $sm->get('Config');

        $search_by = $routeMatch->getParam('search_by') ? $routeMatch->getParam('search_by') : '';
        $searchFilter = array();
        if (!empty($search_by)) {
          $searchFilter = (array) json_decode($search_by);
        }
        if(isset($searchFilter['keyword'])){
          $viewModel->setVariable('keyword', urldecode($searchFilter['keyword']));
        }
        if(isset($searchFilter['search_category_id'])){
          $viewModel->setVariable('search_category_id', urldecode($searchFilter['search_category_id']));
        }

        $orderCategory = array(
          'category',
        );
        $categoryMapper = $sm->get('CategoryMapper');
        $categories = $categoryMapper->fetch(false, null, $orderCategory);
        $viewModel->setVariable('optionCategories', $categories);

        $authService = $sm->get('auth_service');
        if ($authService->getIdentity()!=null) {
          $userMapper = $sm->get('UserMapper');
          $authService = $sm->get('auth_service');
          $user = $userMapper->getUser($authService->getIdentity()->id);
      		if($user){
            $viewModel->setVariable('sessionUser', $user);
      		}
        }
      }, -100);

      // $this->bootstrapSession($e);
      $this->initAcl($e);
      $e->getApplication()->getEventManager()->attach('route', array($this, 'checkAcl'));
    } elseif($e->getRequest() instanceof ConsoleRequest ) {
      // Console/Cron
    }
  }

  public function bootstrapSession($e)
  {
    $session = $e->getApplication()
                 ->getServiceManager()
                 ->get('Zend\Session\SessionManager');
    $session->start();

    $container = new Container('initialized');
    if (!isset($container->init)) {
      $serviceManager = $e->getApplication()->getServiceManager();
      $request        = $serviceManager->get('Request');

      $session->regenerateId(true);
      $container->init          = 1;
      $container->remoteAddr    = $request->getServer()->get('REMOTE_ADDR');
      $container->httpUserAgent = $request->getServer()->get('HTTP_USER_AGENT');

      $config = $serviceManager->get('Config');
      if (!isset($config['session'])) {
          return;
      }

      $sessionConfig = $config['session'];
      if (isset($sessionConfig['validators'])) {
        $chain   = $session->getValidatorChain();

        foreach ($sessionConfig['validators'] as $validator) {
          switch ($validator) {
            case 'Zend\Session\Validator\HttpUserAgent':
              $validator = new $validator($container->httpUserAgent);
              break;
            case 'Zend\Session\Validator\RemoteAddr':
              $validator  = new $validator($container->remoteAddr);
              break;
            default:
              $validator = new $validator();
          }

          $chain->attach('session.validate', array($validator, 'isValid'));
        }
      }
    }
  }

  public function initAcl(MvcEvent $e) {
    $acl = new \Zend\Permissions\Acl\Acl();
    $roles = include __DIR__ . '/config/module.acl.roles.php';
    $allResources = array();
    foreach ($roles as $role => $resources) {
      $role = new \Zend\Permissions\Acl\Role\GenericRole($role);
      $acl->addRole($role);
      $allResources = array_merge($resources, $allResources);
      foreach ($resources as $resource) {
        if(!$acl->hasResource($resource))
          $acl->addResource(new \Zend\Permissions\Acl\Resource\GenericResource($resource));
      }
      foreach ($allResources as $resource) {
          $acl->allow($role, $resource);
      }
      // print_r($allResources);
    }
    // var_dump($acl->isAllowed('guest','blog-comment-member'));

    //setting to view
    $e->getViewModel()->acl = $acl;
  }

  public function checkAcl(MvcEvent $e) {
    $application = $e->getApplication();
    $sm = $application->getServiceManager();
    $authService = $sm->get('auth_service');

    $route = $e->getRouteMatch()->getMatchedRouteName();
    //you set your role
    if($authService->hasIdentity()) {
      $userRole = $authService->getIdentity()->role;
    }else{
      $userRole = 'guest';
    }

    if (!$e->getViewModel()->acl->isAllowed($userRole, $route)) {
      $router   = $e->getRouter();
      $url      = $router->assemble(array(), array(
        'name' => 'login'
      ));
      $response = $e->getResponse();
      $response->getHeaders()->addHeaderLine('Location', $url);
      $response->setStatusCode(302);
      return $response;
    }
  }

  public function getConfig()
  {
    return include __DIR__ . '/config/module.config.php';
  }

  public function getAutoloaderConfig()
  {
    return array(
      'Zend\Loader\StandardAutoloader' => array(
        'namespaces' => array(
          __NAMESPACE__ => __DIR__ . '/src/' . __NAMESPACE__,
        ),
      ),
    );
  }

  public function getViewHelperConfig() {
    return array(
      'factories' => array(
        'getShortBody' => function($sm){
          return new \Application\View\Helper\GetShortBody($sm->getServiceLocator());
        },
      )
    );
  }
}
