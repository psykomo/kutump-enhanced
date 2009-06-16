<?php
class Admin_UserController extends Kutu_Controller_Action
{
	function preDispatch() 
    { 
		$this->_helper->layout()->setLayout('layout-fb2');
		
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
    public function browseAction()
    {
		$this->_helper->layout()->setLayout('layout-fb2');
		$r = $this->getRequest();
		$node = $r->getParam('node');
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/user/UserPager.php');
		$w = new UserPager(2);
		$this->view->userPager = $w->render();
		
	}
	public function addAction()
	{
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/user/UserMainMenu.php');
		$w = new UserMainMenu();
		$this->view->userMainMenu = $w->render();
		
		$r = $this->getRequest();
		
		if ($r->isPost())
		{
			$username = $r->getParam('username');
			if(empty($username))
			{
				die('ERROR: Username can not be empty!');
			}
			$password = $r->getParam('password');
			$crypt = new Kutu_Crypt_Password();
			$password = $crypt->encryptPassword($password);
			$firstname = $r->getParam('firstname');
			$lastname = $r->getParam('lastname');
			$email = $r->getParam('email');
			
			$tblUser = new Kutu_Core_Orm_Table_User();
			$row = $tblUser->createRow();
			$row->username = $username;
			$row->password = $password;
			$row->firstname = $firstname;
			$row->lastname = $lastname;
			$row->email = $email;
			$row->save();
			$this->_helper->viewRenderer->setScriptAction('add-success');
			
		}
	}
	public function editAction()
	{
		$r = $this->getRequest();
		$userGuid = $r->getParam('guid');
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->find($userGuid)->current();
		//print_r($row->password);
		$this->view->row = $row;
		$this->view->message = "";
		
		if ($r->isPost())
		{
			$guid = $r->getParam('guid');
			$firstname = $r->getParam('firstname');
			$email = $r->getParam('email');
			
			$tblUser = new Kutu_Core_Orm_Table_User();
			$row = $tblUser->find($guid)->current();
			$row->firstname = $firstname;
			$row->lastname = $r->getParam('lastname');
			$row->email = $email;
			$row->bbPin = $r->getParam('bbPin');
			$row->clientId = $r->getParam('clientId');
			$row->mainAddress = $r->getParam('mainAddress');
			$row->url = $r->getParam('url');
			$row->countryId = $r->getParam('countryId');
			$row->company = $r->getParam('company');
			$row->companySizeId = $r->getParam('companySizeId');
			$row->jobId = $r->getParam('jobId');
			$row->industryId = $r->getParam('industryId');
			$row->isActive = $r->getParam('isActive');
			$row->save();
			
			$this->view->row = $row;
			$this->view->message = "Data has been successfully saved.";
			//$this->_helper->viewRenderer->setScriptAction('add-success');
			
		}
	}
	public function assigngroupAction()
	{
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->find($guid)->current();
		
		$acl = Kutu_Acl_Manager::getAdapter();
		$aGroup = $acl->getGroups();
		$this->view->availableGroups = $aGroup;
		
		$aCurrentGroup = $acl->getUserGroupIds($row->username);
		$this->view->currentGroups = $aCurrentGroup;
		
		$this->view->rowUser = $row;
		$this->view->message = '';
		
		if ($r->isPost())
		{
			$assignedGroups = $r->getParam('assignedGroups');
			//print_r($currentGroups);
			foreach($aCurrentGroup as $currGroup)
			{
				$acl->removeUserFromGroup($row->username, $currGroup);
			}
			foreach($assignedGroups as $group)
			{
				//echo $group;
				$acl->addUserToGroup($row->username, $group);
			}
			
			$aGroup = $acl->getGroups();
			$this->view->availableGroups = $aGroup;

			$aCurrentGroup = $acl->getUserGroupIds($row->username);
			$this->view->currentGroups = $aCurrentGroup;
			$this->view->message = "Data was Saved";
		}
	}
	public function changepasswordAction()
	{
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->find($guid)->current();
		
		$this->view->rowUser = $row;
		if ($r->isPost())
		{
			echo "<strong> THIS FUNCTION IS NOT YET IMPLEMENTED</strong>";
		}
	}
	public function deleteAction()
	{
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		
		//check if $guid is an array
		if(is_array($guid))
		{
			echo "this is array: ";
			print_r($guid);
			die();
		}
		
		print_r($guid);
		die();
	}
}
?>