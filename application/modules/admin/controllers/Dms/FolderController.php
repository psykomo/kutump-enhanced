<?php
class Admin_Dms_FolderController extends Kutu_Controller_Action
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
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		$node= $r->getParam('node');
	
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$newRow = $tblFolder->createRow();
		
		if($node!='root')
		{
			$rowNode = $tblFolder->find($node)->current();
			$this->view->nodeTitle = $rowNode->title;
		}
		else
			$this->view->nodeTitle = 'ROOT';
		
		$message = '';
		
		if($r->isPost())
		{
			//die('post');
			
			$newRow->parentGuid = $node;
			$newRow->title = $r->getParam('title');
			$newRow->description = $r->getParam('description');
			$newRow->viewOrder = $r->getParam('viewOrder');
			$newRow->cmsParams = $r->getParam('cmsParams');
			$newRow->save();
			
			$message = 'Data was successfully saved.';
			
		}
		$this->view->row = $newRow;
		$this->view->node = $node;
		$this->view->message = $message;
	}
	public function editAction()
	{
		$r = $this->getRequest();
		
		$guid = $r->getParam('guid');
		$previousNode = $r->getParam('node');
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$rowFolder = $tblFolder->find($guid)->current();
		$message = '';
		
		
		if($r->isPost())
		{
			//die('post');
			$rowFolder->title = $r->getParam('title');
			$rowFolder->description = $r->getParam('description');
			$rowFolder->viewOrder = $r->getParam('viewOrder');
			$rowFolder->cmsParams = $r->getParam('cmsParams');
			$rowFolder->save();
			$message = 'Data was successfully saved.';
			
		}
		$this->view->row = $rowFolder;
		$this->view->previousNode = $previousNode;
		$this->view->message = $message;
	}
	public function moveAction()
	{
		$urlReferer = $_SERVER['HTTP_REFERER'];
		
		//$this->_helper->layout()->disableLayout();
		$r = $this->getRequest();
		
		$tblFolder = new Kutu_Core_Orm_Table_Folder();

		$guid = $r->getParam('guid');
		$message = '';
		

		//check if $guid is an array
		if(is_array($guid))
		{
			$sGuid = '';
			$sTitle = '';
			echo "this is array: ";
			for($i=0;$i<count($guid);$i++)
			{
				$sGuid .= $guid[$i].';';
				
				$rowFolder = $tblFolder->find($guid[$i])->current();
				$sTitle .= $rowFolder->title.', ';
			}
			$guid = $sGuid;
		}
		else
		{
			$sTitle = '';
			if(!empty($guid))
			{
				$rowFolder = $tblFolder->find($guid)->current();
				$sTitle .= $rowFolder->title;
			}
		}
		if($r->isPost())
		{
			$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
			$urlReferer = $sessHistory->urlReferer;
			
			print_r($_POST);
			$guid = $r->getParam('guid');
			$targetNode = $r->getParam('targetNode');
			if(is_array($guid))
			{
				foreach($guid as $folderId)
				{
					$row = $tblFolder->find($folderId)->current();
					$row->move($targetNode);
				}
			}
			else
			{
				$guid = $r->getParam('guid');
				$targetNode = $r->getParam('targetNode');
				$row = $tblFolder->find($guid)->current();
				$row->move($targetNode);
			}
			$message = "Data was successfully saved.";
		}
		
		$this->view->guid = $guid;
		$this->view->folderTitle = $sTitle;
		
		$backToNode = $r->getParam('backToNode');
		$this->view->backToNode = $backToNode;
		
		
		$rowFolder = $tblFolder->find($guid)->current();
		
		
		$this->view->row = $rowFolder;
		$this->view->message = $message;
		
		
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->urlReferer = $urlReferer;
		$this->view->urlReferer = $sessHistory->urlReferer;
		
		echo $this->view->urlReferer;
	}
	public function deleteAction()
	{
		$r = $this->getRequest();
		
		$folderGuid = $r->getParam('guid');
		$bpm = new Kutu_Core_Bpm_Folder();
		
		if(is_array($folderGuid))
		{
			foreach($folderGuid as $guid)
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
				$bpm->delete($folderGuid);
			}
			catch(Exception $e)
			{
				throw new Zend_Exception($e->getMessage());
			}
		}
		$this->view->message = "Folder(s) have been deleted.";
	}
	public function getchildreninjsonAction()
	{
		// Make sure nothing is cached
		header("Cache-Control: must-revalidate");
		header("Cache-Control: post-check=0, pre-check=0", false);
		header("Pragma: no-cache");
		header("Expires: ".gmdate("D, d M Y H:i:s", mktime(date("H")-2, date("i"), date("s"), date("m"), date("d"), date("Y")))." GMT");
		header("Last-Modified: ".gmdate("D, d M Y H:i:s")." GMT");

		// So that the loading indicator is visible
		sleep(1);

		// The id of the node being opened
		$id = $_REQUEST["id"];
		//echo $id;
		if($id == "0") {
			
			$tblFolder = new Kutu_Core_Orm_Table_Folder();
			$rowset = $tblFolder->fetchChildren('root');
			echo '['."\n";
			for($i=0;$i<count($rowset);$i++)
			{
				$row = $rowset->current();
			   if($i==(count($rowset)-1))
					echo "\t".'{ attributes: { id : "'.$row->guid.'" }, state: "closed", data: "'.$row->title.'" }'."\n";
				else
					echo "\t".'{ attributes: { id : "'.$row->guid.'" }, state: "closed", data: "'.$row->title.'" },'."\n";
				$rowset->next();
				
			}
			echo ']'."\n";
		}
		else {
			$tblFolder = new Kutu_Core_Orm_Table_Folder();
			$rowset = $tblFolder->fetchChildren($id);
			echo '['."\n";
			for($i=0;$i<count($rowset);$i++)
			{
				$row = $rowset->current();
			   if($i==(count($rowset)-1))
					echo "\t".'{ attributes: { id : "'.$row->guid.'" }, state: "closed", data: "'.$row->title.'" }'."\n";
				else
					echo "\t".'{ attributes: { id : "'.$row->guid.'" }, state: "closed", data: "'.$row->title.'" },'."\n";
				$rowset->next();
				
			}
			echo ']'."\n";
		}
		exit();
	}
	
}
?>