<?php
class Site_DownloadController extends Zend_Controller_Action
{
	protected $_username;
	protected $_auth;
	
	function preDispatch()
	{
		$this->_helper->layout()->setLayout('layout-iht');
		
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		Zend_Session::start();
		
		$sReturn = "http://".$_SERVER['HTTP_HOST'].$_SERVER['REQUEST_URI'];
		$sReturn = urlencode($sReturn);
		$this->view->returnTo = $sReturn;
		
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		
		$auth =  Zend_Auth::getInstance();
		$this->_auth = $auth;
		if(!$auth->hasIdentity())
		{
			$this->_redirect(KUTU_ROOT_URL.'/helper/sso/login'.'?returnTo='.$sReturn);
		}
		else
		{
			// [TODO] else: check if user has access to admin page
			$username = $auth->getIdentity()->username;
			$this->view->username = $username;
			$this->_username = $username;
		}
		
	}
	public function indexAction()
	{
		$req = $this->getRequest();
    	$this->view->message = "We detected that you don't have yet access to this resource. If you believe you have one, please contact our support. Thank you.";
		$catalogGuid = $req->getParam('guid');
		
		// MUST CHECK IF USER HAS PERMISSION TO VIEW PARENT CATALOG
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
    	$rowsetCatalog = $tblCatalog->find($catalogGuid);
		if(count($rowsetCatalog))
    	{
			$rowCatalog = $rowsetCatalog->current();
			$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
			$rowsetRelatedItem = $tblRelatedItem->fetchAll("itemGuid='$catalogGuid' AND relateAs='RELATED_FILE'");
			
			$rowRelatedItem = $rowsetRelatedItem->current();
			
			if(!$this->_checkAccess($rowRelatedItem->relatedGuid))
			{
				$this->view->message = "We detected that you don't have yet access to this resource. If you believe you have one, please contact our support. Thank you.";
				return true;
			}
			
		}

		
		
		$this->_helper->layout()->disableLayout();
    	//$this->view->addHelperPath(KUTU_ROOT_DIR.'/mix_lib/Kutu/View/Helper', 'Kutu_View_Helper');
    	
    	
    	
    	
    	
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
						//update number of downloads
						$bpm = new Kutu_Core_Bpm_Catalog();
						$bpm->updateNumberOfDownloads($parentGuid);
						
						header("Content-type: $contentType");
						header("Content-Disposition: attachment; filename=$filename");
						@readfile($sDir1);
					}
					else 
						if(file_exists($sDir2))
						{
							$flagFileFound = true;
							//update number of downloads
							$bpm = new Kutu_Core_Bpm_Catalog();
							$bpm->updateNumberOfDownloads($parentGuid);
							//die();
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
	public function sampleAction()
	{
		$req = $this->getRequest();
    	//$this->view->message = "We detected that you don't have yet access to this resource. If you believe you have one, please contact our support. Thank you.";
		$catalogGuid = $req->getParam('guid');
		
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
    	$rowsetCatalog = $tblCatalog->find($catalogGuid);
		
		$this->_helper->layout()->disableLayout();
    	
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
						
						if(trim($contentType) == 'application/pdf')
							$this->_generatePdfSamplePage($sDir1);
						else
							echo 'NO SAMPLE PAGE';
					}
					else 
						if(file_exists($sDir2))
						{
							$flagFileFound = true;
						
							if(trim($contentType) == 'application/pdf')
								$this->_generatePdfSamplePage($sDir2);
							else
								echo 'NO SAMPLE PAGE';
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
	private function _generatePdfSamplePage($filePath)
	{
		error_reporting(E_ALL);
		//Zend_Loader::registerAutoload(false);
		
		//require_once('TCPDF.php');
		require_once('PdfTool/fpdf/fpdf.php');
		require_once('PdfTool/fpdi/fpdi.php');

		// initiate FPDI
		$pdf = new FPDI();
		// add a page
		$pdf->AddPage();
		// set the sourcefile
		$pageCount = $pdf->setSourceFile($filePath);
		//print_r($pageCount);
		//die();
		
		// import page 1
		$tplIdx = $pdf->importPage(1);
		// use the imported page and place it at point 10,10 with a width of 100 mm
		$pdf->useTemplate($tplIdx, 10, 10, 100);

		// now write some text above the imported page
		$pdf->SetFont('Arial');
		$pdf->SetTextColor(255,0,0);
		$pdf->SetXY(10, 10);
		$pdf->Write(0, "SAMPLE FOR VIEWING ONLY");

		$pdf->Output('newpdf.pdf', 'I');
		
		/*$pdf=new FPDF();
		$pdf->AddPage();
		$pdf->SetFont('Arial','B',16);
		$pdf->Cell(40,10,'Hello World!');
		$pdf->Output();*/
		die();
	}
	private function _checkAccess($itemGuid)
	{
		$bpm = new Kutu_Core_Bpm_Catalog();
		
		$acl = Kutu_Acl::manager();
		if($acl->checkAcl("site",'all','user', $this->_auth->getIdentity()->username, false,false))
				return true;
		
		if($bpm->getPrice($itemGuid)<=0)
		{
			// can be downloaded
			return true;
		}
		else
		{
			//check if the logged in user has once bought the parent Catalog
			return false;
		}
	}
}
?>