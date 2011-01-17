<?php
/**
 * WPSC Product form generation functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */

global $wpsc_product_defaults;
$wpsc_product_defaults = array(
	'id' => '0',
	'name' => '',
	'description' => '',
	'additional_description' => '',
	'price' => '0.00',
	'weight' => '0',
	'weight_unit' => 'pound',
	'pnp' => '0.00',
	'international_pnp' => '0.00',
	'file' => '0',
	'image' => '',
	'category' => '0',
	'brand' => '0',
	'quantity_limited' => '0',
	'quantity' => '0',
	'special' => '0',
	'special_price' => 0.00,
	'display_frontpage' => '0',
	'notax' => '0',
	'publish' => '1',
	'active' => '1',
	'donation' => '0',
	'no_shipping' => '0',
	'thumbnail_image' => '',
	'thumbnail_state' => '1',
	'meta' =>
	array(
		'external_link' => NULL,
		'external_link_text' => NULL,
		'external_link_target' => NULL,
		'merchant_notes' => NULL,
		'sku' => NULL,
		'engrave' => '0',
		'can_have_uploaded_image' => '0',
		'table_rate_price' =>
		array(
			'quantity' =>
			array(
				0 => '',
			),
			'table_price' =>
			array(
				0 => '',
			),
		),
	),
);
add_action('admin_head', 'wpsc_css_header');

function wpsc_css_header() {
        global $post_type;
	?>
	<style>
	<?php if (($_GET['post_type'] == 'wpsc-product') || ($post_type == 'wpsc-product')) : ?>
	#icon-edit { background:transparent url('<?php echo WPSC_CORE_IMAGES_URL.'/icon32.png';?>') no-repeat; }
	<?php endif; ?>
        </style>
        <?php
}
function wpsc_price_control_forms(){
	global $post, $wpdb, $variations_processor, $wpsc_product_defaults;
        $product_data = get_post_custom( $post->ID );
        $product_data['meta'] = maybe_unserialize( $product_data );

        foreach( $product_data['meta'] as $meta_key => $meta_value )
            $product_data['meta'][$meta_key] = $meta_value[0];

        $product_meta = maybe_unserialize( $product_data["_wpsc_product_metadata"][0] );

	if( isset( $product_data['meta']['_wpsc_currency']))
		$product_alt_currency = maybe_unserialize( $product_data['meta']['_wpsc_currency'] );
		
	if ( !isset( $product_data['meta']['_wpsc_table_rate_price'] ) )
		$product_data['meta']['_wpsc_table_rate_price'] = $wpsc_product_defaults['meta']['table_rate_price'];
	$table_rate_price = $product_data['meta']['_wpsc_table_rate_price'];
	
	if ( !isset( $product_data['meta']['_wpsc_is_donation'] ) )
		$product_data['meta']['_wpsc_is_donation'] = $wpsc_product_defaults['donation'];
	
	if ( !isset( $product_meta['table_rate_price']['state'] ) )
		$product_meta['table_rate_price']['state'] = null;
	
	if ( !isset( $product_meta['table_rate_price']['quantity'] ) )
		$product_meta['table_rate_price']['quantity'] = $wpsc_product_defaults['meta']['table_rate_price']['quantity'][0];
	
	if ( !isset( $product_data['meta']['_wpsc_price'] ) )
		$product_data['meta']['_wpsc_price'] = $wpsc_product_defaults['price'];
	
	if ( !isset( $product_data['special'] ) )
		$product_data['special'] = $wpsc_product_defaults['special'];
	
	if ( !isset( $product_data['meta']['_wpsc_special_price'] ) )
		$product_data['meta']['_wpsc_special_price'] = $wpsc_product_defaults['special_price'];	
		
	$currency_data = $wpdb->get_results( "SELECT * FROM `" . WPSC_TABLE_CURRENCY_LIST . "` ORDER BY `country` ASC", ARRAY_A );	
	?>
        <input type="hidden" id="parent_post" name="parent_post" value="<?php echo $post->post_parent; ?>" />
        <?php /* Lots of tedious work is avoided with this little line. */ ?>
        <input type="hidden" id="product_id" name="product_id" value="<?php echo $post->ID; ?>" />

    	<?php /* Check product if a product has variations (Wording doesn't make sense.  If Variations box is closed, you don't go there, and it's not necessarily "below") */ ?>
    	<?php if( wpsc_product_has_children( $post->ID ) ) : ?>
    		<?php $price = wpsc_product_variation_price_available( $post->ID ); ?>
			<p><?php printf( __( 'This Product has variations, to edit the price please use the %1s Variation Controls%2s below.' , 'wpsc' ), '<a href="#variation_control">','</a>' ); ?></p>
			<p><?php printf( __( 'Price: %s and above.' ,'wpsc' ) , $price ); ?></p>
		<?php else: ?>
		
    	<div class='wpsc_floatleft' style="width:85px;">
    		<label><?php _e( 'Price', 'wpsc' ); ?>:</label><br />
			<input type='text' class='text' size='10' name='meta[_wpsc_price]' value='<?php echo number_format( $product_data['meta']['_wpsc_price'],2,'.','' ); ?>' />
		</div>
		<div class='wpsc_floatleft' style='display:<?php if ( ($product_data['special'] == 1) ? 'block' : 'none'
						); ?>; width:85px; margin-left:30px;'>
			<label for='add_form_special'><?php _e( 'Sale Price', 'wpsc' ); ?>:</label>
			<div id='add_special'>
				<input type='text' size='10' value='<?php echo number_format( $product_data['meta']['_wpsc_special_price'], 2,'.','' ); ?>' name='meta[_wpsc_special_price]' />
			</div>
		</div>
		<br style="clear:both" />
		<br style="clear:both" />
		<a href='#' class='wpsc_add_new_currency'>+ <?php _e( 'New Currency', 'wpsc' ); ?></a>
		<br />
		<!-- add new currency layer -->
		<div class='new_layer'>
			<label for='newCurrency[]'><?php _e( 'Currency type', 'wpsc' ); ?>:</label><br />
			<select name='newCurrency[]' class='newCurrency' style='width:42%'>
			<?php
				foreach ( (array)$currency_data as $currency ) {?>
					<option value='<?php echo $currency['id']; ?>' >
						<?php echo htmlspecialchars( $currency['country'] ); ?> (<?php echo $currency['currency']; ?>)
					</option> <?php
				} ?>
			</select>
			<?php _e( 'Price', 'wpsc' ); ?> : 
			<input type='text' class='text' size='8' name='newCurrPrice[]' value='0.00' style='display:inline' />
			<a href='' class='deletelayer' rel='<?php echo $isocode; ?>'><img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png' /></a>

		</div> <!-- close new_layer -->
