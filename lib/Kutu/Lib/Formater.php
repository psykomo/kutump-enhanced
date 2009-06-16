<?php

/**
 * manage formater for application
 * 
 * @author Nihki Prihadi <nihki@hukumonline.com>
 * @package Kutu
 * 
 */

class Kutu_Lib_Formater
{
	/**
	 * select month
	 * @param $montharray
	 * @return $month
	 */
	function monthPullDown($month='')
	{
		$montharray = array("Month","Januari","Februari","Maret","April","Mei","Juni","Juli","Agustus","September","Oktober","November","Desember");
		$monthSelect = "\n<select name=\"month\" id=\"month\">";
		if ($month) {
			$monthSelect .= " <option value=\"" . $month . "\" selected>$montharray[$month]</option>\n";
			$monthSelect .= "<option value=''>Month</option>";
		} else {
			$monthSelect .= "<option value='' selected>Month</option>";
		}
		for($j=1; $j <= 12; $j++) {
			if (($month) and ($j == $month)) {
				continue;
			} else {
				$monthSelect .= " <option value=\"" . $j . "\">$montharray[$j]</option>\n";
			}
		}
		$monthSelect .= "</select>\n\n";
		return $monthSelect;		
	}
	
	/**
	 * dayPullDown
	 * @param $day
	 * @return day
	 */
	 
	function dayPullDown($tday='')
	{
		$day = "<select name=\"day\" id=\"day\">\n";
		if ($tday) {
			$day .= "<option value=\"" . $tday . "\" selected>$tday</option>\n";
			$day .= "<option value=''>Date</option>";
		} else {
			$day .= "<option value='' selected>Date</option>";
		}
		for($i=1;$i <= 31; $i++) {
			if (($tday) and ($i == $tday)) {
				continue;
			} else {
				$day .= " <option value=\"" . $i ."\">$i</option>\n";
			}
		}
	
		$day .= "</select>\n\n";
		return $day;
	}
	
	/**
	 * educationPullDown
	 * @return education
	 */
	
	function educationPullDown($edu='')
	{
		$tblEducation = new Kutu_Core_Orm_Table_Education();
		$row = $tblEducation->fetchAll();
		$education = "<select name=\"pendidikan\" id=\"pendidikan\">\n";
		if ($edu) {
			$rowEducation = $tblEducation->find($edu)->current();
			$education .= "<option value='$rowEducation->educationid' selected>$rowEducation->description</option>";
			$education .= "<option value =''>--------------------Choose--------------------</option>";
		} else {
			$education .= "<option value ='' selected>--------------------Choose--------------------</option>";
		}
		foreach ($row as $rowset) {
			if (($edu) and ($rowset->educationid == $rowEducation->educationid)) {
				continue;
			} else {
				$education .= "<option value='$rowset->educationid'>$rowset->description</option>";
			}
		}
		$education .= "</select>\n\n";
		return $education;
	}
	
	/**
	 * expensePullDown
	 * @return expense
	 */
	
	function expensePullDown($exp='')
	{
		$tblExpense = new Kutu_Core_Orm_Table_Expense();
		$row = $tblExpense->fetchAll();
		$expense = "<select name=\"pengeluaran\" id=\"pengeluaran\">\n";
		if ($exp) {
			$rowExpense = $tblExpense->find($exp)->current();	
			$expense .= "<option value='$rowExpense->expenseId' selected>$rowExpense->description</option>";
			$expense .= "<option value=''>--------------------Choose--------------------</option>";
		} else {
			$expense .= "<option value='' selected>--------------------Choose--------------------</option>";
		}
		foreach ($row as $rowset) {
			if (($exp) and ($rowset->expenseId == $rowExpense->expenseId)) {
				continue;
			} else {
				$expense .= "<option value='$rowset->expenseId'>$rowset->description</option>";
			}
		}
		$expense .= "</select>\n\n";
		return $expense;
	}
	
