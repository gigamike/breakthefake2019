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
use Site\Model\SiteEntity;

class SiteMapper
{
	protected $tableName = 'site';
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

		if(isset($filter['ids']) && is_array($filter['ids']) && count($filter['ids']) > 0){
		    $where->in("id", $filter['ids']);
		}

		if(isset($filter['site'])){
		    $where->equalTo("site", $filter['site']);
		}

		if(isset($filter['created_user_id']) && !empty($filter['created_user_id'])){
		    $where->equalTo("created_user_id", $filter['created_user_id']);
		}

		if(isset($filter['site_keyword'])){
			$where->addPredicate(
					new \Zend\Db\Sql\Predicate\Like("site", "%" . $filter['site_keyword'] . "%")
			);
		}

		if (!empty($where)) {
			$select->where($where);
		}

		if(count($order) > 0){
		    $select->order($order);
		}

		// echo $select->getSqlString($this->dbAdapter->getPlatform());exit();

		if($paginated) {
		    $entityPrototype = new SiteEntity();
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

		    $entityPrototype = new SiteEntity();
		    $hydrator = new ClassMethods();
		    $resultset = new HydratingResultSet($hydrator, $entityPrototype);
		    $resultset->initialize($results);
		}

		return $resultset;
	}

	public function save(SiteEntity $site)
	{
		$hydrator = new ClassMethods();
		$data = $hydrator->extract($site);

		if ($site->getId()) {
			// update action
			$action = $this->sql->update();
			$action->set($data);
			$action->where(array('id' => $site->getId()));
		} else {
			// insert action
			$action = $this->sql->insert();
			unset($data['id']);
			$action->values($data);
		}
		$statement = $this->sql->prepareStatementForSqlObject($action);
		$result = $statement->execute();

		if (!$site->getId()) {
			$site->setId($result->getGeneratedValue());
		}
		return $result;
	}

	public function getSite($id)
	{
		$select = $this->sql->select();
		$select->where(array('id' => $id));

		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute()->current();
		if (!$result) {
			return null;
		}

		$hydrator = new ClassMethods();
		$site = new SiteEntity();
		$hydrator->hydrate($result, $site);

		return $site;
	}

	public function delete($id)
	{
	    $delete = $this->sql->delete();
	    $delete->where(array('id' => $id));

	    $statement = $this->sql->prepareStatementForSqlObject($delete);
	    return $statement->execute();
	}
}
