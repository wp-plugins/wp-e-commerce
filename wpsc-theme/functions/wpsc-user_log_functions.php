<?php

/**
 * WP eCommerce User Account class
 *
 * This class is responsible for theming the User Account page.
 *
 * @package wp-e-commerce
 * @since 3.8
 */
global $wpdb, $user_ID, $wpsc_purchlog_statuses, $seperator;

if ( get_option( 'permalink_structure' ) != '' )
	$seperator = "?";
else
	$seperator = "&amp;";

$siteurl = site_url();

function is_wpsc_profile_page() {
	if ( $_REQUEST['edit_profile'] == 'true' )
		return true;
	else
		return false;
}

function is_wpsc_downloads_page() {
	if ( $_REQUEST['downloads'] == 'true' )
		return true;
	else
		return false;
}

function validate_form_data() {

	global $wpdb, $user_ID, $wpsc_purchlog_statuses;

	$any_bad_inputs = false;
	$changes_saved = false;

	$_SESSION['collected_data'] = null;

	if ( $_POST['collected_data'] != null ) {

		foreach ( (array)$_POST['collected_data'] as $value_id => $value ) {
			$form_sql = "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `id` = '$value_id' LIMIT 1";
			$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
			$form_data = $form_data[0];
			$bad_input = false;
			if ( $form_data['mandatory'] == 1 ) {
				switch ( $form_data['type'] ) {
					case "email":
						if ( !preg_match( "/^[a-zA-Z0-9._-]+@[a-zA-Z0-9-.]+\.[a-zA-Z]{2,5}$/", $value ) ) {
							$any_bad_inputs = true;
							$bad_input = true;
						}
						break;

					case "delivery_country":
						if ( ($value != null ) ) {
							$_SESSION['delivery_country'] == $value;
						}
						break;

					default:
						break;
				}
				if ( $bad_input === true ) {

					switch ( $form_data['name'] ) {
						case __( 'First Name', 'wpsc' ):
							$bad_input_message .= __( 'Please enter a valid name', 'wpsc' ) . "";
							break;

						case __( 'Last Name', 'wpsc' ):
							$bad_input_message .= __( 'Please enter a valid surname', 'wpsc' ) . "";
							break;

						case __( 'Email', 'wpsc' ):
							$bad_input_message .= __( 'Please enter a valid email address', 'wpsc' ) . "";
							break;

						case __( 'Address 1', 'wpsc' ):
						case __( 'Address 2', 'wpsc' ):
							$bad_input_message .= __( 'Please enter a valid address', 'wpsc' ) . "";
							break;

						case __( 'City', 'wpsc' ):
							$bad_input_message .= __( 'Please enter your town or city.', 'wpsc' ) . "";
							break;

						case __( 'Phone', 'wpsc' ):
							$bad_input_message .= __( 'Please enter a valid phone number', 'wpsc' ) . "";
							break;

						case __( 'Country', 'wpsc' ):
							$bad_input_message .= __( 'Please select your country from the list.', 'wpsc' ) . "";
							break;

						default:
							$bad_input_message .= __( 'Please enter a valid', 'wpsc' ) . " " . strtolower( $form_data['name'] ) . ".";
							break;
					}
					$bad_input_message .= "<br />";
				} else {
					$meta_data[$value_id] = $value;
				}
			} else {
				$meta_data[$value_id] = $value;
			}
		}
		$new_meta_data = serialize( $meta_data );
		update_usermeta( $user_ID, 'wpshpcrt_usr_profile', $meta_data );
	}
	if ( $changes_saved == true ) {
		$message = __( 'Thanks, your changes have been saved.', 'wpsc' );
	} else {
		$message = $bad_input_message;
	}
	return apply_filters( 'wpsc_profile_message', $message );
}

