<?php
class Kutu_Core_Orm_Table_OrderDetail extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuOrderDetail';
	
	public function hasAccess($itemId, $userId)
	{
		$access=false;
		$db = $this->_db->query("SELECT itemid 
                                FROM
									kutuorderdetail 
                                WHERE 
									userId = '$userId'
								AND
									(KO.orderStatus = 3 
									OR
									KO.orderStatus = 5)
								AND 
									itemId='$itemId';");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
		if(count($dataFetch)>0)$access=true; 
		return $access;		
	}
}
?>