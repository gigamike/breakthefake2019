<?php
namespace Site;

use Site\Model\SiteMapper;
use Site\Model\SiteCategoryMapper;
use Site\Model\SiteArticleMapper;
use Site\Form\YellowPagesSearchForm;

class Module
{
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

    public function getServiceConfig()
    {
        return array(
          'factories' => array(
            'SiteMapper' => function ($sm) {
              $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
              $mapper = new SiteMapper($dbAdapter);
              return $mapper;
            },
            'SiteCategoryMapper' => function ($sm) {
              $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
              $mapper = new SiteCategoryMapper($dbAdapter);
              return $mapper;
            },
            'SiteArticleMapper' => function ($sm) {
              $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
              $mapper = new SiteArticleMapper($dbAdapter);
              return $mapper;
            },
            'YellowPagesSearchForm' => function ($sm) {
              $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
              $form = new YellowPagesSearchForm($dbAdapter);
              return $form;
            },
          ),
        );
    }
}
