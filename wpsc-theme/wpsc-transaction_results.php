<?php
	/**
	 * The Transaction Results Theme.
	 *
	 * Displays everything within transaction results.  Hopefully much more useable than the previous implementation.
	 *
	 * @package WPSC
	 * @since WPSC 3.8
	 */

	global $wpsc_query, $purchase_log, $wpdb, $errorcode, $sessionid, $echo_to_screen, $cart, $message_html;

?>
<div class="wrap">

	<?php
			echo wpsc_transaction_theme();
			if ( ( true === $echo_to_screen ) && ( $cart != null ) && ( $errorcode == 0 ) && ( $sessionid != null ) ) {
			
						_e('The Transaction was successful', 'wpsc')."<br />";
						echo "<br />" . nl2br(str_replace("$",'\$',$message_html));
						
					}
			elseif ( true === $echo_to_screen && ( !isset($purchase_log) ) ) {
					_e('Oops, there is nothing in your cart.', 'wpsc') . "<a href=".get_option("product_list_url").">" . __('Please visit our shop', 'wpsc') . "</a>";
				}
	?>	
	
</div>