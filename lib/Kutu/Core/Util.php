<?php

/**
 * module URL
 * 
 * @author Himawan Anindya Putra
 * @package Kutu
 * 
 */

class Kutu_Core_Util
{
	/**
	 * this will return URL where KUTU framework is installed.
	 * Example: http://localhost/kutu3, http://www.mydomain.com
	 *
	 */
	function getRootUrl($kutuRootDir)
	{
		$aPath = (pathinfo($kutuRootDir));
		
		//$serverHttpHost = '';
		
		$serverHttpHost = $_SERVER['HTTP_HOST'];
		$serverHttpHost = str_replace(':443','',$serverHttpHost);
		
		if (isset($_SERVER["HTTPS"]) && $_SERVER["HTTPS"] == "on")
		{
			//$serverHttpHost .= ':443';
			$httpPrefix = 'https://';
		}
		else 
		{
			$httpPrefix = 'http://';
		}
		
		$sTmpPathUrl = $serverHttpHost .'/'.$aPath['basename'];
		//$sTmpPathUrl = strstr($this->selfURLNoPort(), $sTmpPathUrl);
		//$sTmpPathUrl = strstr($this->selfURL(), $sTmpPathUrl);
		
		if(!empty($sTmpPathUrl))
			return $httpPrefix.$serverHttpHost.'/'.$aPath['basename'];
		else 
			return $httpPrefix.$serverHttpHost; 
	}
	
	function selfURL() 
	{ 
		$s = empty($_SERVER["HTTPS"]) ? '' 
				: ($_SERVER["HTTPS"] == "on") ? "s" 
				: ""; 
		$protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" 
				: (":".$_SERVER["SERVER_PORT"]); 
		return $protocol."://".$_SERVER['SERVER_NAME'].$port.$_SERVER['REQUEST_URI']; 
	} 
	function selfURLNoPort() 
	{ 
		$s = empty($_SERVER["HTTPS"]) ? '' 
				: ($_SERVER["HTTPS"] == "on") ? "s" 
				: ""; 
		$protocol = $this->strleft(strtolower($_SERVER["SERVER_PROTOCOL"]), "/").$s; 
		$port = ($_SERVER["SERVER_PORT"] == "80") ? "" 
				: (":".$_SERVER["SERVER_PORT"]); 
		return $protocol."://".$_SERVER['SERVER_NAME'].$_SERVER['REQUEST_URI']; 
	} 
	function strleft($s1, $s2) 
	{ 
		return substr($s1, 0, strpos($s1, $s2)); 
	}
	
	function getControllerUrl()
	{
		$front = Zend_Controller_Front::getInstance();
		$request = $front->getRequest();
		$module  = $request->getModuleName();
		$dirs    = $front->getControllerDirectory();
		if (empty($module) || !isset($dirs[$module])) {
			$module = $front->getFrontController()->getDispatcher()->getDefaultModule();
		}
		$baseDir = dirname($dirs[$module]);
		$kutuRootDir = str_replace("\\", "/", KUTU_ROOT_DIR);
		$baseDir = str_replace("\\", "/", dirname($baseDir));
		$baseDir = str_replace($kutuRootDir,'', $baseDir);
		$baseDir = dirname($baseDir);
		return KUTU_ROOT_URL . $baseDir.'/'.$module.'/'.$request->getControllerName();
	}
	static function getCatalogAttributeValue($catalogGuid, $attributeGuid)
	{
		/*$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($catalogGuid)->current();
		$rowsetAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		$rowTitle = $rowsetAttribute->findByAttributeGuid($attributeGuid);
		return $rowTitle->value;*/
		$db = Zend_Db_Table::getDefaultAdapter()->query("select value from KutuCatalogAttribute where catalogGuid='$catalogGuid' and attributeGuid='$attributeGuid'");
		return $db->fetchColumn(0);
	}
}