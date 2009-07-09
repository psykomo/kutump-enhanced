<?php
class PaymentGateway_HtmlMail{
	protected $sSource;
	protected $sEmailTo;
	protected $sSubject;
	protected $sEmailFrom;
	protected $sHeader;
	protected $aDataSet;
	protected $sMessages;

	//send html mail 
	public function SendFileMail($sSource, $sEmailTo, $sSubject, $sEmailFrom, $sHeader, $aDataSet ){
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/config/mail.ini', 'general');
		$options = array('auth' => $config->mail->auth,
				                'username' => $config->mail->username,
				                'password' => $config->mail->password);
		$transport = new Zend_Mail_Transport_Smtp($config->mail->host, $options);

		$mail = new Zend_Mail();
		/* example :
			$sMailSource="My Name is {NAME}. Thank's for using {USING}.";
			$sMailEmailTo='destination@email.com';
			$sMailSubject="Using Send File Mail";
			$sMailEmailFrom='from@email.com';
			$sMailHeader='';
			$aMailDataSet=array('NAME' 	=> 'Firman',
								'USING'	=> 'HTML Mail Templates' );
			HtmlMail::SendFileMail($filename, $value, "Error on Create New Member", "info@belajarforex.com", "", $dataset );
		 */
		$this->sSource=$sSource;
		$this->sEmailTo=$sEmailTo;
		$this->sSubject=$sSubject;
		$this->sEmailFrom=$sEmailFrom;
		$this->sHeader=$sHeader;
		$this->aDataSet=$aDataSet;

		/*//check for info@belajarforex.com email 
		if(strtolower(trim($this->sEmailFrom))=="info@belajarforex.com")
			$this->sEmailFrom="BelajarForex<info@BelajarForex.com>";*/

		//parse dataset and put in 2 separate array
		$aSearch=array();
		$aReplace=array();
		foreach($this->aDataSet as $sSearch=>$sReplace){
			$aSearch[]='{'.$sSearch.'}';
			$aReplace[]=$sReplace;
		}	

		//replace the HTML variables
		if( !empty( $this->aDataSet ) ){
			$this->sMessages = str_replace( $aSearch, $aReplace, $this->sSource );
		}else{
			$this->sMessages = $this->sSource;
		}	

		//set the headers		
		$this->sHeader = "MIME-Version: 1.0\n";
		$this->sHeader .= "Content-type: text/html; charset=iso-8859-1\n";
		$this->sHeader .= "From: $this->sEmailFrom \n";
		$this->sHeader .= $this->sHeader;
		
		
		
		$mail->setBodyHtml($this->sMessages);
		$mail->setFrom($config->mail->sender->support->email, $config->mail->sender->support->name);
		$mail->addTo($this->sEmailTo, '');
		$mail->setSubject($this->sSubject);
		//send email
		
        try {
			$mail->send($transport);
			//ini_set(
            //$oSendMail = mail($this->sEmailTo, $this->sSubject, $this->sMessages, $this->sHeader);
            //return true;
        } catch (Exception $e) {
            echo $e->getMessage();
        }
        /*var_dump('to '.$this->sEmailTo,' subject '. $this->sSubject);
		var_dump('MSG'.$this->sMessages, 'HEADER'.$this->sHeader);*/
	}
}
?>