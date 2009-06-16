<?php
/*
+-------------------------------------------------------------------------------+
|   Copyright 2008 Peter Reisinger - p.reisinger@gmail.com                      |
|                                                                               |
|   This program is free software: you can redistribute it and/or modify        |
|   it under the terms of the GNU General Public License as published by        |
|   the Free Software Foundation, either version 3 of the License, or           |
|   (at your option) any later version.                                         |
|                                                                               |
|   This program is distributed in the hope that it will be useful,             |
|   but WITHOUT ANY WARRANTY; without even the implied warranty of              |
|   MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the               |
|   GNU General Public License for more details.                                |
|                                                                               |
|   You should have received a copy of the GNU General Public License           |
|   along with this program.  If not, see <http://www.gnu.org/licenses/>.       |
+-------------------------------------------------------------------------------+ 
 */

abstract class PayPalNVP 
{
    //path to main ini files
    const INI_FILES     = "/etc/nvp/";
    //name of this action
    private static $name = "PayPalNVP";
    //holds all name value pairs - NVP
    private $default    = array();

    //set main NVP and calling class's NVP
    protected function __construct($action)
    {
        try {
            $this->checkFile(self::INI_FILES.self::$name.".ini");
            $this->checkFile(self::INI_FILES.$action.".ini");
        } catch (Exception $e) {
            echo "Message: ".$e->getMessage();
        }
        $this->default[self::$name] = parse_ini_file(dirname(__FILE__).self::INI_FILES.self::$name.".ini", false);
        $this->default[$action]     = parse_ini_file(dirname(__FILE__).self::INI_FILES.$action.".ini", false);
    }

    //used by children classes to set NVP
    protected function setDefault($action, $key, $value)
    {
        $this->default[$action][$key] = $value;
    }

    //used by constructor
    private function checkFile($file)
    {
        if (!(file_exists(dirname(__FILE__).$file) && is_readable(dirname(__FILE__).$file))) {
            throw new Exception("Error ".dirname(__FILE__).$file." must exist and be readable");
        }
    }

    //set main NVP
    public function setPayPalNVP ($key, $value)
    {
        $this->default['PayPalNVP'][$key] = $value;
    }

    //used by PPHttpPost, to get needed values
    protected function getNVP ($action, $key = null)
    {
        if ($key == null) {
            return($this->default[$action]);
        } else {
            if (array_key_exists($key, $this->default[$action])) {
                return($this->default[$action][$key]);
            } else {
                throw new Exception("Value ".$key." cannot be found.");
            }
        }
    }

    protected function getEnvironment()
    {
        $environment = $this->getNVP(self::$name, "environment");
        if ($environment == 'live') {
            return "";
        } else {
            return $environment.".";
        }
    }

    protected function PPHttpPost($methodName_) 
    {
        $API_UserName = urlencode($this->getNVP(self::$name, "API_UserName"));
        $API_Password = urlencode($this->getNVP(self::$name, "API_Password"));
        $API_Signature = urlencode($this->getNVP(self::$name, "API_Signature"));
        $API_Endpoint = "https://api-3t.".$this->getEnvironment()."paypal.com/nvp";
        $version = urlencode($this->getNVP(self::$name, "version"));

        $nvpStr = '';
        $NVP = $this->getNVP($methodName_);
        foreach ($NVP as $key=>$value) {
            $nvpStr .= '&'.$key.'='.urlencode($value);
        }

        // setting the curl parameters.
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $API_Endpoint);
        curl_setopt($ch, CURLOPT_VERBOSE, 1);

        // turning off the server and peer verification(TrustManager Concept).
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, FALSE);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, FALSE);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_POST, 1);

        // NVPRequest for submitting to server
        $nvpreq = "METHOD=$methodName_&VERSION=$version&PWD=$API_Password&USER=$API_UserName&SIGNATURE=$API_Signature$nvpStr";

        // setting the nvpreq as POST FIELD to curl
        curl_setopt($ch, CURLOPT_POSTFIELDS, $nvpreq);


        // getting response from server
        $httpResponse = curl_exec($ch);

        if(!$httpResponse) {
            exit("$methodName_ failed: ".curl_error($ch).'('.curl_errno($ch).')');
        }

        // Extract the RefundTransaction response details
        $httpResponseAr = explode("&", $httpResponse);

        $httpParsedResponseAr = array();
        foreach ($httpResponseAr as $i => $value) {
            $tmpAr = explode("=", $value);
            if(sizeof($tmpAr) > 1) {
                $httpParsedResponseAr[$tmpAr[0]] = $tmpAr[1];
            }
        }

        if((0 == sizeof($httpParsedResponseAr)) || !array_key_exists('ACK', $httpParsedResponseAr)) {
            exit("Invalid HTTP Response for POST request($nvpreq) to $API_Endpoint.");
        }

        return $httpParsedResponseAr;
    }

    abstract public function setNVP($key, $value);
    abstract public function getResponse();
}

