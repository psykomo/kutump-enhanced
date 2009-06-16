For added security I would recommend placing the whole paypal folder outside 
of web root, and then include it because in the PayPalNVP.ini file it shows 
values such as password etc.

You need to copy only etc folder together with paypal.php

To use this class you need php5+ and curl

Every action (SetExpressCheckout, DoExpressCheckoutPayment, ...) is a class and 
has its own .ini file located in etc/nvp/NameOfClass.ini.

You can set all default values in their .ini files. If you need to change any of them 
you can do it using method setNVP("NAME", "VALUE"). If you need to change main 
settings - paypal password, ... these settings are located in PayPalNVP.ini. 
You can change it using method as well. Just call setPayPalNVP("NAME", "VALUE").

Examples:

//paypal express checkout
$paypal = new SetExpressCheckout("50.00");  //amount is optional, if not provided,
                                            //amount from .ini file will be used
$paypal->getResponse();

//or you can just use static method
SetExpressCheckout::request();  //all default values from 
                                //SetExpressCheckout.ini are used

if everything is ok, the user is redirected to paypal and then (depending if they 
want to proceed) they are redirected to RETURNURL, or CANCELURL, as specified in 
SetExpressCheckout.ini or the values specified using the setNVP() method

if there are any problems you'll get error message

!!!  If you want to use recurring payment do not forget to set these two values in 
SetExpressCheckout: !!!

$test->setNVP("L_BILLINGTYPE0", "RecurringPayments");           //same for every 
                                                                //recurring payment
$test->setNVP("L_BILLINGAGREEMENTDESCRIPTION0", "description"); //your description

Then in CreateRecurringPaymentsProfile you have to use the same description as in
SetExpressCheckout. Description is fist value passed to constructor. Second value
is amount and it is optional. So if omitted then amount from 
CreateRecurringPaymentsProfile.ini will be used.

Example:

$rPayment = new CreateRecurringPaymentsProfile("description", "50.00");
print_r($rPayment->getResponse());

Values BILLINGPERIOD and BILLINGFREQUENCY are required as well but are set in 
CreateRecurringPaymentsProfile.ini, so you can change them there or you can use
setNVP() method.

For more examples go to examples.php
