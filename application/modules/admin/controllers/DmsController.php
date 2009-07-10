<?php
class Admin_DmsController extends Zend_Controller_Action
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
			
			$acl = Kutu_Acl::manager();
			if(!($acl->checkAcl("site",'all','user', $username, false,false)) && !($acl->checkAcl("site",'admin_dms','user', $username, false,false)))
			{
				$this->_forward('restricted', "error", 'admin');
			}
		}
		
    }

	public function browseORIAction()
	{
		$r = $this->getRequest();
		$node = $r->getParam('node');
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/user/CatalogPager.php');
		$w = new CatalogPager(2);
		$this->view->catalogPager = $w->render();
	}
	public function browseAction()
	{
		@$cart =& $_SESSION['jCart'];
		$cart = null;
		
		//$this->_helper->layout()->setLayout('layout-fb');
		$this->_helper->viewRenderer->setScriptAction('browse-hol');
		
		$this->_helper->viewRenderer->setScriptAction('browse');
	}
	public function exploreAction()
	{
		@$cart =& $_SESSION['jCart'];
		$cart = null;
		
		$r = $this->getRequest();
		
		$node = ($r->getParam('node')?$r->getParam('node'):'root');
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/ViewFolder.php');
		$w = new Admin_Dms_ViewFolder($node);
		$this->view->widget1 = $w;
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/FolderBreadcrumbs.php');
		$w = new Admin_Dms_FolderBreadcrumbs($node);
		$this->view->widget2 = $w;
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/TreeSlideMenu.php');
		$w3 = new Admin_Dms_TreeSlideMenu();
		$this->view->slideMenu = $w3;
		
		$this->view->currentNode = $node;
		
		
		//View catalogs
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):12;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		
		$sort = ($r->getParam('sort'))?$r->getParam('sort'):"modifiedDate desc";  //"regulationType desc, year desc";
		$this->view->sort = $sort;
		
		$db = Zend_Db_Table::getDefaultAdapter()->query
		("SELECT catalogGuid as guid from KutuCatalogFolder where folderGuid='$node'");
		$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
		
		$solrAdapter = Kutu_Search::manager();
		
		$numi = count($rowset);
		$sSolr = "id:(";
		for($i=0;$i<$numi;$i++)
		{
			$row = $rowset[$i];
			$sSolr .= $row->guid .' ';
		}
		$sSolr .= ')';
		
		if(!$numi)
			$sSolr="id:(hfgjhfdfka)";
			
		$solrResult = $solrAdapter->findAndSort($sSolr,$offset,$limit, array($sort));
		$solrNumFound = count($solrResult->response->docs);
		$this->view->totalItems = $solrResult->response->numFound;
		$this->view->hits = $solrResult;
		
		$bpm = new Kutu_Core_Bpm_Catalog();
		$this->view->bpm = $bpm;
		
	}
	public function testAction()
	{
		//$this->_helper->layout()->disableLayout();
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/TreeSlideMenu.php');
		$w3 = new Admin_Dms_TreeSlideMenu();
		$this->view->slideMenu = $w3;
	}
	public function searchAction()
	{
		$this->view->pageTitle = "Search";
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		//$sessHistory->relatedGuid = ($this->_getParam('relatedGuid'))? $this->_getParam('relatedGuid') : $sessHistory->relatedGuid;
		//$this->view->relatedGuid = $sessHistory->relatedGuid;
		
		//echo "related: ".$this->view->relatedGuid;
		
		$r = $this->getRequest();
		$sQuery = ($r->getParam('sQuery'))?$r->getParam('sQuery'):'';
		$this->view->sQuery = $sQuery;
		$sOffset = $r->getParam('sOffset');
		$this->view->sOffset = $sOffset;
		$sLimit = $r->getParam('sLimit');
		$this->view->sLimit = $sLimit;
		
		$indexingEngine = Kutu_Search::manager();
    	
		if(empty($sQuery))
			$hits = $indexingEngine->find("fjkslfjdkfjls",$sOffset, $sLimit);
		else
			$hits = $indexingEngine->find($sQuery." -profile:kutu_doc",$sOffset, $sLimit);
		
		$this->view->hits = $hits;
	}
	public function searchFolderAction()
	{
		$this->view->pageTitle = "Search Folder";
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		//$sessHistory->relatedGuid = ($this->_getParam('relatedGuid'))? $this->_getParam('relatedGuid') : $sessHistory->relatedGuid;
		//$this->view->relatedGuid = $sessHistory->relatedGuid;
		
		//echo "related: ".$this->view->relatedGuid;
		
		$r = $this->getRequest();
		$sQuery = ($r->getParam('sQuery'))?$r->getParam('sQuery'):'';
		$this->view->sQuery = $sQuery;
		$sOffset = $r->getParam('sOffset');
		$this->view->sOffset = $sOffset;
		$sLimit = $r->getParam('sLimit');
		$this->view->sLimit = $sLimit;
		
		$indexingEngine = Kutu_Search::manager();
    	
		if(empty($sQuery))
			$hits = array();
		else
		{
			$sQuery1 = str_replace(' ','%', $sQuery);
			$db = Zend_Db_Table::getDefaultAdapter()->query("select * from KutuFolder where title LIKE '%$sQuery1%'");
			$rowset = $db->fetchAll(Zend_Db::FETCH_OBJ);
			
			$hits = $rowset;
		}
		
		$this->view->hits = $hits;
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Dms/FolderBreadcrumbs.php');
	}
	public function downloadAction()
	{
		$this->_helper->layout()->disableLayout();
    	//$this->view->addHelperPath(KUTU_ROOT_DIR.'/mix_lib/Kutu/View/Helper', 'Kutu_View_Helper');
    	
    	$req = $this->getRequest();
    	
		$catalogGuid = $req->getParam('guid');
    	
    	$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
    	$rowsetCatalog = $tblCatalog->find($catalogGuid);
    	
    	if(count($rowsetCatalog))
    	{
    		$rowCatalog = $rowsetCatalog->current();
    		$rowsetCatAtt = $rowCatalog->findDependentRowsetCatalogAttribute();
    		
	    	$contentType = $rowsetCatAtt->findByAttributeGuid('docMimeType')->value;
			$systemname = $rowsetCatAtt->findByAttributeGuid('docSystemName')->value;
			$filename = $rowsetCatAtt->findByAttributeGuid('docOriginalName')->value;
			
			$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
			$rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$catalogGuid' AND relateAs='RELATED_FILE'");
			
			$flagFileFound = false;
			
			foreach($rowsetRelatedItem as $rowRelatedItem)
			{
				if(!$flagFileFound)
				{
					$parentGuid = $rowRelatedItem->relatedGuid;
					$sDir1 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$systemname;
					$sDir2 = KUTU_ROOT_DIR.DIRECTORY_SEPARATOR.'uploads'.DIRECTORY_SEPARATOR.'files'.DIRECTORY_SEPARATOR.$parentGuid.DIRECTORY_SEPARATOR.$systemname;
					
					if(file_exists($sDir1))
					{
						$flagFileFound = true;
						
						header("Content-type: $contentType");
						header("Content-Disposition: attachment; filename=$filename");
						@readfile($sDir1);
					}
					else 
						if(file_exists($sDir2))
						{
							$flagFileFound = true;
							
							header("Content-type: $contentType");
							header("Content-Disposition: attachment; filename=$filename");
							@readfile($sDir2);
						}
						else 
						{
							echo 'No FILE';
							$flagFileFound = false;
						}
				}
			}
			
    	}
    	else 
    	{
    		echo 'NO FILE';
    	}
	}
}
?>