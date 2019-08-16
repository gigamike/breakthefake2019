<?php

namespace Member\Controller;

use Zend\Db\Adapter\Adapter;
use Zend\Mvc\Controller\AbstractActionController;
use Zend\View\Model\ViewModel;

use Member\Form\MemberSiteForm;
use Site\Model\SiteEntity;
use Site\Model\SiteCategoryEntity;

use Gumlet\ImageResize;

class SiteController extends AbstractActionController
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
    if($this->getRequest()->isPost()) {
      $ids = $this->getRequest()->getPost('ids');
			if(count($ids) > 0){
				foreach($ids as $id){
          if($this->identity()->id != $id){
            $this->getSiteMapper()->delete($id);
          }
				}
			}else{
        $this->flashMessenger()->setNamespace('error')->addMessage('Please select at least 1 user.');
        return $this->redirect()->toRoute('member-site');
      }

      $this->flashMessenger()->setNamespace('success')->addMessage('Selected users successfully deleted.');
      return $this->redirect()->toRoute('member-site');
    }

    $page = $this->params()->fromRoute('page');
    $search_by = $this->params()->fromRoute('search_by') ? $this->params()->fromRoute('search_by') : '';

		$filter = array();
		if (!empty($search_by)) {
			$filter = (array) json_decode($search_by);
		}
    $filter['created_user_id'] = $this->identity()->id;

    $form = $this->getServiceLocator()->get('MemberSiteSearchForm');
    $form->setData($filter);

    $order = ['name'];
    $paginator = $this->getSiteMapper()->fetch(true, $filter,$order);
    $paginator->setCurrentPageNumber($this->params()->fromRoute('page'));
    $paginator->setItemCountPerPage(10);

    $route = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
    $action = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getParam('action');

    return new ViewModel([
      'form' => $form,
      'paginator' => $paginator,
      'route' => $route,
      'action' => $action,
    ]);
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
				return $this->redirect()->toRoute('member-site', array('search_by' => $search_by));
			}else{
				return $this->redirect()->toRoute('member-site');
			}
		}else{
			return $this->redirect()->toRoute('member-site');
		}
	}

  public function addAction()
  {
    $config = $this->getServiceLocator()->get('Config');

    $dbAdapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');

    $form = new MemberSiteForm($dbAdapter, $this->getCategoryMapper(), 0);
    if($this->getRequest()->isPost()) {
      $data = $this->params()->fromPost();
      $form->setData($data);

      if($form->isValid()) {
        $isError = false;

        if(!isset($_FILES['photo'])){
          $isError = true;
	        $form->get('photo')->setMessages(array('Required field photo.'));
		    }else{
	        $allowed =  array('jpg');
	        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
	        if(!in_array($ext, $allowed) ) {
            $isError = true;
            $form->get('photo')->setMessages(array("File type not allowed. Only " . implode(',', $allowed)));
	        }
	        switch ($_FILES['photo']['error']){
            case 1:
              $isError = true;
              $form->get('photo')->setMessages(array('The file is bigger than this PHP installation allows.'));
              break;
            case 2:
              $isError = true;
              $form->get('photo')->setMessages(array('The file is bigger than this form allows.'));
              break;
            case 3:
              $isError = true;
              $form->get('photo')->setMessages(array('Only part of the file was uploaded.'));
              break;
            case 4:
              $isError = true;
              $form->get('photo')->setMessages(array('No file was uploaded.'));
              break;
            default:
	        }
        }

        if(!$isError){
          $data = $form->getData();

          $site = new SiteEntity;
          $site->setSiteUrl($data['site_url']);
          $site->setName($data['name']);
          $site->setDescription($data['description']);
          $site->setSitemapUrl($data['sitemap_url']);
  				$site->setCreatedUserId($this->identity()->id);
          $site->setPhotoName($_FILES['photo']['name']);
          $this->getSiteMapper()->save($site);

          if(count($data['category_id']) > 0){
            foreach ($data['category_id'] as $category_id) {
              $siteCategory = new SiteCategoryEntity;
              $siteCategory->setSiteId($site->getId());
              $siteCategory->setCategoryId($category_id);
              $this->getSiteCategoryMapper()->save($siteCategory);
            }
          }

          $directory = $config['pathSitePhoto']['absolutePath'] . $site->getId();
          if(!file_exists($directory)){
            mkdir($directory, 0755);
          }

          $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
          $destination = $directory . "/photo-orig." . $ext;
          if(!file_exists($destination)){
             move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
          }
          $destination2 = $directory . "/photo-700x400." . $ext;
          if(file_exists($destination2)){
             unlink($destination2);
          }
          $image = new ImageResize($destination);
          $image->resize(700, 400);
          $image->save($destination2);

          $this->flashMessenger()->setNamespace('success')->addMessage('Site added successfully.');
          return $this->redirect()->toRoute('member-site', array('action' => 'badge', 'id' => $site->getId(),));
        }


      }
    }

    $route = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
    $action = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getParam('action');

    return new ViewModel([
      'form' => $form,
      'config' => $config,
      'route' => $route,
      'action' => $action,
    ]);
  }

  public function editAction()
  {
    $id = (int)$this->params('id');
    if (!$id) {
      $this->flashMessenger()->setNamespace('error')->addMessage('Invalid site.');
			return $this->redirect()->toRoute('member-site');
		}
		$site = $this->getSiteMapper()->getSite($id);
		if(!$site){
			$this->flashMessenger()->setNamespace('error')->addMessage('Invalid site.');
			return $this->redirect()->toRoute('member-site');
		}

    $config = $this->getServiceLocator()->get('Config');

    $dbAdapter = $this->serviceLocator->get('Zend\Db\Adapter\Adapter');

    $form = new MemberSiteForm($dbAdapter, $this->getCategoryMapper(), $site->getId());
    $form->bind($site);
	  $form->get('submit')->setAttribute('value', 'Edit');

    $request = $this->getRequest();
    if($this->getRequest()->isPost()) {
      $form->setData($request->getPost()->toArray());
      if($form->isValid()) {
        $isError = false;

        $data = $form->getData();

        if(!$isError){
          $isUploadPhoto = false;
          if(isset($_FILES['photo']) && !empty($_FILES['photo']['tmp_name'])){
            $isUploadPhoto = true;

  	        $allowed =  array('jpg');
  	        $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
  	        if(!in_array($ext, $allowed) ) {
              $isError = true;
              $form->get('photo')->setMessages(array("File type not allowed. Only " . implode(',', $allowed)));
  	        }
  	        switch ($_FILES['photo']['error']){
              case 1:
                $isError = true;
                $form->get('photo')->setMessages(array('The file is bigger than this PHP installation allows.'));
                break;
              case 2:
                $isError = true;
                $form->get('photo')->setMessages(array('The file is bigger than this form allows.'));
                break;
              case 3:
                $isError = true;
                $form->get('photo')->setMessages(array('Only part of the file was uploaded.'));
                break;
              case 4:
                $isError = true;
                $form->get('photo')->setMessages(array('No file was uploaded.'));
                break;
              default:
  	        }
  		    }

          if(!$isError){
            $site->setModifiedDatetime(date('Y-m-d H:i:s'));
            $site->setModifiedUserId($this->identity()->id);
            $this->getSiteMapper()->save($site);

            $this->getSiteCategoryMapper()->deleteBySiteId($site->getId());
            $category_ids = $request->getPost('category_id');
            if(count($category_ids) > 0){
              foreach ($category_ids as $category_id) {
                $siteCategory = new SiteCategoryEntity;
                $siteCategory->setSiteId($site->getId());
                $siteCategory->setCategoryId($category_id);
                $this->getSiteCategoryMapper()->save($siteCategory);
              }
            }

            $directory = $config['pathSitePhoto']['absolutePath'] . $site->getId();
            if(!file_exists($directory)){
              mkdir($directory, 0755);
            }

            if($isUploadPhoto){
              $ext = pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION);
              $destination = $directory . "/photo-orig." . $ext;
              if(!file_exists($destination)){
                 move_uploaded_file($_FILES['photo']['tmp_name'], $destination);
              }
              $destination2 = $directory . "/photo-700x400." . $ext;
              if(file_exists($destination2)){
                 unlink($destination2);
              }
              $image = new ImageResize($destination);
              $image->resize(700, 400);
              $image->save($destination2);
            }

            $this->flashMessenger()->setNamespace('success')->addMessage('Site edited successfully.');
            return $this->redirect()->toRoute('member-site');
          } // is error
        } // is error
      }
    }else{
      $filter = array(
        'site_id' => $site->getId(),
      );
      $siteCategories = $this->getSiteCategoryMapper()->fetch(false, $filter);
      if(count($siteCategories) > 0){
        foreach ($siteCategories as $row){
          $currentCategorySelected[] = $row->getCategoryId();
        }
        $form->get('category_id')->setAttribute('value', $currentCategorySelected);
      }
    }

    $ext = pathinfo($site->getPhotoName(), PATHINFO_EXTENSION);
    $directory = $config['pathSitePhoto']['absolutePath'] . $site->getId();
    $photo = $directory . "/photo-700x400." . $ext;
    if(file_exists($photo)){
      $photo = $config['pathSitePhoto']['relativePath'] . $site->getId() . "/photo-700x400." . $ext;
    }else{
      $photo = null;
    }

    $route = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
    $action = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getParam('action');

    return new ViewModel([
      'form' => $form,
      'site' => $site,
      'photo' => $photo,
      'route' => $route,
      'action' => $action,
    ]);
  }

  public function badgeAction()
  {
    $id = (int)$this->params('id');
    if (!$id) {
      $this->flashMessenger()->setNamespace('error')->addMessage('Invalid site.');
			return $this->redirect()->toRoute('member-site');
		}
		$site = $this->getSiteMapper()->getSite($id);
		if(!$site){
			$this->flashMessenger()->setNamespace('error')->addMessage('Invalid site.');
			return $this->redirect()->toRoute('member-site');
		}

    $config = $this->getServiceLocator()->get('Config');

    $ext = pathinfo($site->getPhotoName(), PATHINFO_EXTENSION);
    $directory = $config['pathSitePhoto']['absolutePath'] . $site->getId();
    $photo = $directory . "/photo-700x400." . $ext;
    if(file_exists($photo)){
      $photo = $config['pathSitePhoto']['relativePath'] . $site->getId() . "/photo-700x400." . $ext;
    }else{
      $photo = null;
    }

    $route = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getMatchedRouteName();
    $action = $this->getServiceLocator()->get('Application')->getMvcEvent()->getRouteMatch()->getParam('action');

    return new ViewModel([
      'site' => $site,
      'photo' => $photo,
      'route' => $route,
      'action' => $action,
      'config' => $config,
    ]);
  }

  public function deleteAction()
  {
    $id = (int)$this->params('id');
    if (!$id) {
      $this->flashMessenger()->setNamespace('error')->addMessage('Invalid site.');
      return $this->redirect()->toRoute('member-site');
    }
    $site = $this->getSiteMapper()->getSite($id);
    if(!$site){
      $this->flashMessenger()->setNamespace('error')->addMessage('Invalid site.');
      return $this->redirect()->toRoute('member-site');
    }

    $config = $this->getServiceLocator()->get('Config');
    $directory = $config['pathSitePhoto']['absolutePath'] . $site->getId();

    $ext = pathinfo($site->getPhotoName(), PATHINFO_EXTENSION);
    $file = $directory . "/photo-700x400." . $ext;
    if(file_exists($file)){
      unlink($file);
    }
    $file = $directory . "/photo-orig." . $ext;
    if(file_exists($file)){
      unlink($file);
    }

    if(file_exists($directory)){
      rmdir($directory);
    }

    $this->getSiteCategoryMapper()->deleteBySiteId($id);
    $this->getSiteMapper()->delete($id);

    $this->flashMessenger()->setNamespace('success')->addMessage('Site deleted successfully.');
    return $this->redirect()->toRoute('member-site');
  }
}
