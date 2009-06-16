<?php

class Kutu_Core_Orm_Table_Packed extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuUserPacked';
	protected $_referenceMap = array(
		'User' => array(
			'columns'		=> 'packetid',
			'refTableClass'	=> 'Kutu_Core_Orm_Table_User',
			'refColumns'	=> 'packetId'
		)
	);
	public function fetch_packed($packedId)
	{
		return $this->fetchRow("packetid=".$packedId);
	}
}