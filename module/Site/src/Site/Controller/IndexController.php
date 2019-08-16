<?php

namespace Site\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

class IndexController extends AbstractActionController
{
  public function getSiteMapper()
  {
    $sm = $this->getServiceLocator();
    return $sm->get('SiteMapper');
  }

  public function getSiteCategoryMapper()
  {
    $sm = $this->getServiceLocator();
    return $sm->get('SiteCategoryMapper');
  }

  public function getCategoryMapper()
  {
    $sm = $this->getServiceLocator();
    return $sm->get('CategoryMapper');
  }

  public function indexAction()
  {
    $page = $this->params()->fromRoute('page');
    $search_by = $this->params()->fromRoute('search_by') ? $this->params()->fromRoute('search_by') : '';

		$filter = array();
		if (!empty($search_by)) {
			$filter = (array) json_decode($search_by);
		}

    $filter = array();
		if (!empty($search_by)) {
			$filter = (array) json_decode($search_by);
		}

    $form = $this->getServiceLocator()->get('YellowPagesSearchForm');
    $form->setData($filter);

    $order = ['name'];
    $paginator = $this->getSiteMapper()->fetch(true, $filter,$order);
    $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
    $paginator->setItemCountPerPage(10);

		return new ViewModel(array(
      'form' => $form,
      'paginator' => $paginator,
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
				return $this->redirect()->toRoute('yellow-pages', array('search_by' => $search_by));
			}else{
				return $this->redirect()->toRoute('yellow-pages');
			}
		}else{
			return $this->redirect()->toRoute('yellow-pages');
		}
	}
}
