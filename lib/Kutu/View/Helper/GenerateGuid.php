<?php

/**
 * Generator guid
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_View_Helper_GenerateGuid
{
	function generateGuid($prefix=null)
	{
		$o = new Kutu_Core_Guid();
		return $o->generateGuid($prefix);
	}
}

?>