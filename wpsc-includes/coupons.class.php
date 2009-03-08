<?php
/**
 * Coupons class.
 *
 * Conditional coupons use an 'ALL' logic. Now admins can achieve an 'ANY' logic by adding multiple coupons.
 *
 * TODO: Implement 'ANY' logic of conditional coupons.
 *
 * @package Wp-shopping-cart
 * @since 3.6.9
 */
class wpsc_coupons {
	var $code;
	var $value;
	var $is_percentage;
	var $conditions;
	var $start_date;
	var $active;
	var $end_date;
	var $use_once;
	var $is_used;
	
	var $discount;
	
	/**
	 * Coupons constractor
	 *
	 * Instantiate a coupons object with optional variable $code;
	 *
	 * @param string code (optional) the coupon code you would like to use.
	 * @return bool True if coupon code exists, False otherwise.
	 */
	function wpsc_coupons($code = ''){
		global $wpdb;
		if ($code == '') {
			return false;
		} else {
			$this->code = $code;
			
			$coupon_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}wpsc_coupon_codes WHERE coupon_code='$code' LIMIT 1", ARRAY_A);
			$coupon_data = $coupon_data[0];
			
			if ($coupon_data == '') {
				return false;
			} else {
				$this->value = $coupon_data['value'];
				$this->is_percentage = $coupon_data['is-percentage'];
				$this->conditions = unserialize($coupon_data['condition']);
				$this->is_used = $coupon_data['is-used'];
				$this->active = $coupon_data['active'];
				$this->use_once = $coupon_data['use-once'];
				$this->start_date = $coupon_data['start'];
				$this->end_date = $coupon_data['expiry'];
				$valid = $this->validate_coupon();
				return $valid;
			}
		}
	}
	
	/**
	 * Coupons validator
	 *
	 * Checks if the current coupon is valid to use (Expiry date, Active, Used).
	 *
	 * @return bool True if coupon is not expried, used and still active, False otherwise.
	 */
	function validate_coupon() {
		$now = date("Y-m-d H:i:s");
		$now = strtotime($now);
		
		if ( ($this->active=='1') && !(($this->use_once == '1') && ($this->is_used=='1'))){
			if ((strtotime($this->start_date) < $now)&&(strtotime($this->end_date) > $now)){
				return true;
			}
		}
		return false;
	}
	
	
	function calculate_discount() {
		global $wpdb;
		
		if ($this->conditions == '') {
			//Calculates the discount for the whole cart if there is no condition on this coupon.
			if ($this->is_percentage == '1') {
				$total_price = nzshpcrt_overall_total_price_numeric();
				$this->discount = $total_price*$this->value/100;
				return $this->discount;
			} else {
				return $this->value;
			}
		} else {
			//Loop throught all products in the shopping cart, apply coupons on the ones match the conditions. 
			$cart  =& $_SESSION['nzshpcrt_cart'];
			foreach ($cart as $key => $item) {
				$match = true;
				$product_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}product_list WHERE id='{$item->product_id}'");
				$product_data = $product_data[0];
				foreach ($this->conditions as $c) {
					//Check if all the condictions are returning true, so it's an ALL logic, if anyone want to implement a ANY logic please do.
					$match = $match && $this->compare_logic($c, $item);
				}
				if ($match) {
				    if ($this->is_percentage == '1') {
						$this->discount = $product_data->price*$item->quantity*$this->value/100;
						$item->discount = $this->discount;
						$return += $this->discount;
						//echo $item->discount."-";
					} else {
						$item->discount = $this->value;
						$return += $this->value;
					}
				}
			}
		}
		return $return;
	}
	
	
	
	/**
	 * Comparing logic with the product information
	 *
	 * Checks if the product matchs the logic
	 *
	 * @return bool True if all conditions are matched, False otherwise.
	 */
	function compare_logic($c, $product_obj) {
		global $wpdb;
		if ($c['property'] == 'item_name') {
			$product_data = $wpdb->get_results("SELECT * FROM {$wpdb->prefix}product_list WHERE id='{$product_obj->product_id}'");
			$product_data = $product_data[0];
			switch($c['logic']) {
				case 'equal': //Checks if the product name is exactly the same as the condition value
				if ($product_data->name == $c['value']) {
					
					return true;
				}
				break;
				
				case 'greater'://Checks if the product name is not the same as the condition value
				if ($product_data->name > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the product name is not the same as the condition value
				if ($product_data->name < $c['value'])
					return true;
				break;
				
				case 'contains'://Checks if the product name contains the condition value
				preg_match("(.*)".$c['value']."(.*)", $product_data->name, $match);
				if (!empty($match))
					return true;
				break;
				
				case 'not_contain'://Checks if the product name contains the condition value
				preg_match("/(.*)".$c['value']."(.*)/", $product_data->name, $match);
				if (empty($match))
					return true;
				break;
				
				case 'begins'://Checks if the product name begins with condition value
				preg_match("/^".$c['value']."/", $product_data->name, $match);
				if (!empty($match))
					return true;
				break;
				
				case 'ends'://Checks if the product name ends with condition value
				preg_match("/^".$c['value']."/", $product_data->name, $match);
				if (!empty($match))
					return true;
				break;
				
				default:
				return false;
			}
		} else if ($c['property'] == 'item_quantity'){
			switch($c['logic']) {
				case 'equal'://Checks if the quantity of a product in the cart equals condition value
				if ($product->quantity == $c['value'])
					return true;
				break;
				
				case 'greater'://Checks if the quantity of a product is greater than the condition value
				if ($product->quantity > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the quantity of a product is less than the condition value
				if ($product->quantity < $c['value'])
					return true;
				break;
				
				default:
				return false;
			}
		} else if ($c['property'] == 'total_quantity'){
			$total_quantity = shopping_cart_total_quantity();
			switch($c['logic']) {
				case 'equal'://Checks if the quantity of products in the cart equals condition value
				if ($total_quantity == $c['value'])
					return true;
				break;
				
				case 'greater'://Checks if the quantity in the cart is greater than the condition value
				if ($total_quantity > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the quantity in the cart is less than the condition value
				if ($total_quantity < $c['value'])
					return true;
				break;
				
				default:
				return false;
			}
		
		} else if ($c['property'] == 'subtotal_amount'){
			$subtotal = nzshpcrt_overall_total_price();
			switch($c['logic']) {
				case 'equal'://Checks if the subtotal of products in the cart equals condition value
				if ($subtotal == $c['value'])
					return true;
				break;
				
				case 'greater'://Checks if the subtotal of the cart is greater than the condition value
				if ($subtotal > $c['value'])
					return true;
				break;
				
				case 'less'://Checks if the subtotal of the cart is less than the condition value
				if ($subtotal < $c['value'])
					return true;
				break;
				
				default:
				return false;
			}
		}
	}
	
	
	
}
?>
