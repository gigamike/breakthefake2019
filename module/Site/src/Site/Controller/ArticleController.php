<?php

namespace Site\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class ArticleController extends AbstractActionController
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
      'searchFilter' => $searchFilter,
    ));
  }

  public function searchAction()
	{
		$request = $this->getRequest();
		if ($request->isPost()) {
			$formdata = (array) $request->getPost();
			$search_data = array();
			foreach ($formdata as $key => $value) {
				if ($key != 'submit') {
					if (!empty($value)) {
						$search_data[$key] = $value;
					}
				}
			}

			if (!empty($search_data)) {
				$search_by = json_encode($search_data);
				return $this->redirect()->toRoute('articles', array('search_by' => $search_by));
			}else{
				return $this->redirect()->toRoute('articles');
			}
		}else{
			return $this->redirect()->toRoute('articles');
		}
	}
}
