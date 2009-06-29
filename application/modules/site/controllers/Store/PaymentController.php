<?php

class Site_Store_PaymentController extends Zend_Controller_Action{
    
    protected $_model; 
    protected $_payment;
    protected $_paymentVars;
    protected $_paymentMethod;
    protected $_testMode;
    protected $_userInfo;
    protected $_orderIdNumber;
    protected $_defaultCurrency;
	protected $_userDetailInfo;
	protected $_lgsMail;
        
    function preDispatch(){
        /*
        - Load Configuration dari tabel kutupaymentSetting
        - set TestMode = True or False 
        */
        $this->_testMode=true;
		$this->_defaultCurrency='USD';
        $this->_helper->layout()->setLayout('layout-iht');
        
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
        
        $userId=$auth->getIdentity()->guid;
        $tblUserFinance= new Kutu_Core_Orm_Table_UserFinance();
        $this->_userInfo=$tblUserFinance->find($userId)->current();
		
		$tblUser= new Kutu_Core_Orm_Table_User();
        $this->_userDetailInfo=$tblUser->find($userId)->current();
                
        $tblPaymentSetting = new Kutu_Core_Orm_Table_PaymentSetting();        
        $rowSet = $tblPaymentSetting->fetchAll();
        //var_dump($rowSet);
        
        for($iRow=0; $iRow<count($rowSet);$iRow++){
            $key=$rowSet[$iRow]->settingKey;
            $this->_paymentVars[$key]=$rowSet[$iRow]->settingValue;
        }
		
        $tblSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        $this->_lgsMail = $tblSetting->fetchAll($tblSetting->select()->where("settingKey = 'paypalBusiness'"));
        //var_dump($this->_paymentVars);
    }
    
    
    protected function completeItem(){
        $cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();
	$bpm = new Kutu_Core_Bpm_Catalog();
        $result = array('subTotal' => 0, 'taxAmount' => 0, 'grandTotal'=> 0,'items'=>array()); 
        for($iCart=0;$iCart<count($cart->items);$iCart++){
            $itemId=$cart->items[$iCart];
            $qty= 1;
            //$itemPrice=$bpm->getPrice($itemId);
	    $itemPrice=20;
            $result['items'][$iCart]['itemId']= $itemId;
            $result['items'][$iCart]['item_name'] = Kutu_Core_Util::getCatalogAttributeValue($itemId,'fixedTitle'); 
            $result['items'][$iCart]['itemPrice']= $itemPrice;
            $result['items'][$iCart]['qty']= $qty;
            $result['subTotal']+=$itemPrice*$qty;
	}
        $result['taxAmount']= $result['subTotal']*$this->_paymentVars['taxRate']/100;
        $result['grandTotal'] = $result['subTotal']+$result['taxAmount'];

        return $result;
    }
    public function indexAction(){
/*
    1. Calculate Tax, Save Order and Order Detail
    2. Set Payment Method
    3. Submit Variable to Payment Gateway or Manual 


*/    
      // Process and order...
   
    $paymentMethod=$this->_request->getParam('type');
    $this->_paymentMethod=$paymentMethod;
        //$this->submitAction();    
      // There should be no output at this point.  To process the POST data,
      // the submit_paypal_post() function will output all the HTML tags which
      // contains a FORM which is submited instantaneously using the BODY onload
      // attribute.  In other words, don't echo or printf anything when you're
      // going to be calling the submit_paypal_post() function.
    
      // This is where you would have your form validation  and all that jazz.
      // You would take your POST vars and load them into the class like below,
      // only using the POST values instead of constant string expressions.
    
      // For example, after ensureing all the POST variables from your custom
      // order form are valid, you might have:
      //
      // $p->add_field('first_name', $_POST['first_name']);
      // $p->add_field('last_name', $_POST['last_name']);
        switch($this->_paymentMethod){ 
            case 'paypal':
                /*
                 - Detect Multi Item and set accordingly
                 - Logic for test mode 
                */
                require_once('PaymentGateway/Paypal.php');  // include the class file
                $paymentObject = new Paypal;             // initiate an instance of the class
                
                if($this->_testMode){                    
                    $paymentObject->addField('business', $this->_paymentVars['paypalTestBusiness']);
                    $paymentObject->addField('return', $this->_paymentVars['paypalTestSuccessUrl']);
                    $paymentObject->addField('cancel_return', $this->_paymentVars['paypalTestCancelUrl']);
                    $paymentObject->addField('notify_url', $this->_paymentVars['paypalTestNotifyUrl']);
                    $paymentObject->enableTestMode();
                }else{                
                    $paymentObject->addField('business', $this->_paymentVars['paypalBusiness']);
                    $paymentObject->addField('return', $this->_paymentVars['paypalSuccessUrl']);
                    $paymentObject->addField('cancel_return', $this->_paymentVars['paypalCancelUrl']);
                    $paymentObject->addField('notify_url', $this->_paymentVars['paypalNotifyUrl']);
                }
                
                $cart=$this->completeItem();
                for($iCart=0;$iCart<count($cart['items']);$iCart++){
                    $i=$iCart+1;
                    $paymentObject->addField("item_number_".$i, $cart['items'][$iCart]['itemId']); 
                    $paymentObject->addField("item_name_".$i, $cart['items'][$iCart]['item_name']); //nama barang [documentName]
                    $paymentObject->addField("amount_".$i, $cart['items'][$iCart]['itemPrice']); //harga satuan [price]
                    $paymentObject->addField("quantity_".$i, $cart['items'][$iCart]['qty']); //jumlah barang [qty]\
                }
                $paymentObject->addField('tax_cart',$cart['taxAmount']);
                $paymentObject->addField('currency_code',$this->_defaultCurrency);
                
                $this->saveOrder($cart);
				$this->updateInvoiceMethod('paypal', 1, 0, 'paid with paypal method');
                //$paymentObject->dumpFields();
                $paymentObject->submitPayment();
                echo "<pre>";
				//print_r($paymentObject);
                echo "</pre>";
				//print_r($this->completeItem());
                //$this->saveOrder($cart);
				
				//setting payment and status as pending (1), notify = 0, notes = 'paid with...'
                break;
            case '2co':
                /*
                require_once('PaymentGateway/TwoCo.php');  
                $paymentObject = new TwoCo;
                $paymentAccount=$this->paymentVars->twoco->business;
                $paymentNotifyUrl=$this->paymentVars->returnUrl;
                $paymentObject->addField('sid', 'YOUR_VENDOR_ID');
                $paymentObject->addField('x_Receipt_Link_URL', 'http://YOUR_HOST/payment/twoco_ipn.php');
                $paymentObject->addField('tco_currency', 'USD');                
                
                $paymentObject->addField('cart_order_id', rand(1, 100));
                $paymentObject->addField('total', '9.99');

                // Specify the url where authorize.net will send the IPN                
                $paymentObject->addField('custom', 'muri');
                
                // Enable test mode if needed
                if($this->testMode)$paymentObject->enableTestMode();
                $paymentObject->dumpFields();
                // Let's start the train!
                $paymentObject->submitPayment();
                */
                break;
            case 'postpaid':
                /*
                 1. validate POSTPAID status of the client 
                 2. validate CREDIT LIMIT (per user) with current Outstanding Bill + New Bill
                 3. update order status
                 4. redirect to success or failed 
                */
				/*
                * if userid isn't listed as postpaid user will be redirected
                */
                if(!$this->_userInfo->isPostPaid){
                    echo 'Not Post Paid Customer';
                    //$paymentObject->submitPayment();
                    return $this->_helper->redirector('notpostpaid');
                }
                /*====================VALIDATE CREDIT LIMIT=====================*/
                /*
                * validate credit limit :
                * 1. count total transaction 
                * 2. counting total previous unpaid postpaid transaction
                * 3. validate
                */
                $cart = $this->completeItem();
                
                /*-----count total amount of prevous unpaid transaction------*/
                $tblOrder = new Kutu_Core_Orm_Table_Order(); 
				//table kutuOrder
                //select previous transaction that are postpaid based on userid
				//echo ($tblOrder->outstandingUserAmout($this->_userInfo->userId));
                $outstandingAmount=$tblOrder->outstandingUserAmout($this->_userInfo->userId);
                /*count total amount of prevous unpaid transaction------*/ 
                if($this->_userInfo->creditLimit == 0){
                            $limit = 'Unlimited';
                            $netLimit = 'Unlimited';
                    }else{
                            $limit = number_format($this->_userInfo->creditLimit,2);
                            $netLimit = $limit - $outstandingAmount;
                            $netLimit = number_format($netLimit,2);
                    }
                $superTotal = $cart['grandTotal']+$outstandingAmount;
                if(($this->_userInfo->creditLimit != 0) AND ($this->_userInfo->creditLimit <  $superTotal )){
                    echo $superTotal.$limit;
                    
                    $this->_helper->redirector('postpaidLimit');
                    echo 'Credit Limit Reached, Please Contact Our Billing';
                    
                /*====================VALIDATE CREDIT LIMIT=====================*/
                } else {
					/*
                    echo '<div align="center">';
                    echo 'Limit transaksi anda : ' . $limit . '<br/>';
                    echo 'total  sebelumnya : ' . number_format($outstandingAmount,2) . '<br/>';
                    echo 'total  sekarang : ' . number_format($cart['grandTotal'],2). '<br/>';
                    echo 'sisa limit anda : ' . $netLimit . '<br/>';
                    echo '<input type="button" value= "back" onclick="javascript:history.back();" />
                            <input type="button" value="Pay" onClick="parent.location.href=\'../../postpaid\'" />
                            </div>
                            ';
					*/
					/*view file =index.phtml*/
                    $this->view->type = "postpaid";
					$this->view->limit = $limit;
					$this->view->outstandingAmount = $outstandingAmount;
					$this->view->grandTotal = $cart['grandTotal'];
					$this->view->netLimit = $netLimit;
					$this->view->taxInfo = $this->_request->getParams();
					echo '<pre>';
					//print_r($_SESSION['jCart']);
					echo '</pre>';
                }
                break;
            case 'manual':
                /*
                 1. update order status
                 2. redirect to instruction page 
                */
				$cart = $this->completeItem();
				$this->saveOrder($cart);
				$this->updateInvoiceMethod('bank', 1, 0, 'paid with manual method');
				$tblOrder = new Kutu_core_Orm_Table_Order();
				$orderId = $tblOrder->getLastInsertId();
		
				$this->Mailer($orderId, 'admin-order', 'admin');
				$this->Mailer($orderId, 'user-order', 'user');
				//setting payment and status as pending (1), notify = 0, notes = 'paid with...'
				$this->_helper->redirector('instruction');
                break;            
        }
        /*var_dump($this->_paymentVars);        
        echo "<hr>";
		
        var_dump($this->getRequest());
		echo "<hr>";
        
        var_dump($this->_userInfo);
		echo "<hr>";*/
		
		//Add New Master Order

    }
	
