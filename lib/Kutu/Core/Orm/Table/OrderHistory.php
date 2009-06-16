<?php
class Kutu_Core_Orm_Table_OrderHistory extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuOrderHistory';
    public function getHistory($id){
     		$sql = $this->_db->select()
                    ->from('KutuOrderHistory')
                    ->where("orderId = '$id'");
		
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
	public function getUserHistory($orderId){
		//echo $orderId;
     	$db = $this->_db->query("Select KOH.*, KOS.ordersStatus  
							FROM 
								KutuOrderHistory AS KOH
							LEFT JOIN
								KutuOrderStatus AS KOS
							ON
								KOS.orderStatusId = KOH.orderStatusId
							WHERE
								KOH.orderId = $orderId");
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