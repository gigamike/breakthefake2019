<?php
return array(
		'controllers' => array(
			'invokables' => array(
				'Site\Controller\Index' => 'Site\Controller\IndexController',
				'Site\Controller\Article' => 'Site\Controller\ArticleController',
			),
		),
		'view_manager' => array(
				'template_path_stack' => array(
						'site' => __DIR__ . '/../view',
				),
		),
);
