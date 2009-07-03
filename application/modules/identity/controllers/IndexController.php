<?php
class Identity_IndexController extends Zend_Controller_Action
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
		$this->_helper->layout()->setLayout('layout-lgs');
		
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
					
				//[TODO] update last login date in tbl User
				//[TODO] update last login IP in tbl User
				$tblUser = new Kutu_Core_Orm_Table_User();
				$row = $tblUser->fetchRow($tblUser->select()->where('username = ?', $username));
				$ip=$this->real_ip_address();
				$row->lastLoginIp = $ip;
				$today = date('Y-m-d h:i:s');
				$row->lastLoginDate = $today;
				$row->save();
					
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
				//$returnTo = KUTU_ROOT_URL.'/identity/account';
				$returnTo = KUTU_ROOT_URL.'/pages';
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
			//$returnTo = KUTU_ROOT_URL.'/identity/login';
			$returnTo = KUTU_ROOT_URL.'/pages';
		}
		
		//check sudah login belum
		$auth = Zend_Auth::getInstance();
        $auth->clearIdentity();
		header("location: ".$returnTo);
	}
	
	public function signupAction()
	{
		$this->_helper->layout()->setLayout('layout-lgs');
		
		$r = $this->getRequest();
		
		if($r->isPost())
		{
			$bpm = new Kutu_Core_Bpm_User();
			try
			{
				$row = $bpm->signup($r->getParams());
				$this->_helper->viewRenderer->setScriptAction('signup-success');
			}
			catch (Exception $e)
			{
				print_r($e->getMessage());
				$this->_helper->viewRenderer->setScriptAction('signup-error');
			}
		}
	}
	public function signupsuccessAction()
	{
		$this->_helper->layout()->setLayout('layout');
	}
	public function forgetpasswordAction()
	{
		$this->_helper->layout()->setLayout('layout-lgs');
		$r = $this->getRequest();
		if($r->isPost())
		{
			$bpm = new Kutu_Core_Bpm_User();
			try
			{
				$bpm->forgetPassword($r->getParam('username'), $r->getParam('email'));
				$this->view->message = "Please check your email for the new password.";
			}
			catch (Exception $e)
			{
				$this->view->message = $e->getMessage();
			}
		}
	}
	private function real_ip_address() {


		if (!empty($_SERVER['HTTP_CLIENT_IP'])){     //check if ip is from ISP
			$ip = $_SERVER['HTTP_CLIENT_IP'];
		}
		elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])){     //check if ip is passed from a proxy
			$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
		}
		else{
			$ip = $_SERVER['REMOTE_ADDR'];
		}
		return $ip;
	}

	
}
?>