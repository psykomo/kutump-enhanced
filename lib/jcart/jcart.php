<?php
// SESSION BASED SHOPPING CART CLASS FOR JCART

/**********************************************************************
Based on Webforce Cart v.1.5
(c) 2004-2005 Webforce Ltd, NZ, http://www.webforce.co.nz/cart/
**********************************************************************/

// USER CONFIG
require_once('jcart-config.php');
// DEFAULT CONFIG VALUES
require_once('jcart-defaults.php');

// JCART
class jCart {
	var $total = 0;
	var $itemcount = 0;
	var $items = array();
	var $itemprices = array();
	var $itemqtys = array();
	var $iteminfo = array();

	// CONSTRUCTOR FUNCTION
	function cart() {}

	// GET CART CONTENTS
	function get_contents()
		{
		$items = array();
		foreach($this->items as $tmp_item)
			{
			$item = FALSE;

			$item['id'] = $tmp_item;
			$item['qty'] = $this->itemqtys[$tmp_item];
			$item['price'] = $this->itemprices[$tmp_item];
			$item['info'] = $this->iteminfo[$tmp_item];
			$item['subtotal'] = $item['qty'] * $item['price'];
			$items[] = $item;
			}
		return $items;
		}


	// ADD AN ITEM
	function add_item($itemid,$qty=1,$price = FALSE, $info = FALSE)
		{
		if(!$price)
			{
			$price = wf_get_price($itemid,$qty);
			}

		if(!$info)
			{
			$info = wf_get_info($itemid);
			}
			
		// THE ITEM IS ALREADY IN THE CART SO INCREASE THE QTY
		if(@$this->itemqtys[$itemid] > 0)
			{
			$this->itemqtys[$itemid] = $qty + $this->itemqtys[$itemid];
			$this->_update_total();
			}
		else
			{
			$this->items[] = $itemid;
			$this->itemqtys[$itemid] = $qty;
			$this->itemprices[$itemid] = $price;
			$this->iteminfo[$itemid] = $info;
			}
		$this->_update_total();
		}


	// CHANGE AN ITEM QTY
	function edit_item($itemid,$qty)
		{
		if($qty < 1)
			{
			$this->del_item($itemid);
			}
		else
			{
			$this->itemqtys[$itemid] = $qty;

			// UNCOMMENT IF USING wf_get_price FUNCTION
			// $this->itemprices[$itemid] = wf_get_price($itemid,$qty);
			}
		$this->_update_total();
		}


	// REMOVE AN ITEM
	function del_item($itemid)
		{
		$ti = array();
		$this->itemqtys[$itemid] = 0;
		foreach($this->items as $item)
			{
			if($item != $itemid)
				{
				$ti[] = $item;
				}
			}
		$this->items = $ti;
		$this->_update_total();
		}


	// EMPTY THE CART
	function empty_cart()
		{
		$this->total = 0;
		$this->itemcount = 0;
		$this->items = array();
		$this->itemprices = array();
		$this->itemqtys = array();
		$this->iteminfo = array();
		}


	// INTERNAL FUNCTION TO RECALCULATE TOTAL
	function _update_total()
		{
		$this->itemcount = 0;
		$this->total = 0;
		if(sizeof($this->items > 0))
			{
			foreach($this->items as $item)
				{
				$this->total = $this->total + ($this->itemprices[$item] * $this->itemqtys[$item]);
				// TOTAL ITEMS IN CART (ORIGINAL wfCart COUNTED TOTAL NUMBER OF LINE ITEMS)
				$this->itemcount += $this->itemqtys[$item];
				}
			}
		}