    public function successAction(){
            // This is where you would probably want to thank the user for their order
            // or what have you.  The order information at this point is in POST 
            // variables.  However, you don't want to "process" the order until you
            // get validation from the IPN.  That's where you would have the code to
            // email an admin, update the database with payment status, activate a
			// membership, etc.  
				
            //foreach ($_POST as $key => $value) { echo "$key: $value<br>"; }
            
            
            // You could also simply re-direct them to another page, or your own 
            // order status page which presents the user with the status of their
            // order based on a database (which can be modified with the IPN code 
            // below).
            
    }
    
	public function verificationAction(){
        /*
         - check payment type use switch if necessary (paypal, twoco, manual )
         - use verification function from existing library of paypal/twoco
         - set order status if verified
         - redirect to proper page? or trigger mail?          
        */
        
        
        // It's important to remember that paypal calling this script.  There
        // is no output here.  This is where you validate the IPN data and if it's
        // valid, update your database to signify that the user has payed.  If
        // you try and use an echo or printf function here it's not going to do you
        // a bit of good.  This is on the "backend".  That is why, by default, the
        // class logs all IPN data to a text file.
        
        // Create an instance of the paypal library
		require_once('PaymentGateway/Paypal.php');
        $myPaypal = new Paypal();
        
        // Log the IPN results
        $myPaypal->ipnLog = TRUE;
        
        // Enable test mode if needed
		if($this->_testMode){
        $myPaypal->enableTestMode();
        }
        // Check validity, status, amount and tax amount and write down it
        if ($myPaypal->validateIpn())
        {
            if ($myPaypal->ipnData['payment_status'] == 'Completed' && $myPaypal['']=='')
            {
				 $this->paypalsave('SUCCESS');
				 $this->Mailer($data['orderId'], 'admin-paypal', 'admin');
				 $this->Mailer($data['orderId'], 'user-paypal', 'admin');
            }
            else
            {
				 $this->paypalsave('FAILED');
				 $this->Mailer($data['orderId'], 'admin-paypal', 'admin');
				 $this->Mailer($data['orderId'], 'user-paypal', 'admin');
            }
        }
        
        if ($this->paypal->validate_ipn()) {
            
           // Payment has been recieved and IPN is verified.  This is where you
           // update your database to activate or process the order, or setup
           // the database with the user's order details, email an administrator,
           // etc.  You can access a slew of information via the ipn_data() array.
    
           // Check the paypal documentation for specifics on what information
           // is available in the IPN POST variables.  Basically, all the POST vars
           // which paypal sends, which we send back for validation, are now stored
           // in the ipn_data() array.
    
           // For this example, we'll just email ourselves ALL the data.
           $subject = 'Instant Payment Notification - Recieved Payment';
           $to = 'chenrijano@gmail.com';    //  your email
           $body =  "An instant payment notification was successfully recieved\n";
           $body .= "from ".$this->paypal->ipn_data['payer_email']." on ".date('m/d/Y');
           $body .= " at ".date('g:i A')."\n\nDetails:\n";
           
           foreach ($this->paypal->ipn_data as $key => $value) { 
			$body .= "\n$key: $value"; 
		}
           mail($to, $subject, $body);
        }
        $_SESSION['jCart'] = '';      
    }
    
