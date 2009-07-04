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
	
	static function formatDateTimeFromMySql($datetime)
	{
		$time = strtotime($datetime);
		//return date('Y-m-d \a\t H:i', $time);
		return date('l, F d, Y', $time);
	}
	static function splitWordsFromCatalog($catalogGuid, $iLimit)
	{
		$desc = Kutu_Core_Util::getCatalogAttributeValue($catalogGuid, 'fixedDescription');
		$content = Kutu_Core_Util::getCatalogAttributeValue($catalogGuid, 'fixedContent');
		
		$desc = Zend_Search_Lucene_Document_Html::loadHTML($desc);
		$content = Zend_Search_Lucene_Document_Html::loadHTML($content);
		
		$desc = $desc->getFieldValue('body');
		$content = $content->getFieldValue('body');
		
		if(!empty($desc))
		{
			if($iLimit > str_word_count($desc))
				return $desc;
			else
			{
				return Kutu_Core_Util::getNumberOfWords($desc, $iLimit);
			}
		}
		if(!empty($content))
		{
			if($iLimit > str_word_count($content))
				return $content;
			else
			{
				return Kutu_Core_Util::getNumberOfWords($content, $iLimit);
			}
		}
		return '';
	}
	static function getNumberOfWords($sSentences, $iNumberOfWords)
	{
		$sReturn = $sSentences;
		
		$arr = preg_split("/[\s]+/", $sReturn,$iNumberOfWords+1);
		$arr = array_slice($arr,0,$iNumberOfWords);
		return join(' ',$arr);
	
	}
	function wordCount($string){
	     $words = "";
	     $string = eregi_replace(" +", " ", $string);
	     $array = explode(" ", $string);
	     for($i=0;$i < count($array);$i++)
	   {
	         if (eregi("[0-9A-Za-zÀ-ÖØ-öø-ÿ]", $array[$i]))
	             $words[$i] = $array[$i];

	     }
	     return count($words);
	 }
	
}