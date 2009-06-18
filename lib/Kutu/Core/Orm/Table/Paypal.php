<?php
class Kutu_Core_Orm_Table_Paypal extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuPaypal';
<<<<<<< HEAD:lib/Kutu/Core/Orm/Table/Paypal.php
=======
	
	public function getLastInsertId(){
		return $this->_db->lastInsertId();
	}
>>>>>>> update views, store payment controller, table model modification:lib/Kutu/Core/Orm/Table/Paypal.php
}