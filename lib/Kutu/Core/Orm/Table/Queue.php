<?php

class Kutu_Core_Orm_Table_Queue extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuNewsletterQueue';
	
	public function insert (array $data)
	{
		return parent::insert($data);
	}
	
	public function delete($where)
	{
		return parent::delete($where);
	}

	public function fetchTable($table_name, $where = false,$parameters = array())
	{
	    $range       = ( isset($parameters['range'])       && !empty($parameters['range']) )       ? $parameters['range']       : " * " ;
	    $sortColumn  = ( isset($parameters['sortColumn'])  && !empty($parameters['sortColumn']) )  ? $parameters['sortColumn']  : false ;
	    $sortType    = ( isset($parameters['sortType'])    && !empty($parameters['sortType']) )    ? $parameters['sortType']    : "ASC" ;
	    $limitOffset = ( isset($parameters['limitOffset']) && !empty($parameters['limitOffset']) ) ? $parameters['limitOffset'] : false ;
	    $rowCount    = ( isset($parameters['rowCount'])    && !empty($parameters['rowCount']) )    ? $parameters['rowCount']    : false ;
		
	    $queryString= "SELECT $range FROM $table_name ";
	    if ( $where !== false ) $queryString .= " WHERE ".$where;
	    if ( $sortColumn !== false ) $queryString .= " ORDER BY $sortColumn $sortType ";	    
	    if ( $rowCount !== false ) {
	    	$queryString .= " LIMIT ";
	    	if ( $limitOffset !== false ) $queryString .= " $limitOffset, ";
	    	$queryString .= " $rowCount ";
	    }
	    
	    $db = $this->_db->query($queryString);
	    $dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
	    
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        
	    if (count($dataFetch) < 1)
	    { 
	    	return false;
	    }
	    else {
	    	return new $this->_rowsetClass($data);
	    }
	}
}

?>