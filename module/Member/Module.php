<?php
namespace Member;

use Member\Form\MemberSiteSearchForm;
use Category\Model\CategoryMapper;

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
          'MemberSiteSearchForm' => function ($sm) {
            $dbAdapter = $sm->get('Zend\Db\Adapter\Adapter');
            $form = new MemberSiteSearchForm($dbAdapter);
            return $form;
          },
  			),
    	);
    }
}