	/**
	 * attributePullDown
	 * @return attribute
	 */
	
	function attributePullDown()
	{
		$tblAttribute = new Kutu_Core_Orm_Table_Attribute();
		$row = $tblAttribute->fetchAll(null,'guid ASC');
		$attribute = "<select name=\"attribute\" id=\"attribute\">\n";
		$attribute .= "<option value='' selected>----------Choose Attribute----------</option>";
		foreach ($row as $rowset) {
			$attribute .= "<option value='$rowset->guid'>$rowset->guid</option>";
		}
		$attribute .= "</select>\n\n";
		return $attribute;
	}
	
	/**
	 * businessTypePullDown
	 * @return businessType
	 */
	
	function businessTypePullDown($businessTypeId='')
	{
		$tblBusiness = new Kutu_Core_Orm_Table_Business();
		$row = $tblBusiness->fetchAll();
		$businessType = "<select name=\"businessType\" id=\"businessType\">\n";
		if ($businessTypeId) {
			$rowBusinessType = $tblBusiness->find($businessTypeId)->current();
			$businessType .= "<option value='$rowBusinessType->businessTypeId' selected>$rowBusinessType->description</option>";
			$businessType .= "<option value=''>--------------------Choose--------------------</option>";			
		} else {
			$businessType .= "<option value='' selected>--------------------Choose--------------------</option>";
		}
		foreach ($row as $rowset) {
			if (($businessTypeId) and ($rowset->businessTypeId == $rowBusinessType->businessTypeId)) {
				continue;
			} else {
				$businessType .= "<option value='$rowset->businessTypeId'>$rowset->description</option>";
			}
		}
		$businessType .= "</select>\n\n";
		return $businessType;		
	}	
	
	/**
	 * periodOfPaymentPullDown
	 * @return periodOfPayment
	 */
	
	function periodOfPaymentPullDown()
	{
		$tblPayment = new Kutu_Core_Orm_Table_Payment();
		$row = $tblPayment->fetchAll();
		$periodOfPayment = "<select name=\"periodepembayaran\" id=\"periodepembayaran\">\n";
		$periodOfPayment .= "<option value='' selected>--------------------Choose--------------------</option>";
		foreach ($row as $rowset) {
			$periodOfPayment .= "<option value='$rowset->paymentId'>$rowset->description</option>";
		}
		$periodOfPayment .= "</select>\n\n";
		return $periodOfPayment;
	}
	function writeLog()
	{
		$tblUserAccessLog = new Kutu_Core_Orm_Table_UserLog();
		if ($rowUserAccessLog = $tblUserAccessLog->fetchRow("user_id='".Zend_Auth::getInstance()->getIdentity()->guid."' AND (lastlogin='0000-00-00 00:00:00' or isnull(lastlogin))"))
		{
			$rowUserAccessLog->lastlogin = date('Y-m-d H:i:s');			
		}
		else 
		{
			$rowUserAccessLog = $tblUserAccessLog->fetchNew();
			$rowUserAccessLog->user_id = Zend_Auth::getInstance()->getIdentity()->guid;
			$rowUserAccessLog->user_ip = $_SERVER['REMOTE_ADDR'];
			$rowUserAccessLog->login = date('Y-m-d H:i:s');
		}
		$rowUserAccessLog->save();
	}	
	function add_mail($sender,$recepientMail,$recepientName,$subject,$body)
	{
		$data=array('sender'        => $sender,
					'recepientMail' => $recepientMail,
					'recepientName' => $recepientName,
					'subject'       => $subject,
					'body'          => $body,
					'ContentType'	=> 'text/html'
					);
					
		$newsletter = new Kutu_Lib_Newsletter();
		
		$add = $newsletter->addMail($data);
		
		if ($add===false) return $newsletter->errorMsg;
	}
	
