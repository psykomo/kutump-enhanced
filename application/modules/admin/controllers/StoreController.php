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
    //$this->_xl();
    	$tblOrder= new Kutu_Core_Orm_Table_Order();
		//View catalogs
		
		$limit = ($this->_request->getParam('limit'))?$this->_request->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($this->_request->getParam('offset'))?$this->_request->getParam('offset'):0;
		$this->view->offset = $offset;
		$sStatus = ($this->_request->getPost('sStatus'))?$this->_request->getPost('sStatus'):($this->_request->getParam('sStatus'));
		$this->view->sStatus = $sStatus;
		$sUsername = ($this->_request->getPost('sUsername'))?$this->_request->getPost('sUsername'):($this->_request->getParam('sUsername'));
		$this->view->sUsername = $sUsername;
		$fdate = ($this->_request->getPost('fdate'))?$this->_request->getPost('fdate'):($this->_request->getParam('fdate'));
		$this->view->fdate = $fdate;
		$ldate = ($this->_request->getPost('ldate'))?$this->_request->getPost('ldate'):($this->_request->getParam('ldate'));
		$this->view->ldate = $ldate;
		

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
		
		if(!empty($sStatus)){
			$where .= " AND KOS.ordersStatus LIKE '%$sStatus%'";
		}
		if(!empty($sStatus)){
            $where .= " AND KU.username LIKE '%$sUsername%'";
        }
		if(!empty($fdate)){
			$where .= " AND datePurchased > '$fdate'";
		}
		if(!empty($ldate)){
            $where .= " AND datePurchased < '$ldate'  ";
        }
		
		$rowset = $tblOrder->getOrderSummaryAdmin($where,$limit, $offset);
		$numi = $tblOrder->countOrdersAdmin($where);
        
        $this->view->totalItems = $numi;
		$this->view->rows = $rowset;
		/*$xls = new PaymentGateway_ExcelExport();
 
		$xls->addRow(Array("First Name","Last Name","Website","ID"));
		$xls->addRow(Array("james","lin","www.chumby.net",0));
		$xls->addRow(Array("bhaven","mistry","www.mygumballs.com",1));
		$xls->addRow(Array("erica","truex","www.wholegrainfilms.com",2));
		$xls->addRow(Array("eliot","gann","www.dissolvedfish.com",3));
		$xls->addRow(Array("trevor","powell","gradius.classicgaming.gamespy.com",4));
		$xls->download("websites.xls");*/
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
        
        $rowset = $tblOrder->getOrderAndStatus($idOrder);
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
		$limit = ($this->_request->getParam('limit'))?$this->_request->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($this->_request->getParam('offset'))?$this->_request->getParam('offset'):0;
		$this->view->offset = $offset;
		$sStatus = ($this->_request->getPost('sStatus'))?$this->_request->getPost('sStatus'):($this->_request->getParam('sStatus'));
		$this->view->sStatus = $sStatus;
		$sUsername = ($this->_request->getPost('sUsername'))?$this->_request->getPost('sUsername'):($this->_request->getParam('sUsername'));
		$this->view->sUsername = $sUsername;
		$fdate = ($this->_request->getPost('fdate'))?$this->_request->getPost('fdate'):($this->_request->getParam('fdate'));
		$this->view->fdate = $fdate;
		$ldate = ($this->_request->getPost('ldate'))?$this->_request->getPost('ldate'):($this->_request->getParam('ldate'));
		$this->view->ldate = $ldate;
		
		//print_r($Query);
		$db = Zend_Db_Table::getDefaultAdapter();
		
		$tblStatus = new Kutu_Core_Orm_Table_OrderStatus();
        $where = '(KO.orderStatus = 3 OR KO.orderStatus = 5 OR KO.orderStatus = 2) ';
		
		if(!empty($sStatus)){
			$where .= " AND KOS.ordersStatus LIKE '%$sStatus%'";
		}
		if(!empty($sStatus)){
            $where .= " AND KU.username LIKE '%$sUsername%'";
        }
		if(!empty($fdate)){
			$where .= " AND datePurchased > '$fdate'";
		}
		if(!empty($ldate)){
            $where .= " AND datePurchased < '$ldate'  ";
        }
        
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
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        
		$where ='';
        $r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$this->view->Query = ($r->getParam('Query'))?$r->getParam('Query'):'';
        $sort = ($r->getParam('sort'))?($r->getParam('sort')):'';
		
		if($this->_request->get('Query')){
		$Query = $this->_request->get('Query');
		$where .=" (KU.username LIKE '%" . $Query . "%'
		OR KO.orderStatus LIKE '%" . $Query . "%'
		OR KU.lastname LIKE '%" . $Query . "%'
		OR KU.firstname LIKE '%" . $Query . "%'
		OR KU.company LIKE '%" . $Query . "%') ";
		}
        if($sort == 'exist'){
            $order = "ORDER BY total DESC";
        }elseif($sort == 'notExist'){
            $order = "ORDER BY total ASC";
        }else{
            $order = "";
        }
		//echo($where);
        $rowset = $tblOrder->getPostpaidSummary($where, $limit, $offset, $order);
        $total = $tblOrder->getPostpaidCount($where);
		
		for($i=0;$i<count($rowset);$i++){
			$last[] =$rowset[$i]->guid;
		}
		for($i=0;$i<count(@$last);$i++){
			$coba = ($tblOrder->getLastTransactionDate($last[$i]));
			$lastTransaction[$coba[0]->userId] = $coba[0]->datePurchased;//$dateP);
		}
        @$this->view->lastTransaction = $lastTransaction;
        $this->view->totalItems = $total;
        $this->view->rowset = $rowset;
        $this->view->sort = $sort;
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
	public function paypalpaymentAction(){
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
        $where = "KO.orderStatus = 3 AND KO.paymentMethod ='paypal'";
        
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
    public function refundAction(){
        $orderId = $this->_request->getParam('id');
        
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        $tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail();
        $rowset = $tblOrder->getOrderAndStatus($orderId);
        $rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId = ". $orderId));
        
		$this->view->id = $orderId;
   		$this->view->rows = $rowset;
   		$this->view->rowsDetail = $rowsetDetail;
        //echo $orderId;
    }
	public function refundedAction(){
        $orderId = $this->_request->getParam('id');
        print_r($this->_request->getParams());
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        $tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail();
        $tblOrderHistory = new Kutu_Core_Orm_Table_OrderHistory();
        
        $data['orderStatus'] = 2;
        $rowOrder = $tblOrder->update($data, 'orderId = '.$orderId);
        
        $data2['orderId'] = $orderId;
        $data2['orderStatusId'] = 2;
        $data2['dateCreated'] = date('Y-m-d H:i:s');
        $data2['userNotified'] = '1';
        $data2['note'] = 'Refund Payment on process';
        $updateHistory = $tblOrderHistory->insert($data2);
        $this->_helper->redirector('transaction');
    }
	public function confirmAction(){
		$tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation();
		$where ='';
		$limit = ($this->_request->getParam('limit'))?$this->_request->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($this->_request->getParam('offset'))?$this->_request->getParam('offset'):0;
		$this->view->offset = $offset;
		$sStatus = ($this->_request->getPost('sStatus'))?$this->_request->getPost('sStatus'):($this->_request->getParam('sStatus'));
		$this->view->sStatus = $sStatus;
		$sUsername = ($this->_request->getPost('sUsername'))?$this->_request->getPost('sUsername'):($this->_request->getParam('sUsername'));
		$this->view->sUsername = $sUsername;
		$fdate = ($this->_request->getPost('fdate'))?$this->_request->getPost('fdate'):($this->_request->getParam('fdate'));
		$this->view->fdate = $fdate;
		$ldate = ($this->_request->getPost('ldate'))?$this->_request->getPost('ldate'):($this->_request->getParam('ldate'));
		$this->view->ldate = $ldate;
		
		if(!empty($sStatus)){
			$where .= " AND KOS.ordersStatus LIKE '%$sStatus%'";
		}
		if(!empty($sStatus)){
            $where .= " AND KU.username LIKE '%$sUsername%'";
        }
		if(!empty($fdate)){
			$where .= " AND datePurchased > '$fdate'";
		}
		if(!empty($ldate)){
            $where .= " AND datePurchased < '$ldate'  ";
        }
		
		$rowset = $tblConfirm->unconfirmList($where,$limit, $offset);
		$count = $tblConfirm->unconfirmListCount($where);
		echo '<pre>';
		//print_r(($rowset));
		echo '</pre>';
		$this->view->rowset = $rowset;
		$this->view->totalItems = $count;
	}
	public function payconfirmAction(){
		$idOrder = $this->_request->getParam('id');
		$tblOrder = new Kutu_Core_Orm_Table_Order;
        $tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail;
        $tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation;
		
        $rowset = $tblOrder->getOrderAndStatus($idOrder);
        $rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId = ". $idOrder));
		$rowsetConfirm = $tblConfirm->fetchAll($tblConfirm->select()->where("orderId = ". $idOrder));
		$Paid = $tblConfirm->fetchAll($tblConfirm->select()->where("orderId = ". $idOrder)->order('paymentId DESC')->limit(0,1));
		
        $this->view->Paid = $Paid[0]->paymentDate;
		$this->view->idOrder = $idOrder;
		$this->view->rowset = $rowset;
		$this->view->rowsetDetail = $rowsetDetail;
		$this->view->rowsetConfirm = $rowsetConfirm;
		echo '<pre>';
		//print_r($rowset);
		//print_r($rowsetDetail);
		//print_r($rowsetConfirm);
		echo '</pre>';
	}
	public function payconfirmyesAction(){
		print_r($this->_request->getParams());
		$id = $this->_request->getParam('id');
		$tblOrder = new Kutu_Core_Orm_Table_Order;
		$tblHistory = new Kutu_Core_Orm_Table_OrderHistory;
        $tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation;
		
		//select payment date from paymentconfirmation
		$date = $tblConfirm->fetchAll($tblConfirm->select()->where("orderId = ". $id." AND confirmed = 0"));
		$data['paidDate'] = @$date[0]->paymentDate;
		//update order
		$data['orderStatus'] = 3;
		$tblOrder->update($data,"orderId = ". $id);
		
		//mailer 
		$this->Mailer($id, 'user-confirm', 'user');
		
		//update paymentconfirmation
		$dataConfirm['confirmed'] =1;
		$tblConfirm->update($dataConfirm, "orderId = ". $id);
		
		//add history
		$dataHistory = $tblHistory->fetchNew();
		//history data
		$dataHistory['orderId'] = $id; 
		$dataHistory['orderStatusId'] = 3; 
		$dataHistory['dateCreated'] = date('Y-m-d'); 
		$dataHistory['userNotified']   = 1; 
		$dataHistory['note'] = 'confirmed'; 
		$dataHistory->save();
		//redirect to confirmation page
		$this->_helper->redirector('confirm');
	}
	public function payconfirmnoAction(){
		//print_r($this->_request->getParams());
		$id = $this->_request->getParam('id');
		//$method = $this->_request->getParam('method');
		/*if($this->_request->getParam('method') == 'bank'){
			$method = 1;
		}else{
			$method = 5;
		}*/
		$method = 6;
		$tblOrder = new Kutu_Core_Orm_Table_Order;
		$tblHistory = new Kutu_Core_Orm_Table_OrderHistory;
        $tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation;
		//echo $method;
				//select payment date from paymentconfirmation
		$date = $tblConfirm->fetchAll($tblConfirm->select()->where("orderId = ". $id." AND confirmed = 0"));
		//$data['paidDate'] = @$date[0]->paymentDate;
		//update order
		$data['orderStatus'] = $method;
		$tblOrder->update($data,"orderId = ". $id);
		
		//mailer 
		$this->Mailer($id, 'user-confirm', 'user');
		//update paymentconfirmation
		$dataConfirm['confirmed'] =1;
		$tblConfirm->update($dataConfirm, "orderId = ". $id);
		
		//add history
		$dataHistory = $tblHistory->fetchNew();
		//history data
		$dataHistory['orderId'] = $id; 
		
		$dataHistory['orderStatusId'] = $method; 
		$dataHistory['dateCreated'] = date('Y-m-d'); 
		$dataHistory['userNotified']   = 1; 
		$dataHistory['note'] = 'rejected'; 
		$dataHistory->save();
		//redirect to confirmation page
		$this->_helper->redirector('confirm');
	}
	public function postpaidaddAction(){
		$act = $this->_request->get('act');
		$this->view->action = $act;
		if($act == 'select'){
			$tblUserFinance = new Kutu_Core_Orm_Table_UserFinance();
			$userList = $tblUserFinance->getUser($this->_request->getParam('username'));
			$this->view->userList = $userList;
			$this->view->Query = $this->_request->getParam('username');
			$lmt = $this->_request->getParam('creditLimit');
			if(empty($lmt)){
				$limit = 'unlimited';
			}else{
				$limit = $this->_request->getParam('creditLimit');
			}
			$this->view->creditLimit = $limit;
		}elseif($act == 'conf'){
			$id = $this->_request->getParam('id');
			$CL = $this->_request->getParam('CL');
			$tblUserFinance = new Kutu_Core_Orm_Table_UserFinance();
			$rowset = $tblUserFinance->getUserFinance($id);
			
			$this->view->rowset = $rowset;
			$this->view->CL = $CL;
			//print_r($this->_request->getParams());
		}elseif($act == 'done'){
			$tblUserFinance = new Kutu_Core_Orm_Table_UserFinance();
			if($this->_request->getParam('CL')=='unlimited'){
				$data['creditLimit'] = 0;
			}else{
				$data['creditLimit'] = $this->_request->getParam('CL');
			}
			$data['isPostpaid'] = 1;
			$userId = $this->_request->getParam('id');
			$tblUserFinance->update($data, "userId = '".$userId."'");
		}
	}
	public function Mailer($idOrder, $key, $userTo){
        $mail = new PaymentGateway_HtmlMail();
		
		$tblSetting = new Kutu_Core_Orm_Table_PaymentSetting();
		$template = $tblSetting->fetchAll($tblSetting->select()->where("settingKey = '$key'"));
		
		$tblOrder = new Kutu_Core_Orm_Table_Order;
		$tbluser = new Kutu_Core_Orm_Table_User;
        $tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail;
        $tblSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        $lgsMail = $tblSetting->fetchAll($tblSetting->select()->where("settingKey = 'paypalBusiness'"));
		
		$userDetailInfo = $tbluser->userInfoOrder($idOrder);
		
		
        $rowset = $tblOrder->getOrderAndStatus($idOrder);
        $rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId = ". $idOrder));
		$tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation;
		
		if($rowset[0]->orderStatus == 6){
			$status = 'rejected';
		}else{
			$status = 'confirmed';
		}
		
		
		$unConfirmed = $tblConfirm->fetchAll($tblConfirm->select()->where("confirmed =0 AND orderId = ". $idOrder));
		echo '<pre>';
		//print_r($userDetailInfo);
		echo '</pre>';
		$detail = "ORDER ID : ".$idOrder.'<br/>'
					.'Detail : <br/><blockquote><ol>';
		foreach($rowsetDetail as $row){
				$detail .= '<li><ul>
							<li>Document Name: '.$row->documentName.'</li>
							<li>Quantity : '.$row->qty.'</li>
							<li>Price : USD '.number_format($row->price,2).' </li>
							<li>Tax : '.number_format($row->tax,2).' %</li>
							<li>Final Price : '.number_format($row->finalPrice,2).'</li>
							</ul></li>';
		}
		$detail .= '</ol><blockquote><br />';
		
		//print_r($detail); 
		$sMailSource=$template[0]->note;
		if( $userTo == 'admin'){
			$sMailEmailTo= $lgsMail[0]->settingValue;
			$sMailEmailFrom= $userDetailInfo[0]->email;
			$link = '<a href="'.KUTU_ROOT_URL.'/admin/store/detailOrder/id/'.$idOrder.'">here</a>';
		}else{
			$sMailEmailTo= $userDetailInfo[0]->email;
			$sMailEmailFrom= $lgsMail[0]->settingValue;
			$link = '<a href="'.KUTU_ROOT_URL.'/site/store_payment/detail/id/'.$idOrder.'">here</a>';
		}
        $sMailSubject="Confirmation for user payment";
        $sMailHeader='';
        $aMailDataSet=array('PAYMENTDATE' 	=> $unConfirmed[0]->paymentDate,
							'PAIDTIME' => $unConfirmed[0]->paymentDate,
                            'PAYMENT'	=> $rowset[0]->paymentMethod,
							'DESCRIPTION'	=> $detail,
							'TOTALORDER'	=> $rowset[0]->orderTotal,
							'ORDERTIME'	=> $rowset[0]->datePurchased,
							'INVOICE'	=>	$rowset[0]->invoiceNumber,
							'METHOD' =>$rowset[0]->paymentMethod,
							'LINK' => $link,
							'STATUS' => $status);
        $mail->SendFileMail($sMailSource, $sMailEmailTo, $sMailSubject, $sMailEmailFrom, $sMailHeader, $aMailDataSet);
    }
    
	protected function _xl($data_array='', $filename='excel'){
	$headers = ''; // Nama/Header Kolom
	$data = ''; // Data Kolom
    
    
	$data_array[]=array('A' => 'A1','B' => 'B1','C' => 'C1');
    $data_array[]=array('A' => 'A2','B' => 'B2','C' => 'C2');
    /*echo '<pre>';
    var_dump($data_array);
    echo '</pre>';
    exit;*/
	if(count($data_array) == 0){
		echo '<p>Tidak ada data untuk diexport</p>';
	}else{
		$n_count=0;
		foreach($data_array as $row){
			$line = '';
			foreach($row as $field=>$value){
			if($n_count==0){
				$headers .= '"'. $field . '"' . "\t";
			}
			if((!isset($value)) || ($value == "")){
				$value = "\t";
			}else{
				$value = str_replace('"', '""', $value);
				$value = '"' . $value . '"' . "\t";
			}
			$line .= $value;
			}
			$n_count++;
			$data .= trim($line)."\n";
		}

		$data = str_replace("\r","",$data);
								 
				header("Content-type: application/x-msdownload");
				header("Content-Disposition: attachment; filename=$filename.xls");
				echo "$headers\n$data";  
	}
}
}
?>