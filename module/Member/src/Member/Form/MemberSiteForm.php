<?php

namespace Member\Form;

use Zend\Db\Adapter\Adapter;
use Zend\Form\Form;
use Zend\Stdlib\Hydrator\ClassMethods;

use Member\Form\MemberSiteFilter;

class MemberSiteForm extends Form
{
    public function __construct(Adapter $dbAdapter, $categoryMapper, $id = 0)
    {
      parent::__construct('member-site');
      $this->setInputFilter(new MemberSiteFilter($dbAdapter, $id));
      $this->setAttribute('method', 'post');
      $this->setAttribute('enctype', 'multipart/form-data');
      $this->setHydrator(new ClassMethods());

      $this->add([
          'name' => 'site_url',
          'type' => 'text',
          'options' => [
              'label' => 'Site URL',
          ],
          'attributes' => [
              'class' => 'form-control',
              'id' => 'name',
              'required' => 'required',
              'autofocus' => 'autofocus',
              'placeholder' => 'Site URL',
          ],
      ]);

      $this->add([
          'name' => 'name',
          'type' => 'text',
          'options' => [
              'label' => 'Name',
          ],
          'attributes' => [
              'class' => 'form-control',
              'id' => 'name',
              'required' => 'required',
              'placeholder' => 'Name',
          ],
      ]);

      $this->add([
          'name' => 'description',
          'type' => 'textarea',
          'options' => [
              'label' => 'Description',
          ],
          'attributes' => [
              'class' => 'form-control',
              'id' => 'description',
              'required' => 'required',
              'placeholder' => 'Description',
          ],
      ]);

      $this->add([
          'name' => 'sitemap_url',
          'type' => 'text',
          'options' => [
              'label' => 'Sitemap URL',
          ],
          'attributes' => [
              'class' => 'form-control',
              'id' => 'sitemap_url',
              'required' => 'required',
              'placeholder' => 'Sitemap URL',
          ],
      ]);

      $this->add([
        'name' => 'category_id',
        'type' => 'Select',
        'options' => [
          'label' => 'Category',
        ],
        'attributes' => [
          'multiple' => 'multiple',
	        'class' => 'form-control',
	        'options' => $this->_getCategories($categoryMapper),
          'id' => 'category_id',
          'required' => 'required',
          'data-validation-required-message' => 'Please enter your category.',
          'size' => 10,
		    ],
      ]);

      $this->add(array(
		    'name' => 'photo',
		    'attributes' => array(
	        'type'  => 'file',
          'id' => 'photo',
		    ),
		    'options' => array(
	        'label' => 'Photo',
		    ),
  		));

      $this->add([
          'name' => 'submit',
          'type' => 'submit',
          'attributes' => [
              'value' => 'Submit',
              'class' => 'btn btn-primary btn-block',
          ],
      ]);
    }

    private function _getCategories($categoryMapper){
      $temp = array();

	    $filter = array();
      $order = array('category');
	    $categories = $categoryMapper->fetch(false, $filter, $order);
	    foreach ($categories as $row){
	       $temp[$row->getId()] = $row->getCategory();
	    }

	    return $temp;
    }
}