<?php
	if ( isset( $product_alt_currency ) && is_array($product_alt_currency)) :
		$i = 0;
		foreach ($product_alt_currency as $iso => $alt_price ) {
			$i++; ?>
			<div class='wpsc_additional_currency'>
			<label for='newCurrency[]'><?php _e( 'Currency type', 'wpsc' ); ?>:</label><br />
			<select name='newCurrency[]' class='newCurrency' style='width:42%'> <?php
				foreach ( $currency_data as $currency ) {
					if ( $iso == $currency['isocode'] )
						$selected = "selected='selected'";
					else
						$selected = ""; ?>
					<option value='<?php echo $currency['id']; ?>' <?php echo $selected; ?> >
						<?php echo htmlspecialchars( $currency['country'] ); ?> (<?php echo $currency['currency']; ?>)
					</option> <?php
				} ?>
			</select>
			<?php _e( 'Price', 'wpsc' ); ?>:  <input type='text' class='text' size='8' name='newCurrPrice[]' value='<?php echo $alt_price; ?>' style=' display:inline' />
			<a href='' class='wpsc_delete_currency_layer' rel='<?php echo $iso; ?>'><img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png' /></a></div>
<?php	}
		
		endif;

		echo "<br style='clear:both' />
          <br/><input id='add_form_donation' type='checkbox' name='meta[_wpsc_is_donation]' value='yes' " . (($product_data['meta']['_wpsc_is_donation'] == 1) ? 'checked="checked"' : '') . " />&nbsp;<label for='add_form_donation'>" . __( 'This is a donation, checking this box populates the donations widget.', 'wpsc' ) . "</label>";
?>
				<br /><br /> <input type='checkbox' value='1' name='table_rate_price[state]' id='table_rate_price'  <?php echo (((bool)$product_meta['table_rate_price']['state'] == true) ? 'checked=\'checked\'' : ''); ?> />
				<label for='table_rate_price'><?php _e( 'Table Rate Price', 'wpsc' ); ?></label>
				<div id='table_rate'>
					<a class='add_level' style='cursor:pointer;'>+ Add level</a><br />
					<br style='clear:both' />
					<table>
						<tr>
							<th><?php _e( 'Quantity In Cart', 'wpsc' ); ?></th>
							<th colspan='2'><?php _e( 'Discounted Price', 'wpsc' ); ?></th>
						</tr>
<?php
		if ( count( $product_meta['table_rate_price']['quantity'] ) > 0 ) {
			foreach ( (array)$product_meta['table_rate_price']['quantity'] as $key => $quantity ) {
				if ( $quantity != '' ) {
					$table_price = number_format( $product_meta['table_rate_price']['table_price'][$key], 2, '.', '' );
?>
						<tr>
							<td>
								<input type="text" size="5" value="<?php echo $quantity; ?>" name="table_rate_price[quantity][]"/><span class='description'>and above</span> 
							</td>
							<td>
								<input type="text" size="10" value="<?php echo $table_price; ?>" name="table_rate_price[table_price][]" />
							</td>
							<td><img src="<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png" class="remove_line" /></td>
						</tr>
<?php
					}
				}
			}
?>
						<tr>
							<td><input type="text" size="5" value="<?php echo $quantity; ?>" name="table_rate_price[quantity][]"/><span class='description'>and above</span> </td>
							<td><input type='text' size='10' value='' name='table_rate_price[table_price][]'/></td>
						</tr>
					</table>
				</div>
				<?php endif; ?>
