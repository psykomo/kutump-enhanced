<?php
require_once KUTU_ROOT_DIR."/app/servers/eventcalendar/config/config.php";
class Kutu_Lib_Calendar
{
	/**
	 * select month
	 * @param $month, $montharray
	 * @return month
	 */
	 
	 function monthPullDown($month, $montharray) {
	 	$monthSelect = "\n<select name=\"month\">\n";
		for($j=0; $j < 12; $j++) {
			if ($j != ($month - 1)) 
				$monthSelect .= " <option value=\"" . ($j+1) . "\">$montharray[$j]</option>\n";
			else
				$monthSelect .= " <option value=\"" . ($j+1) . "\" selected>$montharray[$j]</option>\n";
		}
		
		$monthSelect .= "</select>\n\n";
		return $monthSelect;
	 }

	/**
	 * select year
	 * @param $year
	 * @return $year
	 */
	 
	 function yearPullDown($year) {
	 	$yearSelect = "<select name=\"year\">\n";
		
		$z = 3;
		for($j=1; $j < 8; $j++) {
			if ($z==0)
				$yearSelect .= " <option value=\"" . ($year-$z) . "\" selected>" . ($year-$z) . "</option>\n";
			else
				$yearSelect .= " <option value=\"" . ($year-$z) . "\">" . ($year-$z) . "</option>\n";
				
			$z--;
		}
		
		$yearSelect .= "</select>\n\n";
		return $yearSelect;
	 }
	
	/**
	 * getFirstDayOfMonthPosition
	 * @param $month, $year
	 * 
	 */
	 
	function getFirstDayOfMonthPosition($month, $year)
	{
		$weekpos = date("w",mktime(0,0,0,$month,1,$year));
		
		// adjust position if weekstart not Sunday
		if (WEEK_START != 0)
			if ($weekpos < WEEK_START)
				$weekpos = $weekpos + 7 - WEEK_START;
			else
				$weekpos = $weekpos - WEEK_START;
		
		return $weekpos;
	}
	
	/**
	 * getDayNameHeader
	 * @return $string
	 */
	 
	function getDayNameHeader()
	{
		global $lang;

		// day container
		$lang['abrvdays'] 	= array("Sun", "Mon", "Tue", "Wed", "Thur", "Fri", "Sat");

		// adjust day name order if weekstart not Sunday
		if (WEEK_START != 0) {
			for($i=0; $i < WEEK_START; $i++) {
				$tempday = array_shift($lang['abrvdays']);
				array_push($lang['abrvdays'], $tempday);
			}
		}
		
		$s = "<table cellpadding=\"1\" cellspacing=\"1\" border=\"0\">\n<tr>\n";
		
		foreach($lang['abrvdays'] as $day) {
			$s .= "\t<td class=\"column_header\">&nbsp;$day</td>\n";
		}
	
		$s .= "</tr>\n\n";
		return $s;
	}
	
	/**
	 * getEventDataArray
	 * @param $month, $year
	 */
	 
	 function getEventDataArray($month, $year, $whatFor=null) {
	 	$eventdata = array();
	 	$tblCalendarMsgs = new Kutu_Core_Orm_Table_Calendar();
	 	$sql = $tblCalendarMsgs->EventDateCalendar($month, $year);
		foreach ($sql as $sqlDB) { 
			$eventdata[$sqlDB->d]["id"][] = $sqlDB->id;
			
			if (strlen($sqlDB->title) > TITLE_CHAR_LIMIT) {  
				$eventdata[$sqlDB->d]["title"][] = substr(stripslashes($sqlDB->title), 0, TITLE_CHAR_LIMIT) . "...";
			} else {
				$eventdata[$sqlDB->d]["title"][] = stripslashes($sqlDB->title);
			}
			
			$eventdata[$sqlDB->d]["text"][] = $sqlDB->text;
				
			if (!($sqlDB->start_time == "55:55:55" && $sqlDB->end_time == "55:55:55")) {
				if ($sqlDB->start_time == "55:55:55") {
					$starttime = "- -";
				} else {
					$starttime = $sqlDB->stime;
				}
				if ($sqlDB->end_time == "55:55:55") {
					$endtime = "- -";
				} else {
					$endtime = $sqlDB->etime;
				}
				if ($whatFor) {
				$timestr = "<div class=\"time_str\">($starttime - $endtime)&nbsp;</div>";
				} else {
					$timestr = "<div align=\"right\" class=\"time_str\">($starttime - $endtime)&nbsp;</div>";
				}
			} else {
				$timestr = "<br>";
			}
			
			$eventdata[$sqlDB->d]["timestr"][] = $timestr;
		} 
			return $eventdata;
	 }

	/**
	 * writeCalendar
	 * @param $month, $year
	 * @return $string
	 */
	
