<?php
class Kutu_Core_Bpm_User
{
	public function changePassword($userGuid, $oldPassword, $newPassword)
	{
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->find($userGuid)->current();
		
		$obj = new Kutu_Crypt_Password();
		if($obj->matchPassword($oldPassword, $row->password))
		{
			$row->password = $obj->encryptPassword($newPassword);
			$row->save();
			return true;
		}
		else
			return false;
	}
	public function forgetPassword($username, $emailAddress)
	{
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->fetchRow("email='$emailAddress' AND username='$username'");
		if(empty($username))
			throw new Zend_Exception("Username can not be empty.");
		if(empty($emailAddress))
			throw new Zend_Exception("Email address can not be empty.");
			
		if(empty($row))
		{
			throw new Zend_Exception("We can not find your account. No data was saved.");
		}
		else
		{
			$obj = new Kutu_Crypt_Password();
			$oldPassword = $obj->decryptPassword($row->password);
			$gman = new Kutu_Core_Guid();
			$randomPassword = $gman->generateGuid();
			$this->changePassword($row->guid, $oldPassword, $randomPassword);
			
			//Send email notification
			$bodyMail = 
"
Dear $row->firstname $row->lastname, 

Your password has been successfully reseted.

Please use your temporary password below to login.

Username: $row->username
New Password: $randomPassword

After you are logged in, please use Change Password facility to change the password to your desired password.

Regards,
LGS Online
";
			$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/config/mail.ini', 'general');

			$options = array('auth' => $config->mail->auth,
			                'username' => $config->mail->username,
			                'password' => $config->mail->password);
			$transport = new Zend_Mail_Transport_Smtp($config->mail->host, $options);

			$mail = new Zend_Mail();
			$mail->setBodyText($bodyMail);
			$mail->setFrom($config->mail->sender->support->email, $config->mail->sender->support->name);
			$mail->addTo($row->email, $row->firstname.' '.$row->lastname);
			$mail->setSubject('Your password has been reseted');
			
			try 
			{
				//echo $config->mail->auth;
				//die();
				$mail->send($transport);
			}
			catch (Zend_Exception $e)
			{
				//no need to do anything. The error is only about sending email.
				//maybe, we may set status in table user indicating that we never send
				// the user with welcome email.
				echo $e->getMessage();
			}
		}
	}
	
	public function editProfileByUser($aData)
	{
		unset($aData['clientId']);
		unset($aData['isActive']);
		unset($aData['registrationDate']);
		unset($aData['activationDate']);
		unset($aData['expirationDate']);
		unset($aData['currentFund']);
		unset($aData['username']);
		unset($aData['password']);
		
		
		return $this->save($aData);
	}
	public function signup($aData)
	{	
		$aData['isActive'] = 1;
		$today = date('Y-m-d h:i:s');
		$aData['registrationDate'] = $today;
		$aData['activationDate'] = $today;
		$aData['expirationDate'] = "2100-12-31 00:00:00";
		
		unset($aData['currentFund']);
		unset($aData['clientId']);
		
		$row = $this->save($aData);
		
		//Must also assign assign user as group:member_free
		$acl = Kutu_Acl_Manager::getAdapter();
		$acl->addUserToGroup($row->username,"member_free");
		
		//if saving to tbl user success, then send confirmation/welcome email
		$config = new Zend_Config_Ini(KUTU_ROOT_DIR.'/application/config/mail.ini', 'general');
		
		$siteOwner = "LGS Online";
		$siteName = "Lgsonline.com";
		$contactEmail = "support@lgsonline.com";
		$message = 
"---------------------------------------------------------------------
Welcome to $siteName
Thank you very much for signing up as a member.
---------------------------------------------------------------------

Dear $row->firstname $row->lastname,

Everyone at $siteOwner welcomes you as a valuable member. You will
now be able to take advantage of all the features available on our
website. You will now be able to access our Legal Database. 

Your new user information is provided below.

Username : $row->username
Password : ****** (hidden for security purposes)

You can login to your account any time. Please visit our website and
click on the \"Login\" link on our home page. If you have forgotten your
password, you can recover it back. Goto our Login screen and click
on the \"Password Reminder\" link on the navigation bar.

If you have any questions or if you need any help, please feel free
to e-mail us at $contactEmail. Thank you and welcome again
to $siteName.

Sincerely,
$siteOwner Team
http://www.$siteName/
Date: $today

---------------------------------------------------------------------
NOTE: If you have not really signed up for this account and if you
think that someone else has used your e-mail address, please send us
an e-mail at support@lgsonline.com.
--------------------------------------------------------------------- ";

		$config = array('auth' => 'login',
		                'username' => 'putra@langit.biz',
		                'password' => 'putra');

		$transport = new Zend_Mail_Transport_Smtp('mail.langit.biz', $config);

		$mail = new Zend_Mail();
		$mail->setBodyText($message);
		$mail->setFrom('support@lgsonline.com', 'LGS Online');
		$mail->addTo($row->email, $row->firstname.' '.$row->lastname);
		$mail->setSubject('Welcome to LGS Online');
		
		try 
		{
			$mail->send($transport);
		}
		catch (Zend_Exception $e)
		{
			//no need to do anything. The error is only about sending email.
			//maybe, we may set status in table user indicating that we never send
			// the user with welcome email.
		}
	}
	
