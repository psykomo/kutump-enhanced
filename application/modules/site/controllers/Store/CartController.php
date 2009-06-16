<?php
class Site_Store_CartController extends Zend_Controller_Action
{
	function preDispatch() 
    {
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		Zend_Session::start();
	}
	public function indexAction()
	{
		die('index cart');
	}
	public function removeitemAction()
	{
		$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();
		
		$r = $this->getRequest();
		$cart->del_item($r->getParam('id'));
		
		$this->_forward("checkout","store");
	}
}
?>