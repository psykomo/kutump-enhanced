<?php

/**
 * module search for application
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Kutu_Search_Engine
{
	public static function factory($adapter, $config = array())
    {
    	switch (strtolower($adapter)) 
    	{
    		case 'solr':
    			$solrHost = $config['host'];
    			$solrPort = $config['port'];
    			$solrHomeDir = $config['homedir'];
    			$newAdapter = new Kutu_Search_Adapter_Solr($solrHost, $solrPort, $solrHomeDir);
    			
    			//check if newAdapter is an abstract of Kutu_Search_Adapter
    			
    			return $newAdapter;
    			break;
    		case 'zendlucene':
    			if(isset($config['homedir']))
    				$luceneHomeDir = $config['homedir'];
    			else 
    				$luceneHomeDir = null;
    			$newAdapter = new Kutu_Search_Adapter_ZendLucene($luceneHomeDir);
    			
    			//check if newAdapter is an abstract of Kutu_Search_Adapter
    			
    			return $newAdapter;
    			break;
    	}
    	return false;
    }
}

?>