    public function instructionAction(){
		$userId = $this->_userInfo->userId;
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$rowset = $tblOrder->getLastOrder($userId);
		$this->view->rowset = $rowset;
		$_SESSION['jCart'] = '';        
    }
    
    private function getInvoiceNumber(){
        return date('YmdHis');
    }
	/*========FUNCTION FOR INSERTING A NEW ROW BEFORE PAYMENT PROCESS=============*/
    private function saveOrder($cart){
		$tblOrder=new Kutu_Core_Orm_Table_Order();
        $row=$tblOrder->fetchNew();
        
        $row->invoiceNumber= ''; 
		//empty invoice for first initialisation
		$row->userId=$this->_userInfo->userId;
        //get value from post var (store/checkout.phtml)
		if($this->getRequest()->getPost()){
				$value = $this->getRequest()->getPost(); 
				// get posted value
				
				$row->taxNumber=$value['taxNumber'];
				$row->taxCompany=$value['taxCompany'];
				$row->taxAddress=$value['taxAddress'];
				$row->taxCity=$value['taxCity'];
				$row->taxZip=$value['taxZip'];
				$row->taxProvince=$value['taxProvince'];
				$row->taxCountryId=$value['taxCountry'];
			
        }
        $row->datePurchased=date('YmdHis');
        $row->orderStatus=1; //pending
        $row->currency=$this->_defaultCurrency;        
        $row->orderTotal=$cart['grandTotal'];
        $row->orderTax=$cart['taxAmount'];
        $row->ipAddress= $this->getRealIpAddress();
		echo '<pre>';
		//print_r($row);
		echo '</pre>';
        $row->save();
		
        $orderId = $tblOrder->getLastInsertId();
        $_SESSION['orderIdNumber']=$orderId;
       
        $tblOrderDetail=new Kutu_Core_Orm_Table_OrderDetail();
        for($iCart=0;$iCart<count($cart['items']);$iCart++){        
            $rowDetail=$tblOrderDetail->fetchNew();
            
            $itemId=$cart['items'][$iCart]['itemId'];        
            $rowDetail->orderId=$orderId;
            $rowDetail->itemId=$itemId;
            $rowDetail->documentName=$cart['items'][$iCart]['item_name'];
            $rowDetail->price=$cart['items'][$iCart]['itemPrice'];
			$itemPrice = $rowDetail->price;
            $rowDetail->tax=((($cart['grandTotal']-$cart['subTotal']))/$cart['subTotal'])*100;
            $rowDetail->qty=$cart['items'][$iCart]['qty'];
            $rowDetail->finalPrice=$itemPrice + ($itemPrice * $this->_paymentVars['taxRate'] / 100);                
            $rowDetail->save();
        }
        
        $tblHistory = new Kutu_Core_Orm_Table_OrderHistory();
        $rowHistory = $tblHistory->fetchNew();        
        $rowHistory->orderId = $orderId;
        $rowHistory->orderStatusId = '0';
        $rowHistory->dateCreated = date('YmdHis');
        $rowHistory->userNotified = '0';
        $rowHistory->note = '';
        $rowHistory->save();

    }
	/*========FUNCTION FOR INSERTING A NEW ROW BEFORE PAYMENT PROCESS=============*/
	
