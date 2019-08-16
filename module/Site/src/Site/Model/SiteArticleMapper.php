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
use SiteArticle\Model\SiteArticleEntity;

class SiteArticleMapper
{
	protected $tableName = 'site_article';
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

		if(isset($filter['title_keyword'])){
			$where->addPredicate(
					new \Zend\Db\Sql\Predicate\Like("title", "%" . $filter['title_keyword'] . "%")
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
		    $entityPrototype = new SiteArticleEntity();
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

		    $entityPrototype = new SiteArticleEntity();
		    $hydrator = new ClassMethods();
		    $resultset = new HydratingResultSet($hydrator, $entityPrototype);
		    $resultset->initialize($results);
		}

		return $resultset;
	}

	public function save(SiteArticleEntity $siteArticle)
	{
		$hydrator = new ClassMethods();
		$data = $hydrator->extract($siteArticle);

		if ($siteArticle->getId()) {
			// update action
			$action = $this->sql->update();
			$action->set($data);
			$action->where(array('id' => $siteArticle->getId()));
		} else {
			// insert action
			$action = $this->sql->insert();
			unset($data['id']);
			$action->values($data);
		}
		$statement = $this->sql->prepareStatementForSqlObject($action);
		$result = $statement->execute();

		if (!$siteArticle->getId()) {
			$siteArticle->setId($result->getGeneratedValue());
		}
		return $result;
	}

	public function getSiteArticle($id)
	{
		$select = $this->sql->select();
		$select->where(array('id' => $id));

		$statement = $this->sql->prepareStatementForSqlObject($select);
		$result = $statement->execute()->current();
		if (!$result) {
			return null;
		}

		$hydrator = new ClassMethods();
		$siteArticle = new SiteArticleEntity();
		$hydrator->hydrate($result, $siteArticle);

		return $siteArticle;
	}

	public function delete($id)
	{
	    $delete = $this->sql->delete();
	    $delete->where(array('id' => $id));

	    $statement = $this->sql->prepareStatementForSqlObject($delete);
	    return $statement->execute();
	}

	public function getSiteArticles($paginated=false, $filter = array(), $order=array(), $limit = null)
	{
    $select = $this->sql->select();
		$select->columns(array(
			'id',
			'site_id',
			'article_url',
			'title',
			'body',
			'photo_name',
			'created_datetime',
		));
		$select->join(
      'site',
      $this->tableName . ".site_id = site.id",
      array(
				'name',
			),
      $select::JOIN_INNER
    );
		$select->join(
        'site_category',
        "site.id = site_category.site_id",
        array(),
        $select::JOIN_LEFT
    );
		$select->join(
        'category',
        "category.id = site_category.category_id",
        array(
					'categories' => new \Zend\Db\Sql\Expression("GROUP_CONCAT(category.category)"),
        ),
        $select::JOIN_LEFT
    );

    $where = new \Zend\Db\Sql\Where();

		if(isset($filter['category_id'])){
			$where->equalTo("site_category.category_id", $filter['category_id']);
		}
		if(isset($filter['category'])){
			$where->equalTo("category.category", $filter['category']);
		}

		if(isset($filter['keyword'])){
			$filter['keyword'] = urldecode($filter['keyword']);
			$where->addPredicate(
					new \Zend\Db\Sql\Predicate\Like($this->tableName . ".name", "%" . $filter['keyword'] . "%")
			);
		}

    if (!empty($where)) {
        $select->where($where);
    }

    if(count($order) > 0){
			$select->order($order);
    }

		if(!is_null($limit)){
	    $select->limit($limit);
		}

		$select->group($this->tableName . ".id");

    // echo $select->getSqlString($this->dbAdapter->getPlatform()) . "<br>"; exit();

    if($paginated) {
      $paginatorAdapter = new DbSelect(
        $select,
        $this->dbAdapter
      );
      $paginator = new Paginator($paginatorAdapter);
      return $paginator;
    }else{
      $statement = $this->sql->prepareStatementForSqlObject($select);
      $result = $statement->execute();
      if ($result instanceof ResultInterface && $result->isQueryResult()) {
        $resultSet = new ResultSet;
        $resultSet->initialize($result);
      }
    }

    return $resultSet;
	}
}
