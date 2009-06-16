<?php
/**
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.txt.
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to calisza@gmail.com so we can send you a copy immediately.
 *
 * @package    FlexiDev_View
 * @copyright  Copyright (c) 2007 - 2008,  Flexible Developments - Barry Roodt (http://calisza.wordpress.com)
 * @license    New BSD License
 */
 class Kutu_View_Standalone {
 	public $tmplVars;
 	public $view;

 	/**
 		Constructor
 	*/
 	public function __construct(){

 		$this->tmplVars = array();
 		// Instantiate our Zend View
		$this->view = new Zend_View();

		/**
		 * reset our basepath and scriptpaths, this is because we don't need the 'scripts', 'helpers' and 'filters' sub folders
		 * relative to this script - of course you could change this as your needs require
		 */
		$this->view->setBasePath("./views");
		$this->view->setScriptPath("./views");

		// set and assign some global vars to our template
		// this is just an example - you can add more here
		$globals  = array("base_url" => $_SERVER["HTTP_HOST"]);
		$this->view->assign($globals);
 	}
 	/**
	 * Method to add to our template variable stack
	 * @param string variable name
	 * @param mixed value
	 */
 	public function addTmplVar($key, $value){
 		/* If our stack doesn't already exist, create a new array */
 		if (!is_array($this->tmplVars)){
 			$this->tmplVars = array($key=>$value);
 		} else {
 			// Add to our variable stack
 			$this->tmplVars[$key] = $value;
 		}
 	}
 	/**
	 * Method to render the required view/template
	 * @param string template script name
	 * @param bool (optional) clear variable stack (default=true)
	 */
 	public function getTemplate($tmpl, $clearVars=true){
 		/* Add our variable stack to the template */
		$this->view->assign($this->tmplVars);
		/* If required, clear our stack so that we can start with a fresh template on the next call */
		if ($clearVars)
			$this->tmplVars = "";

		/* Return the rendered template */
		return $this->view->render($tmpl);
	}

 }

?>