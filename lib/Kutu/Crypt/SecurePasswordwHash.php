<?php

/**
* Class : Secure Password
*
* @PHPVER  :  5.0
* @author  :  MA Razzaque Rupom <rupom_315@yahoo.com>, <rupom.bd@gmail.com>
*             Moderator, phpResource (http://groups.yahoo.com/group/phpresource/)
*             URL: http://www.rupom.info  
*        
* @version :  1.0
* Date     :  05/23/2006
* Purpose  :  Generating and Matching Secure and Strong Password
*/

class Kutu_Crypt_SecurePasswordwHash
{
	 private $salt;
	 private $saltLength = 20; //+ve and <=40
	  
	 /**
	 * Initializes Salt
	 * @param Salt
	 * @return none
	 */ 
   function initSalt($salt = null)
   {
      $this->salt = !empty($salt) ? $this->getSalt($salt) : $this->randomSalt();      
   }
   
   /**
	 * Generates password hash
	 * @param plain password text
	 * @return secure password hash
	 */
   function generatePasswordHash($passwordText)
   {
   	  //data is not only plain, may be binary also
   	  $decodedSalt = base64_decode($this->salt);
      
      //password from salt and sha1(of decoded salt and plain password)     
      $password    = $decodedSalt.sha1($decodedSalt.$passwordText); 
      
      return $password;
   }
   
   /**
   * Creates Random Salt
   * @param none
   * @return Random Salt
   */
   function randomSalt()
   {
      mt_srand($this->makeSeed()); //since PHP 4.2.0, seed is no longer needed
      $randVal = mt_rand(); //random value
      return $this->getSalt($randVal);
   }

   /**
   * Gets Salt
   * @param string to be formatted-salt
   * @return String Salt
   */   
   function getSalt($salt)
   {
      $saltStr = sha1($salt); //use of secure hash algorithm-1   	
      $saltStr = substr($saltStr,0,$this->saltLength); //salt according to saltLength
      return $saltStr;
 	
   }
   /** 
   * Seed with microseconds
   * @param none
   * @return float
   */
   function makeSeed() 
   {
       list($usec, $sec) = explode(' ', microtime());
       return (float) $sec + ((float) $usec * 100000);
   }
   
   /**
   * Manipulates hash and matches it with password 
   * @param plain password and digested password(hash)
   * @return boolean true in success, false otherwise
   */
   function matchPassword($plainPassword, $passwordDigest)
   {            
      $tempStr       = str_repeat('1',$this->saltLength);
      
      //No. of salt chars in the digest
      $saltStrLength = strlen(base64_decode($tempStr));      
      
      //Retrieving salt string 
      $saltStr       = base64_encode(substr($passwordDigest, 0, $saltStrLength)); 
      
      //Extracting sha1() digested string
      $hashPrev      = substr($passwordDigest, $saltStrLength);            
      
      //Creating sha1() digest by the $plainPassword
      $hashNow       = sha1(base64_decode($saltStr).$plainPassword);      
            
      // Comparing the given one and the newly created one
      if(!strcmp($hashPrev, $hashNow))
      {
         return true; //Password is correct
      }
      
      return false; //Password is incorrect
   }
   
   /**
   * Formats output (for debugging purpose)
   * @param debug data
   * @return none
   */
   function dBug($dump)
   {
      echo "<PRE>";	
      print_r($dump);
      echo "</PRE>";	
   }
	
}

?>