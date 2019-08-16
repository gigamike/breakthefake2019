<?php
/**
 * Zend Framework (http://framework.zend.com/)
 *
 * @link      http://github.com/zendframework/ZendSkeletonApplication for the canonical source repository
 * @copyright Copyright (c) 2005-2014 Zend Technologies USA Inc. (http://www.zend.com)
 * @license   http://framework.zend.com/license/new-bsd New BSD License
 */

namespace Application\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
  public function getCategoryMapper()
  {
    $sm = $this->getServiceLocator();
    return $sm->get('CategoryMapper');
  }

  public function getSiteArticleMapper()
  {
    $sm = $this->getServiceLocator();
    return $sm->get('SiteArticleMapper');
  }

  public function indexAction()
  {
    $page = $this->params()->fromRoute('page') ? (int) $this->params()->fromRoute('page') : 1;
    $search_by = $this->params()->fromRoute('search_by') ? $this->params()->fromRoute('search_by') : '';

    $searchFilter = array();
    if (!empty($search_by)) {
      $searchFilter = (array) json_decode($search_by);
    }

    $order = array(
      'site_article.created_datetime DESC',
      'site_article.title',
    );
    $paginator = $this->getSiteArticleMapper()->getSiteArticles(true, $searchFilter, $order);
    $paginator->setCurrentPageNumber($page);
    $paginator->setItemCountPerPage(12);

    $searchFilterCategory = array();
    $orderCategory= array(
      'category',
    );
    $categories = $this->getCategoryMapper()->fetch(false, $searchFilterCategory, $orderCategory);

    return new ViewModel(array(
      'paginator' => $paginator,
      'search_by' => $search_by,
      'page' => $page,
      'categories' => $categories,
    ));
  }
}
