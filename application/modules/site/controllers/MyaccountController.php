<?php
class Site_MyaccountController extends Zend_Controller_Action
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
	function indexAction()
	{
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/MyAccount/Menu.php');
		$w = new MyAccount_Menu();
		$this->view->widget1 = $w;
	}
	function editprofileAction()
	{
		$r = $this->getRequest();
		
		$auth =  Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
		{
			$this->_redirect(KUTU_ROOT_URL.'/helper/sso/login'.'?returnTo='.$sReturn);
		}
		else
		{
			$username = $auth->getIdentity()->username;
			//echo $auth->getIdentity()->guid;
			//die();
			$this->view->username = $username;
		}
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->fetchRow("username='$username'");	
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/MyAccount/Menu.php');
		$w = new MyAccount_Menu();
		$this->view->widget1 = $w;
		
		
		$this->view->row = $row;
		$this->view->message = "";
		
		if ($r->isPost())
		{
			
			$aData = $r->getParams();
			
			//below is for security purpose. Making sure that the GUID being edited is the GUID of the logged in user
			$aData['guid'] = $auth->getIdentity()->guid;
			
			try
			{
				$bpm = new Kutu_Core_Bpm_User();
				$row = $bpm->editProfileByUser($aData);
				
				$this->view->row = $row;
				$this->view->message = "Data has been successfully saved.";
			}
			catch (Zend_Exception $e)
			{
				$this->view->message = $e->getMessage();
			}
			
		}
		
	}
	public function changepasswordAction()
	{
		$r = $this->getRequest();
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/MyAccount/Menu.php');
		$w = new MyAccount_Menu();
		$this->view->widget1 = $w;
		
		$auth =  Zend_Auth::getInstance();
		
		$username = $auth->getIdentity()->username;
		$this->view->username = $username;
	
		$this->view->message = "";
		
		if ($r->isPost())
		{
			
			$bpm = new Kutu_Core_Bpm_User();
			
			if($bpm->changePassword($auth->getIdentity()->guid, $r->getParam('oldPassword'), $r->getParam('password')))
			{
				$this->view->message = "Password was sucessfully changed.";
			}
			else
			{
				$this->view->message = "Old password was wrong. Please retry with correct password.";
			}
		}
	}
	public function changeemailAction()
	{
		
	}
}
?>