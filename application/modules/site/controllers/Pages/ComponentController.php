<?php
class Site_Pages_ComponentController extends Zend_Controller_Action
{
	function preDispatch()
	{
		//$this->_helper->layout()->disableLayout();
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		Zend_Session::start();
		
		
		$auth =  Zend_Auth::getInstance();
		if(!$auth->hasIdentity())
		{
			//$this->view->username = $username = "";
		}
		
		
	}
	public function documentAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_RelatedItem');
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		
		$where = "relatedGuid='$guid' AND relateAs='RELATED_FILE'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		$this->view->rowsetRelatedItem = $rowsetRelatedItem;
	}
	public function breadcrumbsAction()
	{
		$r = $this->getRequest();
		$browserUrl = KUTU_ROOT_URL . '/pages/g';
    	
    	$folderGuid = ($r->getParam('node'))? $r->getParam('node') : 'root';
    	
    	Zend_Loader::loadClass('Kutu_Core_Orm_Table_Folder');
    	$tblFolder = new Kutu_Core_Orm_Table_Folder();
    	
    	
    	$aPath = array();
    	
    	if($folderGuid == 'root')
    	{
    		$aPath[0]['title'] = 'Home';
    		$aPath[0]['url'] = KUTU_ROOT_URL . '/pages';
    		//$this->_helper->layout()->mainFolderTitle = 'Browse Folder';
    	}
    	else 
    	{
    		$rowFolder = $tblFolder->find($folderGuid)->current();
    		//echo 'test' . $rowFolder->path;
    		if(!empty($rowFolder->path))
    		{
	    		$aFolderGuid = explode("/", $rowFolder->path);
	    		$sPath = 'root >';
	    		$aPath[0]['title'] = 'Home';
    			$aPath[0]['url'] = KUTU_ROOT_URL . '/pages';
    			$i = 1;
	    		if(count($aFolderGuid))
	    		{
	    			//print_r($aFolderGuid);
	    			$sPath1 = '';
	    			foreach ($aFolderGuid as $guid)
	    			{
	    				if(!empty($guid))
	    				{
	    					$rowFolder1 = $tblFolder->find($guid)->current();
	    				 	$sPath1 .= $rowFolder1->title . ' > ';
	    				 	$aPath[$i]['title'] = $rowFolder1->title;
    						$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder1->guid;
	    				 	$i++;
	    				}
	    			}
	    			
	    			//echo $sPath . $sPath1 . $rowFolder->title;
	    			$aPath[$i]['title'] = $rowFolder->title;
					$aPath[$i]['url'] = $browserUrl.'/'.$rowFolder->guid;
	    		}
	    		
    		}
    		else 
    		{
    			//echo "root > ". $rowFolder->title;
    			$aPath[0]['title'] = 'Home';
    			$aPath[0]['url'] = KUTU_ROOT_URL . '/pages';
    			$aPath[1]['title'] = $rowFolder->title;
    			$aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
    		}
    		//$this->_helper->layout()->mainFolderTitle = $rowFolder->title;
    		
    	}
    	
    	$this->view->aPath = $aPath;
	}
}
?>