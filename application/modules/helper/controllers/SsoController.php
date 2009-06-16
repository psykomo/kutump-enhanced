<?php
class Helper_SsoController extends Zend_Controller_Action
{
	public function syncsessionAction()
	{
		$this->_helper->layout->disableLayout();
		
		$req = $this->getRequest();
		$returnTo =($req->getParam('returnTo'))? $req->getParam('returnTo') : KUTU_ROOT_URL;
		
		setcookie('returnTo', urldecode($returnTo), null, '/');
		

		$flagSessionIdSent = false;
		if(isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID']))
		{
			$sessid = $_GET['PHPSESSID'];

			Zend_Session::setId($sessid);
			$flagSessionIdSent = true;
		}

		if($flagSessionIdSent)
		{
			$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
			$saveHandlerManager->setSaveHandler();
			
			Zend_Session::start();
			
			if(isset($_COOKIE['returnTo']) && !empty($_COOKIE['returnTo']))
			{
				header("location: ".$_COOKIE['returnTo']);
				exit();
			}
		}
		else 
		{
			$registry = Zend_Registry::getInstance(); 
			$config = $registry->get('config');
			$url = $config->session->config->sessionidgenerator->url;
			$sReturn = KUTU_ROOT_URL.'/helper/sso/syncsession/';
			$sReturn = urlencode($sReturn);
			header("location: $url/?returnTo=".$sReturn); 
			exit();
		}
	}
	public function loginAction()
	{
		$this->_helper->layout->disableLayout();
		
		$req = $this->getRequest();
		$returnTo =($req->getParam('returnTo'))? $req->getParam('returnTo') : KUTU_ROOT_URL;
		
		setcookie('returnMeTo', urldecode($returnTo), null, '/');
		

		$flagSessionIdSent = false;
		if(isset($_GET['PHPSESSID']) && !empty($_GET['PHPSESSID']))
		{
			if(Zend_Session::sessionExists())
			{
				if(!($_COOKIE['PHPSESSID']==$_GET['PHPSESSID']))
				{
					//Zend_Session::destroy(true);
					$sessid = $_GET['PHPSESSID'];
					Zend_Session::setId($sessid);
					
					$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
					$saveHandlerManager->setSaveHandler();

					Zend_Session::start();
				}
			}
			else
			{
				$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
				$saveHandlerManager->setSaveHandler();

				Zend_Session::start();
			}
			
			$flagSessionIdSent = true;
		}

		if($flagSessionIdSent)
		{
			
			if(isset($_COOKIE['returnMeTo']) && !empty($_COOKIE['returnMeTo']))
			{
				header("location: ".$_COOKIE['returnMeTo']);
				exit();
			}
		}
		else 
		{
			$registry = Zend_Registry::getInstance(); 
			$config = $registry->get('config');
			$url = $config->identity->login->url;
			$sReturn = KUTU_ROOT_URL.'/helper/sso/login';
			$sReturn = urlencode($sReturn);
			header("location: $url/?returnTo=".$sReturn); 
			exit();
		}
	}
}
?>