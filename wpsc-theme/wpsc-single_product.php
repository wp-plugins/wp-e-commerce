<?php
	// Setup globals
	// @todo: Get these out of template
	global $wpsc_query, $wpdb, $wpsc_custom_meta, $wp_query;

	// Setup image width and height variables
	// @todo: Investigate if these are still needed here
	$image_width  = get_option( 'single_view_image_width' );
	$image_height = get_option( 'single_view_image_height' );
?>

<div id="products_page_container" class="wrap wpsc_container">
	
	<?php
		// Breadcrumbs
		wpsc_output_breadcrumbs();

		// Plugin hook for adding things to the top of the products page, like the live search
		do_action( 'wpsc_top_of_products_page' );
	?>
	
	<div class="productdisplay">

<?php

		/**
		 * Start the product loop here.
		 * This is single products view, so there should be only one
		 */

		while ( wpsc_have_products() ) : wpsc_the_product(); ?>

			<div class="single_product_display product_view_<?php echo wpsc_the_product_id(); ?>">
				<div class="textcol">
					<div class="imagecol">

						<?php if ( wpsc_the_product_thumbnail() ) : ?>

								<a rel="<?php echo str_replace(array(" ", '"',"'", '&quot;','&#039;'), array("_", "", "", "",''), wpsc_the_product_title()); ?>" class="<?php echo wpsc_the_product_image_link_classes(); ?>" href="<?php echo wpsc_the_product_image(); ?>">
									<img class="product_image" id="product_image_<?php echo wpsc_the_product_id(); ?>" alt="<?php echo wpsc_the_product_title(); ?>" title="<?php echo wpsc_the_product_title(); ?>" src="<?php echo wpsc_the_product_thumbnail(); ?>"/>
								</a>

						<?php else: ?>

							<div class="item_no_image">
								<a href="<?php echo wpsc_the_product_permalink(); ?>">
								<span>No Image Available</span>
								</a>
							</div>

						<?php endif; ?>

					</div><!-- end .imagecol -->

					<div class="producttext">
						
						<?php do_action('wpsc_product_before_description', wpsc_the_product_id(), $wpsc_query->product); ?>

						<div class="wpsc_description">

							<?php echo wpsc_the_product_description(); ?>

						</div><!-- end .wpsc_description -->
		
						<?php do_action( 'wpsc_product_addons', wpsc_the_product_id() ); ?>
						
						<?php if ( wpsc_the_product_additional_description() ) : ?>

							<div class="single_additional_description">

								<?php
									$value = '';
									$the_addl_desc = wpsc_the_product_additional_description();

									if ( is_serialized( $the_addl_desc ) )
										$addl_descriptions = @unserialize( $the_addl_desc );
									else
										$addl_descriptions = array( 'addl_desc', $the_addl_desc );

									if ( isset( $addl_descriptions['addl_desc'] ) )
										$value = $addl_descriptions['addl_desc'];

									if( function_exists( 'wpsc_addl_desc_show' ) )
										echo wpsc_addl_desc_show( $addl_descriptions );
									else
										echo stripslashes( wpautop( $the_addl_desc, $br = 1) );
								?>

							</div><!-- end .single_additional_description -->

						<?php endif; ?>
					
						<?php do_action( 'wpsc_product_addon_after_descr', wpsc_the_product_id() ); ?>
	
						<?php
						/**
						 * Custom meta HTML and loop
						 */
						?>

						<div class="custom_meta">

							<?php while ( wpsc_have_custom_meta() ) : wpsc_the_custom_meta(); ?>

								<strong><?php echo wpsc_custom_meta_name(); ?>: </strong><?php echo wpsc_custom_meta_value(); ?><br />

							<?php endwhile; ?>

						</div>

						<?php
						/**
						 * Form data
						 */
						?>
						
						<form class='product_form' enctype="multipart/form-data" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="1" id="product_<?php echo wpsc_the_product_id(); ?>">

							<?php if ( wpsc_product_has_personal_text() ) : ?>

								<div class='custom_text'>
									<h4><?php _e( 'Personalize your product', 'wpsc' ); ?></h4>
									<?php _e( 'Complete this form to include a personalized message with your purchase.', 'wpsc' ); ?><br />
									<input type='text' name='custom_text' value='' />
								</div>

							<?php endif; ?>
						
							<?php if ( wpsc_product_has_supplied_file() ) : ?>

								<div class='custom_file'>
									<h4><?php _e( 'Upload a File', 'wpsc' ); ?></h4>
									<?php _e( 'Select a file from your computer to include with this purchase.', 'wpsc' ); ?><br />
									<input type='file' name='custom_file' value='' />
								</div>

							<?php endif; ?>
						
						
							<?php
							/**
							 * Variation Group HTML and loop
							 */
							?>

							<div class="wpsc_variation_forms">

								<?php while (wpsc_have_variation_groups() ) : wpsc_the_variation_group(); ?>

									<p>
										<label for="<?php echo wpsc_vargrp_form_id(); ?>"><?php echo wpsc_the_vargrp_name(); ?>:</label>

										<?php
										/**
										 * Specific variation HTML and loop
										 */
										?>

										<select class='wpsc_select_variation' name="variation[<?php echo wpsc_vargrp_id(); ?>]" id="<?php echo wpsc_vargrp_form_id(); ?>">

											<?php while ( wpsc_have_variations() ) : wpsc_the_variation(); ?>

												<option value="<?php echo wpsc_the_variation_id(); ?>" <?php echo wpsc_the_variation_out_of_stock(); ?>><?php echo wpsc_the_variation_name(); ?></option>

											<?php endwhile; ?>

										</select>
									</p>

								<?php endwhile; ?>

							</div><!-- end .wpsc_variation_forms -->

							<?php
							/**
							 * Quantity options - MUST be enabled in Admin Settings
							 */
							?>

							<?php if ( wpsc_has_multi_adding() ): ?>

								<label class='wpsc_quantity_update' for='wpsc_quantity_update'><?php _e('Quantity', 'wpsc'); ?>:</label>
								<input type="text" id='wpsc_quantity_update' name="wpsc_quantity_update" size="2" value="1"/>
								<input type="hidden" name="key" value="<?php echo wpsc_the_cart_item_key(); ?>"/>
								<input type="hidden" name="wpsc_update_quantity" value="true"/>

							<?php endif ;?>
					
							<div class="wpsc_product_price">

								<?php if ( wpsc_product_is_donation() ) : ?>

									<label for='donation_price_<?php echo wpsc_the_product_id(); ?>'><?php _e('Donation', 'wpsc'); ?>:</label>
									<input type='text' id='donation_price_<?php echo wpsc_the_product_id(); ?>' name='donation_price' value='<?php echo $wpsc_query->product['price']; ?>' size='6' />
									<br />
														
								<?php else : ?>

									<?php if ( wpsc_product_on_special() ) : ?>
								
										<span class='oldprice'><span id="old_product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_product_normal_price(); ?></span><?php _e('Price', 'wpsc'); ?>:</span><br />

									<?php endif; ?>

									<span id="product_price_<?php echo wpsc_the_product_id(); ?>" class="pricedisplay"><?php echo wpsc_the_product_price(); ?></span><?php _e('Price', 'wpsc'); ?>:  <br/>
								
									<?php
										if( wpsc_product_has_multicurrency() )
											echo wpsc_display_product_multicurrency();

										if ( get_option( 'display_pnp' ) == 1 ) : ?>

											<span class="pricedisplay"><?php echo wpsc_product_postage_and_packaging(); ?></span><?php _e('P&amp;P', 'wpsc'); ?>:  <br />

									<?php endif; ?>

								<?php endif; ?>

							</div><!-- end #wpsc_product_price -->

							<?php
								if ( function_exists( 'wpsc_akst_share_link' ) && ( get_option( 'wpsc_share_this' ) == 1 ) )
									echo wpsc_akst_share_link('return');
							?>

							<input type="hidden" value="add_to_cart" name="wpsc_ajax_action"/>
							<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="product_id"/>
							
							<?php if( wpsc_product_is_customisable() ) : ?>

								<input type="hidden" value="true" name="is_customisable"/>

							<?php endif; ?>
					
							<?php
							/**
							 * Cart Options
							 */
							?>

							<?php
								if ( ( get_option( 'hide_addtocart_button') == 0 ) && ( get_option( 'addtocart_or_buynow' ) != '1' ) ) :

									if ( wpsc_product_has_stock() ) :

										if ( wpsc_product_external_link( wpsc_the_product_id() ) != '' ) :

											$action =  wpsc_product_external_link(wpsc_the_product_id()); ?>

											<input class="wpsc_buy_button" type='button' value='<?php _e('Buy Now', 'wpsc'); ?>' onclick='gotoexternallink("<?php echo $action; ?>")'>

										<?php else: ?>

											<input type="submit" value="<?php _e('Add To Cart', 'wpsc'); ?>" name="Buy" class="wpsc_buy_button" id="product_<?php echo wpsc_the_product_id(); ?>_submit_button"/>

										<?php endif; ?>
							
										<div class='wpsc_loading_animation'>
											<img title="Loading" alt="Loading" src="<?php echo WPSC_CORE_IMAGES_URL ;?>/indicator.gif" class="loadingimage" />
											<?php _e( 'Updating cart...', 'wpsc' ); ?>
										</div>
							
									<?php else : ?>

										<p class='soldout'><?php _e('This product has sold out.', 'wpsc'); ?></p>

									<?php

									endif;

								endif;
							?>

						</form>
					
						<?php
							if ( (get_option( 'hide_addtocart_button' ) == 0 ) && ( get_option( 'addtocart_or_buynow' ) == '1' ) )
								echo wpsc_buy_now_button( wpsc_the_product_id() );
					
							echo wpsc_product_rater();
						
							if ( function_exists( 'gold_shpcrt_display_gallery' ) )
								echo gold_shpcrt_display_gallery( wpsc_the_product_id() );

							echo wpsc_also_bought( wpsc_the_product_id() );
						?>

					</div><!-- end .producttext -->
		
					<form onsubmit="submitform(this);return false;" action="<?php echo wpsc_this_page_url(); ?>" method="post" name="product_<?php echo wpsc_the_product_id(); ?>" id="product_extra_<?php echo wpsc_the_product_id(); ?>">
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="prodid"/>
						<input type="hidden" value="<?php echo wpsc_the_product_id(); ?>" name="item"/>
					</form>
				</div><!-- end .textcol -->
			</div><!-- end .single_product_display product_view_$ -->
		</div><!-- end .productdisplay -->
		
		<?php echo wpsc_product_comments(); ?>

<?php endwhile;


	if ( function_exists( 'fancy_notifications' ) )
		echo fancy_notifications();
?>	

</div><!-- end #products_page_container -->