	function writeCalendar($month, $year) {
		$str = $this->getDayNameHeader();
		$eventdata = $this->getEventDataArray($month, $year);//print_r($eventdata);
		// get week position of first day of month			
		$weekpos = $this->getFirstDayOfMonthPosition($month, $year);
		// get number of days in month
		$days = 31-((($month-(($month<8)?1:0))%2)+(($month==2)?((!($year%((!($year%100))?400:4)))?1:2):0));
		// initialize day variable to zero, unless $weekpos is zero
		if ($weekpos == 0) $day = 1; else $day = 0;
		// initialize today's date variables for color change
		$timestamp = time() + CURR_TIME_OFFSET * 3600;
		$d = date("d", $timestamp); $m = date("n", $timestamp); $y = date("Y", $timestamp);
		// loop writes empty cells until it reaches position of 1st day of month ($wPos)
		// it writes the days, then fills the last row with empty cells after last day
		
		// get group information		
		$acl = new Kutu_Acl_Adapter_Local();
		$aReturn = (isset(Zend_Auth::getInstance()->getIdentity()->username))? $acl->getUserGroupIds(Zend_Auth::getInstance()->getIdentity()->username) : '';
				
		while($day <= $days) {
			
			$str .="<tr>\n";
	
			for($i=0;$i < 7; $i++) {
				
				if($day > 0 && $day <= $days) {
					
					$str .= "	<td class=\"";
					
					if (($day == $d) && ($month == $m) && ($year == $y)) {
						$str .= "today";
					} else {
						$str .= "day";
					}
					$str .= "_cell\" valign=\"top\"><span class=\"day_number\">";
					
					if (isset($aReturn[1]) == "administrator") 
						$str .= "<a href=\"javascript: openActionDialog('postMessage', '', $day, $month, $year)\">$day</a>";
					else
						$str .= "$day";
					
					$str .= "</span><br>";
					
					// enforce title limit
					if(array_key_exists($day,$eventdata)){
					$eventcount = count($eventdata[$day]['title']);
					if (MAX_TITLES_DISPLAYED < $eventcount) $eventcount = MAX_TITLES_DISPLAYED;
					
					// write title link if posting exists for day
					for($j=0;$j < $eventcount;$j++) {
						$str .= "<span class=\"title_txt\">-";
						$str .= "<a href=\"javascript:openActionDialog('openPosting'," . $eventdata[$day]["id"][$j] . ")\">";
						$str .= $eventdata[$day]["title"][$j] . "</a></span>" . $eventdata[$day]["timestr"][$j];
					}
					} 
					$str .= "</td>\n";
					$day++;
				} elseif($day == 0)  {
					$str .= "	<td class=\"empty_day_cell\" valign=\"top\">&nbsp;</td>\n";
					$weekpos--;
					if ($weekpos == 0) $day++;
				} else {
					$str .= "	<td class=\"empty_day_cell\" valign=\"top\">&nbsp;</td>\n";
				}
			}
			$str .= "</tr>\n\n";
		}
		
		$str .= "</table>\n\n";
		return $str;		
	}
	
	/**
	 * writeCalendarFront
	 * @param $month, $year
	 * @return $string
	 */
	function writeCalendarFront($month, $year) {
		$eventdata = $this->getEventDataArray($month, $year, 'front');
		// get week position of first day of month			
		$weekpos = $this->getFirstDayOfMonthPosition($month, $year);
		// get number of days in month
		$days = 31-((($month-(($month<8)?1:0))%2)+(($month==2)?((!($year%((!($year%100))?400:4)))?1:2):0));
		// initialize day variable to zero, unless $weekpos is zero
		if ($weekpos == 0) $day = 1; else $day = 0;
		// initialize today's date variables for color change
		$timestamp = time() + CURR_TIME_OFFSET * 3600;
		$d = date("d", $timestamp); $m = date("n", $timestamp); $y = date("Y", $timestamp);
		// loop writes empty cells until it reaches position of 1st day of month ($wPos)
		// it writes the days, then fills the last row with empty cells after last day
		
		$str = "<table>";
		
		while($day <= $days) {
			
			for($i=0;$i < 7; $i++) {
				
				if($day > 0 && $day <= $days) {
					
					// enforce title limit
					if(array_key_exists($day,$eventdata)){
					$eventcount = count($eventdata[$day]['title']);
					if (MAX_TITLES_DISPLAYED < $eventcount) $eventcount = MAX_TITLES_DISPLAYED;
					
					// write title link if posting exists for day
					for($j=0;$j < $eventcount;$j++) {
//						$str = "<table>";
//						$str .= date ("l F j, Y", mktime (0,0,0,$month,$day,$year))."<br>";
						$str .= "<tr><td><p>".date ("l F j, Y", mktime (0,0,0,$month,$day,$year))."</p></td></tr>";
						$str .= "<tr><td><b><font size=\"2\" color=\"#FFAD29\">".$eventdata[$day]["title"][$j]."</font></b>"."</td></tr>";
						$str .= "<tr><td>".$eventdata[$day]["timestr"][$j]."</td></tr>";
//						$str .= $eventdata[$day]["title"][$j]."<br>";
//						$str .= $eventdata[$day]["text"][$j].$eventdata[$day]["timestr"][$j]."<br>";
						$str .= "<tr><td><p align=\"justify\">".$eventdata[$day]["text"][$j]."</p></td></tr><br>";
//						$str .= "</table>";
					}
					} 
					$day++;
				} elseif($day == 0)  {
					$weekpos--;
					if ($weekpos == 0) $day++;
				} 
			}
		}
		$str .= "</table>\n\n";
		return isset($str)? $str : "&nbsp;&nbsp;No event ...";		
	}
	function detailsDateFormat( $value ){
    	$d = explode("-", $value);
        $cdate = date ("l F j, Y", mktime (0,0,0,$d[1],$d[2],$d[0]));
        echo $cdate;
    }
	/**
	 * get_month_name
	 * get current local month name
	 * @param int month from 1 to 12
	 * @return string month name
	 */
	
	function get_month_name($month,$format = null) {
		if ($format != null) 
			return strftime($format,gmmktime(0,0,0,$month,1,2007));
		else
			return strftime("%B",gmmktime(0,0,0,$month,1,2007));
	}
}