<?php
}
function wpsc_stock_control_forms() {
	global $post, $wpdb, $variations_processor, $wpsc_product_defaults;

        $product_data = get_post_custom( $post->ID );
        $product_data['meta'] = maybe_unserialize( $product_data );

        foreach( $product_data['meta'] as $meta_key => $meta_value )
            $product_data['meta'][$meta_key] = $meta_value[0];

        $product_meta = maybe_unserialize( $product_data["_wpsc_product_metadata"][0] );

	if ( !isset( $product_meta['unpublish_when_none_left'] ) )
		$product_meta['unpublish_when_none_left'] = ''; ?>
		
        <label for="wpsc_sku"><abbr title="<?php _e( 'Stock Keeping Unit', 'wpsc' ); ?>">SKU:</abbr></label>
<?php
		if ( !isset( $product_data['meta']['_wpsc_sku'] ) )
			$product_data['meta']['_wpsc_sku'] = $wpsc_product_defaults['meta']['sku']; ?><br />
			<input size='32' type='text' class='text' id="wpsc_sku" name='meta[_wpsc_sku]' value='<?php echo htmlentities( stripslashes( $product_data['meta']['_wpsc_sku'] ), ENT_QUOTES, 'UTF-8' ); ?>' />
			<br style="clear:both" />
			<?php
			if ( !isset( $product_data['meta']['_wpsc_stock'] ) )
				$product_data['meta']['_wpsc_stock'] = ''; ?>
			<br /><input class='limited_stock_checkbox' id='add_form_quantity_limited' type='checkbox' value='yes' <?php if(is_numeric( $product_data['meta']['_wpsc_stock'] )) echo 'checked="checked"'; else echo ''; ?> name='meta[_wpsc_limited_stock]' />
			<label for='add_form_quantity_limited' class='small'><?php _e( 'I have limited stock for this Product', 'wpsc' ); ?></label>
			<?php
			if ( $post->ID > 0 ){
				if ( is_numeric( $product_data['meta']['_wpsc_stock'] ) ){?>
					<div class='edit_stock' style='display: block;'> <?php 
				} else { ?>
					<div class='edit_stock' style='display: none;'><?php
				} ?>
					<?php if( wpsc_product_has_children( $post->ID ) ) : ?>
			    		<?php $stock = wpsc_variations_stock_remaining( $post->ID ); ?>
						<p><?php _e( 'This Product has variations, to edit the quantity please use the Variation Controls below.' , 'wpsc' ); ?></p>
						<p><?php printf(_n("%s variant item in stock.", "%s variant items in stock.", $stock), $stock); ?></p>
					<?php else: ?>
						<label for="stock_limit_quantity"><?php _e( 'Quantity:', 'wpsc' ); ?></label>
						<input type='text' id="stock_limit_quantity" name='meta[_wpsc_stock]' size='3' value='<?php echo $product_data['meta']['_wpsc_stock']; ?>' class='stock_limit_quantity' />
					<?php endif; ?>
						<div class='unpublish_when_none_left'>
							<input type='checkbox' id="inform_when_oos" name='meta[_wpsc_product_metadata][unpublish_when_none_left]' class='inform_when_oos'<?php if( $product_meta['unpublish_when_none_left'] == 1 ) echo ' checked="checked"'; ?> />
							<label for="inform_when_oos"><?php _e( 'Notify site owner and unpublish this Product if stock runs out', 'wpsc' ); ?></label>
						</div>
						<?php _e('If stock runs out, this Product will not be available on the shop unless you untick this box or add more stock.', 'wpsc'); ?>
				</div> <?php
			} else { ?>
				<div style='display: none;' class='edit_stock'>
					 <?php _e( 'Stock Qty', 'wpsc' ); ?><input type='text' name='meta[_wpsc_stock]' value='0' size='10' />
					<div style='font-size:9px; padding:5px;'>
						<input type='checkbox' class='inform_when_oos' name='meta[_wpsc_product_metadata][unpublish_when_none_left]' /> <?php _e( 'If this Product runs out of stock set status to Unpublished & email site owner', 'wpsc' ); ?>
					</div>
				</div><?php
			}
?>
<?php	
}
function wpsc_product_taxes_forms(){
	global $post, $wpdb, $wpsc_product_defaults;
        $product_data = get_post_custom( $post->ID );

        $product_data['meta'] = $product_meta = maybe_unserialize($product_data['_wpsc_product_metadata'][0]);
	
	if ( !isset( $product_data['meta']['_wpsc_custom_tax'] ) )
		$product_data['meta']['_wpsc_custom_tax'] = '';
	$custom_tax = $product_data['meta']['_wpsc_custom_tax'];

	
	if ( !isset( $product_meta['custom_tax'] ) )
		$product_meta['custom_tax'] = 0.00;
	//Add New WPEC-Taxes Bands Here
	$wpec_taxes_controller = new wpec_taxes_controller();

	//display tax bands
	$band_select_settings = array(
		'id' => 'wpec_taxes_band',
		'name' => 'meta[_wpsc_product_metadata][wpec_taxes_band]',
		'label' => __( 'Custom Tax Band' )
	);
	$wpec_taxes_band = '';
	if(isset($product_meta['wpec_taxes_band']))
		$wpec_taxes_band = $product_meta['wpec_taxes_band'];
	?>
            <p>
                 <?php echo $wpec_taxes_controller->wpec_taxes_display_tax_bands( $band_select_settings, $wpec_taxes_band ); ?>
            </p>

<?php				
}
function wpsc_product_variation_forms() {
	global $post, $wpdb, $wp_query, $variations_processor, $wpsc_product_defaults;

        $db_version = get_option( 'db_version' );

        $product_data = get_post_custom( $post->ID );
        $product_data['meta'] = maybe_unserialize( $product_data );

        foreach( $product_data['meta'] as $meta_key => $meta_value )
            $product_data['meta'][$meta_key] = $meta_value[0];

        $product_meta = maybe_unserialize( $product_data["_wpsc_product_metadata"][0] );

	$siteurl = get_option( 'siteurl' );
	$output  = '';

	// Get variation data from WP Terms
	$product_term_data = wp_get_object_terms( $post->ID, 'wpsc-variation' );
	echo '<pre>'.print_r($product_term_data,1).'</pre>';
	if ( !empty( $product_term_data ) ) {
		foreach ( $product_term_data as $product_term )
			$product_terms[] = $product_term->term_id;
	} else {
		$product_terms = array();
	}
?>
				<?php if( empty( $post->post_title ) ) : ?>
					<p><?php _e( 'You must first save this Product as a Draft before adding variations', 'wpsc' ); ?></p>
				<?php else : ?>
				<div id="product_variations">
					<div class="variation_checkboxes">
						<?php
						// Get the terms from variations
						$variation_sets = get_terms( 'wpsc-variation', array (
							'hide_empty' => 0,
							'parent'     => 0
						) );
						// Loop through each variation set
						foreach ( (array)$variation_sets as $variation_set ) :
							$set_checked_state = '';

							// If this Product includes this variation, check it
							if ( in_array( $variation_set->term_id, $product_terms ) )
								$set_checked_state = "checked='checked'";	?>
								<div class="variation_set">

									<label class='set_label'>
										<input type="checkbox" <?php echo $set_checked_state; ?> name="variations[<?php echo $variation_set->term_id; ?>]" value="1">
										<?php echo $variation_set->name; ?>
									</label>

										<?php
										$variations = get_terms( 'wpsc-variation', array (
											'hide_empty' => 0,
											'parent'     => $variation_set->term_id
										) );
										// Loop through the variations
										foreach ( (array)$variations as $variation ) :
											$checked_state = '';

											if ( in_array( $variation->term_id, $product_terms ) )
												$checked_state = "checked='checked'";

										?>

										<div class="variation">
											<label>
												<input type="checkbox" <?php echo $checked_state; ?> name="edit_var_val[<?php echo $variation_set->term_id; ?>][<?php echo $variation->term_id; ?>]" value="1">
												<?php echo $variation->name; ?>
											</label>
										</div>

										<?php endforeach; ?>

								</div>

							<?php endforeach; ?>

					</div>
                                <a class="preview button update_variations_action" href='#'><?php _e( 'Update Variations &rarr;', 'wpsc' ); ?></a>

				</div>
	<?php
		$parent_product = $post->ID;

		$query = array(
			'post_type'   => 'wpsc-product',
			'orderby'     => 'menu_order post_title',
			'post_parent' => $parent_product,
			'post_status' => 'all',
			'order'       => "ASC"
		);
                
		$args = array(
			'post_type'   => 'attachment',
			'numberposts' => 1,
			'post_status' => null,
			'post_parent' => $parent_product,
			'orderby'     => 'menu_order',
			'order'       => 'ASC'
		);

		$image_data                   = (array)get_posts( $args );
		$parent_product_data['image'] = array_shift( $image_data );

		$wp_query = new WP_Query( $query );
		if ( !isset( $parent_product_data ) )
			$parent_product_data = null;
	?>

			<p><a name='variation_control'>&nbsp;</a><?php _e( 'Check or uncheck variation boxes and then click Update Variations to add or remove variations.' ) ?></p>

                        <?php if( $db_version <= '15477' ) :  ?>
                        <table class="widefat page" id='wpsc_product_list' cellspacing="0">
				<thead>
					<tr>
						<?php print_column_headers( 'wpsc-product_variants' ); ?>
					</tr>
				</thead>

				<tfoot>
					<tr>
						<?php print_column_headers( 'wpsc-product_variants', false ); ?>
					</tr>
				</tfoot>

				<tbody>
            <?php
		wpsc_admin_product_listing( $parent_product_data );
            ?>
<?php
		if ( count( $wp_query->posts ) < 1 ) :
?>
					<tr>
						<td colspan="8">
							<?php _e( 'You have no Products added.', 'wpsc' ); ?>
						</td>
					</tr>

	<?php endif; ?>
				</tbody>

			</table>
                        
        <?php
            //3.0.4 or lower is above, 3.1+ is below (at the moment, they're the same)
            else:

            wpsc_admin_product_listing_nai();

            endif;
        endif;
        ?>

<?php
}
function wpsc_product_shipping_forms() {
	global $post, $wpdb, $variations_processor, $wpsc_product_defaults;

        $product_data = get_post_custom( $post->ID );
        $product_data['meta'] = maybe_unserialize( $product_data );

        foreach( $product_data['meta'] as $meta_key => $meta_value )
            $product_data['meta'][$meta_key] = $meta_value[0];

        $product_meta = maybe_unserialize( $product_data["_wpsc_product_metadata"][0] );

        $product_data['transformed'] = array();
        if( !isset( $product_meta['weight'] ) )
           $product_meta['weight'] = "";

        $product_data['transformed']['weight'] = wpsc_convert_weight( $product_meta['weight'], "gram", $product_meta['weight_unit'] );

       ?>
		<table>

     <!--USPS shipping changes-->
		   <tr>
			  <td>
				<?php _e( 'Weight', 'wpsc' )?>
			  </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][weight]' value='<?php echo $product_data['transformed']['weight']; ?>' />
				 <select name='meta[_wpsc_product_metadata][weight_unit]'>
					<option value='pound' <?php echo (($product_meta['display_weight_as'] == 'pound') ? 'selected="selected"' : ''); ?> >Pounds</option>
					<option value='ounce' <?php echo ((preg_match( "/o(u)?nce/", $product_meta['display_weight_as'] )) ? 'selected="selected"' : ''); ?> >Ounces</option>
					<option value='gram' <?php echo (($product_meta['display_weight_as'] == 'gram') ? 'selected="selected"' : ''); ?> >Grams</option>
					<option value='kilogram' <?php echo (($product_meta['display_weight_as'] == 'kilogram' || $product_meta['display_weight_as'] == 'kilograms') ? 'selected="selected"' : ''); ?> >Kilograms</option>
				 </select>
			  </td>
                    </tr>
			  <!--dimension-->
			<tr>
			  <td>
				<?php _e( 'Height', 'wpsc' ); ?>
                          </td>
			  <td>
                             <input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][height]' value= '<?php echo $product_meta['dimensions']['height']; ?>'>
                             <select name='meta[_wpsc_product_metadata][dimensions][height_unit]'>
                                    <option value='in' <?php echo (($product_meta['dimensions']['height_unit'] == 'in') ? 'selected' : ''); ?> >inches</option>
                                    <option value='cm' <?php echo (($product_meta['dimensions']['height_unit'] == 'cm') ? 'selected' : ''); ?> >cm</option>
                                    <option value='meter' <?php echo (($product_meta['dimensions']['height_unit'] == 'meter') ? 'selected' : ''); ?> >meter</option>
                             </select>
                             </td>
                         </tr>
                         <tr>
                              <td>
                                    <?php _e( 'Width', 'wpsc' ); ?>
                              </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][width]' value='<?php echo $product_meta['dimensions']['width']; ?> '>
				 <select name='meta[_wpsc_product_metadata][dimensions][width_unit]'>
					<option value='in' <?php echo(($product_meta['dimensions']['width_unit'] == 'in') ? 'selected' : ''); ?> >inches</option>
					<option value='cm' <?php echo (($product_meta['dimensions']['width_unit'] == 'cm') ? 'selected' : ''); ?> >cm</option>
					<option value='meter' <?php echo (($product_meta['dimensions']['width_unit'] == 'meter') ? 'selected' : ''); ?> >meter</option>
				 </select>
				 </td>
				 </tr>
				 <tr>
			  <td>
				 <?php _e( 'Length', 'wpsc' ); ?>
			  </td>
			  <td>
				 <input type='text' size='5' name='meta[_wpsc_product_metadata][dimensions][length]' value='<?php echo $product_meta['dimensions']['length']; ?>'>
				 <select name='meta[_wpsc_product_metadata][dimensions][length_unit]'>
					<option value='in' <?php echo(($product_meta['dimensions']['length_unit'] == 'in') ? 'selected' : ''); ?> >inches</option>
					<option value='cm' <?php echo(($product_meta['dimensions']['length_unit'] == 'cm') ? 'selected' : ''); ?> >cm</option>
					<option value='meter' <?php echo (($product_meta['dimensions']['length_unit'] == 'meter') ? 'selected' : ''); ?> >meter</option>
				 </select>
				 </td>
			 </tr>

    <!--//dimension-->
    <!--USPS shipping changes ends-->
			<tr>
			  <td colspan='2'>
			  <strong><?php _e( 'Flat Rate Settings', 'wpsc' ); ?></strong>
			  </td>
			</tr>
			<tr>
			  <td>
                             <?php _e( 'Local Shipping Fee', 'wpsc' ); ?>
			  </td>
			  <td>
				<input type='text' size='10' name='meta[_wpsc_product_metadata][shipping][local]' value='<?php echo number_format( $product_meta['shipping']['local'], 2, '.', '' ); ?>' />
			  </td>
			</tr>

			<tr>
			  <td>
                            <?php _e( 'International Shipping Fee', 'wpsc' ); ?>
			  </td>
			  <td>
				<input type='text' size='10' name='meta[_wpsc_product_metadata][shipping][international]' value='<?php echo number_format( $product_meta['shipping']['international'], 2, '.', '' ); ?>' />
			  </td>
			</tr>
			<tr>
				 <td>
				 <br />
				  <input id='add_form_no_shipping' type='checkbox' name='meta[_wpsc_product_metadata][no_shipping]' value='1' <?php echo (($product_meta['no_shipping'] == 1) ? 'checked="checked"' : ''); ?> />&nbsp;<label for='add_form_no_shipping'><?php _e( 'Disregard Shipping for this Product', 'wpsc' ); ?></label>
			   </td>
			</tr>
	    </table>
