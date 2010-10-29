<?php

/**
 * WP eCommerce transaction results class
 *
 * This class is responsible for theming the transaction results page.
 *
 * @package wp-e-commerce
 * @since 3.8
 */
function wpsc_transaction_theme() {
	global $wpdb, $user_ID, $nzshpcrt_gateways, $sessionid, $cart_log_id, $errorcode;

	if ( isset( $_GET['sessionid'] ) )
		$sessionid = $_GET['sessionid'];

	if ( !isset( $_GET['sessionid'] ) && isset( $_GET['ms'] ) )
		$sessionid = $_GET['ms'];

	if ( isset( $_GET['gateway'] ) && $_GET['gateway'] == 'google' ) {
		wpsc_google_checkout_submit();
		unset( $_SESSION['wpsc_sessionid'] );
	} elseif ( isset( $_GET['gateway'] ) && $_GET['gateway'] == 'noca' ) {
		wpsc_submit_checkout();
	}

	if ( $_SESSION['wpsc_previous_selected_gateway'] == 'paypal_certified' )
		$sessionid = $_SESSION['paypalexpresssessionid'];

	//exit("test!");
	$errorcode = '';
	$transactid = '';

	if ( isset( $_REQUEST['eway'] ) && $_REQUEST['eway'] == '1' ) {
		$sessionid = $_GET['result'];
	} elseif ( isset( $_REQUEST['eway'] ) && $_REQUEST['eway'] == '0' ) {
		echo $_SESSION['eway_message'];
	} elseif ( isset( $_REQUEST['payflow'] ) && $_REQUEST['payflow'] == '1' ) {
		echo $_SESSION['payflow_message'];
		$_SESSION['payflow_message'] = '';
	}

	//exit('getting here?<pre>'.print_r($_SESSION['wpsc_previous_selected_gateway'], true).'</pre>'.get_option('payment_gateway'));
	if ( $_SESSION['wpsc_previous_selected_gateway'] == 'paypal_certified' ) {
		echo $_SESSION['paypalExpressMessage'];
	} else {
		if ( $_SESSION['wpsc_previous_selected_gateway'] == 'dps' ) {
			$sessionid = decrypt_dps_response();
			//exit($sessionid);
			if ( $sessionid != '' ) {
				return transaction_results( $sessionid, true );
			} else {
				_e( 'Sorry your transaction was not accepted.<br /><a href=' . get_option( "shopping_cart_url" ) . '>Click here to go back to checkout page.</a>' );
			}
		} else {
			//exit('<pre>sess - '.print_r($_SESSION, true).'</pre>');
			return transaction_results( $sessionid, true );
		}
		$cart_log_id = $wpdb->get_var( "SELECT `id` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1" );
	}
}

