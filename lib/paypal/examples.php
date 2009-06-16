<?php
/*
+-------------------------------------------------------------------------------+
|   Here you can find short examples. Bear in mind that this is firs release    |
|   so you need to TEST it before. If you have any questions, suggestions,      |
|   bugs etc. please contact me. Email:                                         |
|                       p.reisinger@gmail.com                                   |
+-------------------------------------------------------------------------------+
 */

//include main file, make sure that /etc folder
//is at the same level as paypal.php
require_once("paypal.php");

/*
+-------------------------------------------------------------------------------+
|   Very simple implementation - using static methods                           |
+-------------------------------------------------------------------------------+
 */

//setExpressCheckout
//if successful/canceled redirects to url specified in .ini file
SetExpressCheckout::request("50.00");   //amount is optional, if not set
                                        //amount from .ini file is used

//GetExpressCheckoutDetails - optional, returns customer's details from paypal
//this has to be one the page specified in SetExpressCheckout.ini
$response = GetExpressCheckoutDetails::request();     

//DoExpressCheckoutPayment - returns success or failure
$response = DoExpressCheckoutPayment::request("50.00");     //amount is optional, if not set
                                                //amount from .ini file is used   

/*
+-------------------------------------------------------------------------------+
|   Recurring payments implementation                                           |
+-------------------------------------------------------------------------------+
 */

$test = new SetExpressCheckout("50.00");    //amount - optional, if not set
                                            //amount form .ini file will be used
$test->setNVP("L_BILLINGTYPE0", "RecurringPayments");
$test->setNVP("L_BILLINGAGREEMENTDESCRIPTION0", "book order");
$test->getResponse();
//!!! you cannot use static method if you wan to use recurring payment, because
//you neet to set L_BILLINGTYPEn and L_BILLINGAGREEMENTDESCRIPTIONn

//get express checkout details (optional)
//the code below has to be placed in the file specified in RETURNURL 
//(in SetExpressCheckout.ini) or setNVP() method
$result = GetExpressCheckoutDetails::request();     
//there's no need to use: $data = new GetExpressCheckoutDetails();
//but if you want, you can
// now you can save data in database or whatever you want

//do express checkout returns success or failure
$doPay = new DoExpressCheckoutPayment("50.00");    //amount is optional but should be
                                                    //similar to the one set in
                                                    //SetExpressCehckout
$result = $doPay->getResponse();
//result holds response from PayPal so you need to check if it was successful

//create recurring payments profile response is success or failure
///first value is description and is required - same as 
//L_BILLINGAGREEMENTDESCRIPTIONn in SetExpressCheckout.
//second value is amountt
$recPay = new CreateRecurringPaymentsProfile("book order", "50.00");  
$result = $recPay->getResponse();
//result holds response from PayPal so you need to check if it was successful
?>
