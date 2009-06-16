<?php

/**
 * module Authentication
 * 
 * @author Himawan Anindya Putra <putra@langit.biz>
 * @package Kutu
 * 
 */

class Hap_Auth_Adapter_DbTable extends Zend_Auth_Adapter_DbTable
{
	function authenticate()
	{
		$exception = null;

        if ($this->_tableName == '') {
            $exception = 'A table must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_identityColumn == '') {
            $exception = 'An identity column must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_credentialColumn == '') {
            $exception = 'A credential column must be supplied for the Zend_Auth_Adapter_DbTable authentication adapter.';
        } elseif ($this->_identity == '') {
            $exception = 'A value for the identity was not provided prior to authentication with Zend_Auth_Adapter_DbTable.';
        } elseif ($this->_credential === null) {
            $exception = 'A credential value was not provided prior to authentication with Zend_Auth_Adapter_DbTable.';
        }

        if (null !== $exception) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception($exception);
        }

        // create result array
        $authResult = array(
            'code'     => Zend_Auth_Result::FAILURE,
            'identity' => $this->_identity,
            'messages' => array()
            );


        // build credential expression
        if (empty($this->_credentialTreatment) || (strpos($this->_credentialTreatment, "?") === false)) {
            $this->_credentialTreatment = '?';
        }

        $credentialExpression = new Zend_Db_Expr(
            $this->_zendDb->quoteInto(
                $this->_zendDb->quoteIdentifier($this->_credentialColumn)
                . ' = ' . $this->_credentialTreatment, $this->_credential
                )
            . ' AS zend_auth_credential_match'
            );

        // get select
        /*$dbSelect = $this->_zendDb->select();
        $dbSelect->from($this->_tableName, array('*', $credentialExpression))
                 ->where($this->_zendDb->quoteIdentifier($this->_identityColumn) . ' = ?', $this->_identity);*/

        // query for the identity
        try {
            //$resultIdentities = $this->_zendDb->fetchAll($dbSelect->__toString());
            $resultIdentities = $this->_zendDb->fetchAll('Select guid, username, password from '.$this->_tableName.' where '.$this->_identityColumn. "='" .$this->_identity."'");
        } catch (Exception $e) {
            /**
             * @see Zend_Auth_Adapter_Exception
             */
            require_once 'Zend/Auth/Adapter/Exception.php';
            throw new Zend_Auth_Adapter_Exception('The supplied parameters to Zend_Auth_Adapter_DbTable failed to '
                                                . 'produce a valid sql statement, please check table and column names '
                                                . 'for validity.');
        }
        
        

        if (count($resultIdentities) < 1) {
            $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_NOT_FOUND;
            $authResult['messages'][] = 'A record with the supplied identity could not be found.';
            return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
        } elseif (count($resultIdentities) > 1) {
            $authResult['code'] = Zend_Auth_Result::FAILURE_IDENTITY_AMBIGUOUS;
            $authResult['messages'][] = 'More than one record matches the supplied identity.';
            return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
        }

        $resultIdentity = $resultIdentities[0];
        
        $obj = new Kutu_Crypt_Password();

        //if(!$this->_httpClient)
        if(true)
        {
	        //if ($resultIdentity['zend_auth_credential_match'] != '1') {
	        if(!$obj->matchPassword($this->_credential, $resultIdentity[$this->_credentialColumn]))
	        {	
	        	
	        	$authResult['code'] = Zend_Auth_Result::FAILURE_CREDENTIAL_INVALID;
	            $authResult['messages'][] = 'Supplied credential is invalid.';
	            return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
	        }
        }

        unset($resultIdentity['zend_auth_credential_match']);
        $this->_resultRow = $resultIdentity;
		
		$registry = Zend_Registry::getInstance(); 
		$config = $registry->get('config');
		
		if(strtolower($config->session->savehandler) != 'directdb')
		{
			throw new Zend_Exception('Session configuration savehandler: '. $config->session->savehandler. ' is not supported for checking user is already login feature!');
			
			//TODO we can avoid above exception by assuming or setting so that the session server is the same server as auth server.
			//$isAlreadyLogin = $this->_zendDb->fetchAll("SELECT sessionId FROM KutuSession WHERE sessionData LIKE '%$this->_identity%'");
		}
		else 
		{
			$db = Zend_Db::factory($config->session->config->db->adapter, $config->session->config->db->param->toArray());
        
        	$isAlreadyLogin = $db->fetchAll("SELECT sessionId FROM KutuSession WHERE sessionData LIKE '%$this->_identity%'");
		}
		
        
		//if(count($isAlreadyLogin))
		if(false)
    	{
    		
            $authResult['code'] = -51; //Zend_Auth_Result::FAILURE_UNCATEGORIZED;
            $authResult['messages'][] = 'You already login';
            return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
    	}
        
        
        $authResult['code'] = Zend_Auth_Result::SUCCESS;
        $authResult['messages'][] = 'Authentication successful.';
        return new Zend_Auth_Result($authResult['code'], $authResult['identity'], $authResult['messages']);
	}
}