	// PROCESS AND DISPLAY CART
	function display_cart($jcart)
		{
		// JCART ARRAY HOLDS USER CONFIG SETTINGS
		extract($jcart);
		
		// ASSIGN USER CONFIG VALUES TO POST VARS
		// VALUES ARE HTML NAME ATTRIBUTES FROM THE ADD-TO-CART FORM
		@$item_id = $_POST[$item_id];
		@$item_qty = ltrim($_POST[$item_qty], '-'); // PREVENT QTY FROM BEING NEGATIVE
		@$item_price = ltrim($_POST[$item_price], '-'); // PREVENT PRICE FROM BEING NEGATIVE
		@$item_name = $_POST[$item_name];

		// ADD ITEM
		if(@$_POST[$item_add])
			{
			$sanitized_item_id = filter_var($item_id, FILTER_SANITIZE_SPECIAL_CHARS);
			$valid_item_qty = filter_var($item_qty, FILTER_VALIDATE_INT);
			$valid_item_price = filter_var($item_price, FILTER_VALIDATE_FLOAT);
			$sanitized_item_name = filter_var($item_name, FILTER_SANITIZE_SPECIAL_CHARS);

			// VALIDATION
			if (!$valid_item_qty)
				{
				$error_message = $text['quantity_error'];
				}
			else if (!$valid_item_price)
				{
				$error_message = $text['price_error'];
				}
			else
				{
				// ADD THE ITEM
				$this->add_item($sanitized_item_id, $valid_item_qty, $valid_item_price, $sanitized_item_name);
				}
			}

		// REMOVE ITEM
		/*
		GET VAR COMES FROM A LINK, WITH THE ITEM ID TO BE REMOVED IN ITS QUERY STRING
		AFTER AN ITEM IS REMOVED ITS ID STAYS SET IN THE QUERY STRING, PREVENTING THE SAME ITEM FROM BEING ADDED BACK TO THE CART
		SO WE CHECK TO MAKE SURE ONLY THE GET VAR IS SET, AND NOT THE POST VARS

		USING POST VARS TO REMOVE ITEMS DOESN'T WORK BECAUSE WE HAVE TO PASS THE ID OF THE ITEM TO BE REMOVED AS THE VALUE OF THE BUTTON
		IF USING AN INPUT WITH TYPE SUBMIT, ALL BROWSERS DISPLAY THE ITEM ID, INSTEAD OF ALLOWING FOR USER FRIENDLY TEXT SUCH AS 'remove'
		IF USING AN INPUT WITH TYPE IMAGE, INTERNET EXPLORER DOES NOT SUBMIT THE VALUE, ONLY X AND Y COORDINATES WHERE BUTTON WAS CLICKED
		CAN'T USE A HIDDEN INPUT EITHER SINCE THE CART FORM HAS TO ENCOMPASS ALL ITEMS TO RECALCULATE TOTAL WHEN A QUANTITY IS CHANGED, WHICH MEANS THERE ARE MULTIPLE REMOVE BUTTONS AND NO WAY TO ASSOCIATE THEM WITH THE CORRECT HIDDEN INPUT
		*/
		if(@$_GET['jcart_remove'] && @!$_POST[$item_add] && @!$_POST['jcart_update_cart'] && @!$_POST['jcart_check_out'])
			{
			// ENSURE THE VALUE IS AN INTEGER
			//$rid = intval($_GET['jcart_remove']);
			$rid = $_GET['jcart_remove'];
			// REMOVE THE ITEM
			//die($rid);
			$this->del_item($rid);
			}

		// EMPTY CART
		if(@$_POST['jcart_empty'])
			{
			$this->empty_cart();
			}

		// UPDATE ALL ITEMS IN CART SINCE VISITOR MAY UPDATE MULTIPLE FIELDS BEFORE CLICKING UPDATE
		// ONLY USED WHEN JAVASCRIPT IS DISABLED
		// WHEN JAVASCRIPT IS ENABLED, THE CART IS UPDATED WHEN AN ITEM QTY IS CHANGED
		if(@$_POST['jcart_update_cart'])
			{
			// POST VALUE IS AN ARRAY OF ALL ITEM IDs IN THE CART
			$item_ids = $_POST['jcart_item_id'];

			// IF NO ITEM IDs, THE CART IS EMPTY
			if ($item_ids)
				{
				// POST VALUE IS AN ARRAY OF ALL ITEM QUANTITIES IN THE CART
				// TREAT VALUES AS A STRING FOR VALIDATION
				$item_qtys = implode($_POST['jcart_item_qty']);

				$valid_item_qtys = filter_var($item_qtys, FILTER_VALIDATE_INT);

				// VALIDATION
				// ITEM QTY CAN ONLY BE AN INTEGER OR ZERO
				if (!$valid_item_qtys && $item_qtys !== '0')
					{
					$error_message = $text['quantity_error'];
					}

				// UPDATE ITEMS
				else
					{
					// THE INDEX OF THE ITEM AND ITS QUANTITY IN THEIR RESPECTIVE ARRAYS
					$count = 0;

					// FOR EACH ITEM IN THE CART
					foreach ($item_ids as $item_id)
						{
						// SANITIZE THE ITEM ID
						$sanitized_item_id = filter_var($item_id, FILTER_SANITIZE_SPECIAL_CHARS);

						// GET THE ITEM QTY AND DOUBLE-CHECK THAT THE VALUE IS AN INTEGER
						$update_item_qty = intval($_POST['jcart_item_qty'][$count]);

						// UPDATE THE ITEM
						$this->edit_item($sanitized_item_id, $update_item_qty);

						// INCREMENT INDEX FOR THE NEXT ITEM
						$count++;
						}
					}
				}
			}


		// CHECKING POST VALUE AGAINST $text ARRAY FAILS??
		// HAVE TO CHECK AGAINST $jcart ARRAY
		if (@$_POST['jcart_update_item'] == $jcart['text']['update_button'])
			{
			// SANITIZE THE ITEM ID
			$item_id = $_POST['item_id'];
			$sanitized_item_id = filter_var($item_id, FILTER_SANITIZE_SPECIAL_CHARS);

			// GET THE ITEM QTY AND CHECK THAT THE VALUE IS AN INTEGER
			$item_qty = $_POST['item_qty'];
			$valid_item_qty = filter_var($item_qty, FILTER_VALIDATE_INT);

			// VALIDATION
			// ITEM QTY CAN ONLY BE AN INTEGER, OR ZERO, OR EMPTY
			if (!$valid_item_qty && $item_qty !== '0' && $item_qty !== '')
				{
				$error_message = $text['quantity_error'];
				}
			else
				{
				// UPDATE THE ITEM
				$this->edit_item($sanitized_item_id, $valid_item_qty);
				}
			}


		// OUTPUT THE CART

		// DETERMINE WHICH TEXT TO USE FOR THE NUMBER OF ITEMS IN THE CART
		if ($this->itemcount >= 0)
			{
			$text['items_in_cart'] = $text['multiple_items'];
			}
		if ($this->itemcount == 1)
			{
			$text['items_in_cart'] = $text['single_item'];
			}

		// IF THERE'S AN ERROR MESSAGE WRAP IT IN SOME HTML
		if (@$error_message)
			$error_message = "<p class='jcart-error'>$error_message</p>";

		// DISPLAY THE CART HEADER
		echo "<!-- BEGIN JCART -->\n<div id='jcart'>\n";
		echo @$error_message . "\n";
		echo "<form method='post' action='" . $form_action . "'>\n\n";
		echo "<table border='1'>\n";
		echo "<tr>\n";
		echo "<th colspan='3'>\n";
		echo "<strong id='jcart-title'>" . $text['cart_title'] . "</strong> (" . $this->itemcount . "&nbsp;" . $text['items_in_cart'] .")\n";
		echo "</th>\n";
		echo "</tr>". "\n";

		// IF ANY ITEMS IN THE CART
		if($this->itemcount > 0)
			{
			// DISPLAY LINE ITEMS
			foreach($this->get_contents() as $item)
				{
				echo "<tr>\n";
				// ADD THE ITEM ID AS THE INPUT ID ATTRIBUTE
				// THIS ALLOWS US TO ACCESS THE ITEM ID VIA JAVASCRIPT ON QTY CHANGE, AND THEREFORE UPDATE THE CORRECT ITEM
				// NOTE THAT THE ITEM ID IS ALSO PASSED AS A SEPARATE FIELD FOR PROCESSING VIA PHP
				echo "<td class='jcart-item-qty'>\n";
				echo "<input type='text' size='2' id='jcart-item-id-" . $item['id'] . "' name='jcart_item_qty[ ]' value='" . $item['qty'] . "' />\n";
				echo "</td>\n";
				echo "<td class='jcart-item-name'>\n";
				echo $item['info'] . "<input type='hidden' name='jcart_item_info[ ]' value='" . $item['info'] . "' />\n";
				echo "<input type='hidden' name='jcart_item_id[ ]' value='" . $item['id'] . "' />\n";
				echo "</td>\n";
				echo "<td class='jcart-item-price'><span>\n";
				echo $text['currency_symbol'] . number_format($item['subtotal'],2) . "</span><input type='hidden' name='jcart_item_price[ ]' value='" . $item['price'] . "' />\n";
				echo "<a class='jcart-remove' href='?jcart_remove=" . $item['id'] . "'>" . $text['remove_link'] . "</a>\n";
				echo "</td>\n";
				echo "</tr>\n";
				}
			}

		// THE CART IS EMPTY
		else
			{
			echo "<tr><td colspan='3' class='empty'>" . $text['empty_message'] . "</td></tr>\n";
			}

		// DISPLAY THE CART FOOTER
		echo "<tr>\n";
		echo "<th colspan='3'>\n";
		echo "<input type='submit' id='jcart-checkout' name='jcart_checkout' class='jcart-button' value='" . $text['checkout_button'] . "' />\n";
		echo "<span id='jcart-subtotal'>" . $text['subtotal'] . ": <strong>" . $text['currency_symbol'] . number_format($this->total,2) . "</strong></span>\n";
		echo "</th>\n";
		echo "</tr>\n";
		echo "</table>\n\n";
		echo "<div class='jcart-hide'>\n";
		echo "<input type='submit' name='jcart_update_cart' value='" . $text['update_button'] . "' class='jcart-button' />\n";
		echo "<input type='submit' name='jcart_empty' value='" . $text['empty_button'] . "' class='jcart-button' />\n";
		echo "</div>\n";
		echo "</form>\n";
		echo "</div>\n<!-- END JCART -->\n";
		}


	}
	
?>