<?php
class Kutu_View_Helper_GetViewUrl
{
	public function getViewUrl()
	{
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		
		
		$module  = $request->getModuleName();
		
		return KUTU_APP_URL . '/modules/'.$module.'/views';
	
	}
}
?>