function wpsc_display_form_fields() {
// Field display and Data saving function

	global $wpdb, $user_ID, $wpsc_purchlog_statuses, $gateway_checkout_form_fields;

	$meta_data = null;
	$saved_data_sql = "SELECT * FROM `" . $wpdb->usermeta . "` WHERE `user_id` = '" . $user_ID . "' AND `meta_key` = 'wpshpcrt_usr_profile';";
	$saved_data = $wpdb->get_row( $saved_data_sql, ARRAY_A );

	$meta_data = get_user_meta( $user_ID, 'wpshpcrt_usr_profile' );

	$form_sql = "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `active` = '1' ORDER BY `order`;";
	$form_data = $wpdb->get_results( $form_sql, ARRAY_A );

	foreach ( $form_data as $form_field ) {
		if ( !empty( $form_field['unique_name'] ) ) {
			$ff_tag = $form_field['unique_name'];
		} else {
			$ff_tag = htmlentities( stripslashes( strtolower( str_replace( ' ', '-', $form_field['name'] ) ) ) );
		}
		$meta_data[$form_field['id']] = htmlentities( stripslashes( $meta_data[$form_field['id']] ), ENT_QUOTES );
		if ( $form_field['type'] == 'heading' ) {
			echo "
    <tr>
      <td colspan='2'>\n\r";
			echo "<strong>" . apply_filters( 'wpsc_account_form_field_' . $ff_tag, $form_field['name'] ) . "</strong>";
			echo "
      </td>
    </tr>\n\r";
		} else {
			if ( $form_field['type'] == "country" ) {
				continue;
			}

			echo "
      <tr>
        <td align='left'>\n\r";
			echo apply_filters( 'wpsc_account_form_field_' . $ff_tag, $form_field['name'] );
			if ( $form_field['mandatory'] == 1 ) {
				if ( !(($form_field['type'] == 'country') || ($form_field['type'] == 'delivery_country')) ) {
					echo "*";
				}
			}
			echo "
        </td>\n\r
        <td  align='left'>\n\r";
			switch ( $form_field['type'] ) {
				case "text":
				case "city":
				case "delivery_city":
					echo "<input type='text' value='" . $meta_data[$form_field['id']] . "' name='collected_data[" . $form_field['id'] . "]' />";
					break;

				case "address":
				case "delivery_address":
				case "textarea":
					echo "<textarea name='collected_data[" . $form_field['id'] . "]'>" . $meta_data[$form_field['id']] . "</textarea>";
					break;


				case "region":
				case "delivery_region":
					echo "<select name='collected_data[" . $form_field['id'] . "]'>" . nzshpcrt_region_list( $_SESSION['collected_data'][$form_field['id']] ) . "</select>";
					break;


				case "country":
					break;

				case "delivery_country":
					echo "<select name='collected_data[" . $form_field['id'] . "]' >" . nzshpcrt_country_list( $meta_data[$form_field['id']] ) . "</select>";
					break;

				case "email":
					echo "<input type='text' value='" . $meta_data[$form_field['id']] . "' name='collected_data[" . $form_field['id'] . "]' />";
					break;

				default:
					echo "<input type='text' value='" . $meta_data[$form_field['id']] . "' name='collected_data[" . $form_field['id'] . "]' />";
					break;
			}
			echo "
        </td>
      </tr>\n\r";
		}
	}
	/* Returns an empty array at this point, empty in regards to fields, does show the internalname though.  Needs to be reconsidered, even if it did work, need to check
	 * functionality and PCI_DSS compliance

	  if ( isset( $gateway_checkout_form_fields ) )
	  {
	  echo $gateway_checkout_form_fields;
	  }
	 */
}

function wpsc_has_downloads() {
	global $wpdb, $user_ID, $files, $links, $products;

	$purchases = $wpdb->get_col( "SELECT `id` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE user_ID = " . (int)$user_ID . "" );
	$rowcount = count( $purchases );

	if ( $rowcount >= 1 ) {
		$perchidstr = "(";
		$perchidstr .= implode( ',', $purchases );
		$perchidstr .= ")";
		$sql = "SELECT * FROM `" . WPSC_TABLE_DOWNLOAD_STATUS . "` WHERE `purchid` IN " . $perchidstr . " AND `active` IN ('1') ORDER BY `datetime` DESC";
		$products = $wpdb->get_results( $sql, ARRAY_A );
	}

	foreach ( (array)$products as $key => $product ) {
		$sql = "SELECT `processed` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `id`=" . $product['purchid'];
		$isOrderAccepted = $wpdb->get_var( $sql );
		if ( $isOrderAccepted > 1 ) {
			if ( $product['uniqueid'] == null ) {  // if the uniqueid is not equal to null, its "valid", regardless of what it is
				$links[] = site_url() . "/?downloadid=" . $product['id'];
			} else {
				$links[] = site_url() . "/?downloadid=" . $product['uniqueid'];
			}
			$sql = "SELECT * FROM $wpdb->posts WHERE id = " . (int)$product['fileid'] . "";
			$file = $wpdb->get_results( $sql, ARRAY_A );
			$files[] = $file[0];
		}
	}
	if ( count( $files ) > 0 ) {
		return true;
	} else {
		return false;
	}
}

