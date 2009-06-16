<?php
class Site_IndexController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		Zend_Session::start();
		
		
		$auth =  Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
		{
			$this->view->username = $username = "";
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
		$this->_helper->layout()->setLayout('layout-iht');
	}
	public function aboutAction()
	{
		
	}
}
?>