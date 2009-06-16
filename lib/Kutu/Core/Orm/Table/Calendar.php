<?php

/**
 * manage Table Calendar
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Core_Orm_Table_Calendar extends Zend_Db_Table_Abstract 
{
	protected $_name = 'KutuCalendarMssgs';
	protected $_dependentTables = 'Kutu_Core_Orm_Table_User';
	
	public function EventDateCalendar($month, $year)
	{ 
		$sql = "SELECT id,d,title,text,start_time,end_time, ";	
		
		if (TIME_DISPLAY_FORMAT == "12hr") {
			$sql .= "TIME_FORMAT(start_time, '%l:%i%p') AS stime, ";
			$sql .= "TIME_FORMAT(end_time, '%l:%i%p') AS etime ";
		} elseif (TIME_DISPLAY_FORMAT == "24hr") {
			$sql .= "TIME_FORMAT(start_time, '%H:%i') AS stime, ";
			$sql .= "TIME_FORMAT(end_time, '%H:%i') AS etime ";		
		} else {
			echo "Bad time display format, check your configuration file.";
		}
		
		$sql .= "FROM KutuCalendarMssgs WHERE m = $month AND y = $year ";
		$sql .= "ORDER BY start_time";

		$db = $this->_db->query($sql);
		$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
		$data = array(
			'table'		=> $this,
			'data'		=> $dataFetch,
			'rowClass'	=> $this->_rowClass,
			'stored'	=> true
		);	
		Zend_Loader::loadClass($this->_rowsetClass);
		return new $this->_rowsetClass($data);
	}
	public function openPosting( $pid )
	{
		$sql = "SELECT d, m, y FROM KutuCalendarMssgs WHERE id=".$pid;
		$db = $this->_db->query($sql);
		$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
//		$data = array(
//			'table'		=> $this,
//			'data'		=> $dataFetch,
//			'rowClass'	=> $this->_rowClass,
//			'stored'	=> true
//		);
//		Zend_Loader::loadClass($this->_rowsetClass);
//		return new $this->_rowsetClass($data);
		return $dataFetch;
	}
	public function writePosting( $pid )
	{
		$sql = "SELECT y, m, d, title, text, start_time, end_time, ";
		$sql .= "KutuUser.guid, username, ";
		
		if (TIME_DISPLAY_FORMAT == "12hr") {
			$sql .= "TIME_FORMAT(start_time, '%l:%i%p') AS stime, ";
			$sql .= "TIME_FORMAT(end_time, '%l:%i%p') AS etime ";
		} elseif (TIME_DISPLAY_FORMAT == "24hr") {
			$sql .= "TIME_FORMAT(start_time, '%H:%i') AS stime, ";
			$sql .= "TIME_FORMAT(end_time, '%H:%i') AS etime ";
		} else {
			echo "Bad time display format, check your configuration file.";
		}
		
		$sql .= "FROM KutuCalendarMssgs ";
		$sql .= "LEFT JOIN KutuUser ";
		$sql .= "ON (KutuCalendarMssgs.uid = KutuUser.guid) ";
		$sql .= "WHERE id = " . $pid;
		
		$db = $this->_db->query($sql);
		$dataFetch = $db->fetchAll(Zend_Db::FETCH_ASSOC);
		return $dataFetch;
	}
}