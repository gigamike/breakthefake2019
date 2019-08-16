<?php
namespace Site\Model;

use Zend\Db\Adapter\Adapter;
use Zend\Stdlib\Hydrator\ClassMethods;
use Zend\Db\Sql\Sql;
use Zend\Db\Sql\Select;
use Zend\Db\ResultSet\HydratingResultSet;
use Zend\Paginator\Adapter\DbSelect;
use Zend\Paginator\Paginator;
use Zend\Db\Sql\Expression;
use Zend\Db\Adapter\Driver\ResultInterface;
use Zend\Db\ResultSet\ResultSet;
use Site\Model\SiteCategoryEntity;

class SiteCategoryMapper
{
	protected $tableName = 'site_category';
	protected $dbAdapter;
	protected $sql;

	public function __construct(Adapter $dbAdapter)
	{
		$this->dbAdapter = $dbAdapter;
		$this->sql = new Sql($dbAdapter);
		$this->sql->setTable($this->tableName);
	}

	public function fetch($paginated=false, $filter = array(), $order=array())
	{
		$select = $this->sql->select();
		$where = new \Zend\Db\Sql\Where();

		if(isset($filter['id'])){
			$where->equalTo("id", $filter['id']);
		}

		if(isset($filter['site_id'])){
	    $where->equalTo("site_id", $filter['site_id']);
		}

		if(isset($filter['category_id'])){
	    $where->equalTo("category_id", $filter['category_id']);
		}

		if (!empty($where)) {
			$select->where($where);
		}

		if(count($order) > 0){
		    $select->order($order);
		}

		// echo $select->getSqlString($this->dbAdapter->getPlatform());exit();

		if($paginated) {
		    $entityPrototype = new SiteCategoryEntity();
		    $hydrator = new ClassMethods();
		    $resultset = new HydratingResultSet($hydrator, $entityPrototype);

			$paginatorAdapter = new DbSelect(
					$select,
					$this->dbAdapter,
					$resultset
			);
			$paginator = new Paginator($paginatorAdapter);
			return $paginator;
		}else{
		    $statement = $this->sql->prepareStatementForSqlObject($select);
		    $results = $statement->execute();

		    $entityPrototype = new SiteCategoryEntity();
		    $hydrator = new ClassMethods();
		    $resultset = new HydratingResultSet($hydrator, $entityPrototype);
		    $resultset->initialize($results);
		}

		return $resultset;
	}

	public function save(SiteCategoryEntity $siteCategory)
	{
		$hydrator = new ClassMethods();
		$data = $hydrator->extract($siteCategory);

		if ($siteCategory->getId()) {
			// update action
			$action = $this->sql->update();
			$action->set($data);
			$action->where(array('id' => $siteCategory->getId()));
		} else {
			// insert action
			$action = $this->sql->insert();
			unset($data['id']);
			$action->values($data);
		}
		$statement = $this->sql->prepareStatementForSqlObject($action);
		$result = $statement->execute();

		if (!$siteCategory->getId()) {
			$siteCategory->setId($result->getGeneratedValue());
		}
		return $result;
	}

	public function getSiteCategory($id)
	{
		$select = $this->sql->select();
		$select->where(array('id' => $id));

		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute()->current();
		if (!$result) {
			return null;
		}

		$hydrator = new ClassMethods();
		$siteCategory = new SiteCategoryEntity();
		$hydrator->hydrate($result, $siteCategory);

		return $siteCategory;
	}

	public function delete($id)
	{
    $delete = $this->sql->delete();
    $delete->where(array('id' => $id));

    $statement = $this->sql->prepareStatementForSqlObject($delete);
    return $statement->execute();
	}

	public function deleteBySiteId($site_id)
	{
    $delete = $this->sql->delete();
    $delete->where(array('site_id' => $site_id));

    $statement = $this->sql->prepareStatementForSqlObject($delete);
    return $statement->execute();
	}
}
