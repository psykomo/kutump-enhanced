<?php

class Kutu_Core_Orm_Table_Promosi extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserPromotion';
	
	public function insert(array $data)
	{
		if (empty($data['promotionid']))
		{
			Zend_Loader::loadClass('Kutu_Core_Guid');
			$guid = new Kutu_Core_Guid();
			$data['promotionid'] = $guid->generateGuid();
		}
		
		return parent::insert($data);		
	}
    function countPromote()
    {
    	$db = $this->_db->query
    	("Select count(*) count From KutuUserPromotion");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }
}