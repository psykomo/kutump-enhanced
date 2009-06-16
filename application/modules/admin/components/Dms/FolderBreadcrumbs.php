<?php
class Admin_Dms_FolderBreadcrumbs
{
	public $view;
	public $folderGuid;
	public $rootGuid;
	
	public function __construct($folderGuid, $rootGuid='')
	{
		$this->view = new Zend_View();
		$this->view->setScriptPath(dirname(__FILE__));
		
		$this->folderGuid = $folderGuid;
		$this->rootGuid = $rootGuid;
		
		$this->view();
	}
	public function view()
	{
		$browserUrl = KUTU_ROOT_URL . '/admin/dms/explore/node';
    	
    	$folderGuid = ($this->folderGuid)? $this->folderGuid : 'root';
    	
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
	public function render()
	{
		//$aName = explode('_', basename(__FILE__));
		
		return $this->view->render(str_replace('.php','.phtml',strtolower(basename(__FILE__))));
	}
}
?>