class SetExpressCheckout extends PayPalNVP
{
    private static $name = "SetExpressCheckout";

    public function __construct($amount = null)
    {
        parent::__construct(self::$name);
        if (!is_null($amount)) {
            $this->setNVP("AMT", $amount);
        }
    }

    public function setNVP($key, $value)
    {
        $this->setDefault(self::$name, $key, $value);
    }
    
    public function getResponse()
    {
        $response = $this->PPHttpPost(self::$name);
        if("Success" == $response["ACK"]) {
            $token = $response['TOKEN'];
            $nvp = $this->getNVP(self::$name);
            $nvpStr = '';
            foreach ($nvp as $key=>$value) {
                $nvpStr .= '&'.$key.'='.urlencode($value);
            }
            Header("Location: https://www.".$this->getEnvironment()."paypal.com/webscr?cmd=_express-checkout&token=$token$nvpStr");
			//Header("Location: https://www.".$this->getEnvironment()."paypal.com/webscr?cmd=_cart&token=$token$nvpStr");
            exit;
        } else  {
            exit ('SetExpressCheckout failed: ' .print_r($response));
        }
    }

    public static function request($amount = null)
    {
        $request = new self($amount);
        return($request->getResponse());
    }
}

class GetExpressCheckoutDetails extends PayPalNVP
{
    private static $name = "GetExpressCheckoutDetails";

    public function __construct()
    {
        parent::__construct(self::$name);
        $this->setNVP("TOKEN", $_GET['token']);
    }

    public function setNVP($key, $value)
    {
        $this->setDefault(self::$name, $key, $value);
    }
    
    public function getResponse()
    {
        return($this->PPHttpPost(self::$name));
    }

    public static function request()
    {
        $request = new self();
        return($request->getResponse());
    }
}

class DoExpressCheckoutPayment extends PayPalNVP
{
    private static $name = "DoExpressCheckoutPayment";

    public function __construct($amount = null)
    {
        parent::__construct(self::$name);
        $this->setNVP("TOKEN", urlencode($_GET['token']));
        $this->setNVP("PAYERID", urlencode($_GET['PayerID']));
        if (!is_null($amount)) {
            $this->setNVP("AMT", $amount);
        }
    }

    public function setNVP($key, $value)
    {
        $this->setDefault(self::$name, $key, $value);
    }
    
    public function getResponse()
    {
        return($this->PPHttpPost(self::$name));
    }

    public static function request($amount = null)
    {
        $request = new self($amount);
        return($request->getResponse());
    }
}

class CreateRecurringPaymentsProfile extends PayPalNVP
{
    private static $name = "CreateRecurringPaymentsProfile";

    //desc = description, has to be same as in SetExpressCheckout
    public function __construct($desc, $amount = null)
    {
        parent::__construct(self::$name);
        $this->setNVP("TOKEN", urlencode($_GET['token']));
        //UTC time  date("Y-m-d\TH:i:s\Z", mktime(0,0,0,date("m"), date("d")+1, date("y")));
        //GMT time  gmdate("M d Y H:i:s", mktime(0,0,0,date("m"), date("d")+1, date("y"))); !!! not working need to find out why
        $this->setNVP("PROFILESTARTDATE", date("Y-m-d\TH:i:s\Z", mktime(0,0,0,date("m"), date("d")+1, date("y"))));
        $this->setNVP("DESC", $desc);
        if (!is_null($amount)) {
            $this->setNVP("AMT", $amount);
        }
    }

    public function setNVP($key, $value)
    {
        $this->setDefault(self::$name, $key, $value);
    }
    
    public function getResponse()
    {
        return($this->PPHttpPost(self::$name));
    }

    public static function request($desc, $amount = null)
    {
        $request = new self($desc, $amount);
        return($request->getResponse());
    }
}
?>
