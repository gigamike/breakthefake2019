<?php

namespace Member\Form;

use Zend\Db\Adapter\Adapter;
use Zend\Form\Form;

use User\Form\AdminSearchUserFilter;

class MemberSiteSearchForm extends Form
{
    public function __construct(Adapter $dbAdapter)
    {
        parent::__construct('admin-search-user');
        $this->setInputFilter(new MemberSiteSearchFilter($dbAdapter));
        $this->setAttribute('method', 'post');

        $this->add([
            'name' => 'name_keyword',
            'type' => 'text',
            'attributes' => [
                'class' => 'form-control',
                'placeholder' => '%Name%',
            ],
        ]);

        $this->add([
            'name' => 'submit',
            'type' => 'submit',
            'attributes' => [
                'value' => 'Search',
                'class' => 'btn btn-secondary',
            ],
        ]);
    }
}
