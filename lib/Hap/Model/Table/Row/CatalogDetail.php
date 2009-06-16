<?php
class Hap_Model_Table_Row_Catalog extends Zend_Db_Table_Row_Abstract
{
	protected function _insert()
	{
		if(empty($this->guid))
    	{
    		$guidMan = new Kutu_Core_Guid();
    		$this->guid = $guidMan->generateGuid();
    	}
	}
}
?>