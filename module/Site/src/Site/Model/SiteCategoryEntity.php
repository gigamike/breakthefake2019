<?php
namespace Site\Model;

class SiteCategoryEntity
{
	protected $id;
	protected $site_id;
	protected $category_id;

	public function getId()
	{
		return $this->id;
	}

	public function setId($value)
	{
		$this->id = $value;
	}

	public function getSiteId()
	{
		return $this->product_id;
	}

	public function setSiteId($value)
	{
		$this->product_id = $value;
	}

	public function getCategoryId()
	{
		return $this->category_id;
	}

	public function setCategoryId($value)
	{
		$this->category_id = $value;
	}
}
