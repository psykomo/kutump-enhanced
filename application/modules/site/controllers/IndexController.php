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
		$this->_helper->layout()->setLayout('layout-final');
		$this->view->pageTitle = 'Home';
		
		$cms = new Kutu_Cms_Bpm_Folder();
		$this->view->rowsNews = $cms->fetchCatalogs('lgs4a1d77eb99e7a', 0, 5);
		
		$rowsMessage = $cms->fetchCatalogs('lgs4a1d79d321b76', 0, 1);
		
		$rowMessage = $rowsMessage[0];
		$this->view->rowMfromBoard = $rowMessage;
		
		
	}
	public function aboutAction()
	{
		
	}
}
?>