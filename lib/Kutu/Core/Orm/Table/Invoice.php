<?php

class Kutu_Core_Orm_Table_Invoice extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserInvoice';
	protected $_referenceMap = array(
		'User' => array(
			'columns'		=> 'user_id',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_User',
			'refColumns'	=> 'guid'
		)
	);
	
	public function insert(array $data)
	{
		return parent::insert($data);
	}
}