	public function save($aData)
	{
		
		if(isset($aData['fullname']) && !empty($aData['fullname']))
		{
			$aData['firstname'] = $aData['fullname'];
		}
		
		
		if(empty($aData['firstname']))
			throw new Zend_Exception('Firstname can not be EMPTY!');
		
		$tblUser = new Kutu_Core_Orm_Table_User();
		
		$gman = new Kutu_Core_Guid();
		$guid = (isset($aData['guid']) && !empty($aData['guid']))? $aData['guid'] : $gman->generateGuid();
		
		//if not empty, there are 2 possibilities
		$tblUser = new Kutu_Core_Orm_Table_User();
		$row = $tblUser->fetchRow("guid='$guid'");
		
		if(empty($row))
		{
			if(empty($aData['username']))
				throw new Zend_Exception('Username can not be EMPTY!');
			if(empty($aData['password']))
				throw new Zend_Exception('Password can not be EMPTY!');
				
			$row = $tblUser->createRow();
			
			if(isset($aData['username']) && !empty($aData['username']))
			{
				//check if username was already taken
				$username = $aData['username'];
				$tblUser = new Kutu_Core_Orm_Table_User();
				$rowUsername = $tblUser->fetchRow("username='$username'");
				if($rowUsername)
				{
					throw new Zend_Exception('Username exists');
				}
				
				$row->username = $aData['username'];
			}
			if(isset($aData['password']) && !empty($aData['password']))
			{
				$password = $aData['password'];
				$crypt = new Kutu_Crypt_Password();
				$password = $crypt->encryptPassword($password);
				
				$row->password = $password;
			}
			
		}
		
		if(isset($aData['firstname']))
			$row->firstname = $aData['firstname'];
		if(isset($aData['lastname']))
			$row->lastname = $aData['lastname'];
		if(isset($aData['email']))
			$row->email = $aData['email'];
		if(isset($aData['bbPin']))
			$row->bbPin = $aData['bbPin'];
		if(isset($aData['clientId']))
			$row->clientId = $aData['clientId'];
		if(isset($aData['mainAddress']))
			$row->mainAddress = $aData['mainAddress'];
		if(isset($aData['city']))
			$row->city = $aData['city'];
		if(isset($aData['state']))
			$row->state = $aData['state'];
		if(isset($aData['zip']))
			$row->zip = $aData['zip'];
		if(isset($aData['phone']))
			$row->phone = $aData['phone'];
		if(isset($aData['fax']))
			$row->fax = $aData['fax'];
		if(isset($aData['url']))
			$row->url = $aData['url'];
		if(isset($aData['countryId']))
			$row->countryId = $aData['countryId'];
		if(isset($aData['company']))
			$row->company = $aData['company'];
		if(isset($aData['companySizeId']))
			$row->companySizeId = $aData['companySizeId'];
		if(isset($aData['jobId']))
			$row->jobId = $aData['jobId'];
		if(isset($aData['industryId']))
			$row->industryId = $aData['industryId'];
		
		if(isset($aData['isActive']))
			$row->isActive = $aData['isActive'];
		if(isset($aData['registrationDate']))
			$row->registrationDate = $aData['registrationDate'];
		if(isset($aData['activationDate']))
			$row->activationDate = $aData['activationDate'];
		if(isset($aData['activationCode']))
			$row->activationCode = $aData['activationCode'];
		if(isset($aData['expirationDate']))
			$row->expirationDate = $aData['expirationDate'];
			
		$row->save();
			
		return $row;
	}
	public function delete()
	{
		//delete from KutuUser
		//delete from PHPGACL (use PHPGACL function to delete user)
		//delete from all table that Chenri made (Store related tables)
	}
}
?>