<?php
class Kutu_Core_Orm_Table_Paypal extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuPaypal';
	
	public function getLastInsertId(){
		return $this->_db->lastInsertId();
	}
}