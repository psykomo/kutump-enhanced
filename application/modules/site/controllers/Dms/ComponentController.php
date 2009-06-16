<?php
class Site_Dms_ComponentController extends Zend_Controller_Action
{
	function preDispatch()
	{
		
	}
	public function documentAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->rowsetRelatedItem = $bpm->getFiles($guid);
	}
	public function sejarahAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->aData = $bpm->getSejarah($guid);
		
	}
	public function dasarhukumAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->rowsetDasarHukum = $bpm->getDasarHukum($guid);
		
	}
	public function pelaksanaAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->rowsetPelaksana = $bpm->getPeraturanPelaksana($guid);
	}
	public function othersAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->aData = $bpm->getOthers($guid);
	}
	public function foldersAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Catalog();
		$this->view->rowsetFolder = $bpm->getFolders($guid);
	}
	public function translationsAction()
	{
		$r = $this->getRequest();
		$guid = $r->getParam('g');
		$this->view->catalogGuid = $guid;
		
		$bpm = new Kutu_Core_Bpm_Relation();
		$this->view->aData = $bpm->getTranslations($guid);
	}
	public function relationboxAction()
	{
		
	}
	public function breadcrumbsAction()
	{
		$r = $this->getRequest();
		$browserUrl = KUTU_ROOT_URL . '/dms';
    	
    	$folderGuid = ($r->getParam('node'))? $r->getParam('node') : 'root';
    	
    	Zend_Loader::loadClass('Kutu_Core_Orm_Table_Folder');
    	$tblFolder = new Kutu_Core_Orm_Table_Folder();
    	
    	
    	$aPath = array();
    	
    	if($folderGuid == 'root')
    	{
    		$aPath[0]['title'] = 'Root';
    		$aPath[0]['url'] = $browserUrl;
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
	    		$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
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
    			$aPath[0]['title'] = 'Root';
    			$aPath[0]['url'] = $browserUrl;
    			$aPath[1]['title'] = $rowFolder->title;
    			$aPath[1]['url'] = $browserUrl.'/'.$rowFolder->guid;
    		}
    		//$this->_helper->layout()->mainFolderTitle = $rowFolder->title;
    		
    	}
    	
    	$this->view->aPath = $aPath;
	}
	public function viewfolderAction()
	{
		$time_start = microtime(true);
		
		$r = $this->getRequest();
    	
    	$folderGuid = ($r->getParam('node'))? $r->getParam('node') : 'root';
    	
    	$parentGuid = $folderGuid;
        
        
        $columns = 3;
		
        //Zend_Loader::loadClass('Kutu_Core_Orm_Table_Folder');
        
        $tblFolder = new Kutu_Core_Orm_Table_Folder();
        $rowsetFolder = $tblFolder->fetchChildren($parentGuid);
        
//        $db = Zend_Db_Table::getDefaultAdapter();
//        if($parentGuid=='root')
//        	$query = "select * from KutuFolder where parentGuid=guid";
//        else
//        	$query = "select * from KutuFolder where parentGuid='$parentGuid' AND NOT parentGuid=guid";
//        $rs = $db->query($query);
//        $rowsetFolder = $rs->fetchAll(PDO::FETCH_OBJ);
		
		$num_rows = count($rowsetFolder);
		$rows = ceil($num_rows / $columns);
		
		if($num_rows < 3)
			$columns = $num_rows;
		if($num_rows==0)
		{
			//echo 'No folder(s) found';
		}
		
		$kucrut = 0;
		$data = array();
		foreach ($rowsetFolder as $rowFolder)
		//for($kucrut=0;$kucrut<$num_rows;$kucrut++)
		{
			//$rowFolder = $rowsetFolder[$kucrut];
			
			$data[$kucrut][0] = $rowFolder->title;
			
			$data[$kucrut][1] = $rowFolder->description; 
			$data[$kucrut][2] = $rowFolder->guid; 
			
			$data[$kucrut][3] = ''; 
			$kucrut++;
			
		}
		
		$this->view->rows = $rows;
		$this->view->columns = $columns;
		$this->view->data = $data;
		$this->view->numberOfFolders = $num_rows;
		$this->view->node = $parentGuid;
		
		$time_end = microtime(true);
		$time = $time_end - $time_start;
		
		//echo'<br>WAKTU EKSEKUSI: '. $time;
	}
}
?>