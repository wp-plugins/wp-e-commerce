<?php
/**
 * shipping/weightrate.php
 *
 * @package WP e-Commerce
 */


class weightrate {
	var $internal_name, $name;


	/**
	 *
	 *
	 * @return unknown
	 */
	function weightrate() {
		$this->internal_name = "weightrate";
		$this->name="Weight Rate";
		$this->is_external=false;
		return true;
	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function getName() {
		return $this->name;
	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function getInternalName() {
		return $this->internal_name;
	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function getForm() {

		$output.="<tr><th>".__('Total weight <br />(<abbr alt="You must enter the weight here in pounds, regardless of what you used on your products" title="You must enter the weight here in pounds, regardless of what you used on your products">in Pounds</abbr>)', 'wpsc')."</th><th>".__('Shipping Price', 'wpsc')."</th></tr>";

		$layers = get_option("weight_rate_layers");

		if ($layers != '') {

			foreach ($layers as $key => $shipping) {

				$output.="<tr class='rate_row'><td >";
				$output .="<i style='color: grey;'>".__('If weight is ', 'wpsc')."</i><input type='text' value='$key' name='weight_layer[]'size='4'><i style='color: grey;'>".__(' and above', 'wpsc')."</i></td><td>".wpsc_get_currency_symbol()."<input type='text' value='{$shipping}' name='weight_shipping[]' size='4'>&nbsp;&nbsp;<a href='#' class='delete_button' >".__('Delete', 'wpsc')."</a></td></tr>";

			}

		}

		$output.="<input type='hidden' name='checkpage' value='weight'>";
		$output.="<tr class='addlayer'><td colspan='2'>Layers: <a style='cursor:pointer;' id='addweightlayer' >Add Layer</a></td></tr>";

		return $output;

	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function submit_form() {

		if (!isset($_POST['weight_layer'])) {
			$_POST['weight_layer'] = '';
		}
		if (!isset($_POST['weight_shipping'])) {
			$_POST['weight_shipping'] = '';
		}
		$layers = (array)$_POST['weight_layer'];
		$shippings = (array)$_POST['weight_shipping'];

		if ($shippings != '') {

			foreach ($shippings as $key => $price) {

				if ($price == '') {

					unset($shippings[$key]);
					unset($layers[$key]);

				} else {

					$new_layer[$layers[$key]] = $price;

				}

			}

		}

		if ($_POST['checkpage'] == 'weight') {

			update_option('weight_rate_layers', $new_layer);

		}

		return true;

	}





	/**
	 *
	 *
	 * @return unknown
	 */
	function getQuote() {

		global $wpdb, $wpsc_cart;

		$weight = wpsc_cart_weight_total();
		if (is_object($wpsc_cart)) {
			$cart_total = $wpsc_cart->calculate_subtotal(true);
		}

		$layers = get_option('weight_rate_layers');

		if ($layers != '') {

			krsort($layers);

			foreach ($layers as $key => $shipping) {

				if ($weight >= (float)$key) {

					if (stristr($shipping, '%')) {

						// Shipping should be a % of the cart total
						$shipping = str_replace('%', '', $shipping);
						$shipping_amount = $cart_total * ( $shipping / 100 );
						return array("Weight Rate"=>(float)$shipping_amount);

					} else {

						return array("Weight Rate"=>$shipping);

					}

				}

			}

			$shipping = array_shift($layers);

			if (stristr($shipping, '%')) {
				$shipping = str_replace('%', '', $shipping);
				$shipping_amount = $price * ( $shipping / 100 );
			} else {
				$shipping_amount = $shipping;
			}

			return array("Weight Rate"=>(float)$shipping_amount);
		}

	}





	/**
	 *
	 *
	 * @param unknown $cart_item (reference)
	 * @return unknown
	 */
	function get_item_shipping(&$cart_item) {
		return 0;
	}





}


$weightrate = new weightrate();
$wpsc_shipping_modules[$weightrate->getInternalName()] = $weightrate;