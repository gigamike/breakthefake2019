<?php

namespace Api\Controller;

use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;
use Zend\View\Model\JsonModel;

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

	public function getSiteMapper()
  {
    $sm = $this->getServiceLocator();
    return $sm->get('SiteMapper');
  }

	/*
	* https://apitester.com/
	*
	*/
	public function indexAction()
	{
		$config = $this->getServiceLocator()->get('Config');

		$filter = array();
		$order = array();
		$categories = $this->getCategoryMapper()->fetch(false, $filter, $order);

		return new ViewModel(array(
			'config' => $config,
			'categories' => $categories,
    ));
	}

	private function _getResponseWithHeader()
  {
      $response = $this->getResponse();
      $response->getHeaders()
               // make can accessed by *
               ->addHeaderLine('Access-Control-Allow-Origin','*')
               // set allow methods
               ->addHeaderLine('Access-Control-Allow-Methods','POST PUT DELETE GET')
							 // json
							 ->addHeaderLine('Content-Type', 'application/json');
      return $response;
  }

	/*
	* http://breakthefake2019.gigamike.net/api/categories
	*
	*/
	public function categoriesAction()
	{
		$results = array('text' => '');

		$filter = array();
		$order = array(
			'category',
		);
		$categories = $this->getCategoryMapper()->fetch(false, $filter, $order);
		if(count($categories)>0){
			foreach($categories as $row){
				$list[] = $row->getCategory();
			}
		}

		$ctr = 0;
		$countList = count($list);
		foreach ($list as $row) {
			$ctr++;
			if($ctr == 1){
				$results['text'] .= $row;
			}else if($ctr == $countList){
				$results['text'] .= " or " . $row;
			}else{
				$results['text'] .= ", " . $row;
			}
		}

		$response = $this->_getResponseWithHeader()->setContent(json_encode($results));
    return $response;
	}

	/*
	* http://breakthefake2019.gigamike.net/api/search
	*
	*/
	public function searchAction()
	{
		$results = array('text' => '');

		$keyword = $this->params()->fromQuery('keyword');
		$category_id = $this->params()->fromQuery('category_id');
		$category = $this->params()->fromQuery('category');

		$searchFilter = array();
		if(!empty($category_id)){
			$searchFilter['category_id'] = $category_id;
		}
		if(!empty($category)){
			$searchFilter['category'] = $category;
		}
		if(!empty($keyword)){
			$searchFilter['keyword'] = $keyword;
		}

		$order = array(
      'site_article.created_datetime DESC',
      'site_article.title',
    );
		$limit = 9;
    $siteArticles = $this->getSiteArticleMapper()->getSiteArticles(false, $searchFilter, $order, $limit);
		if(count($siteArticles)>0){
			foreach($siteArticles as $row){
				$list[] = array(
					'title' => $row['title'],
					'site_name' => $row['name'],
				);
			}
		}

		$ctr = 0;
		$countList = count($list);
		foreach ($list as $row) {
			$ctr++;
			if($ctr == 1){
				$results['text'] .= "From " . $row['site_name'] . " " . $row['title'];
			}else if($ctr == $countList){
				$results['text'] .= " and From " . $row['site_name'] . " " . $row['title'];
			}else{
				$results['text'] .= ". From " . $row['site_name'] . " " . $row['title'];
			}
		}

		$response = $this->_getResponseWithHeader()->setContent(json_encode($results));
    return $response;
	}

	/*
	* http://breakthefake2019.gigamike.net/api/yellow-pages
	*
	*/
	public function yellowPagesAction()
	{
		$results = array();

		$searchFilter = array();
		$order = array(
      'name',
    );
    $sites = $this->getSiteMapper()->fetch(false, $searchFilter, $order);
		if(count($sites)>0){
			foreach($sites as $row){
				$results[] = array(
					'id' => $row->getId(),
					'name' => $row->getName(),
				);
			}
		}

		$response = $this->_getResponseWithHeader()->setContent(json_encode($results));
    return $response;
	}
}