function wpsc_has_purchases() {

	global $wpdb, $user_ID, $wpsc_purchlog_statuses, $gateway_checkout_form_fields, $purchase_log, $col_count;

	/*
	 * this finds the earliest timedit-profile in the shopping cart and sorts out the timestamp system for the month by month display
	 */

	$sql = "SELECT COUNT(*) AS `count` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `date`!='' ORDER BY `date` DESC";
	$purchase_count = $wpdb->get_results( $sql, ARRAY_A );

	$earliest_record_sql = "SELECT MIN(`date`) AS `date` FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `date`!=''";
	$earliest_record = $wpdb->get_results( $earliest_record_sql, ARRAY_A );

	$current_timestamp = time();
	$earliest_timestamp = $earliest_record[0]['date'];

	$current_year = date( "Y" );
	$earliest_year = date( "Y", $earliest_timestamp );

	$date_list[0]['start'] = $start_timestamp;
	$date_list[0]['end'] = $end_timestamp;

	if ( $earliest_record[0]['date'] != null ) {
		$form_sql = "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `active` = '1' AND `display_log` = '1';";
		$col_count = 4 + count( $form_data );
		$sql = "SELECT * FROM `" . WPSC_TABLE_PURCHASE_LOGS . "` WHERE `user_ID` IN ('" . $user_ID . "') ORDER BY `date` DESC";
		$purchase_log = $wpdb->get_results( $sql, ARRAY_A );

		return true;
	} else {

		return false;
	}
}

function wpsc_has_purchases_this_month() {
	global $wpdb, $user_ID, $wpsc_purchlog_statuses, $gateway_checkout_form_fields, $purchase_log, $col_count;

	$i = 0;
	$subtotal = 0;

	if ( $purchase_log != null )
		return true;
	else
		return false;
}

