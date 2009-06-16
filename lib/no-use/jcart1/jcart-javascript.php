<?php

// INCLUDE CONFIG SO THIS SCRIPT HAS ACCESS USER FIELD NAMES
include(KUTU_ROOT_DIR.'/lib/jcart/jcart-config.php');

// INCLUDE DEFAULT VALUES SINCE WE NEED TO PASS THE VALUE OF THE UPDATE BUTTON BACK TO jcart.php IF UPDATING AN ITEM QTY
// IF NO VALUE IS SET IN CONFIG, THERE HAS TO BE A DEFAULT VALUE SINCE SIMPLY CHECKING FOR THE VAR ITSELF FAILS
include(KUTU_ROOT_DIR.'/lib/jcart/jcart-defaults.php');

// OUTPUT PHP FILE AS JAVASCRIPT
header('content-type:application/x-javascript');

// PREVENT CACHING
header("Cache-Control: no-cache, must-revalidate"); // HTTP/1.1
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT"); // Date in the past

// CONTINUE THE SESSION
session_start();

?>

// WHEN THE PAGE LOADS
$(function(){

	// HIDE THE UPDATE AND EMPTY BUTTONS
	$('.jcart-hide').remove();

	// WHEN AN ADD-TO-CART FORM IS SUBMITTED
	$('form.jcart').submit(function(){
		// GET INPUT VALUES FOR USE IN AJAX POST
		var itemId = $(this).find('input[name=<?php echo $jcart['item_id']?>]').val();
		var itemPrice = $(this).find('input[name=<?php echo $jcart['item_price']?>]').val();
		var itemName = $(this).find('input[name=<?php echo $jcart['item_name']?>]').val();
		var itemQty = $(this).find('input[name=<?php echo $jcart['item_qty']?>]').val();
		var itemAdd = $(this).find('input[name=<?php echo $jcart['item_add']?>]').val();
	
		// SEND ITEM INFO VIA POST TO INTERMEDIATE SCRIPT WHICH CALLS jcart.php AND RETURNS UPDATED CART HTML
		$.post('<?php echo $jcart['path']?>jcart-relay.php', { <?php echo $jcart['item_id']?>: itemId, <?php echo $jcart['item_price']?>: itemPrice, <?php echo $jcart['item_name']?>: itemName, <?php echo $jcart['item_qty']?>: itemQty, <?php echo $jcart['item_add']?> : itemAdd }, function(data) {

			// REPLACE EXISTING CART HTML WITH UPDATED CART HTML
			$('#jcart').html(data);
			$('.jcart-hide').remove();

			});

		// PREVENT DEFAULT FORM ACTION
		return false;

		})


	// WHEN THE VISITOR HITS THEIR ENTER KEY
	// THE UPDATE AND EMPTY BUTTONS ARE ALREADY HIDDEN
	// BUT THE VISITOR MAY UPDATE AN ITEM QTY, THEN HIT THEIR ENTER KEY BEFORE FOCUSING ON ANOTHER ELEMENT
	// THIS MEANS WE'D HAVE TO UPDATE THE ENTIRE CART RATHER THAN JUST THE ITEM WHOSE QTY HAS CHANGED
	// PREVENT ENTER KEY FROM SUBMITTING FORM SO USER MUST CLICK CHECKOUT OR FOCUS ON ANOTHER ELEMENT WHICH TRIGGERS CHANGE FUNCTION BELOW
	$('#jcart').keydown(function(e) {

		// IF ENTER KEY
		if(e.which == 13) {

		// PREVENT DEFAULT ACTION
		return false;
		}
	});


	// JQUERY live METHOD MAKES FUNCTIONS BELOW AVAILABLE TO ELEMENTS ADDED DYNAMICALLY VIA AJAX

	// WHEN A REMOVE LINK IS CLICKED
	$('#jcart a').live('click', function(){

		// GET THE QUERY STRING OF THE LINK THAT WAS CLICKED
		var queryString = $(this).attr('href');
		queryString = queryString.split('=');

		// THE ID OF THE ITEM TO REMOVE
		var removeId = queryString[1];

		// SEND ITEM ID VIA POST TO INTERMEDIATE SCRIPT WHICH CALLS jcart.php AND RETURNS UPDATED CART HTML
		$.get('<?php echo $jcart['path']?>jcart-relay.php', { jcart_remove: removeId },
			function(data) {

			// REPLACE EXISTING CART HTML WITH UPDATED CART HTML
			$('#jcart').html(data);
			$('.jcart-hide').remove();

			});

		// PREVENT DEFAULT LINK ACTION
		return false;

		})


	// WHEN AN ITEM QTY CHANGES
	$('#jcart input').live('change', function(){

		// GET ITEM ID FROM THE ITEM QTY INPUT ID VALUE, FORMATTED AS jcart-item-id-n
		var updateId = $(this).attr('id');
		updateId = updateId.split('-');

		// THE ID OF THE ITEM TO UPDATE
		updateId = updateId[3];

		// GET THE NEW QTY
		var updateQty = $(this).val();

		// SEND ITEM INFO VIA POST TO INTERMEDIATE SCRIPT WHICH CALLS jcart.php AND RETURNS UPDATED CART HTML
		$.post('<?php echo $jcart['path']?>jcart-relay.php', { item_id: updateId, item_qty: updateQty, jcart_update_item: '<?php echo $jcart['text']['update_button'];?>' }, function(data) {
			// REPLACE EXISTING CART HTML WITH UPDATED CART HTML
			$('#jcart').html(data);
			$('.jcart-hide').remove();

			});

		})

	})

