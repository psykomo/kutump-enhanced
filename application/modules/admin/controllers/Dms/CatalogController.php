<?php
class Admin_Dms_CatalogController extends Kutu_Controller_Action
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
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/mix_lib/Kutu/View/Helper', 'Kutu_View_Helper'); 
		
		$r = $this->getRequest();
		$folderGuid = $r->getParam('node');
		$profileGuid = $r->getParam('profile');
		$generatorForm = new Kutu_Form_Helper_CatalogInputGenerator();
		$aRender = $generatorForm->generateFormAdd(strtolower($profileGuid), $folderGuid);
		
		$this->view->aRenderedAttributes = $aRender;
		
		if($r->isPost())
		{
			//print_r($_POST);
			$this->save();
			
			
		}
	}
	public function editAction()
	{
		$r = $this->getRequest();
		$catalogGuid = ($this->_getParam('guid'))? $this->_getParam('guid') : '';
		
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->currentNode = ($this->_getParam('node'))? $this->_getParam('node') : $sessHistory->currentNode;
		$this->view->currentNode = $sessHistory->currentNode;
		
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		$message = "";
		
		if($r->isPost())
		{
			//print_r($_POST);
			$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
			$urlReferer = $sessHistory->urlReferer;
			
			$this->save();
			$message = "Data was successfully saved.";
			
		}
		
		$gen = new Kutu_Form_Helper_CatalogInputGenerator();
		$aRender = $gen->generateFormEdit($catalogGuid);
		$this->view->aRenderedAttributes = $aRender;
		
		$this->view->message = $message;
		
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->urlReferer = $urlReferer;
		$this->view->urlReferer = $sessHistory->urlReferer;
	}
	private function save()
	{
		$Bpm = new Kutu_Core_Bpm_Catalog();
		$request = $this->getRequest();
		$aData = $request->getParams();
		$auth = Zend_Auth::getInstance();
        
		if (!$auth->hasIdentity()) 
        { 
        	die('You are not login or your session is expired. Please login.');
        }
		else
		{
			$aData['username'] = $auth->getIdentity()->username;
		}
		print_r($aData);
		//die();
		$Bpm->save($aData);
		 
	}
	public function detailsAction()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');
		$r = $this->getRequest();
		$catalogGuid = $r->getParam('guid');
		$this->view->catalogGuid = $catalogGuid;
		
		$folderGuid = $r->getParam('node');
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($catalogGuid)->current();
		$this->view->rowCatalog = $rowCatalog;
		$rowsetAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		$rowTitle = $rowsetAttribute->findByAttributeGuid('fixedTitle');
		$this->view->catalogTitle = $rowTitle->value;
		
		$rowSubTitle = $rowsetAttribute->findByAttributeGuid('fixedSubTitle');
		$this->view->catalogSubTitle = $rowSubTitle->value;
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/Catalog/DetailsViewer.php');
		$w = new Admin_Dms_Catalog_DetailsViewer($catalogGuid, 'root');
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
		$w2 = new Admin_Dms_FolderBreadcrumbs($folderGuid);
		$this->view->widget2 = $w2;
		
		require_once($modDir.'/components/Dms/Relation/OtherViewer.php');
		$w3 = new Admin_Dms_Relation_OtherViewer($catalogGuid);
		$this->view->widget3 = $w3;
		
		require_once($modDir.'/components/Dms/Relation/FolderViewer.php');
		$w4 = new Admin_Dms_Relation_FolderViewer($catalogGuid);
		$this->view->widget4 = $w4;
		
		require_once($modDir.'/components/Dms/Relation/SejarahViewer.php');
		$w5 = new Admin_Dms_Relation_SejarahViewer($catalogGuid);
		$this->view->widgetSejarah = $w5;
		
		require_once($modDir.'/components/Dms/Relation/DasarHukumViewer.php');
		$w6 = new Admin_Dms_Relation_DasarHukumViewer($catalogGuid);
		$this->view->widgetDasarHukum = $w6;
		
		require_once($modDir.'/components/Dms/Relation/PelaksanaViewer.php');
		$w7 = new Admin_Dms_Relation_PelaksanaViewer($catalogGuid);
		$this->view->widgetPelaksana = $w7;
		
		require_once($modDir.'/components/Dms/Relation/TranslationViewer.php');
		$w8 = new Admin_Dms_Relation_TranslationViewer($catalogGuid);
		$this->view->widgetTranslation = $w8;
		
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		/*$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_FILE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		$this->view->rowsetRelatedItem = $rowsetRelatedItem;*/
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->rowsetRelatedItem = $bpm->getFiles($catalogGuid);
	}
	
	public function moveAction()
	{
		
	}
	public function deleteAction()
	{
		$r = $this->getRequest();
		
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		$catalogGuid = $r->getParam('guid');
		$bpm = new Kutu_Core_Bpm_Catalog();
		
		if(is_array($catalogGuid))
		{
			foreach($catalogGuid as $guid)
			{
				try
				{
					$bpm->delete($guid);
				}
				catch(Exception $e)
				{
					throw new Zend_Exception($e->getMessage());
				}
			}
		}
		else
		{
			try
			{
				$bpm->delete($catalogGuid);
			}
			catch(Exception $e)
			{
				throw new Zend_Exception($e->getMessage());
			}
		}
		$this->view->message = "Catalogs have been deleted.";
	}
	public function doindexAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('guid');
		
		$registry = Zend_Registry::getInstance(); 
		$conf = $registry->get('config');
		
		//die($conf->indexing->adapter->param->dir);
		
		$indexingEngine = Kutu_Search::manager();
		$indexingEngine->indexCatalog($guid);
		die();
	}
	public function testindex1Action()
	{
		$indexingEngine = Kutu_Search::manager();
		
		$db = Zend_Db_Table_Abstract::getDefaultAdapter();
		$query="SELECT * FROM KutuCatalog where profileGuid != 'kutu_doc'";
		$results = $db->query($query);
		$rowset = $results->fetchAll(PDO::FETCH_OBJ);
		//print_r($results);
		foreach($rowset as $row)
		{
			try
			{
				$indexingEngine->indexCatalog($row->guid);
			}
			catch (Exception $e)
			{
				print_r('guid: '.$row->guid.' '.$e->getMessage());
			}
		}
		die();
	}
	public function quickreindexallAction()
	{
		$indexingEngine = Kutu_Search::manager();
		$indexingEngine->reIndexCatalog("NORMAL");
		die();
	}
	public function uploadfileAction()
	{
		
	}
	public function removefromfolderAction()
	{
		$req = $this->getRequest();
		$catalogGuid = $req->getParam('guid');
		$folderGuid = $req->getParam('folderGuid');
		
		$bpm = new Kutu_Core_Bpm_Catalog();
		
		try
		{
			$bpm->removeFromFolder($catalogGuid, $folderGuid);
			$this->view->message = "Data was deleted.";
		}
		catch (Exception $e)
		{
			$this->view->message = $e->getMessage();
		}
			
	}
	public function copytofolderAction()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');
		
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		if(empty($guid))
			throw new Zend_Exception('Catalog Guid can not be empty!');
		
		if(is_array($guid))
		{
			$sGuid = '';
			$sTitle = '';
			echo "this is array: ";
			for($i=0;$i<count($guid);$i++)
			{
				$sGuid .= $guid[$i].';';
				
				
				$sTitle .= Kutu_Core_Util::getCatalogAttributeValue( $guid[$i], "fixedTitle").', ';
			}
			$guid = $sGuid;
		}
		else
		{
			$sTitle = '';
			if(!empty($guid))
			{
				$sTitle .= Kutu_Core_Util::getCatalogAttributeValue( $guid, "fixedTitle");
			}
		}
		
		$this->view->catalogTitle = $sTitle;
		$this->view->guid = $guid;
		
		
		if($r->isPost())
		{
			$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
			$urlReferer = $sessHistory->urlReferer;
			
			$req = $this->getRequest();
			$targetNode = $req->getParam('targetNode');
			
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			
			if(is_array($r->getParam('guid')))
			{
				foreach($r->getParam('guid') as $tmpGuid)
				{
					$rowset = $tblCatalog->find($tmpGuid);
					if(count($rowset))
					{
						$row = $rowset->current();
						$row->copyToFolder($targetNode);
					}
				}
			}
			else
			{
				$rowset = $tblCatalog->find($r->getParam('guid'));
				if(count($rowset))
				{
					$row = $rowset->current();
					$row->copyToFolder($targetNode);
				}
			}
			$this->view->message = "Data was successfully saved.";
		}
		
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->urlReferer = $urlReferer;
		$this->view->urlReferer = $sessHistory->urlReferer;
	}
	public function movetofolderAction()
	{
		$this->view->addHelperPath(KUTU_ROOT_DIR.'/lib/Kutu/View/Helper', 'Kutu_View_Helper');
		
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		if(empty($guid))
			throw new Zend_Exception('Catalog Guid can not be empty!');
		$this->view->guid = $guid;
		
		if(is_array($guid))
		{
			$sGuid = '';
			$sTitle = '';
			echo "this is array: ";
			for($i=0;$i<count($guid);$i++)
			{
				$sGuid .= $guid[$i].';';
				
				
				$sTitle .= Kutu_Core_Util::getCatalogAttributeValue( $guid[$i], "fixedTitle").', ';
			}
			$guid = $sGuid;
		}
		else
		{
			$sTitle = '';
			if(!empty($guid))
			{
				$sTitle .= Kutu_Core_Util::getCatalogAttributeValue( $guid, "fixedTitle");
			}
		}
		
		$this->view->catalogTitle = $sTitle;
		$this->view->guid = $guid;
		
		$sourceNode = $r->getParam('sourceNode');
		$this->view->sourceNode = $sourceNode;
		
		
		if($r->isPost())
		{
			$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
			$urlReferer = $sessHistory->urlReferer;
			
			$req = $this->getRequest();
			$targetNode = $req->getParam('targetNode');
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			
			if(is_array($r->getParam('guid')))
			{
				foreach($r->getParam('guid') as $tmpGuid)
				{
					$rowset = $tblCatalog->find($tmpGuid);
					if(count($rowset))
					{
						$row = $rowset->current();
						$row->moveToFolder($sourceNode, $targetNode);
					}
				}
			}
			else
			{
				$rowset = $tblCatalog->find($r->getParam('guid'));
				if(count($rowset))
				{
					$row = $rowset->current();
					$row->moveToFolder($sourceNode, $targetNode);
				}
			}
			
		}
		
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->urlReferer = $urlReferer;
		$this->view->urlReferer = $sessHistory->urlReferer;
	}
	public function alterdateAction()
	{
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->find($guid);
		if(count($rowset))
		{
			$row = $rowset->current();
			$this->view->row = $row;
			$this->view->guid = $row->guid;
			$this->view->catalogTitle = Kutu_Core_Util::getCatalogAttributeValue($row->guid, 'fixedTitle');
			
			if($r->isPost())
			{
				$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
				$urlReferer = $sessHistory->urlReferer;
				
				$createdDate = $r->getParam('createdDate');
				$modifiedDate = $r->getParam('modifiedDate');
				
				$bpm = new Kutu_Core_Bpm_Catalog();
				$bpm->alterDate($r->getParams());
				$this->view->message = "Date has been altered.";
			}
			else
			{
				
			}
		}
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->urlReferer = $urlReferer;
		$this->view->urlReferer = $sessHistory->urlReferer;
	}
}
?>