<?php
    }
function wpsc_product_advanced_forms() {
	global $post, $wpdb, $variations_processor, $wpsc_product_defaults;
        $product_data = get_post_custom( $post->ID );

        $product_data['meta'] = $product_meta = maybe_unserialize($product_data['_wpsc_product_metadata'][0]);

	$custom_fields = $wpdb->get_results( "
		SELECT
			`meta_id`, `meta_key`, `meta_value`
		FROM
			`{$wpdb->postmeta}`
		WHERE
			`post_id` = {$post->ID}
		AND
			`meta_key` NOT LIKE '\_%'
		ORDER BY
			LOWER(meta_key)", ARRAY_A
	);
?>

        <table>
            <tr>
                <td colspan='2' class='itemfirstcol'>
                    <strong><?php _e( 'Custom Meta', 'wpsc' ); ?>:</strong><br />
                    <a href='#' class='add_more_meta' onclick="return add_more_meta(this)"> + <?php _e( 'Add Custom Meta', 'wpsc' );?> </a><br /><br />

                    <?php
                        foreach ( (array)$custom_fields as $custom_field ) {
                            $i = $custom_field['meta_id'];

                            ?>
                            <div class='product_custom_meta'  id='custom_meta_<?php echo $i; ?>'>
                                    <?php _e( 'Name', 'wpsc' ); ?>
                                    <input type='text' class='text'  value='<?php echo $custom_field['meta_key']; ?>' name='custom_meta[<?php echo $i; ?>][name]' id='custom_meta_name_<?php echo $i; ?>'>
                                    <?php _e( 'Value', 'wpsc' ); ?>
                                    <textarea class='text' name='custom_meta[<?php echo $i; ?>][value]' id='custom_meta_value_<?php echo $i; ?>'><?php echo $custom_field['meta_value']; ?></textarea>
                                    <a href='#' class='remove_meta' onclick='return remove_meta(this, <?php echo $i; ?>)'><?php _e( 'Delete' ); ?></a>
                                    <br />
                            </div>
                    <?php
                        }
                    ?>
				<div class='product_custom_meta'>
					<?php _e( 'Name', 'wpsc' ); ?>: <br />
					<input type='text' name='new_custom_meta[name][]' value='' class='text'/><br />
					<?php _e( 'Description', 'wpsc' ); ?>: <br />
					<textarea name='new_custom_meta[value][]' cols='40' rows='10' class='text' ></textarea>
					<br />
				</div>
			</td>
		</tr>
		<tr>
			<td class='itemfirstcol' colspan='2'><br /> <strong><?php _e( 'Merchant Notes', 'wpsc' ); ?>:</strong><br />

			<textarea cols='40' rows='3' name='meta[_wpsc_product_metadata][merchant_notes]' id='merchant_notes'>
                            <?php if ( isset( $product_meta['merchant_notes'] ) )
                                    echo stripslashes( trim( $product_meta['merchant_notes'] ) );
                            ?>
                       </textarea>
			<small><?php _e( 'These notes are only available here.', 'wpsc' ); ?></small>
		</td>
	</tr>
	<tr>
		<td class='itemfirstcol' colspan='2'><br />
			<strong><?php _e( 'Personalisation Options', 'wpsc' ); ?>:</strong><br />
			<input type='hidden' name='meta[_wpsc_product_metadata][engraved]' value='0' />
			<input type='checkbox' name='meta[_wpsc_product_metadata][engraved]' <?php echo (($product_meta['engraved'] == true) ? 'checked="checked"' : ''); ?> id='add_engrave_text' />
			<label for='add_engrave_text'><?php _e( 'Users can personalize this Product by leaving a message on single product page', 'wpsc' ); ?></label>
			<br />
		</td>
	</tr>
	<tr>
		<td class='itemfirstcol' colspan='2'>
			<input type='hidden' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' value='0' />
			<input type='checkbox' name='meta[_wpsc_product_metadata][can_have_uploaded_image]' <?php echo ($product_meta['can_have_uploaded_image'] == true) ? 'checked="checked"' : ''; ?> id='can_have_uploaded_image' />
			<label for='can_have_uploaded_image'> <?php _e( 'Users can upload images on single product page to purchase logs.', 'wpsc' ); ?> </label>
			<br />
		</td>
	</tr>
        <?php
            if ( get_option( 'payment_gateway' ) == 'google' ) {
        ?>
	<tr>
		<td class='itemfirstcol' colspan='2'>

			<input type='checkbox' <?php echo $product_meta['google_prohibited']; ?> name='meta[_wpsc_product_metadata][google_prohibited]' id='add_google_prohibited' /> <label for='add_google_prohibited'>
			<?php _e( 'Prohibited', 'wpsc' ); ?>
			<a href='http://checkout.google.com/support/sell/bin/answer.py?answer=75724'>by Google?</a></label><br />
		</td>
	</tr>
	<?php
            }
            ob_start();
            do_action( 'wpsc_add_advanced_options', $post->ID );
            ob_get_contents();
            ob_end_clean();
        ?>
	<tr>
		<td class='itemfirstcol' colspan='2'><br />
			<strong><?php _e( 'Enable Comments', 'wpsc' ); ?>:</strong><br />
			<select name='meta[_wpsc_product_metadata][enable_comments]'>
				<option value='' <?php echo ((isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '' ) ? 'selected' : ''); ?> >Use Default</option>
				<option value='1' <?php echo ((isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '1') ? 'selected' : ''); ?> >Yes</option>
				<option value='0' <?php echo ((isset( $product_meta['enable_comments'] ) && $product_meta['enable_comments'] == '0') ? 'selected' : ''); ?> >No</option>
			</select>
			<br/><?php _e( 'Allow users to comment on this Product.', 'wpsc' ); ?>
		</td>
	</tr>
    </table>
<?php
    }
