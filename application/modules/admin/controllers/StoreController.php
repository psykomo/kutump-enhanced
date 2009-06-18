<?php
class Admin_StoreController extends Kutu_Controller_Action
{
	function preDispatch() 
    { 
		$this->_helper->layout()->setLayout('layout-fb2');
		
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		Zend_Session::start();
		
		$sReturn = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$sReturn = urlencode($sReturn);
		$this->view->returnTo = $sReturn;
		
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		
		$auth =  Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
		{
			$this->_redirect(KUTU_ROOT_URL.'/helper/sso/login'.'?returnTo='.$sReturn);
		}
		else
		{
			// [TODO] else: check if user has access to admin page
			$username = $auth->getIdentity()->username;
			$this->view->username = $username;
		}
		
    }
	public function indexAction()
	{
        $tblStatus = new Kutu_Core_Orm_Table_OrderStatus();
        $status = $tblStatus->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]->orderStatusId;
            $orderStatus[$i] = $status[$i]->ordersStatus;
        }
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        for($i=0; $i<count($statusId);$i++){
            $rowset[$i] = $tblOrder->getOrderSummaryAdmin(' KO.orderStatus = '. $statusId[$i],5, 0);
            $total[$i] = $tblOrder->countOrdersAdmin(' KO.orderStatus = '.$statusId[$i]);
        }
        //$rowset = $tblOrder->getOrderSummaryAdmin('orderStatus = 1',5, 0);
        //var_dump(count($tblOrder->getOrderSummaryAdmin('orderStatus = 5',5, 0)));
        //var_dump(count($rowset[5]));
        
        $this->view->total = $total;
        $this->view->statusId = $statusId;
        $this->view->status = $orderStatus;
        $this->view->rowset = $rowset;
        
        //var_dump($rowset);
	}
    public function paymentsettingAction(){
        
        $tblPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        
        $rowset = $tblPaymentSetting->fetchAll();
        $numi = count($rowset);
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
    }
    public function editpaymentsettingAction(){
        $idSetting = $this->_request->getParam('id');
        $tblPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        
        $rowset = $tblPaymentSetting->fetchAll($tblPaymentSetting->select()->where("settingId = ".$idSetting));
        $this->view->id = $idSetting;
		$this->view->rows = $rowset;
        
        if($this->_request->isPost($this->_request->getParam('save'))){
            $id = $this->_request->getParam('id');
            $data['settingKey'] = $this->_request->getParam('key');
            $data['settingValue'] = $this->_request->getParam('value');
            $data['note'] = $this->_request->getParam('note');
            $update = $tblPaymentSetting->update($data, 'settingId = '.$id);
            $this->_helper->redirector('paymentsetting');
        }
    }
    public function orderAction(){
    	$tblOrder= new Kutu_Core_Orm_Table_Order();
		//View catalogs
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
		$fdate = ($r->getParam('fdate'))?$r->getParam('fdate'):date('Y-m-d');
		$this->view->fdate = $fdate;
		$ldate = ($r->getParam('ldate'))?$r->getParam('ldate'):date('Y-m-d');
		$this->view->ldate = $ldate;
		
		//print_r($Query);
		$db = Zend_Db_Table::getDefaultAdapter();
		$where ='';
		$tblStatus = new Kutu_Core_Orm_Table_OrderStatus();
		if($this->_request->get('status')){
            $valStatus =$this->_request->getParam('status');
			($valStatus == 0)?$where .=' KO.orderStatus != 0 ':$where .= ' KO.orderStatus = '.$valStatus;
            $statName = $tblStatus->getSpecifiedStatus($valStatus);
            $statName = $statName[0]->ordersStatus;
        }else{
            $where .= 'KO.orderStatus != 0';
            $statName = 'All';
            $valStatus = ' ';
        }
		
        /*$status = $tblStatus->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]->orderStatusId;
            $orderStatus[$i] = $status[$i]->ordersStatus;
        }*/
        if($this->_request->get('Query')){
            $val = $Query;
            $where .= " (AND KOS.ordersStatus LIKE '%$val%' OR KU.username LIKE '%$val%') ";
        }/*else{
            $where .= " AND KO.orderStatus !=0 ";
        }*/
		if($this->_request->get('ldate')){
            $where .= " AND datePurchased BETWEEN '$fdate' AND '$ldate'  ";
        }
		
		//print_r("'".$val);
        $rowset = $tblOrder->getOrderSummaryAdmin($where,$limit, $offset);
		$numi = $tblOrder->countOrdersAdmin($where);
        
        $this->view->Query = $Query;
        //$this->view->statusId = $statusId;
        //$this->view->orderStatus = $orderStatus;
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
        //print_r($r->getParams());
    }
    public function editorderAction(){
        $idOrder = $this->_request->getParam('id');
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        
        $rowset = $tblOrder->fetchAll($tblOrder->select()->where(" orderId = ".$idOrder));
		
		$tblOrderStatus = new Kutu_Core_Orm_Table_OrderStatus();
		$rowsStatus = $tblOrderStatus->fetchAll();
        
		$this->view->offset = $this->_request->getParam('offset');
        $this->view->id = $idOrder;
		$this->view->rows = $rowset;
        $this->view->rowsStatus = $rowsStatus;
		
        if($this->_request->isPost($this->_request->getParam('save'))){
            $id = $this->_request->getParam('id');
            $data['invoiceNumber'] = $this->_request->getParam('invoiceNumber'); 
            $data['userId'] = $this->_request->getParam('userId'); 
            $data['taxNumber'] = $this->_request->getParam('taxNumber'); 
            $data['taxCompany'] = $this->_request->getParam('taxCompany'); 
            $data['taxAddress'] = $this->_request->getParam('taxAddress'); 
            $data['taxCity'] = $this->_request->getParam('taxCity'); 
            $data['taxZip'] = $this->_request->getParam('taxZip'); 
            $data['taxProvince'] = $this->_request->getParam('taxProvince'); 
            $data['taxCountryId'] = $this->_request->getParam('taxCountryId'); 
            $data['telephone'] = $this->_request->getParam('telephone'); 
            $data['paymentMethod'] = $this->_request->getParam('paymentMethod');
            $data['paymentMethodNote'] = $this->_request->getParam('paymentMethodNote'); 
            $data['lastModified'] = $this->_request->getParam('lastModified'); 
            $data['datePurchased'] = $this->_request->getParam('datePurchased');
            $data['orderStatus'] = $this->_request->getParam('orderStatus'); 
            $data['dateOrderFinished'] = $this->_request->getParam('dateOrderFinished'); 
            $data['currency'] = $this->_request->getParam('currency'); 
            $data['currencyValue'] = $this->_request->getParam('currencyValue'); 
            $data['orderTotal'] = $this->_request->getParam('orderTotal'); 
            $data['orderTax'] = $this->_request->getParam('orderTax'); 
            $data['paypalIpnId'] = $this->_request->getParam('paypalIpnId'); 
            $data['ipAddress'] = $this->_request->getParam('ipAddress'); 
            $update = $tblOrder->update($data, 'orderId = '.$id);
			$redirector = $this->_helper->getHelper('redirector');
            $redirector->gotoSimple(array('order', 'store', 'admin', 'order'));
        }
    }
    public function detailorderAction(){
        
        $r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
    
        $idOrder = $r->getParam('id');
        //print_r( $this->_request->getParams());
		//print_r($r->getParams());
        $tblOrder = new Kutu_Core_Orm_Table_Order;
        $tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail;
        
        $rowset = $tblOrder->fetchAll($tblOrder->select()->where("orderId = ". $idOrder));
        $rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId = ". $idOrder));
        
        $tblOrderHistory = new Kutu_Core_Orm_Table_OrderHistory();
        
        $this->view->rowsHistory = $tblOrderHistory->getHistory($idOrder);
        //var_dump($tblOrderHistory->getHistory($idOrder));
		$this->view->id = $idOrder;
   		$this->view->rows = $rowset;
   		$this->view->rowsDetail = $rowsetDetail;
    }
	public function searchAction(){
    	$tblOrder= new Kutu_Core_Orm_Table_Order();
		//View catalogs
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
		$this->view->Query = $Query;
		
		//print_r($Query);
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$tblStatus = new Kutu_Core_Orm_Table_OrderStatus();
		if($this->_request->get('status')){
            $valStatus =$this->_request->getParam('status');
			($valStatus == 0)?$where =' KO.orderStatus != 0 ':$where = ' KO.orderStatus = '.$valStatus;
            $statName = $tblStatus->getSpecifiedStatus($valStatus);
            $statName = $statName[0]->ordersStatus;
        }else{
            $where = 'KO.orderStatus != 0';
            $statName = 'All';
            $valStatus = ' ';
        }
		
		if($this->_request->get('Query')){
		$where .=" AND (KO.orderId LIKE '%" . $Query . "%'
		OR invoiceNumber LIKE '%" . $Query . "%'
		OR taxNumber LIKE '%" . $Query . "%'
		OR taxCompany LIKE '%" . $Query . "%'
		OR taxAddress LIKE '%" . $Query . "%'
		OR taxCity LIKE '%" . $Query . "%'
		OR taxZip LIKE '%" . $Query . "%'
		OR taxProvince LIKE '%" . $Query . "%'
		OR taxCountryId LIKE '%" . $Query . "%'
		OR telephone LIKE '%" . $Query . "%'
		OR paymentMethod LIKE '%" . $Query . "%'
		OR paymentMethodNote LIKE '%" . $Query . "%'
		OR orderStatus LIKE '%" . $Query . "%'
		OR currency LIKE '%" . $Query . "%'
		OR currencyValue LIKE '%" . $Query . "%'
		OR orderTotal LIKE '%" . $Query . "%'
		OR orderTax LIKE '%" . $Query . "%'
		OR paypalIpnId LIKE '%" . $Query . "%'
		OR ipAddress LIKE '%" . $Query . "%') ";
		}
		//print_r($where);
        $status = $tblStatus->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]->orderStatusId;
            $orderStatus[$i] = $status[$i]->ordersStatus;
        }
        
        $rowset = $tblOrder->getOrderSummaryAdmin($where,$limit, $offset);
		$numi = $tblOrder->countOrdersAdmin($where);
        
        $this->view->statusId = $statusId;
        $this->view->orderStatus = $orderStatus;
        $this->view->valStatus = $valStatus;
        $this->view->statName = $statName;
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
		print_r($r->getParams());
    }
	public function transactionAction(){
		$tblOrder= new Kutu_Core_Orm_Table_Order();
		//View catalogs
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
		
		//print_r($Query);
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$tblStatus = new Kutu_Core_Orm_Table_OrderStatus();
        $where = 'KO.orderStatus = 3 OR KO.orderStatus = 5';
        
        $valStatus = ' ';
		
        $status = $tblStatus->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]->orderStatusId;
            $orderStatus[$i] = $status[$i]->ordersStatus;
        }
        
        $rowset = $tblOrder->getOrderSummaryAdmin($where,$limit, $offset);
		$numi = $tblOrder->countOrdersAdmin('('.$where.')');
        
        $this->view->statusId = $statusId;
        $this->view->orderStatus = $orderStatus;
        $this->view->valStatus = $valStatus;
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
	}
    public function trdetailAction(){
	    $orderId = $this->_request->getParam('id');
        
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail();
		$tblOrderHistory = new Kutu_Core_Orm_Table_OrderHistory();
		
		$rowset = $tblOrder->fetchAll($tblOrder->select()->where("orderID ='".$orderId."'"));
		$rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId='".$orderId."'"));
		$rowsetHistory = $tblOrderHistory->getUserHistory($orderId);
		//print_r($rowsetHistory);
		$this->view->listOrder = $rowset;
		$this->view->listOrderDetail = $rowsetDetail;
		$this->view->rowsetHistory = $rowsetHistory;		
		
	}
    public function postpaidAction(){
        $tblUserFinance = new Kutu_Core_Orm_Table_UserFinance();
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        
        $r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$this->view->Query = ($r->getParam('Query'))?$r->getParam('Query'):' ';
        
        $rowset = $tblOrder->getPostpaidSummary($limit, $offset);
        $rowset2 = $tblOrder->getPostpaidSummaryCount($limit, $offset);
        $total = $tblOrder->getPostpaidCount();
        echo '<pre>';
		//print_r($rowset);
		echo '</pre>';
        $this->view->totalItems = $total;
        $this->view->rowset = $rowset;
        $this->view->rowset2 = $rowset2;
		//print_r($this->_request->getParams());
    }
    public function ppeditAction(){
        $userId = $this->_request->getParam('id');
        $tblUserFinance = new Kutu_Core_Orm_Table_UserFinance();
        
        $rowset = $tblUserFinance->getUserFinance($userId);
        if($this->_request->isPost('update')){
            $userId  = $this->_request->getParam('id');
            $data['taxNumber']  = $this->_request->getParam('taxNumber'); 
            $data['taxCompany']  = $this->_request->getParam('taxCompany');
            $data['taxAddress']  =$this->_request->getParam('taxAddress');
            $data['taxCity']  = $this->_request->getParam('taxCity'); 
            $data['taxZip']  = $this->_request->getParam('taxZip'); 
            $data['taxProvince']  = $this->_request->getParam('taxProvince');
            $data['taxCountryId']  = $this->_request->getParam('taxCountryId');  
            $data['creditLimit']  = $this->_request->getParam('creditLimit'); 
            $tblUserFinance->update($data, "userId = '$userId'");
            $this->_helper->redirector('postpaid');
        }
        $this->view->rowset =$rowset;
    }
    public function pppaymentAction(){
		$idOrder = $this->_request->getParam('id');
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        
        $rowset = $tblOrder->fetchAll($tblOrder->select()->where(" orderId = ".$idOrder));
		
		$tblOrderStatus = new Kutu_Core_Orm_Table_OrderStatus();
		$tblHistory = new Kutu_Core_Orm_Table_OrderHistory();
		$rowsStatus = $tblOrderStatus->fetchAll();
        
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
		$this->view->totalItems = $limit;
		
        $this->view->id = $idOrder;
		$this->view->rows = $rowset;
        $this->view->rowsStatus = $rowsStatus;
		
        if($this->_request->isPost($this->_request->getParam('save'))){
            $id = $this->_request->getParam('id');
            
			$data['lastModified'] = $this->_request->getParam('lastModified');
			$data['orderStatus'] = $this->_request->getParam('orderStatus');
            $data['paidDate'] = $this->_request->getParam('paidDate'); 
            $updateOrder = $tblOrder->update($data, 'orderId = '.$id);
			
			$data2['orderId'] = $id;
			$data2['orderStatusId'] = $this->_request->getParam('orderStatus');
			$data2['dateCreated'] = date('Y-m-d H:i:s');
			$data2['userNotified'] = '1';
			$data2['note'] = $this->_request->getParam('note');
			$updateHistory = $tblHistory->insert($data2);
			
			
			$redirector = $this->_helper->getHelper('redirector');
			
			$redirector->gotoSimple('paysuccess','store','admin',array('id'=>$id, 'ctrl' => 'pppayment' ));
			//$this->_helper->redirector('pppsuccess');
		//print_r($this->_request->getParams());
        }
	}
    public function bpaymentAction(){
		$idOrder = $this->_request->getParam('id');
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        
        $rowset = $tblOrder->fetchAll($tblOrder->select()->where(" orderId = ".$idOrder));
		
		$tblOrderStatus = new Kutu_Core_Orm_Table_OrderStatus();
		$tblHistory = new Kutu_Core_Orm_Table_OrderHistory();
		$rowsStatus = $tblOrderStatus->fetchAll();
        
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
		$this->view->totalItems = $limit;
		
        $this->view->id = $idOrder;
		$this->view->rows = $rowset;
        $this->view->rowsStatus = $rowsStatus;
		
        if($this->_request->isPost($this->_request->getParam('save'))){
            $id = $this->_request->getParam('id');
            
			$data['lastModified'] = $this->_request->getParam('lastModified');
			$data['orderStatus'] = $this->_request->getParam('orderStatus');
            $data['paidDate'] = $this->_request->getParam('paidDate'); 
            $updateOrder = $tblOrder->update($data, 'orderId = '.$id);
			
			$data2['orderId'] = $id;
			$data2['orderStatusId'] = $this->_request->getParam('orderStatus');
			$data2['dateCreated'] = date('Y-m-d H:i:s');
			$data2['userNotified'] = '1';
			$data2['note'] = $this->_request->getParam('note');
			$updateHistory = $tblHistory->insert($data2);
			
			
			$redirector = $this->_helper->getHelper('redirector');
			
			$redirector->gotoSimple('paysuccess','store','admin',array('id'=>$id, 'ctrl' => 'bpayment' ));
			//$this->_helper->redirector('pppsuccess');
		//print_r($this->_request->getParams());
        }
	}
	public function paysuccessAction(){
		//print_r($this->_request->getParams());
		$r = $this->getRequest();
		$id = $r->getParam('id');
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
        
        $rowset = $tblOrder->fetchAll($tblOrder->select()->where(" orderId = ".$id));
		
		$this->view->ctrl = $r->getParam('ctrl');
		$this->view->rowset = $rowset;
	}
    public function excellAction(){
		$tblOrder= new Kutu_Core_Orm_Table_Order();
		//View catalogs
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
		$fdate = ($r->getParam('fdate'))?$r->getParam('fdate'):date('Y-m-d');
		$this->view->fdate = $fdate;
		$ldate = ($r->getParam('ldate'))?$r->getParam('ldate'):date('Y-m-d');
		$this->view->ldate = $ldate;
		
		//print_r($Query);
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$tblStatus = new Kutu_Core_Orm_Table_OrderStatus();
		/*if($this->_request->get('status')){
            $valStatus =$this->_request->getParam('status');
			($valStatus == 0)?$where =' KO.orderStatus != 0 ':$where = ' KO.orderStatus = '.$valStatus;
            $statName = $tblStatus->getSpecifiedStatus($valStatus);
            $statName = $statName[0]->ordersStatus;
        }else{
            $where = 'KO.orderStatus != 0';
            $statName = 'All';
            $valStatus = ' ';
        }*/
		
        $status = $tblStatus->getStatus();
        for($i =0 ;$i<count($status);$i++){
            $statusId[$i] = $status[$i]->orderStatusId;
            $orderStatus[$i] = $status[$i]->ordersStatus;
        }
        if($this->_request->get('Query')){
            $val = $Query;
            $where = " (KOS.ordersStatus LIKE '%$val%' OR KU.username LIKE '%$val%') ";
        }else{
            $where = " KO.orderStatus !=0 ";
        }
		if($this->_request->get('ldate')){
            $where .= " AND datePurchased BETWEEN '$fdate' AND '$ldate'  ";
        }
		
		//print_r("'".$val);
        $rowset = $tblOrder->getAllOrderSummaryAdmin($where);
		$numi = $tblOrder->countOrdersAdmin($where);
        
		$this->view->ctrl = $r->getParam('ctrl');
        $this->view->Query = $Query;
        $this->view->statusId = $statusId;
        $this->view->orderStatus = $orderStatus;
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
        //print_r($r->getParams());
    }
}
?>