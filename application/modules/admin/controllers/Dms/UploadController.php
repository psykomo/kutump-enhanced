<?php
class Admin_Dms_UploadController extends Kutu_Controller_Action
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
	public function newAction()
	{
		$this->view->pageTitle = "Upload New File";
		$r = $this->getRequest();
		$relatedGuid = $r->getParam('relatedGuid');
		$this->view->relatedGuid = $relatedGuid;
		if(empty($relatedGuid))
			throw new Zend_Exception("relatedGuid can not be empty!");
		
		$message = '';
		
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		if($r->isPost())
		{
			$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
			$urlReferer = $sessHistory->urlReferer;
			
			$this->_save();
			$message = "File was successfully uploaded.";
		}
		$this->view->message = $message;
		
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->urlReferer = $urlReferer;
		$this->view->urlReferer = $sessHistory->urlReferer;
		
		
	}
	private function _save()
	{
		$bpm = new Kutu_Core_Bpm_Catalog();
		$r = $this->getRequest();
		$aData = $r->getParams();
		
		$bpm->uploadFile($aData, $aData['relatedGuid']);
	}
}
?>