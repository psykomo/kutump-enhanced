<?php
class Site_StoreController extends Zend_Controller_Action
{
	function preDispatch()
	{
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
		
	}
	public function checkoutAction()
	{
		//print_r($_POST);
		$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();
		$this->view->cart = $cart;
		//print_r($cart);
	}
	
}
?>