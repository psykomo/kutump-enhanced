<?php

class Kutu_Lib_Newsletter{
    /**
     * Number of maximum e-mails sent in one session. 
     * This setting is ussefull to not be taged ny the server as a spam sender.
     */
    var $MaxMails       = '40';
    
    /**
     * Number in seconds that the script will run. Set 5s less then the php config to avoid Maximum execution error.
     */
    var $MaxExecutionTime    = '5';
    
    
    /**
     * The table name where the mails are saved
     */
    var $MysqlTableName       = 'KutuNewsletterQueue';    
    
    /**
     * Mysql Object
     */
    var $mysqlObj;
    
    /**
     * phpMailer Object
     */
    var $MailObj;
    
    /**
     * Error Message.
     */
    var $errorMsg;
    
    /**
     * Senders Array
     */
    var $senders=array();
    
    /**#@+
     * @access private 
     * DO NOT MODIFY BELOW THIS LINE ...
     */
    var $m_Start = 0.0;
    var $emailSent = 0;
    
    /**
     * Constructor      
     */
    function Kutu_Lib_Newsletter(){  
    	$this->m_Start = $this->GetMicrotime();
    }    
    
	function GetMicrotime(){
		list($micro_seconds, $seconds) = explode(" ", microtime());
		return ((float)$micro_seconds + (float)$seconds);
	}
	
	function GetTime($Decimals = 2){
		return number_format($this->GetMicrotime() - $this->m_Start, $Decimals, '.', '');
	}		   
    
    /**
     * Ads a new mail in the queue Table
     *
     * @param array $params
     * @return bool
     */
    function addMail($params){
    	$data=array('sender'        => (isset($params['sender']))        ? $params['sender']        : false,
    				'recepientMail' => (isset($params['recepientMail'])) ? $params['recepientMail'] : false,
    				'recepientName' => (isset($params['recepientName'])) ? $params['recepientName'] : '',
    				'subject'       => (isset($params['subject']))       ? $params['subject']       : false,
    				'body'          => (isset($params['body']))          ? $params['body']          : false,
    				'ContentType'   => (isset($params['ContentType']))   ? $params['ContentType']   : 'text/plain',
    				'priority'      => (isset($params['priority']))      ? $params['priority']      : '3',
    				'SendDate'      => (isset($params['SendDate']))      ? $params['SendDate']      : date("Y-m-d H:i:s"),
    				);
    	$error=false;			
    	foreach ( $data as $key=>$value ){
    		if ($value===false) {
    			$error=true;
    			$errorMsg="Cannot add Mail. Empty value for [$key]";
    		}
    	}
    	if ( $error===true ){
    		$this->errorMsg=$errorMsg;
    		return false;
    	} else {
    		
			$tblQueue = new Kutu_Core_Orm_Table_Queue();
			$insertAttempt = $tblQueue->insert($data);
			if ($insertAttempt===true) return true;
			else {
		    	$this->errorMsg='SQL ERROR';
		    	return false;    								
			}

    	}	
    }
    
    /**
     * Sends mails from queue based on the config
     * @param array $params
     * @return bool
     */
    function Sendmail($params=array(),$where = false){
		$parameters['rowCount']=$this->MaxMails; 
		$parameters['sortColumn']='priority'; 
		
		$tblQueue = new Kutu_Core_Orm_Table_Queue();
		$mails = $tblQueue->fetchTable($this->MysqlTableName, $where,$parameters);
		if ( count($mails)>0 ){
			if (class_exists('PHPMailer')) {
				$this->MailObj=new PHPMailer();
			} else { 
	    		$this->errorMsg='Class PHPMailer not defined. Include the class file.';
	    		return false;			
			}
			
			foreach ( $mails as $mail ){  
				$time = $this->GetTime();
				if ($time > $this->MaxExecutionTime) return true;
				else {
						if (isset($mail->sender)) {

						$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/app/config/config.ini','mail');
						
						$method = $config->method;
						
						switch ($method) {
							case 0 : $this->MailObj->IsSMTP(); break;
							case 1 : $this->MailObj->IsMail(); break;
							case 2 : $this->MailObj->IsQmail(); break;
							case 3 : $this->MailObj->IsSendmail(); break;
						}

						$this->MailObj->Host 		= $config->host;
						$this->MailObj->SMTPAuth 	= $config->smtpauth;
						$this->MailObj->Username	= $config->smtpusername;
						$this->MailObj->Password	= $config->smtppassword;
						$this->MailObj->From		= $config->from;
						$this->MailObj->FromName	= $config->fromname; 
						
						$this->MailObj->AddAddress($mail->recepientMail, $mail->recepientName);
						$this->MailObj->AddReplyTo($config->from, $config->fromname);
						
						$this->MailObj->WordWrap = 130; // set word wrap to 50 characters
						if ($mail->recepientMail='text/html') $this->MailObj->IsHTML(true);       // set email format to HTML
						
						$this->MailObj->Subject = $mail->subject;
						$this->MailObj->Body    = $mail->body;
						
						if(!$this->MailObj->Send()) {
							$this->errorMsg=$this->MailObj->ErrorInfo;
							return false;
						} else {
							$this->emailSent++;
							$tblQueue->delete("newsletterQID='$mail->newsletterQID'");
						}
					} else {
						$this->errorMsg='No sender was defined for this mail.';
			    		return false;
					}
				}	
			}
		}
		return true; 	    	
    }
}

?>