function wpsc_product_external_link_forms() {

    global $post, $wpdb, $variations_processor, $wpsc_product_defaults;
        $product_data = get_post_custom( $post->ID );

        $product_data['meta'] = $product_meta = maybe_unserialize($product_data['_wpsc_product_metadata'][0]);

	// Get External Link Values
	$external_link_value        = isset( $product_meta['external_link'] ) ? $product_meta['external_link'] : '';
	$external_link_text_value   = isset( $product_meta['external_link_text'] ) ? $product_meta['external_link_text'] : '';
	$external_link_target_value = isset( $product_meta['external_link_target'] ) ? $product_meta['external_link_target'] : '';
	$external_link_target_value_selected[$external_link_target_value] = ' selected="selected"';
	if( !isset($external_link_target_value_selected['_self']) ) $external_link_target_value_selected['_self'] = '';
	if( !isset($external_link_target_value_selected['_blank']) ) $external_link_target_value_selected['_blank'] = '';

      ?>
        <p><?php _e( 'If this product is for sale on another website enter the link here. For instance if your product is an MP3 file for sale on iTunes you could put the link here. This option overrides the buy now and add to cart links and takes you to the site linked here. You can also customise the Buy Now text and choose to open the link in a new window.', 'wpsc' ); ?>
        <table class="form-table" style="width: 100%;" cellspacing="2" cellpadding="5">
            <tbody>
                <tr class="form-field">
                    <th valign="top" scope="row"><label for="external_link"><?php _e( 'External Link', 'wpsc' ); ?></label></th>
                    <td><input type="text" name="meta[_wpsc_product_metadata][external_link]" id="external_link" value="<?php echo $external_link_value; ?>" size="50" style="width: 95%"></td>
                </tr>
                <tr class="form-field">
                    <th valign="top" scope="row"><label for="external_link_text"><?php _e( 'External Link Text', 'wpsc' ); ?></label></th>
                    <td><input type="text" name="meta[_wpsc_product_metadata][external_link_text]" id="external_link_text" value="<?php echo $external_link_text_value; ?>" size="50" style="width: 95%"></td>
                </tr>
                <tr class="form-field">
                     <th valign="top" scope="row"><label for="external_link_target"><?php _e( 'External Link Target', 'wpsc' ); ?></label></th>
                    <td>
                        <select id="external_link_target" name="meta[_wpsc_product_metadata][external_link_target]">
                            <option value="">Default (set by theme)</option>
                            <option value="_self" <?php echo $external_link_target_value_selected['_self']; ?>>Open link in the same window</option>
                            <option value="_blank" <?php echo $external_link_target_value_selected['_blank']; ?>>Open link in a new window</option>
                        </select>
                    </td>
                </tr>
            </tbody>
        </table>
<?php
}
function wpsc_product_image_forms() {

    global $post;
    
    edit_multiple_image_gallery( $post );

    ?>

    <p><strong <?php if ( isset( $display ) ) echo $display; ?>><a href="media-upload.php?parent_page=wpsc-edit-products&post_id=<?php echo $post->ID; ?>&type=image&tab=gallery&TB_iframe=1&width=640&height=566" class="thickbox" title="Manage Your Product Images"><?php _e( 'Manage Product Images', 'wpsc' ); ?></a></strong></p>
<?php
    }
