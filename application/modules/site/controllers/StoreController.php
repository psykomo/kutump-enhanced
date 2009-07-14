<?php
class Site_StoreController extends Zend_Controller_Action
{
	protected $_userInfo;
	function preDispatch()
	{
		$this->_helper->layout()->setLayout('layout-final-inside');
		
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
		
	}
	public function checkoutAction()
	{
		//print_r($_POST);
		$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();
		$this->view->cart = $cart;
		//print_r($this->_userInfo);
		$this->view->userInfo = $this->_userInfo;
	}
	public function paymentselectedAction(){
	
		echo '<pre>';
		if(($this->_request->getParam('pending'))){
			$cart = new jCart();
			//print_r($this->_request->getParams());
			$orderId = $this->_request->getParam('orderId');
			$tblOrder = new Kutu_Core_Orm_table_Order();
			$items = $tblOrder->getOrderDetail($orderId);
			for($i=0;$i<count($items);$i++){
				$_SESSION['jCart']->total= $items[$i]['orderTotal'];
				$_SESSION['jCart']->itemcount = count($items);
				$_SESSION['jCart']->items[$i] = $items[$i]['itemId'];
				$_SESSION['jCart']->itemprices[$_SESSION['jCart']->items[$i]] = $items[$i]['price'];
				$_SESSION['jCart']->itemqtys[$_SESSION['jCart']->items[$i]] = $items[$i]['qty'];
				$_SESSION['jCart']->iteminfo[$_SESSION['jCart']->items[$i]] = $items[$i]['documentName'];	
				$data['taxNumber'] 	 = $items[$i]['taxNumber'];
				$data['taxCompany']  = $items[$i]['taxCompany'];
				$data['taxAddress']  = $items[$i]['taxAddress'];
				$data['taxCity'] 	 = $items[$i]['taxCity'];
				$data['taxZip'] 	 = $items[$i]['taxZip'];
				$data['taxProvince'] = $items[$i]['taxProvince'];
				$data['taxCountry']  = $items[$i]['taxCountryId'];
				$data['method']		 = 'paypal';
				$_SESSION['_orderIdNumber'] = $orderId;
				$_SESSION['_method'] = 'paypal';
			}
			$cart =& $_SESSION['jCart'];
		}else{
			$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();
			$data = array();
			foreach($this->_request->getParams() as $key=>$value){
				$data[$key] = $value;
			}
		}
		$this->view->cart = $cart;
		
		$this->view->data = $data;
		//print_r($_SESSION['jCart']);
	}

}
?>