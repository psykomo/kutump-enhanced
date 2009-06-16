<?php
class Kutu_Core_Orm_Table_OrderStatus extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuOrderStatus';
    
    public function getStatus(){
		$sql = $this->_db->select()->from('KutuOrderStatus');
		
    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
		
	}   
    public function getSpecifiedStatus($where){
		$sql = $this->_db->select()->from('KutuOrderStatus')
            ->where('orderStatusId = '.$where);
            
    	$db = $this->_db->query($sql);
    	$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
        
    	$data  = array(
            'table'    => $this,
            'data'     => $dataFetch,
            'rowClass' => $this->_rowClass,
            'stored'   => true
        );

        Zend_Loader::loadClass($this->_rowsetClass);
        return new $this->_rowsetClass($data);
		
	}
}