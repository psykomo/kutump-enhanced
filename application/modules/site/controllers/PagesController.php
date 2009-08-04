<?php
class Site_PagesController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$this->_helper->layout()->setLayout('layout-final');
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
		$this->view->currentNode = $guid;
		
		
		
		//Get ready ZEND paginator 
		
		$limit = ($r->getParam('limit'))?$r->getParam('limit'):12;
		$this->view->limit =$limit;
		$currentPage = $this->_getParam('page',1);
		$sort = ($r->getParam('sort'))?$r->getParam('sort'):"createdDate desc";  //"regulationType desc, year desc";
		$this->view->sort = $sort;
		
		//----
		
		
		$offset = ($currentPage-1) * $limit;
		
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
			$this->view->catalogGuid = $solrResult->response->docs[0]->id;
			
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			$row = $tblCatalog->find($solrResult->response->docs[0]->id)->current();
			$this->view->row = $row;
		}
		
		
		//ZEND PAGINATOR
		
		$adapter = new Zend_Paginator_Adapter_Null($solrResult->response->numFound);
		
		$paginator = new Zend_Paginator($adapter);
		$paginator->setCurrentPageNumber($currentPage);
		$paginator->setItemCountPerPage($limit);
		$this->view->paginator = $paginator;
		
	}
	//list2 action will render layout with left menu
	public function list2Action()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('f');
		$g = $r->getParam('g');
		$guid = (!empty($g))?$r->getParam('g'):$guid;
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$row = $tblFolder->find($guid)->current();
		
		$this->view->listTitle = $row->title;
		$this->view->pageTitle = $row->title;
		
		
		
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
			$this->view->catalogGuid = $solrResult->response->docs[0]->id;
			
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			$row = $tblCatalog->find($solrResult->response->docs[0]->id)->current();
			$this->view->row = $row;
		}
	}
	public function indexORIAction()
	{
		$r = $this->getRequest();
		
		$folderLegalDb = "lgs4a0ee4ab533b4";
		
		switch($r->getParam('m'))
		{
			case 'list':
				if($r->getParam('f'))
				{
					$folderGuid = $r->getParam('f');
					$tblFolder = new Kutu_Core_Orm_Table_Folder();
					$row = $tblFolder->find($folderGuid)->current();
					if(strpos($row->path, $folderLegalDb)!==false)
					{
						//$this->_forward('list', "pages", 'site', $r->getParams);
						$this->_redirect(KUTU_ROOT_URL.'/dms/'.$folderGuid);
					}
				}
				$this->_forward('list', "pages", 'site', $r->getParams());
				break;
			case 'home':
				$this->_forward('index', "index", 'site', $r->getParams());
				break;
			default:
				$guid = $r->getParam('g');
				if(!empty($guid))
				{
					$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
					$rowset = $tblCatalog->find($guid);
					if(count($rowset))
					{
						$row = $rowset->current();
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
									//$this->_forward('list', "pages", 'site', $r->getParams);
									//$this->_redirect(KUTU_ROOT_URL.'/dms/catalog/'.$guid.'/node/'.$folderGuid);
								}
								else
								{
									$this->_forward('details', "pages", 'site', $r->getParams());
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
						$tblFolder = new Kutu_Core_Orm_Table_Folder();
						$rowset = $tblFolder->find($guid);
						if(count($rowset))
						{
							$row = $rowset->current();
							
							if(strpos($row->path, $folderLegalDb)!==false)
							{
								//$this->_forward('list', "pages", 'site', $r->getParams);
								$this->_redirect(KUTU_ROOT_URL.'/dms/'.$guid);
							}
							else
							{
								$aData = $r->getParams();
								$aData['f'] = $guid;
								$this->_forward('list', "pages", 'site', $aData);
								break;
							}
						}
					}
				}
				
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
			$this->_forward('index', "index", 'site', $r->getParams());
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
					$this->_forward('list', "pages", 'site', $r->getParams());
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
						$this->_forward('list', "pages", 'site', $aData['p']);
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
				$bpm->updateNumberOfViews($row->guid);
				
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
							$this->_forward('generic', "pages_details", 'site', $aParams);
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
}
?>