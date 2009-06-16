<?php
// INCLUDE JCART BEFORE SESSION START
include 'jcart/jcart.php';

// START SESSION
session_start();

// INITIALIZE JCART AFTER SESSION START
$cart =& $_SESSION['jCart']; if(!is_object($cart)) $cart = new jCart();

?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN"
"http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">

<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">
	<head>
		<meta http-equiv="content-type" content="text/html; charset=utf-8" />

		<title>jCart - Free AJAX/PHP shopping cart</title>

		<style type="text/css">
			* { margin:0; padding:0; }

			html { background:#fff; font-family:trebuchet ms, candara, sans-serif; font-size:62.5%; }
			body { font-size:1.5em; }

			#wrapper { margin:30px auto 250px auto; width:890px; border:solid 1px #ccc; padding:30px; background:#efefef; }

			#sidebar { width:35%; float:right; }

			#content  { width:60%; }

			.jcart { margin:0 20px 20px 0; padding-top:20px; border:dashed 2px #66cc66; float:left; background:#fff; text-align:center; }
			.jcart ul { margin:0; list-style:none; padding:0 20px; text-align:left; }
			.jcart fieldset { border:0; }
			.jcart strong { color:#000066; }
			.jcart .button { margin:20px; padding:5px; }

			.clear { clear:both; }
		</style>


		<link rel="stylesheet" type="text/css" media="screen, projection" href="jcart/jcart.css" />

		<script type="text/javascript" src="jcart/jquery-1.3.2.min.js"></script>

		<script type="text/javascript" src="jcart/jcart-javascript.php"></script>

	</head>
	<body>

		<div id="wrapper">

			<div id="sidebar">
				<?php $cart->display_cart($jcart);?>
			</div>

			<div id="content">

				<form method="post" action="" class="jcart">
					<fieldset>
						<input type="hidden" name="my_item_id" value="1" />
						<input type="hidden" name="my_item_name" value="Soccer Ball" />
						<input type="hidden" name="my_item_price" value="25.00" />

						<ul>
							<li><strong>Soccer Ball</strong></li>
							<li>Price: $25.00</li>
							<li>
								<label>Qty: <input type="text" name="my_item_qty" value="1" size="3" /></label>
							</li>
						</ul>

						<input type="submit" name="my_add_button" value="add to cart" class="button" />
					</fieldset>
				</form>

				<form method="post" action="" class="jcart">
					<fieldset>
						<input type="hidden" name="my_item_id" value="2" />
						<input type="hidden" name="my_item_name" value="Baseball Mitt" />
						<input type="hidden" name="my_item_price" value="19.50" />

						<ul>
							<li><strong>Baseball Mitt</strong></li>
							<li>Price: $19.50</li>
							<li>
								<label>Qty: <input type="text" name="my_item_qty" value="1" size="3" /></label>
							</li>
						</ul>

						<input type="submit" name="my_add_button" value="add to cart" class="button" />
					</fieldset>
				</form>

				<form method="post" action="" class="jcart">
					<fieldset>
						<input type="hidden" name="my_item_id" value="3" />
						<input type="hidden" name="my_item_name" value="Hockey Stick" />
						<input type="hidden" name="my_item_price" value="33.25" />

						<ul>
							<li><strong>Hockey Stick</strong></li>
							<li>Price: $33.25</li>
							<li>
								<label>Qty: <input type="text" name="my_item_qty" value="1" size="3" /></label>
							</li>
						</ul>

						<input type="submit" name="my_add_button" value="add to cart" class="button" />
					</fieldset>
				</form>

				<div class="clear"></div>

			</div>

		</div>

	</body>
</html>
