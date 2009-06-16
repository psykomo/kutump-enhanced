<?php
class Site2_PagesController extends Zend_Controller_Action
{
	function preDispatch()
	{
		$this->_helper->layout()->setLayout('layout');
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
	public function index1Action()
	{
		
	}
	public function homeAction()
	{
		$this->_helper->layout()->setLayout('layout-home');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowset = $tblCatalog->fetchFromFolder('lgs4a1bb7ec78d99', 0,1);
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
		
		
	}
	public function listAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('f');
		$g = $r->getParam('g');
		$guid = (!empty($g))?$r->getParam('g'):$guid;
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$row = $tblFolder->find($guid)->current();
		
		$this->view->folderTitle = $row->title;
		$this->view->currentNode = $guid;
		
		$modDir = $this->getFrontController()->getModuleDirectory();
		require_once($modDir.'/components/Pages/FolderBreadcrumbs.php');
		$wBread = new Site_Pages_FolderBreadcrumbs($guid, 'root');
		$this->view->breadcrumbs = $wBread;
		
		
		
		
		
		
		if($r->getParam('h'))
		{
			$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
			$rowset = $tblCatalog->fetchFromFolder($guid, 0,1);
			
			$modDir = $this->getFrontController()->getModuleDirectory();
			require_once($modDir.'/components/Pages/DetailsViewer.php');
			$w = new Site_Pages_DetailsViewer($rowset->current()->guid, 'root');
			$this->view->widget1 = $w;
			
			$this->view->showHeadline = 1;
			$this->view->listTitle = Kutu_Core_Util::getCatalogAttributeValue($rowset->current()->guid, 'fixedTitle');
		}
		else
		{
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
		}
	}
	public function indexAction()
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
				$this->_forward('list', "pages", 'site2', $r->getParams());
				break;
			case 'home':
				$this->_forward('home', "pages", 'site2', $r->getParams());
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
									$this->_forward('details', "pages", 'site2', $r->getParams());
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
								$this->_forward('list', "pages", 'site2', $aData);
								break;
							}
						}
					}
				}
				
		}
	}
	public function detailsAction()
	{
		die('Details Page');
	}
	public function testmenuAction()
	{
		/*echo '<ul class="sf-menu">';
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowset = $tblFolder->fetchChildren('lgs4a1bb86c12807');	
		foreach($rowset as $row)
		{
			echo $this->_traverseFolder($row->guid,'',0);
		}
		
		
		echo '</ul>';*/
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
				$sGuid = '<li>'."<a href='".KUTU_ROOT_URL."/pages/g/$row->guid/h/1'>".$row->title.'</a><ul>';
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
	protected function _traverseFolder1($folderGuid, $sGuid, $level)
	{
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Folder');
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowSet = $tblFolder->fetchChildren($folderGuid);
		$row = $tblFolder->find($folderGuid)->current();
		$sGuid = '';
		if(count($rowSet))
		{
			$sGuid = '<li>'."<a href='KUTU_ROOT_URL/pages/g/$row->guid/h/1'>".$row->title.'</a><ul>';
		}
		else
		{
			$sGuid = '<li>'."<a href='KUTU_ROOT_URL/pages/g/$row->guid/h/1'>".$row->title.'</a>';
		}
		
		if($level<1)
		{
			echo $level;
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
?>