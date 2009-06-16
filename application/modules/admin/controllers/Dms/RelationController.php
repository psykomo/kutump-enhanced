<?php
class Admin_Dms_RelationController extends Kutu_Controller_Action
{
	function preDispatch() 
    { 
		//$front = Zend_Controller_Front::getInstance();
		//$front->registerPlugin(new Zend_Controller_Plugin_ErrorHandler());
		
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
	public function searchAction()
	{
		$this->view->pageTitle = "Search for Relation";
		$sessHistory = new Zend_Session_Namespace('BROWSER_HISTORY');
		$sessHistory->relatedGuid = ($this->_getParam('relatedGuid'))? $this->_getParam('relatedGuid') : $sessHistory->relatedGuid;
		$this->view->relatedGuid = $sessHistory->relatedGuid;
		
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
	public function newAction()
	{
		$this->view->message = "Nothing is saved";
		
		$req = $this->getRequest();
		$item = $req->getParam('guid');
		$relatedItem = $req->getParam('relatedGuid');
		$as = $req->getParam('relateAs');
		
		Zend_Loader::loadClass('Kutu_Core_Orm_Table_Catalog');
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		
		if(empty($relatedItem))
		{
			$this->view->message = "No relatedGuid specified!";
		}
		
		//check if $guid is an array
		if(is_array($item))
		{
			foreach($item as $guid)
			{
				echo "<br>".$guid;
				
				$rowCatalog = $tblCatalog->find($guid)->current();
				echo $rowCatalog->guid;
				$rowCatalog->relateTo($relatedItem, $as);
			}
			
		}
		else
		{
			$rowCatalog = $tblCatalog->find($item)->current();
			$rowCatalog->relateTo($relatedItem, $as);
			$this->view->message = "Data was successfully saved";
		}
	}
	public function removeAction()
	{
		$this->view->message = "";
		
		$req = $this->getRequest();
		$itemGuid = ($req->getParam('guid'))? $req->getParam('guid') : 'XXX';
		$relatedGuid = ($req->getParam('relatedGuid')) ? $req->getParam('relatedGuid') : 'XXX';
		$relateAs = ($req->getParam('relateAs')) ? $req->getParam('relateAs') : 'XXX';
		
		$bpm = new Kutu_Core_Bpm_Relation();
		if($bpm->delete($itemGuid, $relatedGuid, $relateAs))
		{
			$this->view->message = 'Relation Removed';
		}
		else 
		{
			$this->view->message = 'No Relation Removed';
		}
	}
}
?>