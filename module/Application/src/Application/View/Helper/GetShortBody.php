<?php

namespace Application\View\Helper;

use Zend\View\Helper\AbstractHelper;

class GetShortBody extends AbstractHelper
{
	public function __invoke($body)
	{
		$limit = 200;
		if(strlen($body) > $limit){
			$body = substr($body, 0, $limit) . "...";
		}

	 	return $body;
	}
}