	function send_mail()
	{
		require_once(KUTU_ROOT_DIR.'/mix_lib/Kutu/Lib/class.phpmailer.php');
		
		// set all attribute
		// ------------------------------- LOAD FROM CONFIG.ini
		
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/app/config/config.ini','mail');
		
//		$data=array('method'   => 'SMTP',
//					'From'     => 'layanan@hukumonline.com',
//					'FromName' => 'Hukumonline services',
//					'Host'     => 'smtp.biz.net.id',
//					'SMTPAuth' => true,
//					'Username' => 'nihki',
//					'Password' => 'np2008'
//					);
		
		$data=array('method'   => $config->method,
					'From'     => $config->from,
					'FromName' => $config->fromname,
					'Host'     => $config->host,
					'SMTPAuth' => $config->smtpauth,
					'Username' => $config->smtpusername,
					'Password' => $config->smtppassword
					);
		
		$newsletter = new Kutu_Lib_Newsletter();

		$newsletter->addSender($data);
		
		return $newsletter->Sendmail();
	}
	function get_user_id($username)
	{ 
		$tblUser = new Kutu_Core_Orm_Table_User();
		$rowUser = $tblUser->fetchRow("username='".$username."'");
		return $rowUser->guid;
	}	
	function getLastLogin()
	{
		$tblUserAccessLog = new Kutu_Core_Orm_Table_UserLog();
		if (Zend_Auth::getInstance()->hasIdentity()) $rowUserAccessLog = $tblUserAccessLog->fetchRow("user_id='".Zend_Auth::getInstance()->getIdentity()->guid."' AND NOT (lastlogin='0000-00-00 00:00:00' or isnull(lastlogin))",'user_access_log_id DESC');
		if (isset($rowUserAccessLog)) return strftime('%d-%m-%Y %H:%M:%S',strtotime($rowUserAccessLog->lastlogin));
	}
	function get_date($tanggal) {
		$id = $tanggal;
		$id = substr($id,8,2).".".substr($id,5,2).".".substr($id,2,2)." ".substr($id,11,2).":".substr($id,14,2);
		return	$id; 
	}
	function get_date_english($tanggal) {
		$id = $tanggal;
		$id = substr($id,0,4)."-".substr($id,5,2)."-".substr($id,8,2);
		return	$id; 
	}
	function get_date_ina($tanggal) {
		$id = $tanggal;
		$id = substr($id,8,2).".".substr($id,5,2).".".substr($id,0,4);
		return	$id; 
	}
	
	/**
	 * he syntax is DateAdd (interval,number,date).
	 * The interval is a string expression that defines the interval you want to add. 
	 * For example minutes or days, 
	 * the number is the number of that interval that you wish to add, and the date is the date.
	 * Interval can be one of:
	 * @params yyyy	year
	 * @params q	Quarter
	 * @params m	Month
	 * @params y	Day of year
	 * @params d	Day
	 * @params w	Weekday
	 * @params ww	Week of year
	 * @params h	Hour
	 * @params n	Minute
	 * @params s	Second
	 * As far as I can tell, w,y and d do the same thing, 
	 * that is add 1 day to the current date, q adds 3 months and ww adds 7 days. 
	 *
	 */
		