function wpsc_additional_desc() {
    global $post;
?>
    <textarea name='additional_description' id='additional_description' cols='40' rows='5' ><?php echo stripslashes( $post->post_excerpt ); ?></textarea>
<?php

}
function wpsc_product_download_forms() {

    global $post, $wpdb, $wpsc_product_defaults;
        $product_data = get_post_custom( $post->ID );

        $product_data['meta'] = $product_meta = maybe_unserialize($product_data['_wpsc_product_metadata'][0]);

	$upload_max = wpsc_get_max_upload_size();
    ?>
	<?php echo wpsc_select_product_file( $post->ID ); ?>
	<h4><?php _e( 'Upload New File', 'wpsc' ); ?>:</h4>
	<input type='file' name='file' value='' /><br /><?php _e( 'Max Upload Size', 'wpsc' ); ?>:<span><?php echo $upload_max; ?></span><br /><br />
	<h4><a href="admin.php?wpsc_admin_action=product_files_existing&product_id=<?php echo $post->ID; ?>" class="thickbox" title="<?php _e( 'Select all downloadable files for ' . $post->post_title, 'wpsc' ); ?>"><?php _e( 'Select from existing files', 'wpsc' ); ?></a></h4>
        <?php
            if ( function_exists( "make_mp3_preview" ) || function_exists( "wpsc_media_player" ) ) {
        ?>
            <br />
            <h4>" . __( "Select an MP3 file to upload as a preview" ) . "</h4>";
            <input type='file' name='preview_file' value='' /><br />";
            <br />
        <?php
	}
	$output = apply_filters('wpsc_downloads_metabox', $output);
}
function wpsc_product_label_forms() {
    //This is new to me...not sure why this is even here
?>
	<div id='wpsc_product_label_forms' class='postbox <?php echo ((array_search( 'wpsc_product_label_forms', $product_data['closed_postboxes'] ) !== false) ? 'closed' : ''); ?>'><div class="handlediv" title="Click to toggle"><br></div>
		<h3 class="<?php if ( function_exists( 'add_object_page' ) ) : ?>hndle<?php endif; ?>">
			<?php if ( !function_exists( 'add_object_page' ) ) : ?>

				<a class='togbox'>+</a>

			<?php endif; ?>

			<?php _e( 'Label Control', 'wpsc' ); ?>
		</h3>
		<div class='inside'>
			<table>
				<tr>
					<td colspan='2'>
<?php _e( 'Add Label', 'wpsc' ); ?> :
						<a id='add_label'><?php _e( 'Add Label', 'wpsc' ); ?></a>
					</td>
				</tr>
				<tr>
					<td colspan='2'>
						<div id="labels">
							<table>
								<tr>
									<td><?php _e( 'Label', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[labels][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'Label Description', 'wpsc' ) ?> :</td>
									<td><textarea name="productmeta_values[labels_desc][]"></textarea></td>
								</tr>
								<tr>
									<td><?php _e( 'Life Number', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[life_number][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'Item Number', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[item_number][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'Product Code', 'wpsc' ) ?> :</td>
									<td><input type="text" name="productmeta_values[product_code][]"></td>
								</tr>
								<tr>
									<td><?php _e( 'PDF', 'wpsc' ) ?> :</td>
									<td><input type="file" name="pdf[]"></td>
								</tr>
							</table>
						</div>
					</td>
				</tr>
			</table>
		</div>
	</div>

<?php
}
/**
 * Adding function to change text for media buttons
 */
function change_context( $context ) {
    global $current_screen;

    if( $current_screen->id != 'wpsc-product' )
        return $context;
    return __( 'Upload Image%s' );
}
function change_link( $link ) {
    global $post_ID, $current_screen;

    if( $current_screen->id != 'wpsc-product' )
        return $link;

	$uploading_iframe_ID = $post_ID;
	$media_upload_iframe_src = "media-upload.php?post_id=$uploading_iframe_ID";

	return $media_upload_iframe_src . "&amp;type=image&parent_page=wpsc-edit-products";
}
function wpsc_form_multipart_encoding() {
    echo ' enctype="multipart/form-data"';
}

add_action( 'post_edit_form_tag', 'wpsc_form_multipart_encoding' );
add_filter( 'media_buttons_context', 'change_context' );
add_filter( 'image_upload_iframe_src', "change_link" );
/*
* Modifications to Media Gallery
*/

if ( (isset( $_REQUEST['parent_page'] ) && ( $_REQUEST['parent_page'] == 'wpsc-edit-products' ) ) ) {
    add_filter( 'media_upload_tabs', 'wpsc_media_upload_tab_gallery', 12 );
    add_filter( 'attachment_fields_to_save', 'wpsc_save_attachment_fields', 9, 2 );
    add_filter( 'media_upload_form_url', 'wpsc_media_upload_url', 9, 1 );
    add_action( 'admin_head', 'wpsc_gallery_css_mods' );
}
    add_filter( 'gettext','wpsc_filter_delete_text',12 , 3 );
    add_filter( 'attachment_fields_to_edit', 'wpsc_attachment_fields', 11, 2 );
    add_filter( 'gettext','wpsc_filter_feature_image_text', 12, 3 );

if( isset( $_REQUEST["save"] ) && isset($_REQUEST["attachments"]) && is_array($_REQUEST["attachments"]) ) {
	wpsc_regenerate_thumbnails();
}	

/*
 * This filter translates string before it is displayed 
 * specifically for the words 'Use as featured image' with 'Use as Product Thumbnail' when the user is selecting a Product Thumbnail
 * using media gallery.
 * 
 * @param $translation The current translation
 * @param $text The text being translated
 * @param $domain The domain for the translation
 * @return string The translated / filtered text.
 */
function wpsc_filter_feature_image_text($translation, $text, $domain) {

	if( 'Use as featured image' == $text && isset( $_REQUEST['post_id'] ) ){
		$translations = &get_translations_for_domain($domain);
		return $translations->translate('Use as Product Thumbnail') ;
	}
	return $translation;
}
function wpsc_attachment_fields($form_fields, $post) {

	$parent_post = get_post($post->post_parent);

	if ($parent_post->post_type == "wpsc-product" || $parent_post->post_title == "stopgap") {
	
	//Unfortunate hack, as I'm not sure why the From Computer tab doesn't process filters the same way the Gallery does
	
	echo '	
<script type="text/javascript"> 

	jQuery(function($){
	
		var product_image = $("a.wp-post-thumbnail").text();
		
		if (product_image == "Use as featured image") {
			$("a.wp-post-thumbnail").text("Use as Product Thumbnail");
		}
		
		var trash = $("#media-upload a.del-link").text();
		
		if (trash == "Delete") {
			$("#media-upload a.del-link").text("Trash");
		}
		
		
		});
	
</script>';
        $size_names = array('small-product-thumbnail' => __('Small Product Thumbnail'), 'medium-single-product' => __('Medium Single Product'), 'full' => __('Full Size'));
			
		$check = get_post_meta( $post->ID, '_wpsc_selected_image_size', true );

	//This loop attaches the custom thumbnail/single image sizes to this page
        foreach ( $size_names as $size => $name ) {
            $downsize = image_downsize($post->ID, $size);

	        // is this size selectable?
           $enabled = ( $downsize[3] || 'full' == $size );
			$css_id = "image-size-{$size}-{$post->ID}";
			// if this size is the default but that's not available, don't select it
	
            $html = "<div class='image-size-item'><input type='radio' " . disabled( $enabled, false, false ) . "name='attachments[$post->ID][image-size]' id='{$css_id}' value='{$size}' ".checked($size, $check, false)." />";

            $html .= "<label for='{$css_id}'>" . __($name). "</label>";
            // only show the dimensions if that choice is available
            if ( $enabled )
                $html .= " <label for='{$css_id}' class='help'>" . sprintf( __("(%d&nbsp;&times;&nbsp;%d)"), $downsize[1], $downsize[2] ). "</label>";

            $html .= '</div>';

            $out .= $html;
	}
		
		unset($form_fields['post_excerpt'],$form_fields['image_url'], $form_fields['post_content'], $form_fields['post_title'], $form_fields['url'], $form_fields['align'], $form_fields['image_alt']['helps'], $form_fields["image-size"]);
		$form_fields['image_alt']['helps'] =  __('Alt text for the product image, e.g. &#8220;Rockstar T-Shirt&#8221;');

		$form_fields["image-size"] = array(
				 'label' => __('Size'),
				 'input' => 'html',
				 'html'  => $out,
				 'helps' => "<span style='text-align:left; clear:both; display:block; padding-top:3px;'>Thumbnail size for this image on the single product page</span>"
		);
		
	//This is for the custom thumbnail size.	
	
		$custom_thumb_size_w = get_post_meta($post->ID, "_wpsc_custom_thumb_w", true);
		$custom_thumb_size_h = get_post_meta($post->ID, "_wpsc_custom_thumb_h", true);
		$custom_thumb_html = "
			
			<input style='width:50px; text-align:center' type='text' name='attachments[{$post->ID}][wpsc_custom_thumb_w]' value='{$custom_thumb_size_w}' /> X <input style='width:50px; text-align:center' type='text' name='attachments[{$post->ID}][wpsc_custom_thumb_h]' value='{$custom_thumb_size_h}' />
		
		";
		$form_fields["wpsc_custom_thumb"] = array(
		"label" => __("Custom Thumbnail Size"),
		"input" => "html", // this is default if "input" is omitted
		"helps" => "<span style='text-align:left; clear:both; display:block; padding-top:3px;'>Custom thumbnail size for this image on the main Product Page</span>",
		"html" => $custom_thumb_html
	);
		
		}
        return $form_fields;
		
	}	
function wpsc_save_attachment_fields($post, $attachment) {

	if ( isset  ( $attachment['wpsc_custom_thumb_w']) ) 
		update_post_meta($post['ID'], '_wpsc_custom_thumb_w', $attachment['wpsc_custom_thumb_w']);
		
	if ( isset  ( $attachment['wpsc_custom_thumb_h']) ) 
		update_post_meta($post['ID'], '_wpsc_custom_thumb_h', $attachment['wpsc_custom_thumb_h']);
		
	if ( isset  ( $attachment['image-size']) ) 
		update_post_meta($post['ID'], '_wpsc_selected_image_size', $attachment['image-size']);

	return $post;
}
function wpsc_media_upload_url($form_action_url) {

	$form_action_url = esc_url(add_query_arg(array('parent_page'=>'wpsc-edit-products')));

return $form_action_url;

}
function wpsc_gallery_css_mods() {

			print '<style type="text/css">
			#gallery-settings *{
			display:none;
			}
			a.wp-post-thumbnail {
					color:green;
			}
			#media-upload a.del-link {
				color:red;
			}
			#media-upload a.wp-post-thumbnail {
				margin-left:0px;
			}	
			td.savesend input.button {
				display:none;
			}
	</style>';
	print '	
	<script type="text/javascript"> 

	jQuery(function($){
		$("td.A1B1").each(function(){
		
			var target = $(this).next();
				$("p > input.button", this).appendTo(target);

		});
		
		var title = $("div.media-item span.title").text();
		
		if(title == "stopgap") {
			$("div.media-item").hide();
		}
		
		var product_image = $("a.wp-post-thumbnail").text();
		
		if (product_image == "Use as featured image") {
			$("a.wp-post-thumbnail").text("Use as Product Thumbnail");
		}
		
		
		
	});
	
	</script>';
}
function wpsc_media_upload_tab_gallery($tabs) {
	
		unset($tabs['gallery']);
		$tabs['gallery'] = __('Product Image Gallery');

	return $tabs;
}
function wpsc_filter_delete_text($translation, $text, $domain){

	if( 'Delete' == $text && isset( $_REQUEST['post_id'] ) && isset( $_REQUEST["parent_page"] ) ){
		$translations = &get_translations_for_domain($domain);
		return $translations->translate('Trash') ;
	}
	return $translation;
}
function edit_multiple_image_gallery( $post ) {
	global $wpdb;
//Not sure when this was added, but this is ONLY going to show the product thumbnail, one image, not all the images, in the product
//images metabox...is that what we want?
	$siteurl = get_option( 'siteurl' );
	
	if ( $post->ID > 0 ) {
		if ( has_post_thumbnail( $post->ID ) )
			echo get_the_post_thumbnail( $post->ID, 'admin-product-thumbnails' );
	
		$args = array(
			'post_type' => 'attachment',
			'numberposts' => -1,
			'post_status' => null,
			'post_parent' => $post->ID,
			'orderby' => 'menu_order',
			'order' => 'ASC'
		);

		$attached_images = (array)get_posts( $args );
		
		if(count($attached_images) > 0){
			foreach($attached_images as $images){
				$attached_image = wp_get_attachment_image( $images->ID, 'admin-product-thumbnails' );
				echo $attached_image. '&nbsp;';
			}
		}
		
	}
}

