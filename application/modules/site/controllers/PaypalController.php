<?php
class Site_PaypalController extends Zend_Controller_Action
{
	function preDispatch() 
    { 
		$this->_helper->layout()->setLayout('layout');
		
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		
		
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
	public function checkoutAction()
	{
		Zend_Session::start();
		
		$r = $this->getRequest();
		$paymentType = $r->getParam('paymentType');
		
		//$paymentType = $_GET['paymentType'];
		$this->view->paymentType = $paymentType;
		
		require_once("paypal/paypal.php");
		
		//paypal express checkout
		$paypal = new SetExpressCheckout();  //amount is optional, if not provided,
		                                            //amount from .ini file will be used
		$paypal->setNVP("L_NAME0", "Peraturan 2 2009");
		$paypal->setNVP("L_NUMBER0", "1");
		$paypal->setNVP("L_DESC0", "Hak Paten");
		$paypal->setNVP("L_AMT0", "20.00");
		$paypal->setNVP("L_QTY0", "1");
		$paypal->setNVP("AMT", "20.00");
		$paypal->setNVP("ALLOWNOTE", "1");
		
		$paypal->getResponse();
		
	}
	public function revieworderAction()
	{
		require_once("paypal/paypal.php");
		
		$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();
		$this->view->cart = $cart;
		$jcartId = $this->view->cart->items;
		
		$totalAmount = number_format(count($jcartId)*20.00,2);
		
		if(!empty($_GET['token']))
		{
			//get express checkout details (optional)
			//the code below has to be placed in the file specified in RETURNURL 
			//(in SetExpressCheckout.ini) or setNVP() method
			$result = GetExpressCheckoutDetails::request();     
			//there's no need to use: $data = new GetExpressCheckoutDetails();
			//but if you want, you can
			// now you can save data in database or whatever you want
			$this->view->token = $_GET['token'];
			$this->view->payerId = $_GET['PayerID'];
			$this->view->amount = $totalAmount;
		}

		
	}
	public function payAction()
	{
		//THIS MAY BE THE PLACE WHERE WE CREATE AN INTERNAL INVOICE
		
		
		require_once("paypal/paypal.php");
		//do express checkout returns success or failure
		$doPay = new DoExpressCheckoutPayment($_GET['amount']);    //amount is optional but should be
		                                                    //similar to the one set in
		                                                    //SetExpressCehckout
		$result = $doPay->getResponse();
		//result holds response from PayPal so you need to check if it was successful
		print_r($result);
	}
}
?>