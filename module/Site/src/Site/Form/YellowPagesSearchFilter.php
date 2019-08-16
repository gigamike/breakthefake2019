<?php

namespace Site\Form;

use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\InputFilter;

class YellowPagesSearchFilter extends InputFilter
{
    public function __construct(Adapter $dbAdapter)
    {
      $this->add([
        'name' => 'name_keyword',
        'filters' => [
          ['name' => 'StripTags'],
          ['name' => 'StringTrim'],
        ],
      ]);
    }
}
