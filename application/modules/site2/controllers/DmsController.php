<?php
class Site_DmsController extends Zend_Controller_Action
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
			//$this->_forward('about');
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
		$this->_helper->layout()->setLayout('layout-iht');
		
		$r = $this->getRequest();
		$node = ($r->getParam('node')?$r->getParam('node'):'lgs4a0ee4ab533b4');
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/ViewFolder.php');
		$w = new ViewFolder($node);
		$this->view->widget1 = $w;
		
		require_once($modDir.'/components/ViewCatalogsInFolder.php');
		$w2 = new Dms_ViewCatalogsInFolder($node);
		$this->view->widget2 = $w2;
		
		require_once($modDir.'/components/Dms/FolderBreadcrumbs.php');
		$w3 = new Dms_FolderBreadcrumbs($node);
		$this->view->widget3 = $w3;
	}
	public function detailsAction()
	{
		$this->_helper->layout()->setLayout('layout-iht');
		
		$r = $this->getRequest();
		$catalogGuid = $r->getParam('guid');
		$this->view->catalogGuid = $catalogGuid;
		
		$folderGuid = $r->getParam('node');
		
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($catalogGuid)->current();
		$rowsetAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		$rowTitle = $rowsetAttribute->findByAttributeGuid('fixedTitle');
		$this->view->catalogTitle = $rowTitle->value;
		
		$rowSubTitle = $rowsetAttribute->findByAttributeGuid('fixedSubTitle');
		$this->view->catalogSubTitle = $rowSubTitle->value;
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/Catalog/DetailsViewer.php');
		$w = new Dms_Catalog_DetailsViewer($catalogGuid, 'root');
		$this->view->widget1 = $w;
		
		if(empty($folderGuid))
		{
			$rowsetFolder = $rowCatalog->findManyToManyRowset('Kutu_Core_Orm_Table_Folder', 'Kutu_Core_Orm_Table_CatalogFolder');
			if(count($rowsetFolder)>0)
			{
				$rowFolder = $rowsetFolder->current();
				$folderGuid = $rowFolder->guid;
			}
		}
		
		require_once($modDir.'/components/Dms/FolderBreadcrumbs.php');
		$w2 = new Dms_FolderBreadcrumbs($folderGuid);
		$this->view->widget2 = $w2;
	}
	public function aboutAction()
	{
		
	}
	public function searchAction()
	{
		$this->_helper->layout()->setLayout('layout-iht');
		
		$r = $this->getRequest();
		$sQuery = $r->getParam('sQuery');
		$this->view->sQuery = $sQuery;
		$sOffset = $r->getParam('sOffset');
		$this->view->sOffset = $sOffset;
		$sLimit = $r->getParam('sLimit');
		$this->view->sLimit = $sLimit;
		
		$indexingEngine = Kutu_Search::manager();
    	
		$hits = $indexingEngine->find($sQuery,$sOffset, $sLimit);
		
		$this->view->hits = $hits;
		
		//print_r($hits);
	}
}
?>