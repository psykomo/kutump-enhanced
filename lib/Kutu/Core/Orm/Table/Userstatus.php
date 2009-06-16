<?php

class Kutu_Core_Orm_Table_Userstatus extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserStatus';
	protected $_referenceMap = array(
		'User' => array(
			'columns'		=> 'accountStatusId',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_User',
			'refColumns'	=> 'periodId'
		)
	);
}