	function DateAdd($interval, $number, $date) {
	
	    $date_time_array = getdate($date);
	    $hours = $date_time_array['hours'];
	    $minutes = $date_time_array['minutes'];
	    $seconds = $date_time_array['seconds'];
	    $month = $date_time_array['mon'];
	    $day = $date_time_array['mday'];
	    $year = $date_time_array['year'];
	
	    switch ($interval) {
	    
	        case 'yyyy':
	            $year+=$number;
	            break;
	        case 'q':
	            $year+=($number*3);
	            break;
	        case 'm':
	            $month+=$number;
	            break;
	        case 'y':
	        case 'd':
	        case 'w':
	            $day+=$number;
	            break;
	        case 'ww':
	            $day+=($number*7);
	            break;
	        case 'h':
	            $hours+=$number;
	            break;
	        case 'n':
	            $minutes+=$number;
	            break;
	        case 's':
	            $seconds+=$number;
	            break;            
	    }
	    $timestamp= mktime($hours,$minutes,$seconds,$month,$day,$year);
	    return $timestamp;
	}	
	function _traverseHistory($catalogGuid, $aData)
	{
		$tblRelatedItem = new Kutu_Core_Orm_Table_RelatedItem();
		$sGuid = '';
		
		$where = "relatedGuid='$catalogGuid' AND relateAs='RELATED_HISTORY'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where);
		foreach ($rowsetRelatedItem as $rowRelatedItem)
		{
			if(!isset($aData[$catalogGuid]['right']))
			{
				//echo 'right: '.$rowRelatedItem->itemGuid . '|';
				$sGuid .= $catalogGuid .'|';
				$aData[$catalogGuid]['right']= $catalogGuid;
				$sGuid .= $this->_traverseHistory($rowRelatedItem->itemGuid, $aData) . '|';
			}
		}
		
		$where2 = "itemGuid='$catalogGuid' AND relateAs='RELATED_HISTORY'";
		$rowsetRelatedItem = $tblRelatedItem->fetchAll($where2);
		foreach ($rowsetRelatedItem as $rowRelatedItem)
		{
			if(!isset($aData[$catalogGuid]['left']))
			{
				//echo 'left: '.$rowRelatedItem->relatedGuid . '|';
				$sGuid .= $catalogGuid .'|';
				$aData[$catalogGuid]['left]'] = $catalogGuid;
				$sGuid .= $this->_traverseHistory($rowRelatedItem->relatedGuid, $aData). '|';
			}
		}
		return $sGuid;
	}
	function _checkMemberExist($guid, $aGuids)
	{
		for ($i=0;$i<count($aGuids);$i++)
		{
			if($aGuids[$i]==$guid)
				return true;
		}
		return false;
	}
	function checkUserExist($username)
	{
		$tbluser = new Kutu_Core_Orm_Table_User();
		$where = $tbluser->getAdapter()->quoteInto("username=?",$username);
		$rowset = $tbluser->fetchRow($where);
		if ($rowset)
		{
			$response['failure'] = true;
			$response['message'] = "$username already exist, please another username";
			echo Zend_Json::encode($response);
			exit();
		}
	}
	function checkUserEmail($email)
	{
		$tbluser = new Kutu_Core_Orm_Table_User();
		$where = $tbluser->getAdapter()->quoteInto("email=?",$email);
		$rowset = $tbluser->fetchRow($where);
		if ($rowset) 
		{
			$response['failure'] = true;
			$response['message'] = "Your email $email is not available";
			echo Zend_Json::encode($response);
			exit();
		}
	}
	function checkPromoValidation($whatPromo,$packed,$promoid,$payment)
	{
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/app/config/config.ini','package');
		
		switch ($packed)
		{
			case 26:
				
				$charge = $config->individual;
				
			break;
			case 27:
				
				$charge = $config->corporate;
				
			break;
		}
		
		$periode = $charge * $payment;
		
		$tblPromosi = new Kutu_Core_Orm_Table_Promosi();
		$rowpromo = $tblPromosi->find($promoid)->current();
		
		// check promotionID if exist then dischard query
		if (isset($rowpromo)) {
			if ($payment == 6) {
				$disc = $rowpromo->discount + 5;
			} elseif ($payment == 12) {
				$disc = $rowpromo->discount + 10;
			} else {
				$disc = $rowpromo->discount;
			}
			$total = ($periode - ($disc/100 * $periode)) * 1.1;
		} else {
			$getpromo = $tblPromosi->fetchRow("periodeStart<='".date("Y-m-d")."' AND periodEnd>='".date("Y-m-d")."' AND bulan_langganan=".$payment."");
			if (isset($getpromo))
			{
				if ($payment == 6) {
					$disc = $getpromo->discount + 5;
				} elseif ($payment == 12) {
					$disc = $getpromo->discount + 10;
				} else {
					$disc = $getpromo->discount;
				}
				$total = ($periode - ($disc/100 * $periode)) * 1.1;
			} else { 
				if ($payment == 6) {
					$disc = 5;
				} elseif ($payment == 12) {
					$disc = 10;
				} else {
					$disc = 0;
				}
				$total = ($periode - ($disc/100 * $periode)) * 1.1;
			}
		}
		
		switch ($whatPromo)
		{
			case 'Disc':
				return $disc;
			break;
			case 'Total':
				return $total;
			break;
		}
	}
	function _writeInvoice($memberId, $totalPromo, $discPromo, $payment)
	{
		$formater = new Kutu_Lib_Formater();
		$tblInvoice = new Kutu_Core_Orm_Table_Invoice();
		$where = $tblInvoice->getAdapter()->quoteInto("memberid=?",$memberId);
		$rowInvoice = $tblInvoice->fetchAll($where);
		if (count($rowInvoice) <= 0)
		{
			$rowInvoice = $tblInvoice->fetchNew();
			$rowInvoice->memberid = $memberId;
			$rowInvoice->price = $totalPromo;
			$rowInvoice->discount = $discPromo;
			$rowInvoice->dtinvoice_out = date("Y-m-d");
			$rowInvoice->dtinvoice_confirm = "0000-00-00";
			$temptime = time();
			$temptime = $formater->DateAdd('d',5,$temptime);
			$rowInvoice->expiration_date = strftime('%Y-%m-%d',$temptime);
			$rowInvoice->save();
		}
	}
	function _writeConfirmFreeEmail($mailcontent, $fullname, $username, $password, $guid, $email)
	{
		$formater		= new Kutu_Lib_Formater();
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$username',$username,$mailcontent);
		$mailcontent 	= str_replace('$password',$password,$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		$mail_body = $mailcontent;
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/app/config/config.ini','mail');
		$mailAttempt = $formater->add_mail($config->from,$email,$username,'Hukumonline-ID',$mail_body);		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $formater->send_mail();
			if ($sendAttempt)
			{
				$response['success'] = true;
				$response['message'] = "Please check your email at $email!";
			}
			else 
			{
				ob_clean();
				$response['failure'] = false;
				$response['message'] = "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$response['failure'] = true;
			$response['message'] = "Error saving mail!";
		}
		echo Zend_Json::encode($response);
	}
	function _writeConfirmIndividualEmail($mailcontent, $fullname, $username, $password, $payment, $disc, $total, $guid, $email)
	{
		$formater		= new Kutu_Lib_Formater();
		$mailcontent 	= str_replace('$fullname',$fullname,$mailcontent);
		$mailcontent 	= str_replace('$username',$username,$mailcontent);
		$mailcontent 	= str_replace('$password',$password,$mailcontent);
		$mailcontent 	= str_replace('$disc',$disc,$mailcontent);
		$mailcontent 	= str_replace('$timeline',$payment,$mailcontent);
		$mailcontent 	= str_replace('$price',number_format($total),$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		$mail_body 		= $mailcontent;
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/app/config/config.ini','mail');
		$mailAttempt = $formater->add_mail($config->from,$email,$username,'Hukumonline-ID',$mail_body);		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $formater->send_mail();
			if ($sendAttempt)
			{
				$response['success'] = true;
				$response['message'] = "Please check your email at $email!";
			}
			else 
			{
				ob_clean();
				$response['failure'] = false;
				$response['message'] = "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$response['failure'] = true;
			$response['message'] = "Error saving mail!";
		}
		echo Zend_Json::encode($response);
	}
	function _writeConfirmCorporateEmail($mailcontent, $company, $payment, $disc, $total, $username, $guid, $email)
	{
		$formater 		= new Kutu_Lib_Formater();
		$obj 			= new Kutu_Crypt_Password();
		$mailcontent 	= str_replace('$company',$company,$mailcontent);
		$mailcontent 	= str_replace('$timeline',$payment,$mailcontent);
		$mailcontent 	= str_replace('$disc',$disc,$mailcontent);
		$mailcontent 	= str_replace('$price',number_format($total),$mailcontent);
		$mailcontent 	= str_replace('$username1',$username,$mailcontent);
		$mailcontent 	= str_replace('$guid',$guid,$mailcontent);
		// table User
		$tblUser = new Kutu_Core_Orm_Table_User();
		$where = $tblUser->getAdapter()->quoteInto('company=?',$company);
		$rowUser = $tblUser->fetchAll($where,'username ASC');
		$tag = '<table>';
		$tag .= '<tr><td><b>Username</b></td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td><b>Password</b></td></tr>';
		foreach ($rowUser as $rowsetUser)
		{
			$tag .= '<tr><td>'.$rowsetUser->username.'</td><td>&nbsp;</td><td>&nbsp;</td><td>&nbsp;</td><td>'.$obj->decryptPassword($rowsetUser->password).'</td></tr>';					
		}
		$tag .= '</table>';
		$mailcontent = str_replace('$tag',$tag,$mailcontent);
		$mail_body = $mailcontent;
		// parse ini_file
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/app/config/config.ini','mail');
		$mailAttempt = $formater->add_mail($config->from,$email,$username,'Hukumonline-ID',$mail_body);		
		// try to save mail before send
		if ($mailAttempt)			
		{
			$sendAttempt = $formater->send_mail();
			if ($sendAttempt)
			{
				$response['success'] = true;
				$response['message'] = "Please check your email at $email!";
			}
			else 
			{
				ob_clean();
				$response['failure'] = false;
				$response['message'] = "Error send mail but register user successfully!<br>Please contact our customer service for more information";
			}
		}
		else 
		{
			$response['failure'] = true;
			$response['message'] = "Error saving mail!";
		}
		echo Zend_Json::encode($response);
	}
	function getMailContent($title)
	{
		// table Folder
		$tblFolder = new Kutu_Core_Orm_Table_Folder();
		$where = $tblFolder->getAdapter()->quoteInto("title=?",$title);
		$rowFolder = $tblFolder->fetchRow($where);
		// table CatalogFolder
		$tblCatalogFolder = new Kutu_Core_Orm_Table_CatalogFolder();
		$find = $tblCatalogFolder->getAdapter()->quoteInto("folderGuid=?",$rowFolder->guid);
		$rowCatFolder = $tblCatalogFolder->fetchRow($find);
		
		if (isset($rowCatFolder))
			$catalogGuid = $rowCatFolder->catalogGuid;
		else 
			$catalogGuid = '';
			
		// table Catalog
		$tblCatalog = new Kutu_Core_Orm_Table_Catalog();
		$rowCatalog = $tblCatalog->find($catalogGuid)->current();
		// table ProfileAttribute
		$tblProfileAttributes = new Kutu_Core_Orm_Table_ProfileAttribute();
		$search = $tblProfileAttributes->getAdapter()->quoteInto("profileGuid=?",$rowCatalog->profileGuid);
		$rowsetProfileAttributes = $tblProfileAttributes->fetchAll($search,array('viewOrder ASC'));
		$rowsetCatalogAttribute = $rowCatalog->findDependentRowsetCatalogAttribute();
		$i = 0;
		foreach ($rowsetProfileAttributes as $row)
		{
			$rowCatalogAttribute = $rowsetCatalogAttribute->findByAttributeGuid($row->attributeGuid);
			$rowAttribute = $row->findParentRow('Kutu_Core_Orm_Table_Attribute');
									
			if (isset($rowCatalogAttribute->value))
				$attributeValue = $rowCatalogAttribute->value;
			else 
				$attributeValue = '';
											
			$mailcontent = $attributeValue;
								
			$i++;
		}
		return $mailcontent;		
	}
	function selectDate (
                        $sel_d = 0			// selected day
                      , $sel_m = 0       	// selected month
                      , $sel_y = 0       	// selected year
                      , $var_d = 'd'     	// name for day variable
                      , $var_m = 'm'    // name for month variable
                      , $var_y = 'y'     // name for year variable
                      , $min_y = 2007       	// minimum year
                      , $max_y = 0       	// maximum year
                      , $enabled = true  	// enable drop-downs?
                    ) {
                    	
	  	// Default day is today
	  	if ($sel_d == 0) 
	    	$sel_d = date('j');
	  	// Default month is this month
	  	if ($sel_m == 0) 
	    	$sel_m = date('n');
	  	// Default year is this year
	  	if ($sel_y == 0) 
	    	$sel_y = date('Y');
	  	// Default minimum year is this year
	  	if ($min_y == 0) 
	    	$min_y = date('Y');
	  	// Default maximum year is two years ahead
	  	if ($max_y == 0) 
			$max_y = ($min_y + 2);
                    	
		// --------------------------------------------------------------------------
	  	// Start off with the drop-down for Days
	  	// Start opening the select element
	  	$dateout = '<select name="'. $var_d. '"';
	  	// Add disabled attribute if necessary
	  	if (!$enabled) 
	    	$dateout .= ' disabled="disabled"';
	  	// Finish opening the select element
	  	$dateout .= '>\n';
	  	// Loop round and create an option element for each day (1 - 31)
	  	for ($i = 1; $i <= 31; $i++) {
	    	// Start the option element
	    	$dateout .= '\t<option value="'. $i. '"';
	    	// If this is the selected day, add the selected attribute
	    	if ($i == $sel_d) 
	      		$dateout .= ' selected="selected"';
	    	// Display the value and close the option element
	    	$dateout .= '>'. $i. '</option>\n';
	  	}
		// Close the select element
	  	$dateout .= '</select>';
	  	
		// --------------------------------------------------------------------------
  		// Now do the drop-down for Months
  		// Start opening the select element
  		$dateout .= '<select name="'. $var_m. '"';

  		// Add disabled attribute if necessary
  		if (!$enabled) 
    		$dateout .= ' disabled="disabled"';

  		// Finish opening the select element
  		$dateout .= '>\n';

  		// Loop round and create an option element for each month (Jan - Dec)
  		for ($i = 1; $i <= 12; $i++) {
    		// Start the option element
    		$dateout .= '\t<option value="'. $i. '"';
    		// If this is the selected month, add the selected attribute
    		if ($i == $sel_m) 
      			$dateout .= ' selected="selected"';
    		// Display the value and close the option element
    		$dateout .= '>'. date('F', mktime(3, 0, 0, $i)). '</option>\n';
  		}

  		// Close the select element
  		$dateout .= '</select>';	  	
  		
  		// --------------------------------------------------------------------------
  		// Finally, the drop-down for Years
  		// Start opening the select element
  		$dateout .= '<select name="'. $var_y. '"';

  		// Add disabled attribute if necessary
  		if (!$enabled) 
    		$dateout .= ' disabled="disabled"';

  		// Finish opening the select element
  		$dateout .= '>\n';

  		// Loop round and create an option element for each year ($min_y - $max_y)
  		for ($i = $min_y; $i <= $max_y; $i++) {
    		// Start the option element
    		$dateout .= '\t<option value="'. $i. '"';
    		// If this is the selected year, add the selected attribute
    		if ($i == $sel_y) 
      			$dateout .= ' selected="selected"';
    		// Display the value and close the option element
    		$dateout .= '>'. $i. '</option>\n';
  		}
  		// Close the select element
  		$dateout .= '</select>';  	
  		return $dateout;	
	}
}
