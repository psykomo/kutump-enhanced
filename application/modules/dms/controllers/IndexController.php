<?php
class Dms_IndexController extends Kutu_Controller_Action
{
	function preDispatch() 
    { 
		
    }
    public function indexAction()
    {
 		//$this->startSession();
		
		$sReturn = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$sReturn = urlencode($sReturn);
		$this->view->returnTo = $sReturn;
		
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		$this->view->loginUrl = $config->identity->login->url;
		$this->view->logoutUrl = $config->identity->logout->url;
		
		$auth =  Zend_Auth::getInstance();
		
		if($auth->hasIdentity())
		{
			$username = $auth->getIdentity()->username;
			echo $username;
		}
		else
		{
			echo 'tidak ada';
		}
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/ViewFolder.php');
		$w = new ViewFolder('root');
		echo $w->render();
		
		echo 'phpsessid:'.$_COOKIE['PHPSESSID'];
		
    }
	public function browseAction()
	{
		$r = $this->getRequest();
		$node = $r->getParam('node');
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/ViewFolder.php');
		$w = new ViewFolder($node);
		echo $w->render();
		
		require_once($modDir.'/components/ViewCatalogsInFolder.php');
		$w2 = new Dms_ViewCatalogsInFolder($node);
		echo $w2->render();
	}
	public function getjsonpagercatalogsinfolder()
	{
		$r = $this->getRequest();
		$node = $r->getParam('node');
		$page = $r->getParam('page');
		$limit = $r->getParam('limit');
		
	}
	public function newsAction()
	{
		$this->_helper->layout->setLayout('layout1');
	}
	public function news2Action()
	{
		$this->_helper->layout->setLayout('layout-nlrp-1');
	}
}
?>