	public function postpaidAction(){
		$cart = $this->completeItem();
		/*echo '<pre>';
		print_r($cart);
		print_r($this->_request->getParams());
		echo '</pre>';*/
		$this->saveOrder($cart);
		$this->updateInvoiceMethod('postpaid', 5, 0, 'paid with postpaid method');
		$tblOrder = new Kutu_core_Orm_Table_Order();
		$orderId = $tblOrder->getLastInsertId();
		
		$this->Mailer($orderId, 'admin-order', 'admin');
		$this->Mailer($orderId, 'user-order', 'user');
		
		//setting payment and status as postpaid (5), notify = 0, notes = 'paid with...'
		$this->_helper->redirector('postpaidSuccess');
	}

	public function postpaidsuccessAction(){
		$userId = $this->_userInfo->userId;
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$rowset = $tblOrder->getLastOrder($userId);
		$this->view->rowset = $rowset;
		//print_r($rowset);
		$_SESSION['jCart'] ='';
	}
	public function postpaidlimitAction(){
	
	}
	public function notpostpaidAction(){
	}
    //function for updating invoice and add 1 row to history   
	protected function updateInvoiceMethod($payMethod, $status, $notify, $note){
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$orderId = $_SESSION['orderIdNumber'];
		$row = $tblOrder->find( $orderId )->current();

		$row->invoiceNumber=$this->getInvoiceNumber();
		$this->_paymentMethod=$payMethod;//set payment method on table
		$row->paymentMethod=$this->_paymentMethod;
		$row->orderStatus=$status;
		$row->save();
		
		
		$tblHistory = new Kutu_Core_Orm_Table_OrderHistory();
		$rowHistory = $tblHistory->fetchNew();
		
		$rowHistory->orderId = $orderId;
		$rowHistory->orderStatusId = $status;
		$rowHistory->dateCreated = date('YmdHis');
		$rowHistory->userNotified = $notify;
		$rowHistory->note = $note;
		$rowHistory->save();
	}
	protected function getRealIpAddress(){
		if (!empty($_SERVER['HTTP_CLIENT_IP'])){   //check ip from share internet
			$ip=$_SERVER['HTTP_CLIENT_IP'];
		}elseif(!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){   //to check ip is pass from proxy
			$ip=$_SERVER['HTTP_X_FORWARDED_FOR'];
		}else{
			$ip=$_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}
	public function listAction(){
		
		$r = $this->getRequest();
		
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = (($r->getPost('Query')))?$r->getPost('Query'):$r->getParam('Query');
		$this->view->Query = $Query;
		$query ='';
		if(!empty($Query)){
			$query = " AND KOD.documentName LIKE '%$Query%' ";
		}
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		
        $where=$this->_userInfo->userId;
		$rowsetTotal = $tblOrder->countOrders ($query,"'".$where."'");
		$rowset = $tblOrder->getOrderSummary($query,"'".$where."'",$limit,$offset);

        $this->view->numCount = $rowsetTotal;
		$this->view->listOrder = $rowset;
		//print_r($this->_request->getParams());
	}
    public function detailAction(){
        $orderId = $this->_request->getParam('id');
        $tblOrder = new Kutu_Core_Orm_Table_Order();
		$rowset = $tblOrder->getOrderAndStatus($orderId);
		$this->view->listOrder = $rowset;
		$tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail();
		$rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId='".$orderId."'"));
		$this->view->listOrderDetail = $rowsetDetail;
	}
	public function searchAction(){
		$r = $this->getRequest();
		$sQuery = $r->getParam('sQuery');
		$this->view->sQuery = $sQuery;
		$sLimit = ($r->getParam('sLimit'))?$r->getParam('sLimit'):10;
		$this->view->sLimit =$sLimit;
		$itemsPerPage = $sLimit;
		$this->view->itemsPerPage = $itemsPerPage;
		$sOffset = ($r->getParam('sOffset'))?$r->getParam('sOffset'):0;
		$this->view->sOffset = $sOffset;
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$where="'".$this->_userInfo->userId."'
        AND (KO.orderId LIKE '%" . $sQuery . "%'
		OR invoiceNumber LIKE '%" . $sQuery . "%'
		OR taxNumber LIKE '%" . $sQuery . "%'
		OR taxCompany LIKE '%" . $sQuery . "%'
		OR taxAddress LIKE '%" . $sQuery . "%'
		OR taxCity LIKE '%" . $sQuery . "%'
		OR taxZip LIKE '%" . $sQuery . "%'
		OR taxProvince LIKE '%" . $sQuery . "%'
		OR taxCountryId LIKE '%" . $sQuery . "%'
		OR telephone LIKE '%" . $sQuery . "%'
		OR paymentMethod LIKE '%" . $sQuery . "%'
		OR paymentMethodNote LIKE '%" . $sQuery . "%'
		OR orderStatus LIKE '%" . $sQuery . "%'
		OR currency LIKE '%" . $sQuery . "%'
		OR currencyValue LIKE '%" . $sQuery . "%'
		OR orderTotal LIKE '%" . $sQuery . "%'
		OR orderTax LIKE '%" . $sQuery . "%'
		OR paypalIpnId LIKE '%" . $sQuery . "%'
		OR ipAddress LIKE '%" . $sQuery . "%') ";
        $rowset = $tblOrder->getOrderSummary($where, $sLimit, $sOffset);
        $rowsetTotal = $tblOrder->countOrders($where);

		$this->view->numCount = $rowsetTotal;
        $this->view->listOrder = $rowset;
        //print_r($r->getParams());
	}
	public function transactionAction(){
			
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$Query = (($r->getPost('Query')))?$r->getPost('Query'):$r->getParam('Query');
		$this->view->Query = $Query;
		$query ='';
		if(!empty($Query)){
			$query = " AND KOD.documentName LIKE '%$Query%' ";
		}
		
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		
		$rowsetTotal = $tblOrder->countOrders ($query,"'".$this->_userInfo->userId."' AND (orderStatus = 3 OR orderStatus = 5)");
        $where="'".$this->_userInfo->userId."' AND (orderStatus = 3 OR orderStatus = 5)";
		$rowset = $tblOrder->getOrderSummary($query,$where,$limit,$offset);
		
        $this->view->numCount = $rowsetTotal;
		$this->view->listOrder = $rowset;
		//print_r($this->_request->getParams());
	}
	public function billingAction(){
		$userFinance = new Kutu_Core_Orm_Table_UserFinance();
		$userId = $this->_userInfo->userId;
		$rowset = $userFinance->getUserFinance($userId);
		/*echo '<pre>';
		print_r($rowset);
		echo '</pre>';*/
		$tblOrder = new Kutu_Core_Orm_Table_Order(); 
		//table kutuOrder
		//select previous transaction that are postpaid based on userid
		$outstandingAmount = $tblOrder->outstandingUserAmout($this->_userInfo->userId);
		$this->view->rowset = $rowset;
		$this->view->outstandingAmount = $outstandingAmount;
		//print_r($this->_request->getParams());
		if($this->_request->isPost('save')){
			$data['taxNumber'] = $this->_request->getParam('taxNumber');
			$data['taxCompany'] = $this->_request->getParam('taxCompany');
			$data['taxAddress'] = $this->_request->getParam('taxAddress');
			$data['taxCity'] = $this->_request->getParam('taxCity');
			$data['taxProvince'] = $this->_request->getParam('taxProvince');
			$data['taxZip'] = $this->_request->getParam('taxZip');
			$data['taxCountryId'] = $this->_request->getParam('taxCountryId');
			$where = "userId = '".$userId."'";
			$userFinance->update($data,$where);
			$this->_helper->redirector('bilupdsuc');
		}else{
			//echo 'falset';
		}
	}
	public function bilupdsucAction(){
		$this->_helper->redirector('billing');
	}
	public function documentAction(){
		
		$r = $this->getRequest();
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):10;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		
		if($this->_request->get('Query')){
			$where = $r->getParam('Query');
			$this->view->Query = $where;
		}else{
			$where = ' ';
			$this->view->Query = 'search base on document name';
		}
		
		$tblOrder = new Kutu_Core_Orm_Table_Order(); 
		$userId = $this->_userInfo->userId;
		
		$rowset = $tblOrder->getDocumentSummary($userId, $where, $limit, $offset );
		$rowsetTotal = $tblOrder->countDocument($userId, $where);
		
		//print_r($r->getParams());
		$this->view->numCount = $rowsetTotal;
		$this->view->rowset = $rowset;
	}
	public function trdetailAction(){
	    $orderId = $this->_request->getParam('id');
		$userId = $this->_userInfo->userId;
        
		$tblOrder = new Kutu_Core_Orm_Table_Order();
		$tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail();
		$tblOrderHistory = new Kutu_Core_Orm_Table_OrderHistory();
		
		$rowset = $tblOrder->getOrderAndStatus($orderId);
		$rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId='".$orderId."'"));
		$rowsetHistory = $tblOrderHistory->getUserHistory($orderId);
		//print_r($rowsetHistory);
		$this->view->listOrder = $rowset;
		$this->view->listOrderDetail = $rowsetDetail;
		$this->view->rowsetHistory = $rowsetHistory;		
	}
	public function paypalsave($status){
        $tblOrder = new Kutu_Core_Orm_Table_Order();
        $tblHistory = new Kutu_Core_Orm_Table_OrderHistory();
        $orderId = $_SESSION['orderIdNumber'];
        $dataPrice = $tblOrder->fetchAll($tblOrder->select()->where('orderId = '.$orderId));
        if($datPrice[0]->orderTotal == $this->_request->getParam('mc_gross')){
            $payStatus = 3;
        }else{
            $payStatus = 7;
        }
		/*$cart = $this->completeItem();
		//save order
		@$this->saveOrder($cart);*/
		//update invoice
		$this->updateInvoiceMethod('paypal', $payStatus, 0, 'paid with paypal method');
		
		$tblPaypal = new Kutu_Core_Orm_Table_Paypal();
		$data = $tblPaypal->fetchNew();
		//take return value to array
		$data->mcGross  = $this->_request->getParam('mc_gross'); 
        
		$data->addressStatus  = $this->_request->getParam('address_status');  
		$data->payerId  = $this->_request->getParam('payer_id');  
		$data->addressStreet  = $this->_request->getParam('address_street');  
		$data->paymentDate  = $this->_request->getParam('payment_date');
		$data->paymentStatus  = $status; 						
		$data->addressZip  = $this->_request->getParam('address_zip'); 
		$data->firstName  = $this->_request->getParam('first_name');  
		$data->mcFee  = $this->_request->getParam('mc_fee');
		$data->addressName  = $this->_request->getParam('address_name'); 
		$data->notifyVersion  = $this->_request->getParam('notify_version'); 
		$data->payerStatus  = $this->_request->getParam('payer_status'); 
		$data->addressCountry  = $this->_request->getParam('address_country'); 
		$data->addresCity  = $this->_request->getParam('address_city');  
		$data->payerEmail  = $this->_request->getParam('payer_email');  
		$data->verifySign  = $this->_request->getParam('verify_sign'); 
		$data->paymentType  = $this->_request->getParam('payment_type');
		$data->txnId  = $this->_request->getParam('txn_id');  
		$data->lastName  = $this->_request->getParam('last_name');  
		$data->receiverEmail  = $this->_request->getParam('receiver_email');  
		$data->addressState  = $this->_request->getParam('address_state'); 
		$data->receiverId  = $this->_request->getParam('receiver_id'); 
		$data->txnType  = $this->_request->getParam('txn_type');  
		$data->mcCurrency  = $this->_request->getParam('mc_currency'); 
		$data->paymentGross  = $this->_request->getParam('payment_gross'); 
		$data->paymentFee  = $this->_request->getParam('payment_fee'); 
		$data->numCartItems  = $this->_request->getParam('num_cart_items');
		$data->business  = $this->_request->getParam('business');
		$data->parentTxnId  = $this->_request->getParam('txn_id');
		$data->lastModified = date('Y-m-d');
		$data->dateAdded = date('Y-m-d');
		
		$data->save();
		//echo($tblPaypal->getLastInsertId());
		$paypalHistory = new Kutu_Core_Orm_Table_PaypalPaymentHistory();
		$row = $paypalHistory->fetchNew();
		$row['paypalIpnId'] = $tblPaypal->getLastInsertId();
		$row['txnId'] = $this->_request->getParam('txn_id');
		$row['parentTxnId'] = $this->_request->getParam('txn_id');
		$row['paymentStatus'] = $this->_request->getParam('payment_status');
		$row['dateAdded'] = date('Y-m-d');
		
		$row->save();
        
        
        $rowHistory = $tblHistory->fetchNew();        
        $rowHistory->orderId = $orderId;
        $rowHistory->orderStatusId = $payStatus;
        $rowHistory->dateCreated = date('YmdHis');
        $rowHistory->userNotified = '0';
        $rowHistory->note = '';
        $rowHistory->save();
	}
    public function confirmAction(){
        $userId = $this->_userInfo->userId;
        $tblOrder = new Kutu_Core_Orm_table_Order();
        $tblSetting = new Kutu_Core_Orm_Table_PaymentSetting();
        
        $rowset = $tblOrder->getTransactionToConfirm($userId);//,$limit,$offset);
        $numCount = $tblOrder->getTransactionToConfirmCount($userId);
        $bankAccount = $tblSetting->fetchAll($tblSetting->select()->where("settingKey = 'bankAccount'"));
        
		if($this->_request->get('sended') == 1){
			$this->view->sended = 'Payment Confirmation Sent';
		}
        $this->view->numCount = $numCount;
        $this->view->rowset = $rowset;
        $this->view->bankAccount = $bankAccount;
		/*echo'<pre>';
		print_r($this->_userDetailInfo);
        echo'</pre>';*/
    }
    public function payconfirmAction(){
		$r = $this->getRequest();
		foreach($r->getParam('orderId')as $key=>$row){
			$tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation();
			$data = $tblConfirm->fetchNew();
			
			$data['paymentMethod'] = $r->getParam('paymentMethod');
			$data['destinationAccount'] = $r->getParam('destinationAccount');
			$data['paymentDate'] = $r->getParam('paymentDate');
			$data['amount'] = $r->getParam('amount');
			$data['senderAccount'] = $r->getParam('senderAccount');
			$data['senderAccountName'] = $r->getParam('senderAccountName');
			$data['bankName'] = $r->getParam('bankName');
			$data['note'] = $r->getParam('note');
			$data['orderId'] = $row;
			$data->save();
			
			$tblOrder = new Kutu_Core_Orm_Table_Order();
			$statdata['orderStatus'] = 4;
			$tblOrder->update($statdata, 'orderId = '.$data['orderId']);
			
			$tblHistory = new Kutu_Core_Orm_Table_OrderHistory;
			
			//add history
			$dataHistory = $tblHistory->fetchNew();
			//history data
			$dataHistory['orderId'] = $data['orderId']; 

			$dataHistory['orderStatusId'] = 6; 
			$dataHistory['dateCreated'] = date('Y-m-d'); 
			$dataHistory['userNotified']   = 1; 
			$dataHistory['note'] = 'Waiting Confirmation'; 
			$dataHistory->save();
			$this->Mailer($data['orderId'], 'admin-confirm', 'admin');
		}
		$this->_helper->redirector->gotoSimple('confirm', 'store_payment', 'site', array('sended' => '1'));
	}
    public function Mailer($idOrder, $key, $userTo){
        $mail = new PaymentGateway_HtmlMail();
		
		$tblSetting = new Kutu_Core_Orm_Table_PaymentSetting();
		$template = $tblSetting->fetchAll($tblSetting->select()->where("settingKey = '$key'"));
		
		$tblOrder = new Kutu_Core_Orm_Table_Order;
        $tblOrderDetail = new Kutu_Core_Orm_Table_OrderDetail;
        
        $rowset = $tblOrder->getOrderAndStatus($idOrder);
        $rowsetDetail = $tblOrderDetail->fetchAll($tblOrderDetail->select()->where("orderId = ". $idOrder));
		$tblConfirm = new Kutu_Core_Orm_Table_PaymentConfirmation;
		
		$unConfirmed = $tblConfirm->fetchAll($tblConfirm->select()->where("confirmed =0 AND orderId = ". $idOrder));
		
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
		$detail .= '</ol></blockquote>';
		
		//print_r($unConfirmed); 
		$sMailSource=$template[0]->note;
		if( $userTo == 'admin'){
			$sMailEmailTo= $this->_lgsMail[0]->settingValue;
			$sMailEmailFrom= $this->_userDetailInfo->email;
			$link = '<a href="'.KUTU_ROOT_URL.'/admin/store/detailOrder/id/'.$idOrder.'">here</a>';
		}else{
			$sMailEmailTo= $this->_userDetailInfo->email;
			$sMailEmailFrom= $this->_lgsMail[0]->settingValue;
			$link = '<a href="'.KUTU_ROOT_URL.'/site/store_payment/detail/id/'.$idOrder.'">here</a>';
		}
        $sMailSubject="Confirmation for user payment";
        $sMailHeader='';
        $aMailDataSet=array('PAYMENTDATE' 	=> @$unConfirmed[0]->paymentDate,
                            'PAYMENT'	=> $rowset[0]->paymentMethod,
							'DESCRIPTION'	=> $detail,
							'TOTALORDER'	=> $rowset[0]->orderTotal,
							'ORDERTIME'	=> $rowset[0]->datePurchased,
							'INVOICE'	=>	$rowset[0]->invoiceNumber,
							'METHOD' =>$rowset[0]->paymentMethod,
							'LINK' => $link);
        $mail->SendFileMail($sMailSource, $sMailEmailTo, $sMailSubject, $sMailEmailFrom, $sMailHeader, $aMailDataSet);
    }
}