function wpsc_user_details() {
	global $wpdb, $user_ID, $wpsc_purchlog_statuses, $gateway_checkout_form_fields, $purchase_log, $col_count;

	$nzshpcrt_gateways = nzshpcrt_get_gateways();

	foreach ( (array)$purchase_log as $purchase ) {
		$status_state = "expand";
		$status_style = "";
		$alternate = "";
		$i++;

		if ( ($i % 2) != 0 )
			$alternate = "class='alt'";

		echo "<tr $alternate>\n\r";
		echo " <td class='processed'>";
		echo "<a href='#' onclick='return show_details_box(\"status_box_" . $purchase['id'] . "\",\"log_expander_icon_" . $purchase['id'] . "\");'>";

		if ( $_GET['id'] == $purchase['id'] ) {
			$status_state = "collapse";
			$status_style = "style='display: block;'";
		}

		echo "<img class='log_expander_icon' id='log_expander_icon_" . $purchase['id'] . "' src='" . WPSC_URL . "/images/icon_window_$status_state.gif' alt='' title='' />";

		if ( $stage_data['colour'] != '' )
			$colour = "style='color: #" . $stage_data['colour'] . ";'";

		echo "<span id='form_group_" . $purchase['id'] . "_text'>" . __( 'Details', 'wpsc' ) . "</span>";
		echo "</a>";
		echo " </td>\n\r";

		echo " <td>";
		echo date( "jS M Y", $purchase['date'] );
		echo " </td>\n\r";

		echo " <td>";

		if ( $purchase['shipping_country'] != '' ) {
			$billing_country = $purchase['billing_country'];
			$shipping_country = $purchase['shipping_country'];
		} else {
			$country_sql = "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id` = '" . $purchase['id'] . "' AND `form_id` = '" . get_option( 'country_form_field' ) . "' LIMIT 1";
			$country_data = $wpdb->get_results( $country_sql, ARRAY_A );
			$billing_country = $country_data[0]['value'];
			$shipping_country = $country_data[0]['value'];
		}
		echo nzshpcrt_currency_display( $purchase['totalprice'], 1 );
		$subtotal += $purchase['totalprice'];
		echo " </td>\n\r";


		if ( get_option( 'payment_method' ) == 2 ) {
			echo " <td>";
			$gateway_name = '';
			foreach ( (array)$nzshpcrt_gateways as $gateway ) {
				if ( $purchase['gateway'] != 'testmode' ) {
					if ( $gateway['internalname'] == $purchase['gateway'] ) {
						$gateway_name = $gateway['name'];
					}
				} else {
					$gateway_name = "Manual Payment";
				}
			}
			echo $gateway_name;
			echo " </td>\n\r";
		}

		echo "</tr>\n\r";
		echo "<tr>\n\r";
		echo " <td colspan='$col_count' class='details'>\n\r";
		echo "  <div id='status_box_" . $purchase['id'] . "' class='order_status' $status_style>\n\r";
		echo "  <div>\n\r";

		//order status code lies here
		//check what $purchase['processed'] reflects in the $wpsc_purchlog_statuses array
		$status_name = wpsc_find_purchlog_status_name( $purchase['processed'] );
		echo "  <strong class='form_group'>" . __( 'Order Status', 'wpsc' ) . ":</strong>\n\r";
		echo $status_name . "<br /><br />";

		//written by allen
		$usps_id = get_option( 'usps_user_id' );
		if ( $usps_id != null ) {
			$XML1 = "<TrackFieldRequest USERID=\"$usps_id\"><TrackID ID=\"" . $purchase['track_id'] . "\"></TrackID></TrackFieldRequest>";
			//eecho cho  "--->".$purchase['track_id'];
			$ch = curl_init();
			curl_setopt( $ch, CURLOPT_URL, "http://secure.shippingapis.com/ShippingAPITest.dll?" );
			curl_setopt( $ch, CURLOPT_RETURNTRANSFER, 1 );
			curl_setopt( $ch, CURLOPT_POST, 1 );
			curl_setopt( $ch, CURLOPT_HEADER, 0 );
			$postdata = "API=TrackV2&XML=" . $XML1;
			curl_setopt( $ch, CURLOPT_POSTFIELDS, $postdata );
			// 		$result = curl_exec($ch);

			$parser = new xml2array;
			$parsed = $parser->parse( $result );
			$parsed = $parsed[0]['children'][0]['children'];
			if ( $purchase['track_id'] != null ) {
				echo "<br /><br />";
				echo " <strong class='form_group'>" . __( 'Shipping Details', 'wpsc' ) . "</strong>\n\r";
				echo "<table>";
				foreach ( (array)$parsed as $parse ) {
					if ( $parse['name'] == "TRACKSUMMARY" )
						foreach ( (array)$parse['children'] as $attrs ) {
							if ( $attrs['name'] != "EVENT" )
								$attrs['name'] = str_replace( "EVENT", "", $attrs['name'] );
							$bar = ucfirst( strtolower( $attrs['name'] ) );
							echo "<tr><td>" . $bar . "</td><td>" . $attrs['tagData'] . "</td></tr>";
						}
				}
				echo "</table>";
			}
			echo "<br /><br />";
		}
		//end of written by allen
		//cart contents display starts here;
		echo "  <strong class='form_group'>" . __( 'Order Details', 'wpsc' ) . ":</strong>\n\r";
		$cartsql = "SELECT * FROM `" . WPSC_TABLE_CART_CONTENTS . "` WHERE `purchaseid`=" . $purchase['id'] . "";
		$cart_log = $wpdb->get_results( $cartsql, ARRAY_A );
		$j = 0;

		// /*
		if ( $cart_log != null ) {
			echo "<table class='logdisplay'>";
			echo "<tr class='toprow2'>";

			echo " <td>";
			_e( 'Name', 'wpsc' );
			echo " </td>";

			echo " <td>";
			_e( 'Quantity', 'wpsc' );
			echo " </td>";

			echo " <td>";
			_e( 'Price', 'wpsc' );
			echo " </td>";

			echo " <td>";
			_e( 'GST', 'wpsc' );
			echo " </td>";

			echo " <td>";
			_e( 'P&amp;P', 'wpsc' );
			echo " </td>";

			echo " <td>";
			_e( 'Total', 'wpsc' );
			echo " </td>";

			echo "</tr>";

			$endtotal = 0;
			foreach ( (array)$cart_log as $cart_row ) {
				$alternate = "";
				$j++;

				if ( ($j % 2) != 0 )
					$alternate = "class='alt'";

				$variation_list = '';

				if ( $purch_data[0]['shipping_country'] != '' ) {
					$billing_country = $purch_data[0]['billing_country'];
					$shipping_country = $purch_data[0]['shipping_country'];
				} else {
					$country_sql = "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE `log_id` = '" . $purchase['id'] . "' AND `form_id` = '" . get_option( 'country_form_field' ) . "' LIMIT 1";
					$country_data = $wpdb->get_results( $country_sql, ARRAY_A );
					$billing_country = $country_data[0]['value'];
					$shipping_country = $country_data[0]['value'];
				}

				$shipping = $cart_row['pnp'];
				$total_shipping += $shipping;
				echo "<tr $alternate>";

				echo " <td>";
				echo $cart_row['name'];
				echo $variation_list;
				echo " </td>";

				echo " <td>";
				echo $cart_row['quantity'];
				echo " </td>";

				echo " <td>";
				$price = $cart_row['price'] * $cart_row['quantity'];
				echo nzshpcrt_currency_display( $price, 1 );
				echo " </td>";

				echo " <td>";
				$gst = $cart_row['tax_charged'];
				$endtotal += $gst * $cart_row['quantity'];

				echo nzshpcrt_currency_display( $gst, 1 );
				echo " </td>";

				echo " <td>";
				echo nzshpcrt_currency_display( $shipping, 1 );
				echo " </td>";

				echo " <td>";
				$endtotal += $price;
				echo nzshpcrt_currency_display( ($shipping + $price + ($gst * $cart_row['quantity']) ), 1 );
				echo " </td>";

				echo '</tr>';
			}
			echo "<tr >";

			echo " <td>";
			echo " </td>";

			echo " <td>";
			echo " </td>";

			echo " <td>";
			echo " </td>";

			echo " <td>";
			echo "<strong>" . __( 'Total Shipping', 'wpsc' ) . ":</strong><br />";
			echo "<strong>" . __( 'Final Total', 'wpsc' ) . ":</strong>";
			echo " </td>";

			echo " <td>";
			$total_shipping += $purchase['base_shipping'];
			$endtotal += $total_shipping;
			echo nzshpcrt_currency_display( $total_shipping, 1 ) . "<br />";
			echo nzshpcrt_currency_display( $endtotal, 1 );
			echo " </td>";

			echo '</tr>';

			echo "</table>";
			echo "<br />";

			echo "<strong>" . __( 'Customer Details', 'wpsc' ) . ":</strong>";
			echo "<table class='customer_details'>";
			$form_sql = "SELECT * FROM `" . WPSC_TABLE_SUBMITED_FORM_DATA . "` WHERE  `log_id` = '" . $purchase['id'] . "'";
			$input_data = $wpdb->get_results( $form_sql, ARRAY_A );

			if ( $input_data != null ) {
				foreach ( (array)$input_data as $form_field ) {
					$form_sql = "SELECT * FROM `" . WPSC_TABLE_CHECKOUT_FORMS . "` WHERE `active` = '1' AND `id` = '" . $form_field['form_id'] . "' LIMIT 1";
					$form_data = $wpdb->get_results( $form_sql, ARRAY_A );
					if ( $form_data != null ) {
						$form_data = $form_data[0];
						if ( $form_data['type'] == 'country' ) {
							if ( $form_field['value'] != null ) {
								echo "  <tr><td>" . $form_data['name'] . ":</td><td>" . wpsc_get_country( $form_field['value'] ) . "</td></tr>";
							} else {
								echo "  <tr><td>" . $form_data['name'] . ":</td><td>" . wpsc_get_country( $purchase['shipping_country'] ) . "</td></tr>";
							}
						} else {
							echo "  <tr><td>" . $form_data['name'] . ":</td><td>" . $form_field['value'] . "</td></tr>";
						}
					}
				}
			} else {
				echo "  <tr><td>" . __( 'Name', 'wpsc' ) . ":</td><td>" . $purchase['firstname'] . " " . $purchase['lastname'] . "</td></tr>";
				echo "  <tr><td>" . __( 'Address', 'wpsc' ) . ":</td><td>" . $purchase['address'] . "</td></tr>";
				echo "  <tr><td>" . __( 'Phone', 'wpsc' ) . ":</td><td>" . $purchase['phone'] . "</td></tr>";
				echo "  <tr><td>" . __( 'Email', 'wpsc' ) . ":</td><td>" . $purchase['email'] . "</td></tr>";
			}

			//if(get_option('payment_method') == 2)
			//{
			$gateway_name = '';
			foreach ( (array)$nzshpcrt_gateways as $gateway ) {
				if ( $purchase_log[0]['gateway'] != 'testmode' ) {
					if ( $gateway['internalname'] == $purchase_log[0]['gateway'] ) {
						$gateway_name = $gateway['name'];
					}
				} else {
					$gateway_name = "Manual Payment";
				}
			}
			//}
			echo "  <tr><td>" . __( 'Payment Method', 'wpsc' ) . ":</td><td>" . $gateway_name . "</td></tr>";
			echo "  <tr><td>" . __( 'Purchase No.', 'wpsc' ) . ":</td><td>" . $purchase['id'] . "</td></tr>";
			if ( $purchase['transactid'] != '' ) {
				echo "  <tr><td>" . __( 'Transaction Id', 'wpsc' ) . ":</td><td>" . $purchase['transactid'] . "</td></tr>";
			}
			echo "</table>";
		} // */
		echo "  </div>\n\r";
		echo "  </div>\n\r";
		echo " </td>\n\r";
		echo "</tr>\n\r";
	}
}

?>