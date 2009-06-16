<?php

/**
 * manage Table KutuUserAccessLog
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_UserLog extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserAccessLog';	
	protected $_referenceMap = array(
		'User' => array(
			'columns'		=> 'user_id',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_User',
			'refColumns'	=> 'guid'
		)
	);
	
	function getInActiveUser()
	{
		$sql = $this->_db->select()
			->distinct()
			->from(array('KU' => 'KutuUser'))
			->join(array('KUL' => 'KutuUserAccessLog'),'KU.guid = KUL.user_id')
			->where('KU.isActive=1')
			->where('DATE_SUB(CURDATE(),INTERVAL 30 DAY) <= KUL.lastlogin')
			->group('KUL.user_id')
			->order('KUL.lastlogin DESC');
			
//    	$sql = $sql->__toString();
//    	print_r($sql);exit();

    	$db = $this->_db->query($sql);
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

?>