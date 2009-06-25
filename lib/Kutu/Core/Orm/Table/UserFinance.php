<?php
class Kutu_Core_Orm_Table_UserFinance extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserFinance';
	public function getUserFinance($where){
	$db = $this->_db->query("SELECT KUF.*, KU.firstname AS FN , KU.lastname AS LN,
							KU.username AS UN
                            FROM       
                            KutuUserFinance AS KUF, KutuUser AS KU
							WHERE userId = '$where' 
                            AND KU.guid = KUF.userId ");
	//$db = $this->_db->query();
	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
	
	$data  = array(
		'table'    => $this,
		'data'     => $dataFetch,
		'rowClass' => $this->_rowClass,
		'stored'   => true
	);

	Zend_Loader::loadClass($this->_rowsetClass);
	return new $this->_rowsetClass($data);
	}
	public function getUser($where){
	$db = $this->_db->query("SELECT KUF.*, KU.*
                            FROM       
                            KutuUserFinance AS KUF, KutuUser AS KU
							WHERE username  LIKE '%$where%' 
                            AND KU.guid = KUF.userId 
							AND isPostpaid != 1 ");
	//$db = $this->_db->query();
	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
	
	$data  = array(
		'table'    => $this,
		'data'     => $dataFetch,
		'rowClass' => $this->_rowClass,
		'stored'   => true
	);

	Zend_Loader::loadClass($this->_rowsetClass);
	return new $this->_rowsetClass($data);
	}
   
}