<?php
class Kutu_Sso_Session
{
	function start()
	{
		$saveHandlerManager = new Kutu_Session_SaveHandler_Manager();
		$saveHandlerManager->setSaveHandler();
		
		$flagSessionIdSent = false;
		
		if(Zend_Session::sessionExists())
		{
			Zend_Session::start();
			
		}
		else
		{
			echo "session has not been started";
			$sReturn = "http://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI'];
			$sReturn = urlencode($sReturn);
			header(KUTU_ROOT_URL.'/helper/sso/syncsession/?returnTo='.$sReturn);
		}
	}
}
?>