/**
* wpsc_save_quickedit_box function
* Saves input for the various meta in the quick edit boxes
*
* @todo UI
* @todo Data validation / sanitization / security
* @todo AJAX should probably return weight unit
* @return $post_id (int) Post ID
*/

function wpsc_save_quickedit_box( $post_id ) {

    if( !defined( 'DOING_AJAX' ) )
        return;

    $is_parent = ( bool )wpsc_product_has_children( $post_id );
    $product_meta = get_post_meta( $post_id, '_wpsc_product_metadata', true );
    $weight_unit = $product_meta["weight_unit"];
    $weight = wpsc_convert_weight( $_POST["weight"], $weight_unit, "gram" );

    if( isset( $product_meta["weight"] ) )
        unset($product_meta["weight"]);

    $product_meta["weight"] = $weight;

    if( !$is_parent ) {
        update_post_meta( $post_id, '_wpsc_product_metadata', $product_meta );
        update_post_meta( $post_id, '_wpsc_stock', $_POST['stock'] );
        update_post_meta( $post_id, '_wpsc_price', $_POST['price'] );
        update_post_meta( $post_id, '_wpsc_special_price', $_POST['sale_price'] );
    }
        update_post_meta( $post_id, '_wpsc_sku', $_POST['sku'] );

    return $post_id;
}

