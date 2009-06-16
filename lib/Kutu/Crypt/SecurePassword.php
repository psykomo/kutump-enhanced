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

class Kutu_Crypt_SecurePassword
{    
	var $k;
   	function __construct($m){
    	$this->k = $m;
   	}
   	function ed($t) {
    	$r = md5($this->k);
      	$c=0;
      	$v = "";
      	for ($i=0;$i<strlen($t);$i++) {
        	if ($c==strlen($r)) $c=0;
         	$v.= substr($t,$i,1) ^ substr($r,$c,1);
         	$c++;
      	}
      	return $v;
   	}
   	function crypt($t){
    	srand((double)microtime()*1000000);
      	$r = md5(rand(0,32000));
      	$c=0;
      	$v = "";
      	for ($i=0;$i<strlen($t);$i++){
        	if ($c==strlen($r)) $c=0;
         	$v.= substr($r,$c,1) .
            	(substr($t,$i,1) ^ substr($r,$c,1));
         	$c++;
      	}
      	return base64_encode($this->ed($v));
   	}
   	function decrypt($t) {
    	$t = $this->ed(base64_decode($t));
      	$v = "";
      	for ($i=0;$i<strlen($t);$i++){
        	$md5 = substr($t,$i,1);
         	$i++;
         	$v.= (substr($t,$i,1) ^ $md5);
      	}
      	return $v;
   	} 
   
}//EO Class

