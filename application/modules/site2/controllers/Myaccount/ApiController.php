<?php
class Site_Myaccount_ApiController extends Zend_Controller_Action
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
	public function verifyoldpasswordAction()
	{
		$r = $this->getRequest();
		
		$oldPassword = $r->getParam('oldPassword');
		
		$auth =  Zend_Auth::getInstance();
		
		$obj = new Kutu_Crypt_Password();
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->find($auth->getIdentity()->guid)->current();
		if($obj->matchPassword($oldPassword, $row->password))
		{
			die('1');
		}
		else
			die('0');
	}
}
?>