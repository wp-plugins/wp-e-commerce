<?php
function wpsc_options_gateway(){
global $wpdb;
$curgateway = get_option('payment_gateway');
 
$payment_gateway_names = get_option('payment_gateway_names');

if (is_array($GLOBALS['nzshpcrt_gateways'])) {
	$selected_gateways = get_option('custom_gateway_options');
	foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
		if($gateway['internalname'] == $curgateway ) {
			$selected = "selected='selected'";
			$form = $gateway['form']();
			$selected_gateway_data = $gateway;
			//exit($form);
		} else {
			$selected = '';
		}
				
		if(isset($gateway['admin_name'])) {
			$gateway['name'] = $gateway['admin_name'];
		}
		$disabled = '';
		if (!in_array($gateway['internalname'], (array)$selected_gateways)) {
		  $disabled = "disabled='disabled'";
		}
		
		if (!isset($gateway['internalname'])) $gateway['internalname'] = '';
		$gatewaylist = '';
		$gatewaylist .="<option $disabled value='".$gateway['internalname']."' ".$selected." >".$gateway['name']."</option>";
	}
}
$nogw = '';
$gatewaylist = "<option value='".$nogw."'>".__('Please Select A Payment Gateway', 'wpsc')."</option>" . $gatewaylist;

?>
		
<script language='javascript' type='text/javascript'>
function selectgateway() {
	document.forms.gateway_opt.submit();
}

</script>
<div class="wrap">
<?php // global $nzshpcrt_gateways; print_r($nzshpcrt_gateways);?>
	<div class='metabox-holder'>
		<form name='gatewayopt' method='post' id='gateway_opt' action='' >
		<input type='hidden' name='gateway_submits' value='true' />
		<input type='hidden' name='wpsc_gateway_settings' value='gateway_settings' />
		<?php 
			if (get_option('custom_gateway') == 1){
				$custom_gateway_hide="style='display:block;'";
				$custom_gateway1 = 'checked="checked"';
			} else {
				$custom_gateway_hide="style='display:none;'";
				$custom_gateway2 = 'checked="checked"';
			}
		?>
		  <h2><?php echo __('Payment Options', 'wpsc');?></h2>
  		<?php 
		/* wpsc_setting_page_update_notification displays the wordpress styled notifications */
		wpsc_settings_page_update_notification(); ?>
		  <table id='gateway_options' >
            <tr>
				<td class='select_gateway'>
			<?php if (IS_WP27) { ?>
				<div class='postbox'>
				<h3 class='hndle'><?php _e('General Settings', 'wpsc'); ?></h3>
				<div class='inside'>
			<?php } else { ?>
					<div class="categorisation_title">
					  <strong class="form_group"><?php echo __('Payment Gateways', 'wpsc'); ?></strong>
					</div>
			<?php }	?>
					
				  <p><?php echo __('Activate the payment gateways that you want to make available to your customers by selecting them below.', 'wpsc'); ?></p>
				  <br />
					<?php
					$selected_gateways = get_option('custom_gateway_options');
					//echo("<pre>".print_r($selected_gateways,true)."</pre>");
					foreach($GLOBALS['nzshpcrt_gateways'] as $gateway) {
					  if(isset($gateway['admin_name'])) {
					    $gateway['name'] = $gateway['admin_name'];
					  }
						if (in_array($gateway['internalname'], (array)$selected_gateways)) {
					?>
					
						<div class="wpsc_shipping_options">
							<div class='wpsc-shipping-actions wpsc-payment-actions'>
									| <span class="edit">
										<a class='edit-payment-module' rel="<?php echo $gateway['internalname']; ?>" onclick="event.preventDefault();" title="Edit this Payment Module" href='<?php echo htmlspecialchars(add_query_arg('payment_module', $gateway['internalname'])); ?>' style="cursor:pointer;">Edit</a>
									</span> |
						   </div>
						<p><input name='wpsc_options[custom_gateway_options][]' checked='checked' type='checkbox' value='<?php echo $gateway['internalname']; ?>' id='<?php echo $gateway['internalname']; ?>_id' /> 
						<label for='<?php echo $gateway['internalname']; ?>_id'><?php echo $gateway['name']; ?></label></p>
						</div>
				<?php	} else { ?>
						<div class="wpsc_shipping_options">
							<div class='wpsc-shipping-actions wpsc-payment-actions'>
									| <span class="edit">
										<a class='edit-payment-module' rel="<?php echo $gateway['internalname']; ?>" onclick="event.preventDefault();" title="Edit this Payment Module" href='<?php echo htmlspecialchars(add_query_arg('payment_module', $gateway['internalname'])); ?>' style="cursor:pointer;">Edit</a>
									</span> |
						   </div>
						<p><input name='wpsc_options[custom_gateway_options][]' type='checkbox' value='<?php echo $gateway['internalname']; ?>' id='<?php echo $gateway['internalname']; ?>_id' />
						<label for='<?php echo $gateway['internalname']; ?>_id'><?php echo $gateway['name']; ?></label></p></div>
				<?php	}
					}
					?>
						<div class='submit gateway_settings'>
							<input type='hidden' value='true' name='update_gateways' />
							<input type='submit' value='<?php echo __('Update &raquo;', 'wpsc')?>' name='updateoption' />
						</div>	
				<?php if (IS_WP27){ ?>
				</div>
				</div>
				<?php } ?>		
				<h4><?php echo __('We Recommend', 'wpsc'); ?></h4>
						<a style="border-bottom:none;" href="https://www.paypal.com/nz/mrb/pal=LENKCHY6CU2VY" target="_blank"><img src="<?php echo WPSC_URL; ?>/images/paypal-referal.gif" border="0" alt="Sign up for PayPal and start accepting credit card payments instantly." /></a> <br /><br />
						<a style="border-bottom:none;" href="http://checkout.google.com/sell/?promo=seinstinct" target="_blank"><img src="https://checkout.google.com/buyer/images/google_checkout.gif" border="0" alt="Sign up for Google Checkout" /></a>

				</td>

				<td class='gateway_settings' rowspan='2'>										
					<div class='postbox'>
					  <?php
					  
					  	if(!isset($_SESSION['previous_payment_name']))
					  		$_SESSION['previous_payment_name'] = "";
					  
						$payment_data = wpsc_get_payment_form($_SESSION['previous_payment_name']);
					  ?>
						<h3 class='hndle'><?php echo $payment_data['name']; ?></h3>
						<div class='inside'>
						<table class='form-table'>
							<?php echo $payment_data['form_fields']; ?>
						</table>
						<?php
							if ( $payment_data['has_submit_button'] == 0) {
								$update_button_css = 'style= "display: none;"';
							} else {
								$update_button_css = '';
							}		 
						?>
							<div class='submit' <?php echo $update_button_css; ?>>
								<?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
								<input type='submit' value='<?php echo __('Update &raquo;', 'wpsc')?>' name='updateoption' />
							</div>
					</div>
				</td>
      </tr>
   	</table>
  </form>
</div>
</div>			
	
<?php
	}
?>