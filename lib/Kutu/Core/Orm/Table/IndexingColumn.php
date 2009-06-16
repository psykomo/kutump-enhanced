<?php

class Kutu_Core_Orm_Table_IndexingColumn extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuIndexingColumn';
	function implode_with_keys($glue, $array, $valwrap='')
    {
    	if ($array) {
	        foreach($array AS $key => $value) {
	            $ret[] = $valwrap.$value.$valwrap;
	        }
	        return implode($glue, $ret);
    	}
    }    
}