/**
* wpsc_quick_edit_boxes function
* Creates inputs for the various meta in the quick edit boxes.
*
 * @todo UI
 * @internal Tried accessing post_id in here to do the is_parent/variation thing - no luck :(
*/

function wpsc_quick_edit_boxes( $col_name, $type ) {
 
    if( $type != 'wpsc-product' )
        return;
    ?>

<fieldset class="inline-edit-col-left wpsc-cols">
    <div class="inline-edit-col">
        <div class="inline-edit-group">
            <?php
                switch ( $col_name ) :
                case 'SKU' :
            ?>
            <label class="alignleft">
                <span class="checkbox-title">SKU: </span>
                <input type="text" name="sku" id="wpsc_ie_sku">
            </label>
            <?php
                break;
                case 'weight' :
            ?>
            <label class="alignleft">
                <span class="checkbox-title">Weight: </span>
                <input type="text" name="weight" id="wpsc_ie_weight">
            </label>
            <?php
                break;
                case 'stock' :
            ?>
            <label class="alignleft">
                <span class="checkbox-title">Stock: </span>
                <input type="text" name="stock" id="wpsc_ie_stock">
            </label>
            <?php
                break;
                case 'price' :
            ?>
            <label class="alignleft">
                <span class="checkbox-title">Price: </span>
                <input type="text" name="price" id="wpsc_ie_price">
            </label>
            <?php
                break;
                case 'sale_price' :
            ?>
            <label class="alignleft">
                <span class="checkbox-title">Sale Price: </span>
                <input type="text" name="sale_price" id="wpsc_ie_sale_price">
            </label>
            <?php
                break;
            endswitch;
            ?>
         </div>
    </div>
</fieldset>
<?php
}

add_action( 'quick_edit_custom_box', 'wpsc_quick_edit_boxes', 10, 2 );
add_action( 'save_post', 'wpsc_save_quickedit_box' );

?>
