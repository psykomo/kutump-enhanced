<?php

/**
 * manage Table KutuUser
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_User extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUser';
	protected $_rowClass = 'Kutu_Core_Orm_Table_Row_User';
    protected $_rowsetClass = 'Kutu_Core_Orm_Table_Rowset_User';
	/*protected $_dependentTables = array('Kutu_Core_Orm_Table_UserLog','Kutu_Core_Orm_Table_Packed','Kutu_Core_Orm_Table_Invoice','Kutu_Core_Orm_Table_Userstatus');
	protected $_referenceMap = array(
		'Packed' => array(
			'columns'		=> 'packetId',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_Packed',
			'refColumns'	=> 'packetid'
		),
		'Status' => array(
			'columns'		=> 'periodId',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_Userstatus',
			'refColumns'	=> 'accountStatusId'
		)
	);*/
	public function userInfoOrder($orderId){
		$db = $this->_db->query("Select KU.* FROM KutuUser AS KU, KutuOrder as KO 
								WHERE KO.userId = KU.guid AND KO.orderId = $orderId");
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