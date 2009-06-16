<?php

//define('KUTU_ROOT_DIR',dirname(__FILE__));echo KUTU_ROOT
///////////////////////////////////////////////////////////////////////
// REQUIRED SETTINGS

// THE HTML NAME ATTRIBUTES USED IN YOUR ADD-TO-CART FORM
$jcart['item_id']		= 'my_item_id';			// ITEM ID
$jcart['item_name']		= 'my_item_name';		// ITEM NAME
$jcart['item_price']	= 'my_item_price';		// ITEM PRICE
$jcart['item_qty']		= 'my_item_qty';		// ITEM QTY
$jcart['item_add']		= 'my_add_button';		// ADD TO CART BUTTON

// PATH TO THE DIRECTORY CONTAINING JCART FILES
$jcart['path'] = 'http://localhost/kutump-enhanced/lib/jcart/';

// THE PATH AND FILENAME WHERE SHOPPING CART CONTENTS SHOULD BE POSTED WHEN A VISITOR CLICKS THE CHECK OUT BUTTON
// USED AS THE ACTION ATTRIBUTE FOR THE SHOPPING CART FORM
$jcart['form_action']	= '';

///////////////////////////////////////////////////////////////////////
// OPTIONAL SETTINGS

// OVERRIDE DEFAULT CART TEXT
$jcart['text']['cart_title']		= '';		// Shopping Cart
$jcart['text']['single_item']		= '';		// Item
$jcart['text']['multiple_items']	= '';		// Items
$jcart['text']['currency_symbol']	= '';		// $
$jcart['text']['subtotal']			= '';		// Subtotal

$jcart['text']['remove_link']		= '';		// remove
$jcart['text']['update_button']		= '';		// update
$jcart['text']['checkout_button']	= '';		// checkout
$jcart['text']['empty_button']		= '';		// empty
$jcart['text']['empty_message']		= '';		// Your cart is empty!

$jcart['text']['price_error']		= '';		// Invalid price format!
$jcart['text']['quantity_error']	= '';		// Item quantities must be whole numbers!

?>
