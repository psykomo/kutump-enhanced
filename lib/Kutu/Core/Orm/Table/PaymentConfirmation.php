<?php
class Kutu_Core_Orm_Table_PaymentConfirmation extends Zend_Db_Table_Abstract 
{
	protected $_name = 'kutupaymentconfirmation';
	
	function unconfirmListCount($where){
    	$db = $this->_db->query("Select count(paymentId) AS count 
                                FROM 
									kutupaymentconfirmation AS KPC, 
									kutuOrder AS KO, 
									KutuUser AS KU
								WHERE 
									KO.orderid = KPC.orderid 
								AND 
									KU.guid = KO.userid
								AND 
									confirmed = 0
								$where");
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }	
	public function unconfirmList($where, $limit, $offset){
        $db = $this->_db->query("SELECT 
									KPC.*,KO.*, KU.*, KOS.ordersStatus 
								FROM 
									kutupaymentconfirmation AS KPC, 
									kutuOrder AS KO, 
									KutuUser AS KU,
									kutuOrderStatus AS KOS
								WHERE 
									KO.orderid = KPC.orderid 
								AND 
									KU.guid = KO.userid
								AND 
									confirmed = 0
								AND
									KO.orderStatus = KOS.orderStatusId
								$where
                                LIMIT $offset, $limit");
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