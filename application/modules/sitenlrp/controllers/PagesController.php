<?php
class Sitenlrp_PagesController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$this->_helper->layout()->setLayout('layout-nosidebar');
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
		
		$sMenuAboutUs = '<ul class="sf-menu">';
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowset = $tblFolder->fetchChildren('lgs4a1bb86c12807');	
		foreach($rowset as $row)
		{
			$sMenuAboutUs .= $this->_traverseFolder($row->guid,'',0);
		}
		
		
		$this->view->sMenuAboutUs = $sMenuAboutUs.'</ul>';
		
		$sMenuPrograms = '<ul class="sf-menu">';
		$rowset = $tblFolder->fetchChildren('lgs4a1c2bf0b0a6a');	
		foreach($rowset as $row)
		{
			$sMenuPrograms .= $this->_traverseFolder($row->guid,'',0);
		}
		
		
		$this->view->sMenuPrograms = $sMenuPrograms.'</ul>';
		
		$sMenuMediaRoom = '<ul class="sf-menu">';
		$rowset = $tblFolder->fetchChildren('nlrp4a1c354d26b45');	
		foreach($rowset as $row)
		{
			$sMenuMediaRoom .= $this->_traverseFolder($row->guid,'',0);
		}
		$this->view->sMenuMediaRoom = $sMenuMediaRoom.'</ul>';
	}
	public function listAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('f');
		$g = $r->getParam('g');
		$guid = (!empty($g))?$r->getParam('g'):$guid;
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$row = $tblFolder->find($guid)->current();
		
		$this->view->listTitle = $row->title;
		$this->view->pageTitle = $row->title;
		$this->view->guid = $guid;
		$bpm = new Kutu_Cms_Bpm_Menu();
		$this->view->bpmCms = $bpm;
		
		
		
		//View catalogs
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):12;
		$this->view->limit =$limit;
		$itemsPerPage = $limit;
		$this->view->itemsPerPage = $itemsPerPage;
		$offset = ($r->getParam('offset'))?$r->getParam('offset'):0;
		$this->view->offset = $offset;
		$this->view->currentNode = $guid;
		
		$sort = ($r->getParam('sort'))?$r->getParam('sort'):"createdDate desc";  //"regulationType desc, year desc";
		$this->view->sort = $sort;
		
		$db = Zend_Db_Table::getDefaultAdapter()->query
		("SELECT catalogGuid as guid from KutuCatalogFolder where folderGuid='$guid'");
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
		//print_r($solrResult);die('gg');
		$solrNumFound = count($solrResult->response->docs);
		$this->view->totalItems = $solrResult->response->numFound;
		$this->view->hits = $solrResult;
		
		
		if($r->getParam('heading'))
		{
			/*$modDir = $this->getFrontController()->getModuleDirectory();
			require_once($modDir.'/components/Pages/DetailsViewer.php');
			$w = new Site_Pages_DetailsViewer($solrResult->response->docs[0]->id, 'root');
			$this->view->widget1 = $w;*/
			
			$this->view->showHeadline = 1;
			$this->view->catalogGuid = @$solrResult->response->docs[0]->id;
			
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			$row = $tblCatalog->find(@$solrResult->response->docs[0]->id)->current();
			$this->view->row = $row;
		}
	}
	
	public function indexAction()
	{
		//$this->view->render(
		$r = $this->getRequest();

		$folderLegalDb = "lgs4a0ee4ab533b4";
		
		$guid = $r->getParam('g');
		
		if(empty($guid))
		{
			$this->_forward('home', "pages", 'sitenlrp', $r->getParams());
			return true;
		}
		if($guid=='search')
		{
			$this->_forward('search', "pages", 'sitenlrp', $r->getParams());
			return true;
		}
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowset = $tblFolder->find($guid);
		if(count($rowset))
		{
			$row = $rowset->current();
			$json = new Zend_Json();
			if(empty($row->cmsParams))
			{
				//check if folder is child of folderLegalDb
				if(strpos($row->path, $folderLegalDb)===false)
				{
					$aParam = array('heading'=>1);
					$aParam = array_merge($aParam, $r->getParams());
					$this->_forward('list', "pages", 'sitenlrp', $aParam);
				}
				else
				{
					$this->_redirect(KUTU_ROOT_URL.'/dms/'.$row->guid);
				}
				
				
				return true;
				//die();
			}
			else
			{
				$aData = $json->decode($row->cmsParams);
				if(!empty($aData['a']) && !empty($aData['c']) && !empty($aData['m']))
				{
					$aData['p'] = array_merge($aData['p'], $r->getParams());
					$this->_forward($aData['a'], $aData['c'], $aData['m'], $aData['p']);
					return true;
				}
				else
				{
					$aData['p'] = array_merge($aData['p'], $r->getParams());
					//check if folder is child of folderLegalDb
					if(strpos($row->path, $folderLegalDb)===false)
					{
						$this->_forward('list', "pages", 'sitenlrp', $aData['p']);
					}
					else
					{
						$this->_redirect(KUTU_ROOT_URL.'/dms/'.$row->guid);
					}
					return true;
				}
				
			}
		}
		else
		{
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			$rowset = $tblCatalog->find($guid);
			if(count($rowset))
			{	
				$row = $rowset->current();
				
				//update number of downloads and number of views
				$bpm = new Kutu_Core_Bpm_Catalog();
				//$bpm->updateNumberOfViews($row->guid);
				
				switch($row->profileGuid)
				{
					case 'kutu_peraturan':
					case 'kutu_putusan':
						$this->_redirect(KUTU_ROOT_URL.'/dms/catalog/'.$guid);
						return true;
				}
				
				$rowsetFolder = $row->findManyToManyRowset('Kutu_Core_Orm_Table_Folder', 'Kutu_Core_Orm_Table_CatalogFolder');
				if(count($rowsetFolder)>0)
				{
					foreach($rowsetFolder as $rowFolder)
					{
						//$rowFolder = $rowsetFolder->current();
						$folderGuid = $rowFolder->guid;
					
						if(strpos($rowFolder->path, $folderLegalDb)!==false)
						{
							//do nothing
						}
						else
						{
							$aParams = $r->getParams();
							$aParams['node'] = $folderGuid;
							
							//Should forward to specific controller based on catalog's profileGuid
							$this->_forward('generic', "pages_details", 'sitenlrp', $aParams);
							return true;
						}
					}
					$this->_redirect(KUTU_ROOT_URL.'/dms/catalog/'.$guid.'/node/'.$folderGuid);
				}
				else
				{
					$this->_redirect(KUTU_ROOT_URL.'/dms/catalog/'.$guid.'/node/'.$folderGuid);
				}
			}
			else
			{
				
			}
		}
			
	}
	public function homeAction()
	{
		$this->view->pageTitle = 'Home';
		$this->_helper->layout()->setLayout('layout-nosidebar');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchFromFolder('nlrp4a35276c91693', 0,1);
		if(count($rowset))
		{
			$row = $rowset->current();
			$this->view->row = $row;
			
			/*$modDir = $this->getFrontController()->getModuleDirectory();
			require_once($modDir.'/components/Pages/DetailsViewer.php');
			$w = new Site_Pages_DetailsViewer($solrResult->response->docs[0]->id, 'root');
			$this->view->widget1 = $w;

			$this->view->showHeadline = 1;
			$this->view->listTitle = Kutu_Core_Util::getCatalogAttributeValue($solrResult->response->docs[0]->id, 'fixedTitle');*/
		}
		
		$cms = new Kutu_Cms_Bpm_Folder();
		$this->view->rows = $cms->fetchCatalogs('nlrp4a40810bd0d63', 0, 5);
	}
	protected function _traverseFolder($folderGuid, $sGuid, $level)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Folder');
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowSet = $tblFolder->fetchChildren($folderGuid);
		$row = $tblFolder->find($folderGuid)->current();
		$sGuid = '';
			if(count($rowSet))
			{
				$sGuid = '<li>'."<a href='".KUTU_ROOT_URL."/pages/g/$row->guid/heading/1'>".$row->title.'</a><ul>';
			}
			else
			{
				$sGuid = '<li>'."<a href='".KUTU_ROOT_URL."/pages/g/$row->guid/h/1'>".$row->title.'</a>';
			}
		
		if(true)
		{
			//echo $level;
			foreach($rowSet as $row)
			{
				//$sTab = '<ul>';
				//$sTab = '';
				//for($i=0;$i<$level;$i++)
					//$sTab .= '<li>';
				
				//$option = '<option value="'.$row->guid.'">'.$sTab.$row->title.'</option>';
				//$option = '"'.$row->guid.'" :'.'"'.$sTab.$row->title.'",';
				//$option = $sTab.$row->title;
				$sGuid .= $this->_traverseFolder($row->guid, '', $level+1)."";
			
				//$sGuid .= $sTab.$row->title . '|<br>'. $this->_traverseFolder($row->guid, '', $level+1);
			
			}
			if(count($rowSet))
			{
				return $sGuid.'</ul></li>';
			}
			else
			{
				return $sGuid.'</li>';
			}
		}
		
		
		
	}
	public function searchAction()
	{
		$r = $this->getRequest();
		
		
		
		$sQuery = ($r->getParam('sQuery'))?$r->getParam('sQuery'):'';
		$this->view->sQuery = $sQuery;
		$sOffset = $r->getParam('sOffset');
		$this->view->sOffset = $sOffset;
		$sLimit = $r->getParam('sLimit');
		$this->view->sLimit = $sLimit;

		$indexingEngine = Kutu_Search::manager();

		$hits = $indexingEngine->find($sQuery,$sOffset, $sLimit);

		$this->view->hits = $hits;

		$bpm = new Kutu_Core_Bpm_Catalog();
		$this->view->bpm = $bpm;
	}
}
?>