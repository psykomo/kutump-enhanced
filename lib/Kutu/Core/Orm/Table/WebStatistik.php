<?php

/**
 * manage Table KutuWebStatistic
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_WebStatistik extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuWebStatistic';
	
	public function getsum()
	{
		$db = $this->_db->query("SELECT sum(hits) as sum_hits FROM KutuWebStatistic");
		$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
		return ($dataFetch[0]['sum_hits']);
	}
	public function getsummonth()
	{
		$now = getdate();
		
		$month = $now['mon'];
		$year = $now['year'];
		
		$db = $this->_db->query("SELECT sum(hits) as sum_hits FROM KutuWebStatistic WHERE month = $month AND year = $year");
		$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
		return ($dataFetch[0]['sum_hits']);
	}
}

?>