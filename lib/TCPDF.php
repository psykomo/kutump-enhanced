<?php
/*This file must be put here in ../lib.

This file is a temporary solution so FPDI can be run to generate sample page.

We need this file because FPDI call for TCPDF class, which will cause error because we use Zend_Loader::registerAutoLoad.
Zend will try to find the class using autoload, and if it can not find it, it will generate error.
*/
?>