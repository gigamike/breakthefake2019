<?php

namespace Member\Form;

use Zend\Db\Adapter\Adapter;
use Zend\InputFilter\InputFilter;

class MemberSiteFilter extends InputFilter
{
    public function __construct(Adapter $dbAdapter, $id = 0)
    {
        $this->add([
          'name' => 'site_url',
          'required' => true,
          'filters' => [
            ['name' => 'StripTags'],
            ['name' => 'StringTrim'],
          ],
          'validators' => [
            [
              'name' => 'StringLength',
              'options' => [
                'encoding' => 'UTF-8',
                'min' => 1,
                'max' => 255,
              ],
            ],
            [
              'name' => 'Callback',
              'options' => [
                'callback' => function($value) {
                  if(!filter_var($value, FILTER_VALIDATE_URL)){
                    return false;
                  }
                  return true;
                }
              ],
            ],
            [
              'name' => 'Zend\Validator\Db\NoRecordExists',
              'options' => [
                'adapter' => $dbAdapter,
                'table' => 'site',
                'field' => 'site_url',
                'exclude' => array(
                  'field' => 'id',
                  'value' => $id,
                ),
              ],
            ],
          ],
        ]);

        $this->add([
          'name' => 'name',
          'required' => true,
          'filters' => [
            ['name' => 'StripTags'],
            ['name' => 'StringTrim'],
          ],
          'validators' => [
            [
              'name' => 'StringLength',
              'options' => [
                'encoding' => 'UTF-8',
                'min' => 1,
                'max' => 255,
              ],
            ],
          ],
        ]);

        $this->add([
            'name' => 'description',
            'required' => true,
            'filters' => [
              ['name' => 'StripTags'],
              ['name' => 'StringTrim'],
            ],
        ]);

        $this->add([
            'name' => 'sitemap_url',
            'required' => true,
            'filters' => [
              ['name' => 'StripTags'],
              ['name' => 'StringTrim'],
            ],
            'validators' => [
              [
                'name' => 'StringLength',
                'options' => [
                  'encoding' => 'UTF-8',
                  'min' => 1,
                  'max' => 100,
                ],
              ],
              [
                'name' => 'Zend\Validator\Db\NoRecordExists',
                'options' => [
                  'adapter' => $dbAdapter,
                  'table' => 'site',
                  'field' => 'sitemap_url',
                  'exclude' => array(
                    'field' => 'id',
                    'value' => $id,
                  ),
                ],
              ],
              [
                'name' => 'Callback',
                'options' => [
                  'callback' => function($value) {
                    if(!filter_var($value, FILTER_VALIDATE_URL)){
                      return false;
                    }
                    return true;
                  }
                ],
              ],
            ],
        ]);
    }
}
