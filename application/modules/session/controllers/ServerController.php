<?php
class Session_ServerController extends Zend_Controller_Action
{
	//this function will start the session, and send the session ID back to the sync session landing page
	public function startAction()
	{
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();

		Zend_Session::start();
		
		$r = $this->getRequest();
		$returnUrl = urldecode($r->getParam('returnTo'));
		
		if(strpos($returnUrl,'?'))
			$sAddition = '&';
		else 
			$sAddition = '?';
		
		header("location: $returnUrl".$sAddition."PHPSESSID=".Zend_Session::getId());
	}
}
?>