<?php
class Site_Pages_DetailsController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$this->_helper->layout()->setLayout('layout-final-inside');
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
	public function genericAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($guid)->current();
		$this->view->row = $rowCatalog;
		
		$rowsetFolder = $rowCatalog->findManyToManyRowset('Kutu_Core_Orm_Table_Folder', 'Kutu_Core_Orm_Table_CatalogFolder');
		if(count($rowsetFolder)>0)
		{
			$rowFolder = $rowsetFolder->current();
			$folderGuid = $rowFolder->guid;
			$this->view->folderGuid = $folderGuid;
			$this->view->folderTitle = $rowFolder->title;
		}
	}
}
?>