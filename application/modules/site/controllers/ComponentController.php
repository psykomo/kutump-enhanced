<?php
class Site_ComponentController extends Zend_Controller_Action
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
	public function mainmenuAction()
	{
		$bpm = new Kutu_Cms_Bpm_Menu();
		$rowsetMenu = $bpm->getMenu();
		$this->view->rowsetMenu = $rowsetMenu;
		
	}
	public function getmenuchildrenAction()
	{
		$r = $this->getRequest();
		
		$node = $r->getParam('node');
		
		$bpm = new Kutu_Cms_Bpm_Menu();
		$rowsetMenu = $bpm->getMenu($node);
		$this->view->rowsetMenu = $rowsetMenu;
	}
	protected function _traverseMenu($folderGuid, $sGuid, $level)
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
}
?>