function transaction_results( $sessionid, $echo_to_screen = true, $transaction_id = null ) {
	global $wpdb, $wpsc_cart, $echo_to_screen, $purchase_log, $order_url, $message_html, $cart, $errorcode,$wpsc_purchlog_statuses;
	$is_transaction = true;
	$curgateway = $wpdb->get_var( "SELECT gateway FROM " . WPSC_TABLE_PURCHASE_LOGS . " WHERE sessionid='$sessionid'" );
	$errorcode = 0;
	$order_status = $purchase_log['processed'];

	if( !is_bool( $echo_to_screen )  )
		$echo_to_screen = true;

	if ( is_numeric( $sessionid ) ) {

		if ( $echo_to_screen )
			echo apply_filters( 'wpsc_pre_transaction_results', '' );

		$purchase_log = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `sessionid`= " . $sessionid . " LIMIT 1", ARRAY_A );
		foreach($wpsc_purchlog_statuses as $status)
			if($status['order'] == $purchase_log['processed'] && isset($status['is_transaction']) && 1 == $status['is_transaction'] )
				$is_transaction = true;
			


		if ( ($purchase_log['gateway'] == "wpsc_merchant_testmode") && ($purchase_log['processed'] < 3) ) {
			$message = stripslashes( __('Thank you, your purchase is pending, you will be sent an email once the order clears. All prices include tax and postage and packaging where applicable. You ordered these items:%product_list%%total_shipping%%total_price%', 'wpsc') );
			$message_html = __('Thank you, your purchase is pending, you will be sent an email once the order clears. All prices include tax and postage and packaging where applicable. You ordered these items:%product_list%%total_shipping%%total_price%', 'wpsc');
		} else {
			$message = stripslashes( get_option( 'wpsc_email_receipt' ) );
			$message_html = $message;
		}
		$order_url = site_url( "/wp-admin/admin.php?page=" . WPSC_DIR_NAME . "/display-log.php&amp;purchcaseid=" . $purchase_log['id'] );

		// Checks for PayPal IPN
		if ( (!isset( $_GET['ipn_request'] ) || $_GET['ipn_request'] != 'true') &&
			 ((get_option( 'paypal_ipn' ) == 1)  && ($purchase_log['gateway'] == 'wpsc_merchant_paypal_standard')) ) {

			if ( $purchase_log == null ) {

				if ( (get_option( 'purch_log_email' ) != null) && ($purchase_log['email_sent'] != 1) ) {
					wp_mail( get_option( 'purch_log_email' ), __( 'New pending order', 'wpsc' ), __( 'There is a new order awaiting processing:', 'wpsc' ) . $order_url, "From: " . get_option( 'return_email' ) . "" );
				}
				_e( 'We&#39;re Sorry, your order has not been accepted, the most likely reason is that you have insufficient funds.', 'wpsc' );

				return false;
			} else if (!$is_transaction) {
				_e( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "<p style='margin: 1em 0px 0px 0px;' >" . nl2br( stripslashes( get_option( 'payment_instructions' ) ) ) . "</p>";
				return;
			}
		}
		$cart = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`='{$purchase_log['id']}'", ARRAY_A );

		if ( $purchase_log['shipping_country'] != '' ) {
			$billing_country = $purchase_log['billing_country'];
			$shipping_country = $purchase_log['shipping_country'];
		} else {
			$country = $wpdb->get_var( "SELECT `value` FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id`=" . $purchase_log['id'] . " AND `form_id` = '" . get_option( 'country_form_field' ) . "' LIMIT 1" );
			$billing_country = $country;
			$shipping_country = $country;
		}

		$email_form_field = $wpdb->get_results( "SELECT `id`,`type` FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `type` IN ('email') AND `active` = '1' ORDER BY `order` ASC LIMIT 1", ARRAY_A );
		$email = $wpdb->get_var( "SELECT `value` FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id`=" . $purchase_log['id'] . " AND `form_id` = '" . $email_form_field[0]['id'] . "' LIMIT 1" );
		$stock_adjusted = false;
		$previous_download_ids = array( 0 );
		$product_list = $product_list_html = '';

		if ( ($cart != null) && ($errorcode == 0) ) {

			foreach ( $cart as $row ) {
				$link = "";
				if ( $purchase_log['email_sent'] != 1 ) {
					$wpdb->query( "UPDATE `" . WPSC_TABLE_DOWNLOAD_STATUS . "` SET `active`='1' WHERE `cartid` = '{$row['id']}' AND `purchid` = '{$purchase_log['id']}'" );
				}

				do_action( 'wpsc_transaction_result_cart_item', array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log ) );

				if ( $is_transaction ) {

					$download_data = $wpdb->get_results( "SELECT *
					FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "`
					WHERE `active`='1'
					AND `purchid`='" . $purchase_log['id'] . "'
					AND `cartid` = '" . $row['id'] . "'", ARRAY_A );


					$link = array( );

					if ( count( $download_data ) > 0 ) {
						foreach ( $download_data as $single_download ) {
							$file_data = get_post( $single_download['fileid'] );

							if ( $single_download['uniqueid'] == null ) {// if the uniqueid is not equal to null, its "valid", regardless of what it is
								$link[] = array( "url" => site_url( "?downloadid=" . $single_download['id'] ), "name" => $file_data->post_title );
							} else {
								$link[] = array( "url" => site_url( "?downloadid=" . $single_download['uniqueid'] ), "name" => $file_data->post_title );
							}
						}
						//$order_status= 4;
					} else {
						$order_status = $purchase_log['processed'];
					}
					$previous_download_ids[] = $download_data['id'];
				}

				do_action( 'wpsc_confirm_checkout', $purchase_log['id'] );
				$total_shipping = '';
				$total = '';
				$shipping = $row['pnp'] * $row['quantity'];
				$total_shipping += $shipping;

				$total += ( $row['price'] * $row['quantity']);
				$message_price = wpsc_currency_display( ($row['price'] * $row['quantity'] ), array( 'display_as_html' => false ) );
				$message_price_html = wpsc_currency_display( ($row['price'] * $row['quantity'] ), array( 'display_as_html' => false ) );

				$shipping_price = wpsc_currency_display( $shipping, array( 'display_as_html' => false ) );

				if ( isset( $purchase['gateway'] ) && $purchase['gateway'] != 'wpsc_merchant_testmode' ) {
					if ( $gateway['internalname'] == $purch_data[0]['gateway'] ) {
						$gateway_name = $gateway['name'];
					}
				} else {
					$gateway_name = "Manual Payment";
				}

				$variation_list = '';

				if ( !empty( $link ) ) {
					$additional_content = apply_filters( 'wpsc_transaction_result_content', array( "purchase_id" => $purchase_log['id'], "cart_item" => $row, "purchase_log" => $purchase_log ) );
					if ( !is_string( $additional_content ) ) {
						$additional_content = '';
					}
					$product_list .= " - " . $row['name'] . "  " . $message_price . " " . __( 'Click to download', 'wpsc' ) . ":";
					$product_list_html .= " - " . $row['name'] . "  " . $message_price_html . "&nbsp;&nbsp;" . __( 'Click to download', 'wpsc' ) . ":\n\r";
					foreach ( $link as $single_link ) {
						$product_list .= "\n\r " . $single_link["name"] . ": " . $single_link["url"] . "\n\r";
						$product_list_html .= "<a href='" . $single_link["url"] . "'>" . $single_link["name"] . "</a>\n";
					}
					$product_list .= $additional_content;
					$product_list_html .= $additional_content;
				} else {

					$plural = '';
					if ( $row['quantity'] > 1 ) {
						$plural = "s";
					}
					$product_list.= " - " . $row['quantity'] . " " . $row['name'] . "  " . $message_price . "\n\r";
					if ( $shipping > 0 )
						$product_list .= " - " . __( 'Shipping', 'wpsc' ) . ":" . $shipping_price . "\n\r";
					$product_list_html.= "\n\r - " . $row['quantity'] . " " . $row['name'] . "  " . $message_price_html . "\n\r";
					if ( $shipping > 0 )
						$product_list_html .= " &nbsp; " . __( 'Shipping', 'wpsc' ) . ":" . $shipping_price . "\n\r";
				}
				$report = get_option( 'wpsc_email_admin' );
				$report_product_list = '';
				$report_product_list.= " - " . $row['name'] . "  " . $message_price . "\n\r";
			}

			// Decrement the stock here
			if ( $is_transaction ) {
				wpsc_decrement_claimed_stock( $purchase_log['id'] );
			}

			if ( $purchase_log['discount_data'] != '' ) {
				$coupon_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_COUPON_CODES . "` WHERE coupon_code='" . $wpdb->escape( $purchase_log['discount_data'] ) . "' LIMIT 1", ARRAY_A );
				if ( $coupon_data['use-once'] == 1 ) {
					$wpdb->query( "UPDATE `" . WPSC_TABLE_COUPON_CODES . "` SET `active`='0', `is-used`='1' WHERE `id`='" . $coupon_data['id'] . "' LIMIT 1" );
				}
			}

			$total_shipping += $purchase_log['base_shipping'];

			$total = $purchase_log['totalprice'];
			// echo $total;
			// $message.= "\n\r";
			$product_list.= "Your Purchase No.: " . $purchase_log['id'] . "\n\r";
			$product_list_html.= "Your Purchase No.: " . $purchase_log['id'] . "<BR/>\n\r";
			if ( $purchase_log['discount_value'] > 0 ) {
				$discount_email.= __( 'Discount', 'wpsc' ) . "\n\r: ";
				$discount_email .=$purchase_log['discount_data'] . ' : ' . wpsc_currency_display( $purchase_log['discount_value'], array( 'display_as_html' => false ) ) . "\n\r";
			}
			$total_price_email = '';
			$total_price_html = '';
			$total_shipping_html = '';
			$total_shipping_email = '';
			$total_shipping_email.= __( 'Total Shipping', 'wpsc' ) . ": " . wpsc_currency_display( $total_shipping, array( 'display_as_html' => false ) ) . "\n\r";
			$total_price_email.= __( 'Total', 'wpsc' ) . ": " . wpsc_currency_display( $total, array( 'display_as_html' => false ) ) . "\n\r";

			if ( $purchase_log['discount_value'] > 0 ) {
				$report.= $discount_email . "\n\r";
				$total_shipping_html.= __( 'Discount', 'wpsc' ) . ": " . wpsc_currency_display( $purchase_log['discount_value'], array( 'display_as_html' => false ) ) . "\n\r";
			}

			$total_shipping_html.= __( 'Total Shipping', 'wpsc' ) . ": " . wpsc_currency_display( $total_shipping, array( 'display_as_html' => false ) ) . "\n\r";
			$total_price_html.= __( 'Total', 'wpsc' ) . ": " . wpsc_currency_display( $total,array( 'display_as_html' => false ) ) . "\n\r";

			if ( isset( $_GET['ti'] ) ) {
				$message.= "\n\r" . __( 'Your Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
				$message_html.= "\n\r" . __( 'Your Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
				$report.= "\n\r" . __( 'Transaction ID', 'wpsc' ) . ": " . $_GET['ti'];
			} else {
				$report_id = "Purchase No.: " . $purchase_log['id'] . "\n\r";
			}

			$message = str_replace( '%product_list%', $product_list, $message );
			$message = str_replace( '%total_shipping%', $total_shipping_email, $message );
			$message = str_replace( '%total_price%', $total_price_email, $message );
			$message = str_replace( '%shop_name%', get_option( 'blogname' ), $message );
			$message = str_replace( '%find_us%', $purchase_log['find_us'], $message );

			$report = str_replace( '%product_list%', $report_product_list, $report );
			$report = str_replace( '%total_shipping%', $total_shipping_email, $report );
			$report = str_replace( '%total_price%', $total_price_email, $report );
			$report = str_replace( '%shop_name%', get_option( 'blogname' ), $report );
			$report = str_replace( '%find_us%', $purchase_log['find_us'], $report );

			$message_html = str_replace( '%product_list%', $product_list_html, $message_html );
			$message_html = str_replace( '%total_shipping%', $total_shipping_html, $message_html );
			$message_html = str_replace( '%total_price%', $total_price_email, $message_html );
			$message_html = str_replace( '%shop_name%', get_option( 'blogname' ), $message_html );
			$message_html = str_replace( '%find_us%', $purchase_log['find_us'], $message_html );

			if ( ($email != '') && ($purchase_log['email_sent'] != 1) ) {

				add_filter( 'wp_mail_from', 'wpsc_replace_reply_address', 0 );
				add_filter( 'wp_mail_from_name', 'wpsc_replace_reply_name', 0 );

				if ( $purchase_log['processed'] < 3 ) {
					$payment_instructions = strip_tags( stripslashes( get_option( 'payment_instructions' ) ) );
					$message = __( 'Thank you, your purchase is pending, you will be sent an email once the order clears.', 'wpsc' ) . "\n\r" . $payment_instructions . "\n\r" . $message;
					wp_mail( $email, __( 'Order Pending: Payment Required', 'wpsc' ), $message );
				} else {
					wp_mail( $email, __( 'Purchase Receipt', 'wpsc' ), $message );
				}
			}
			remove_filter( 'wp_mail_from_name', 'wpsc_replace_reply_name' );
			remove_filter( 'wp_mail_from', 'wpsc_replace_reply_address' );

			$report_user = __( 'Customer Details', 'wpsc' ) . "\n\r";
			$form_sql = "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id` = '" . $purchase_log['id'] . "'";
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );

			if ( $form_data != null ) {
				foreach ( $form_data as $form_field ) {
					$form_data = $wpdb->get_row( "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `id` = '" . $form_field['form_id'] . "' LIMIT 1", ARRAY_A );

					switch ( $form_data['type'] ) {
						case "country":
							$report_user .= $form_data['name'] . ": " . wpsc_get_country( $form_field['value'] ) . "\n";
							$report_user .= __( 'State', 'wpsc' ) . ": " . wpsc_get_region( $purchase_log['billing_region'] ) . "\n";
							break;

						case "delivery_country":
							$report_user .= $form_data['name'] . ": " . wpsc_get_country( $form_field['value'] ) . "\n";
							$report_user .= __( 'Delivery State', 'wpsc' ) . ": " . wpsc_get_region( $purchase_log['shipping_region'] ) . "\n";
							break;

						default:
							$report_user .= wp_kses( $form_data['name'], array( ) ) . ": " . $form_field['value'] . "\n";
							break;
					}
				}
			}

			$report_user .= "\n\r";
			$report = $report_user . $report_id . $report;

			if ( $stock_adjusted == true ) {
				$wpdb->query( "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET `stock_adjusted` = '1' WHERE `sessionid` = " . $sessionid . " LIMIT 1" );
			}

			if ( (get_option( 'purch_log_email' ) != null) && ($purchase_log['email_sent'] != 1) ) {

				wp_mail( get_option( 'purch_log_email' ), __( 'Purchase Report', 'wpsc' ), $report );
			}
			$wpsc_cart->submit_stock_claims( $purchase_log['id'] );

			/// Empty the cart
			$wpsc_cart->empty_cart();
		}

		if ( ($purchase_log['email_sent'] != 1) and ($sessionid != '') ) {
			if ( preg_match( "/^[\w\s._,-]+$/", $transaction_id ) ) {
				$transact_id_sql = "`transactid` = '" . $transaction_id . "',";
			}
		//	$update_sql = "UPDATE `" . WPSC_TABLE_PURCHASE_LOGS . "` SET $transact_id_sql `email_sent` = '1', `processed` = '$order_status' WHERE `sessionid` = " . $sessionid . " LIMIT 1";
		//	$wpdb->query( $update_sql );
		}
	}
}

?>