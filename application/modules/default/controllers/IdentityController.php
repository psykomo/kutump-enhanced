<?php
class IdentityController extends Zend_Controller_Action
{
	public function preDispatch()
	{
		// timeout value for the cookie
		$cookie_timeout = 60 * 60 * 24; // in seconds

		// timeout value for the garbage collector
		// we add 300 seconds, just in case the user's computer clock
		// was synchronized meanwhile; 600 secs (10 minutes) should be
		// enough - just to ensure there is session data until the
		// cookie expires
		$garbage_timeout = $cookie_timeout + 600; // in seconds

		// set the PHP session id (PHPSESSID) cookie to a custom value
		session_set_cookie_params($cookie_timeout);

		// set the garbage collector - who will clean the session files -
		// to our custom timeout
		ini_set('session.gc_maxlifetime', $garbage_timeout);
		// ini_set('session.gc_probability', '1');
		// ini_set('session.gc_divisor', '1');
		//die('identity:'.ini_get('session.gc_maxlifetime'));
		
		//Zend_Session::rememberMe(60*60*24);
		
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
	}
	
	public function loginAction()
	{	
		//$this->_helper->layout->disableLayout();
		$this->_helper->layout()->setLayout('layout-front');
		
		$r = $this->getRequest();
		
		//$returnTo = $r->getParam('returnTo');
		//$this->view->returnTo = urlencode($returnTo);
		
		
		if ($r->isPost())
		{
			$returnTo = $r->getParam('returnTo');
			$this->view->returnTo = $returnTo;

			
			Zend_Session::start();
			
			
			$username = $r->getParam('username');
			$password = $r->getParam('password');
			
			$authAdapterFactory = new Kutu_Auth_Adapter_Factory();
			$authAdapter = $authAdapterFactory->getAdapter();
			$authAdapter->setIdentity($username)
				->setCredential($password);
			
			$auth = Zend_Auth::getInstance();
			$authResult = $auth->authenticate($authAdapter);
			

			if ($authResult->isValid())
			{	
				Zend_Session::regenerateId();
				
				// success : store database row to auth's storage
				$data = $authAdapter->getResultRowObject();
				$auth->getStorage()->write($data);
				
				if(strpos($returnTo,'?'))
					$sAddition = '&';
				else 
					$sAddition = '?';
					
				header("location: ".$returnTo.$sAddition."PHPSESSID=".Zend_Session::getId());
			} 
			else 
			{
				if($authResult->getCode() != -51)
				{
					// failure : clear database row from session
					Zend_Auth::getInstance()->clearIdentity();
				}
				$this->view->errorMessage = "Login GAGAL";
			}
			
			
		}
		else
		{
			Zend_Session::start();
			
			$returnTo = $r->getParam('returnTo');	
			if(!empty($returnTo))
			{
				$returnTo = urldecode($returnTo);
				$this->view->returnTo = $returnTo;
			}
			else
			{
				$returnTo = KUTU_ROOT_URL.'/identity/account';
				$this->view->returnTo = $returnTo;
			}
			
			//check sudah login belum
			$auth = Zend_Auth::getInstance();
	        if ($auth->hasIdentity()) 
			{ 
				//echo "punya identitas";
				if(strpos($returnTo,'?'))
					$sAddition = '&';
				else 
					$sAddition = '?';
	            header("location: ".$returnTo.$sAddition."PHPSESSID=".Zend_Session::getId());
	        }
		}
	}
	public function logoutAction()
	{
		$r = $this->getRequest();
		$returnTo = $r->getParam('returnTo');
		
		if(!empty($returnTo))
		{
			$returnTo = urldecode($returnTo);
		}
		else
		{
			$returnTo = KUTU_ROOT_URL.'/identity/login';
		}
		
		//check sudah login belum
		$auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
		header("location: ".$returnTo);
	}
	public function signupAction()
	{
		$this->_helper->layout()->setLayout('layout-empty');
	}
	public function testAction()
	{
		
	}
}
?>