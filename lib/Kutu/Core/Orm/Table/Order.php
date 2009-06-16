<?php
class Kutu_Core_Orm_Table_Order extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuOrder';

	public function getLastInsertId(){
		return $this->_db->lastInsertId();
	}
	
	public function getLastOrder($userId){
		$db = $this->_db->query("Select * FROM KutuOrder WHERE userId ='".$userId
								."' ORDER BY(orderId) DESC LIMIT 1");
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
	function countOrdersAdmin($where)
    {
    	$db = $this->_db->query("Select count(orderId) AS count 
                                FROM 
                                    KutuOrder AS KO,
                                    KutuOrderStatus AS KOS,
									KutuUser as KU
                                WHERE 
                                    KOS.orderStatusId = KO.orderStatus
								AND
									KU.guid = KO.userid
                                AND
                                     ".$where);
		/*echo("Select count(orderId) AS count 
                                FROM 
                                    KutuOrder AS KO,
                                    KutuOrderStatus AS KOS,
									KutuUser as KU
                                WHERE 
                                    KOS.orderStatusId = KO.orderStatus
								AND
									KU.guid = KO.userid
                                AND
                                     ".$where);*/
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }	
    function countOrders($userId)
    {
    	$db = $this->_db->query
    	("Select count(orderId) AS count From KutuOrder as KO
    	where userId=$userId");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['count']);
    }	
    function outstandingUserAmout($userId)
    {
    	$db = $this->_db->query
    	("SELECT SUM(ordertotal) AS total FROM kutuorder where userid = '$userId' AND  orderstatus=5");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['total']);
    }
    
    public function getOrderSummaryAdmin($where,$limit,$offset){
        /*if($where==0){
            $where = " != 0";
        }else{
            $where = " = ".$where;
        }*/
        //echo $where;
        $db = $this->_db->query("SELECT KO.*,KOS.ordersStatus,
                                COUNT(itemid) AS countTotal,KU.* 
                                from
                                ((Kutuorder AS KO 
                                Left join kutuorderdetail AS KOD 
                                    ON KOD.orderid=KO.orderid)
                                LEFT JOIN kutuuser AS KU 
                                    ON KU.guid = KO.userid)
                                LEFT JOIN kutuorderstatus AS KOS 
                                    ON KOS.orderstatusid = KO.orderstatus
                                WHERE $where
                                GROUP BY(KO.orderId) DESC
                                LIMIT $offset, $limit");
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	/*echo ("SELECT KO.*,KOS.ordersStatus,
                                COUNT(itemid) AS countTotal,KU.* 
                                from
                                ((Kutuorder AS KO 
                                Left join kutuorderdetail AS KOD 
                                    ON KOD.orderid=KO.orderid)
                                LEFT JOIN kutuuser AS KU 
                                    ON KU.guid = KO.userid)
                                LEFT JOIN kutuorderstatus AS KOS 
                                    ON KOS.orderstatusid = KO.orderstatus
                                WHERE $where
                                GROUP BY(KO.orderId) DESC
                                LIMIT $offset, $limit");*/
        $data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
	}
	public function getAllOrderSummaryAdmin($where){
        /*if($where==0){
            $where = " != 0";
        }else{
            $where = " = ".$where;
        }*/
        //echo $where;
        $db = $this->_db->query("SELECT KO.*,KOS.ordersStatus,
                                COUNT(itemid) AS countTotal,KU.* 
                                from
                                ((Kutuorder AS KO 
                                Left join kutuorderdetail AS KOD 
                                    ON KOD.orderid=KO.orderid)
                                LEFT JOIN kutuuser AS KU 
                                    ON KU.guid = KO.userid)
                                LEFT JOIN kutuorderstatus AS KOS 
                                    ON KOS.orderstatusid = KO.orderstatus
                                WHERE $where
                                GROUP BY(KO.orderId) DESC");
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	/*echo ("SELECT KO.*,KOS.ordersStatus,
                                COUNT(itemid) AS countTotal,KU.* 
                                from
                                ((Kutuorder AS KO 
                                Left join kutuorderdetail AS KOD 
                                    ON KOD.orderid=KO.orderid)
                                LEFT JOIN kutuuser AS KU 
                                    ON KU.guid = KO.userid)
                                LEFT JOIN kutuorderstatus AS KOS 
                                    ON KOS.orderstatusid = KO.orderstatus
                                WHERE $where
                                GROUP BY(KO.orderId) DESC
                                LIMIT $offset, $limit");*/
        $data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
	}    
    public function getOrderSummary($where,$limit,$offset){
        //echo $where;
        $db = $this->_db->query("SELECT KO.*,KOS.ordersStatus,
                                COUNT(itemid) AS countTotal,KU.* 
                                from
                                ((Kutuorder AS KO 
                                Left join kutuorderdetail AS KOD 
                                    ON KOD.orderid=KO.orderid)
                                LEFT JOIN kutuuser AS KU 
                                    ON KU.guid = KO.userid)
                                LEFT JOIN kutuorderstatus AS KOS 
                                    ON KOS.orderstatusid = KO.orderstatus
                                WHERE KO.userId = $where
                                GROUP BY(KO.orderId) DESC
                                LIMIT $offset, $limit");
        //$db = $this->_db->query();
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
	public function getDocumentSummary($userId, $where, $limit, $offset){
        $db = $this->_db->query("SELECT KOD.*, KO.datePurchased AS purchasingDate
                                FROM
                                kutuorderdetail AS KOD,
								Kutuorder AS KO 
                                WHERE 
									KO.orderId = KOD.orderId
								AND
									userId = '$userId'
								AND 
									documentName LIKE '%$where%'
                                LIMIT $offset, $limit");
        //$db = $this->_db->query();
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
	function countDocument($userId, $where)
    {
    	$db = $this->_db->query("SELECT count(itemid) as totalDoc
                                FROM
									kutuorderdetail AS KOD,
									Kutuorder AS KO 
                                WHERE 
									KO.orderId = KOD.orderId
								AND
									userId = '$userId'
								AND 
									documentName LIKE '%$where%'");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['totalDoc']);
    }
    public function getPostpaidSummary($limit,$offset){
        //echo $where;
        $db = $this->_db->query("SELECT 
                            KU.*, KUF.creditlimit AS creditLimit 
                            FROM
                                ((kutuuser AS KU
                            LEFT JOIN 
                                kutuuserfinance AS KUF 
                            ON
                                kuf.userid = ku.guid)
                            LEFT JOIN
                                kutuorder AS ko
                            ON
                                ko.userid = kuf.userid)
                            WHERE 
                                isPostpaid =1
                            GROUP BY
                                kuf.userid
                            LIMIT $offset, $limit");
        //$db = $this->_db->query();
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
	public function getPostpaidSummaryCount(){
        //echo $where;
    
	$db = $this->_db->query("SELECT 
                            KO.userId,
							SUM(orderTotal) AS total
							FROM
                                ((kutuuser AS KU
                            LEFT JOIN 
                                kutuuserfinance AS KUF 
                            ON
                                kuf.userid = ku.guid)
                            LEFT JOIN
                                kutuorder AS ko
                            ON
                                ko.userid = kuf.userid)
                            WHERE 
                                isPostpaid =1 AND orderStatus =5 
                            GROUP BY
                                kuf.userid");
        //$db = $this->_db->query();
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
    public function getPostpaidCount(){
        $db = $this->_db->query("SELECT 
                                    COUNT(KU.guid) AS countPostpaid
                                FROM
                                    kutuuser AS KU
                                LEFT JOIN
                                    kutuuserfinance AS KUF 
                                ON
                                    kuf.userid = ku.guid
                                WHERE
                                    ispostpaid = 1");
    	
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
    	
    	return ($dataFetch[0]['countPostpaid']);
    }
}