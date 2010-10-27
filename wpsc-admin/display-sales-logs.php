<?php
/**
 * WP eCommerce edit and view sales page functions
 *
 * These are the main WPSC sales page functions
 *
 * @package wp-e-commerce
 * @since 3.7
 */
global $purchlogs;
if(!isset($purchlogs)){
	$purchlogs = new wpsc_purchaselogs();	
}

 function wpsc_display_sales_logs() {
 	$subpage ='';
 	if(isset($_GET['subpage']))
 		$subpage = $_GET['subpage'];
		
		switch($subpage) {
			case 'upgrade-purchase-logs':
				wpsc_upgrade_purchase_logs();   
			break;
		
			default:
				wpsc_display_sales_log_index();
			break;
		}
}

 function wpsc_display_sales_log_index() {

	 global $purchlogitem;

  	?>
	<div class="wrap">
		<?php //screen_icon(); ?>
		<h2><?php echo esc_html( __('Sales', 'wpsc') ); ?> </h2>
		<?php //START OF PURCHASE LOG DEFAULT VIEW ?>
		<?php
		 if(isset($_GET['view_purchlogs_by']) || isset($_GET['view_purchlogs_by_status'])) {
			wpsc_change_purchlog_view($_GET['view_purchlogs_by'], $_GET['view_purchlogs_by_status']);
			}
			if(!isset($_REQUEST['purchaselog_id'])){
				$columns = array(
					'cb' => '<input type="checkbox" />',
					'purchid' => 'Order ID',
					'date' => 'Date / Time',
					'name' => '',
					'amount' => 'Amount',
					'details' => 'Details',
					'status' => 'Status',
					'delete' => 'Delete',
					'track' => 'Tracking ID'
				);
				register_column_headers('display-sales-list', $columns);
				///// start of update message section //////
				
				$fixpage = get_option('siteurl').'/wp-admin/admin.php?page=wpsc-sales-logs&amp;subpage=upgrade-purchase-logs';
			if (isset($_GET['skipped']) || isset($_GET['updated']) || isset($_GET['deleted']) ||  isset($_GET['locked']) ) { ?>
			<div id="message" class="updated fade"><p>
			<?php if ( isset($_GET['updated']) && (int) $_GET['updated'] ) {
				printf( _n( '%s Purchase Log updated.', '%s Purchase Logs updated.', $_GET['updated'] ), number_format_i18n( $_GET['updated'] ) );
				unset($_GET['updated']);
			}
			
			if ( isset($_GET['skipped']) && (int) $_GET['skipped'] )
				unset($_GET['skipped']);
			
			if ( isset($_GET['locked']) && (int) $_GET['locked'] ) {
				printf( _n( '%s product not updated, somebody is editing it.', '%s products not updated, somebody is editing them.', $_GET['locked'] ), number_format_i18n( $_GET['locked'] ) );
				unset($_GET['locked']);
			}
			
			if ( isset($_GET['deleted']) && (int) $_GET['deleted'] ) {
				printf( _n( '%s Purchase Log deleted.', '%s Purchase Logs deleted.', $_GET['deleted'] ), number_format_i18n( $_GET['deleted'] ) );
				unset($_GET['deleted']);
			}
			?>
			</p></div>
		<?php } 
		
			if(get_option('wpsc_purchaselogs_fixed')== false || (wpsc_check_uniquenames()) ){ ?>
				<div class='error' style='padding:8px;line-spacing:8px;'><span ><?php _e('When upgrading the Wp-E-Commerce Plugin from 3.6.* to 3.7 it is required that you associate your checkout form fields with the new Purchase Logs system. To do so please '); ?> <a href='<?php echo $fixpage; ?>'>Click Here</a></span></div>
	<?php	 } 
		///// end of update message section //////?>
		<div id='dashboard-widgets' style='min-width: 825px;'>
			<?php /* end of sidebar start of main column */ ?>
			<div id='post-body' class='has-sidebar metabox-holder' style='width:95%;'>
				<div id='dashboard-widgets-main-content-wpsc' class='has-sidebar-content'>
			
				<?php 
					if(function_exists('wpsc_right_now')) {
						echo wpsc_right_now();
					}
			   	
			   		?> 
			   	</div><br />
			   	<div id='wpsc_purchlog_searchbox'>
			   		<?php wpsc_purchaselogs_searchbox(); ?>
			   	</div><br />
			   		<?php	wpsc_purchaselogs_displaylist(); ?> 				
				
			</div>
		</div>
		<?php }else{ //NOT IN GENERIC PURCHASE LOG PAGE, IN DETAILS PAGE PER PURCHASE LOG 
						 
				if(isset($_REQUEST['purchaselog_id'])){
					$purchlogitem = new wpsc_purchaselogs_items((int)$_REQUEST['purchaselog_id']);
				}
			if (isset($_GET['cleared']) || isset($_GET['cleared'])) { ?>
			<div id="message" class="updated fade"><p>
			<?php 
				if ( isset($_GET['cleared']) && $_GET['cleared']==true ) {
					printf( _n( 'Downloads for this log have been released.', 'Downloads for this log have been released.', $_GET['cleared'] ), $_GET['cleared']);
					unset($_GET['cleared']);
				}
				if ( isset($_GET['sent']) && (int) $_GET['sent'] ) {
					printf( _n( 'Receipt has been resent ', 'Receipt has been resent ', $_GET['sent'] ),  $_GET['sent']  );
					unset($_GET['sent']);
				}
			?> </p></div>
			<?php
			}			
			?>

			
			<?php
		$page_back = remove_query_arg( array('locked', 'skipped', 'updated', 'deleted','purchaselog_id'), $_SERVER['REQUEST_URI'] );

		$columns = array(
	  	'title' => __('Name','wpsc'),
			'sku' => __('SKU','wpsc'),
			'quantity' => __('Quantity','wpsc'),
			'price' => __('Price','wpsc'),
			'shipping' => __('Shipping','wpsc'),
			'tax' => '',
// 			'discount' => 'Discount',
			'total' => __('Total','wpsc')
		);
      
      if(wpec_display_product_tax())
      {
         $columns['tax'] = __('Tax Included','wpsc');
      }// if
      
		register_column_headers('display-purchaselog-details', $columns); 
		?>
			<div id='post-body' class='has-sidebar' style='width:95%;'>
				<?php if(wpsc_has_purchlog_shipping()) { ?>
				<div id='wpsc_shipping_details_box'>	
					<h3><?php _e('Shipping Details','wpsc'); ?></h3>
					<p><strong><?php echo wpsc_display_purchlog_shipping_name(); ?></strong></p>
					<p>
					<?php echo wpsc_display_purchlog_shipping_address(); ?><br />
					<?php echo wpsc_display_purchlog_shipping_city(); ?><br />
					<?php echo wpsc_display_purchlog_shipping_state_and_postcode(); ?><br />
					<?php echo wpsc_display_purchlog_shipping_country(); ?><br />
					</p>
					<strong><?php _e('Shipping Options','wpsc'); ?></strong>
					<p>
					
					<?php _e('Shipping Method:','wpsc'); ?> <?php echo wpsc_display_purchlog_shipping_method(); ?><br />
					<?php _e('Shipping Option:','wpsc'); ?> <?php echo wpsc_display_purchlog_shipping_option(); ?><br />
					<?php if(wpsc_purchlogs_has_tracking()) : ?>
						<?php _e('Tracking ID:','wpsc'); ?> <?php echo wpsc_purchlogitem_trackid(); ?><br />
						<?php _e('Shipping Status:','wpsc'); ?> <?php echo wpsc_purchlogitem_trackstatus(); ?><br />
						<?php _e('Track History:','wpsc'); ?> <?php echo wpsc_purchlogitem_trackhistory(); ?>
					<?php endif; ?>
					</p>
				</div>
				<?php } ?>
				<div id='wpsc_billing_details_box'>
					<h3><?php _e('Billing Details','wpsc'); ?></h3>
					<p><strong><?php _e('Purchase Log Date:','wpsc'); ?> </strong><?php echo wpsc_purchaselog_details_date(); ?> </p>
					<p><strong><?php _e('Purchase Number:','wpsc'); ?> </strong><?php echo wpsc_purchaselog_details_purchnumber(); ?> </p>
					<p><strong><?php _e('Buyers Name:','wpsc'); ?> </strong><?php echo wpsc_display_purchlog_buyers_name(); ?></p>
					<p><strong><?php _e('Address:','wpsc'); ?> </strong><?php echo wpsc_display_purchlog_buyers_address(); ?></p>

					<p><strong><?php _e('Phone:','wpsc'); ?> </strong><?php echo wpsc_display_purchlog_buyers_phone(); ?></p>
					<p><strong><?php _e('Email:','wpsc'); ?> </strong><a href="mailto:<?php echo wpsc_display_purchlog_buyers_email(); ?>?subject=Message From '<?php echo get_option('siteurl'); ?>'"><?php echo wpsc_display_purchlog_buyers_email(); ?></a></p>
					<p><strong><?php _e('Payment Method:','wpsc'); ?> </strong><?php echo wpsc_display_purchlog_paymentmethod(); ?></p>
					<?php if(wpsc_display_purchlog_display_howtheyfoundus()) : ?>
					<p><strong><?php _e('How User Found Us:','wpsc'); ?> </strong><?php echo wpsc_display_purchlog_howtheyfoundus(); ?></p>
					<?php endif; ?>
				</div>
			
				<div id='wpsc_items_ordered'>
					<br />
					<h3><?php _e('Items Ordered','wpsc'); ?></h3>
					<table class="widefat" cellspacing="0">
						<thead>
							<tr>
						<?php print_column_headers('display-purchaselog-details'); ?>
							</tr>
						</thead>
					
						<tfoot>
							<tr>
						<?php // print_column_headers('display-purchaselog-details', false); ?>
							</tr>
						</tfoot>
					
						<tbody>
						<?php wpsc_display_purchlog_details(); ?>
						<tr> &nbsp;</tr>

						<tr class="wpsc_purchaselog_start_totals">
							<td colspan="5">
								<?php if ( wpsc_purchlog_has_discount_data() ) { ?>
								<?php _e('Coupon Code','wpsc'); ?>: <?php echo wpsc_display_purchlog_discount_data(); ?>
								<?php } ?>
							</td>
							<th><?php _e('Discount','wpsc'); ?> </th>
							<td><?php echo wpsc_display_purchlog_discount(); ?></td>
						</tr>
                  
						<?php if(!wpec_display_product_tax()) { ?>
                     <tr>
                        <td colspan='5'></td>
                        <th><?php _e('Taxes','wpsc'); ?> </th>
                        <td><?php echo wpec_display_purchlog_taxes(); ?></td>
                     </tr>
                  <?php } ?>
                  
						<tr>
							<td colspan='5'></td>
							<th><?php _e('Shipping','wpsc'); ?> </th>
							<td><?php echo wpsc_display_purchlog_shipping(); ?></td>
						</tr>
						<tr>
							<td colspan='5'></td>
							<th><?php _e('Total','wpsc'); ?> </th>
							<td><?php echo wpsc_display_purchlog_totalprice(); ?></td>
						</tr>
						</tbody>
				</table>
				<div id='wpsc_purchlog_order_status'>
					<form action='' method='post'>
					<p><label for='<?php echo $_GET['purchaselog_id']; ?>'><?php _e('Order Status:','wpsc'); ?></label><select class='selector' name='<?php echo $_GET['purchaselog_id']; ?>' title='<?php echo $_GET['purchaselog_id']; ?>' >
	 			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>
	 				<option value='<?php echo wpsc_the_purch_status_id(); ?>' <?php echo wpsc_purchlog_is_checked_status(); ?> ><?php echo wpsc_the_purch_status_name(); ?> </option>
	 			<?php endwhile; ?>
		 			</select></p>
		 			</form>
 			</div>
 				<?php wpsc_purchlogs_custom_fields(); ?>
 				
 				
 				<!-- Start Order Notes (by Ben) -->
 				<?php wpsc_purchlogs_notes(); ?>
				<!-- End Order Notes (by Ben) -->
				
				<?php wpsc_custom_checkout_fields(); ?>
 				
				</div>
				</div>
				
				<div id='wpsc_purchlogitems_links'>
				<h3><?php _e('Actions','wpsc'); ?></h3>
				<?php do_action( 'wpsc_purchlogitem_links_start' ); ?>
				<?php if(wpsc_purchlogs_have_downloads_locked() != false): ?>
<img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/lock_open.png' alt='clear lock icon' />&ensp;<a href='<?php echo $_SERVER['REQUEST_URI'].'&amp;wpsc_admin_action=clear_locks'; ?>'><?php echo wpsc_purchlogs_have_downloads_locked(); ?></a><br /><br class='small' />
				<?php endif; ?>
<img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/printer.png' alt='printer icon' />&ensp;<a href='<?php echo add_query_arg('wpsc_admin_action','wpsc_display_invoice'); ?>'><?php _e('View Packing Slip', 'wpsc'); ?></a>
		
<br /><br class='small' /><img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/email_go.png' alt='email icon' />&ensp;<a href='<?php echo add_query_arg('email_buyer_id',$_GET['purchaselog_id']); ?>'><?php _e('Resend Receipt to Buyer', 'wpsc'); ?></a>
		  
<br /><br class='small' /><a class='submitdelete' title='<?php echo esc_attr(__('Delete this log')); ?>' href='<?php echo wp_nonce_url("admin.php?wpsc_admin_action=delete_purchlog&amp;purchlog_id=".$_GET['purchaselog_id'], 'delete_purchlog_' .$_GET['purchaselog_id']); ?>' onclick="if ( confirm(' <?php echo esc_js(sprintf( __("You are about to delete this log '%s'\n 'Cancel' to stop, 'OK' to delete.",'wpsc'),  wpsc_purchaselog_details_date() )) ?>') ) { return true;}return false;"><img src='<?php echo WPSC_CORE_IMAGES_URL . "/cross.png"; ?>' alt='delete icon' />               &nbsp;<?php _e('Remove this record', 'wpsc') ?></a>

<br /><br class='small' />&emsp;&ensp; 	<a href='<?php echo $page_back ?>'><?php _e('Go Back', 'wpsc'); ?></a>
<br /><br />
			</div>
			</div>
			<br />
			<?php }	?>
	</div>
	<?php

 }
 
 function display_ecomm_admin_menu(){
 	?>	
 	<div class="meta-box-sortables ui-sortable" style="position: relative;"> 
 		<div class='postbox'> 
			<h3 class='hndle'><?php _e('e-Commerce Admin Menu', 'wpsc'); ?></h3>
			<div class='inside'>
				<a href='admin.php?page=wpsc-settings'><?php _e('Shop Settings', 'wpsc'); ?></a><br />
				<a href='admin.php?page=wpsc-settings&amp;tab=gateway'><?php _e('Checkout Settings', 'wpsc'); ?></a><br />
				<a href='admin.php?page=wpsc-settings&amp;tab=checkout'><?php _e('Checkout Settings', 'wpsc'); ?></a><br />
			</div>
		</div>		
	</div>
	<?php
 }
 function display_ecomm_rss_feed(){
 	require_once (ABSPATH . WPINC . '/rss.php');
		$rss = fetch_rss('http://www.instinct.co.nz/feed/');	
		if($rss != null) {
			$rss->items = array_slice((array)$rss->items, 0, 5);
			$current_hash = sha1(serialize($rss->items));
			if((string)get_option('wpsc_ecom_news_hash') !== (string)$current_hash ) {
				?>
				<div class='postbox'> 
					<h3 class='hndle'><?php _e('WP e-Commerce News', 'wpsc'); ?></h3>
					<div class='inside'>
					<ul class='ecom_dashboard'>
					<?php
					foreach($rss->items as $items) {
						echo "<li><a href='".$items['link']."'>".$items['title']."</a></li>";
					}
					?>
					</ul>
					<?php
					if (!IS_WP27)
					 echo "<a href='admin.php?page=<?php echo WPSC_DIR_NAME;?>/display-sales-log.php&#038;hide_news=true' id='close_news_box'>X</a>";
					?>
					</div>
				</div>
				<?php
			}
    }
    
    function wpsc_ordersummary(){
    ?>
    	<div class='postbox'> 
    	<h3 class='hndle'><?php _e('Order Summary', 'wpsc'); ?></h3>
    
   		 <div class='inside'> 
      <div class='order_summary_subsection'>
      <strong><?php _e('Last 30 Days', 'wpsc'); ?></strong>
      <p id='log_total_month'>
<?php 
      $year = date("Y");
      $month = date("m");
      $start_timestamp = mktime(0, 0, 0, $month, 1, $year);
      $end_timestamp = mktime(0, 0, 0, ($month+1), 0, $year);
      echo wpsc_currency_display( admin_display_total_price( $start_timestamp,$end_timestamp ) );
      echo " ".__('(accepted payments)', 'wpsc'); ?>
      </p>
      </div>
      <div class='order_summary_subsection'>
  	    <strong><?php _e('Life Time', 'wpsc'); ?></strong>
	      <p id='log_total_absolute'><?php echo wpsc_currency_display( admin_display_total_price() ); ?></p>
      </div> 
     
      <div class='order_summary_subsection'>
      <strong><?php _e('Subscribe to your orders', 'wpsc'); ?></strong>
      <p>
        <a class='product_log_rss' href='index.php?rss=true&amp;rss_key=key&amp;action=purchase_log'><img align='middle' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/rss-icon.jpg' alt='' title='' />&nbsp;<span><?php _e('Subscribe to an RSS feed', 'wpsc'); ?></span></a> <?php _e('of your orders', 'wpsc'); ?>      </p>
      </div>
         <div class='order_summary_subsection'>
      <strong><?php _e('Plugin News', 'wpsc'); ?></strong>
      <p>
      <?php _e('The <a href="http://instinct.co.nz/blogshop/products-page/" target="_blank">WP DropShop Module</a> is the latest and most cutting edge shopping cart available online. Coupled with Grid View then your site will be the talk of street! <br/><br/>The <a href="http://instinct.co.nz/blogshop/products-page/" target="_blank">GridView Module</a> is a visual module built to enhance the way your product page looks.<br/><br/><a href="http://www.instinct.co.nz/wp-campaign-monitor/100">WP Campaign Monitor</a> is an email newsletter tool built just for WP users who want to send campaigns, track the results and manage their subscribers. The latest version integrates with e-commerce lite meaning that you will be able to send buyers email newsletters and much more. ', 'wpsc'); ?>        
        <br /><br /><?php _e('This shop is powered by ', 'wpsc'); ?><a href='http://www.instinct.co.nz'>Instinct</a>
      </p>
      </div>
    </div>
    <?php
    if(get_option('activation_state') != "true") {
      ?>
      <div class='gold-cart_pesterer'> 
        <div>
        <img src='<?php echo WPSC_CORE_IMAGES_URL; ?>/gold-cart.png' alt='' title='' /><a href='http://www.instinct.co.nz/e-commerce/shop/'><?php _e('Upgrade to Gold', 'wpsc'); ?></a><?php _e(' and unleash more functionality into your shop.', 'wpsc'); ?>
        </div>
      </div>
      
      <?php
    }
    ?>
    </div>

	<?php
    }

 }
 function wpsc_purchaselogs_displaylist(){
 	global $purchlogs;
  ?>
  	<form method='post' action=''>
  	  <div class='wpsc_purchaselogs_options'>
  		<select id='purchlog_multiple_status_change' name='purchlog_multiple_status_change' class='purchlog_multiple_status_change'>
  			<option selected='selected' value='-1'><?php _e('Bulk Actions'); ?></option>
  			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>
 				<option value='<?php echo wpsc_the_purch_status_id(); ?>' >
 					<?php echo wpsc_the_purch_status_name(); ?> 
 				</option>
 			<?php endwhile; ?>
			<option value="delete"><?php _e('Delete'); ?></option>
  		</select>
  		<input type='hidden' value='purchlog_bulk_modify' name='wpsc_admin_action2' />
  		<input type="submit" value="<?php _e('Apply'); ?>" name="doaction" id="doaction" class="button-secondary action" />
  		<?php /* View functions for purchlogs */?>
  		<label for='view_purchlogs_by'><?php _e('View:'); ?></label>

  		<select id='view_purchlogs_by' name='view_purchlogs_by'>
  		  <?php
				$date_is_selected['3mnths'] = '';
				$date_is_selected['all'] = '';
  		  switch($_GET['view_purchlogs_by']) {
					case '3mnths':
						$date_is_selected['3mnths'] = 'selected="selected"';
					break;
					
					case '':
					case 'all':
						$date_is_selected['all'] = 'selected="selected"';
					break;
  		  }

  		  ?>
  			<option value='all' <?php echo $date_is_selected['all']; ?>>All</option>
				<option value='3mnths' <?php echo $date_is_selected['3mnths']; ?>>Three Months</option>
  			<?php  echo wpsc_purchlogs_getfirstdates(); ?>
  		</select>
  		<select id='view_purchlogs_by_status' name='view_purchlogs_by_status'>
  			
  			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>

  			<?php

	  			    $current_status = wpsc_the_purch_status_id();
					$is_selected = '';
					if(isset($_GET['view_purchlogs_by_status']) && $_GET['view_purchlogs_by_status'] == $current_status) {
						$is_selected = 'selected="selected"';
					}
  			?>
 				<option value='<?php echo $current_status; ?>' <?php echo $is_selected; ?> >
 					<?php echo wpsc_the_purch_status_name(); ?> 
 				</option>
 			<?php endwhile; ?>
 			<?php if(empty($is_selected)){ 
					$is_selected = 'selected="selected"';
			}else{
					$is_selected = '';
			 } ?>
			<option <?php echo $is_selected; ?> value='-1'>Status: All</option>
  		</select>
  		<input type='hidden' value='purchlog_filter_by' name='wpsc_admin_action' />
  		<input type="submit" value="<?php _e('Filter'); ?>" name="doaction2" id="doaction2" class="button-secondary action" />
  	</div>
  		<?php if( isset( $_POST['purchlogs_searchbox'] ) && wpsc_have_purch_items() == false ):  ?>
  	<div class="updated settings-error"><p><?php _e('There are no purchase logs for your selection, please try again.'); ?></p></div>
  		<?php endif;?>
	 	<table class="widefat page fixed" cellspacing="0">
			<thead>
				<tr>
			<?php print_column_headers('display-sales-list'); ?>
				</tr>
			</thead>
			<tfoot>
				<tr>
			<?php print_column_headers('display-sales-list', false); ?>
				</tr>
			</tfoot>
			<tbody>
			<?php get_purchaselogs_content(); ?>
			</tbody>
		</table>
		<p><strong style='float:left' ><?php _e('Total:'); ?></strong> <?php echo wpsc_currency_display( wpsc_the_purch_total() ); ?></p>
<?php
			if(!isset($purchlogs->current_start_timestamp) && !isset($purchlogs->current_end_timestamp)){
				$purchlogs->current_start_timestamp = $purchlogs->earliest_timestamp;
				$purchlogs->current_end_timestamp = $purchlogs->current_timestamp;
			}
			$arr_params = array('wpsc_admin_action' => 'wpsc_downloadcsv',
								'rss_key'			=> 'key',
								 'start_timestamp'	=> $purchlogs->current_start_timestamp,
								 'end_timestamp'	=> $purchlogs->current_end_timestamp);
		?>	
		<br />	
		<p><a class='admin_download' href='<?php echo htmlentities(add_query_arg($arr_params)) ; ?>' ><img class='wpsc_pushdown_img' src='<?php echo WPSC_CORE_IMAGES_URL; ?>/download.gif' alt='' title='' /> <span> <?php _e('Download CSV', 'wpsc'); ?></span></a></p>
	</form>
	<br />
	<script type="text/javascript">
	/* <![CDATA[ */
	(function($){
		$(document).ready(function(){
			$('#doaction, #doaction2').click(function(){
				if ( $('select[name^="purchlog_multiple_status_change"]').val() == 'delete' ) {
					var m = '<?php echo esc_js(__("You are about to delete the selected purchase logs.\n  'Cancel' to stop, 'OK' to delete.")); ?>';
					return showNotice.warn(m);
				}
			});
		});
	})(jQuery);
	//columns.init('edit');
	/* ]]> */
	</script>

<?php
 unset($_SESSION['newlogs']);
 }
 function get_purchaselogs_content(){
 	global $purchlogs,$wpsc_purchase_log_statuses;
 	while(wpsc_have_purch_items()) : wpsc_the_purch_item();	
 	//exit('<pre>'.print_r($_SESSION, true).'</pre>');
 	?>
 	<tr>
 		<th class="check-column" scope="row"><input type='checkbox' name='purchlogids[]' class='editcheckbox' value='<?php echo wpsc_the_purch_item_id(); ?>' /></th>
 		<td><?php echo wpsc_the_purch_item_id(); ?></td><!-- purchase ID -->
 		<td><?php echo wpsc_the_purch_item_date(); ?></td> <!--Date -->
 		<td><?php echo wpsc_the_purch_item_name(); ?></td> <!--Name/email -->
 		<td><?php echo wpsc_currency_display( wpsc_the_purch_item_price() ); ?></td><!-- Amount -->
 		<td><a href='<?php echo htmlentities(add_query_arg('purchaselog_id', wpsc_the_purch_item_id())) ; ?>'><?php echo wpsc_the_purch_item_details();?> Items</a></td><!-- Details -->
 		<td>
 		<?php if(!wpsc_purchlogs_is_google_checkout()){ ?>
 			<select class='selector' name='<?php echo wpsc_the_purch_item_id(); ?>' title='<?php echo wpsc_the_purch_item_id(); ?>' >
 			<?php while(wpsc_have_purch_items_statuses()) : wpsc_the_purch_status(); ?>
 				<option value='<?php echo wpsc_the_purch_status_id(); ?>' <?php echo wpsc_is_checked_status(); ?> ><?php echo wpsc_the_purch_status_name(); ?> </option>
 			<?php endwhile; ?>
 			</select>
 		<?php }else { ?>
 			<a href='http://checkout.google.com/' rel=''><img class='google_checkout_logo' src='<?php echo WPSC_CORE_IMAGES_URL . "/checkout_logo.jpg"; ?>' alt='google checkout' /></a>
 		<?php } ?>
 		</td><!-- Status -->
 		<td><a class='submitdelete' title='<?php echo esc_attr(__('Delete this log')); ?>' href='<?php echo wp_nonce_url("admin.php?wpsc_admin_action=delete_purchlog&amp;purchlog_id=".wpsc_the_purch_item_id(), 'delete_purchlog_' . wpsc_the_purch_item_id()); ?>' onclick="if ( confirm(' <?php echo esc_js(sprintf( __("You are about to delete this log '%s'\n 'Cancel' to stop, 'OK' to delete."),  wpsc_the_purch_item_date() )) ?>') ) { return true;}return false;"><img class='wpsc_pushdown_img' src='<?php echo WPSC_CORE_IMAGES_URL . "/cross.png"; ?>' alt='delete icon' /></a></td><!-- Delete -->
 		<td>
 			<a class='wpsc_show_trackingid' title='<?php echo wpsc_the_purch_item_id(); ?>' href=''><?php echo wpsc_trackingid_value(); ?></a>
 		</td>
 	</tr>
 	<tr class='log<?php echo wpsc_the_purch_item_id(); ?> wpsc_trackingid_row'>
 		<td class='wpsc_trackingid_row' colspan='2'>

 			<label for='wpsc_trackingid<?php echo wpsc_the_purch_item_id(); ?>'><?php _e('Tracking ID','wpsc');?> :</label>
 		</td>
 		<td class='wpsc_trackingid_row' colspan='2'>
 			<input type='text' name='wpsc_trackingid<?php echo wpsc_the_purch_item_id(); ?>' value='<?php echo wpsc_trackingid_value(); ?>' size='20' />
 			<input type='submit' name='submit' class='button' value='Add Tracking ID' />
 		</td>
 		<td colspan='4'>
 			<a href='' title='<?php echo wpsc_the_purch_item_id(); ?>' class='sendTrackingEmail'>Send Custom Message</a>
 		</td>
 	</tr>
 	
 	<?php
 	endwhile;
 }
 function wpsc_purchaselogs_searchbox(){ 
 	?>
 	<form  action='' method='post'>
 		<input type='hidden' name='wpsc_admin_action' value='purchlogs_search' />
 		<input type='text' value='<?php if(isset($_POST['purchlogs_searchbox'])) echo $_POST['purchlogs_searchbox']; ?>' name='purchlogs_searchbox' id='purchlogs_searchbox' />
 		<input type="submit" value="<?php _e('Search Logs'); ?>"  class="button-secondary action" />
  	</form>
 	<?php
 }
 
 function wpsc_display_purchlog_details(){
 	while( wpsc_have_purchaselog_details() ) : wpsc_the_purchaselog_item(); ?>
 	<tr>
	 	<td><?php echo wpsc_purchaselog_details_name(); ?></td> <!-- NAME! -->
	 	<td><?php echo wpsc_purchaselog_details_SKU(); ?></td> <!-- SKU! -->
	 	<td><?php echo wpsc_purchaselog_details_quantity(); ?></td> <!-- QUANTITY! -->
	 	<td><?php echo wpsc_currency_display( wpsc_purchaselog_details_price() ); ?></td> <!-- PRICE! -->
	 	<td><?php echo wpsc_currency_display( wpsc_purchaselog_details_shipping() ); ?></td> <!-- SHIPPING! -->
	 	<td><?php if(wpec_display_product_tax()) { echo wpsc_purchaselog_details_tax(); } ?></td> <!-- TAX! -->
	 	<!-- <td><?php echo wpsc_currency_display( wpsc_purchaselog_details_discount() ); ?></td> --> <!-- DISCOUNT! -->
	 	<td><?php echo wpsc_currency_display( wpsc_purchaselog_details_total() ); ?></td> <!-- TOTAL! -->
 	</tr>
<?php
	endwhile;
}
 
function wpsc_purchlogs_custom_fields(){
	if(wpsc_purchlogs_has_customfields()){?>
	<div class='metabox-holder'>
		<div id='purchlogs_customfields' class='postbox'>
		<h3 class='hndle'>Users Custom Fields</h3>
		<div class='inside'>
		<?php $messages = wpsc_purchlogs_custommessages(); ?>
		<?php $files = wpsc_purchlogs_customfiles(); ?>
		<?php if(count($files) > 0){ ?>
		<h4>Cart Items with Custom Files:</h4>	
		<?php
			foreach($files as $file){ 
				echo $file;
			}
		}?>
		<?php if(count($messages) > 0){ ?>
		<h4>Cart Items with Custom Messages:</h4>	
		<?php
			foreach($messages as $message){ 
				echo $message;
			}
		} ?>
		</div>
		</div>
		</div>
<?php }

}


/* Start Order Notes (by Ben) */
function wpsc_purchlogs_notes() {

	if ( true ) { // Need to check if notes column exists in DB and plugin version? ?>
	<div class="metabox-holder">
		<div id="purchlogs_notes" class="postbox">
		<h3 class='hndle'>Order Notes</h3>
		<div class='inside'>
			<form method="post" action="">
				<input type='hidden' name='wpsc_admin_action' value='purchlogs_update_notes' />
				<input type="hidden" name="wpsc_purchlogs_update_notes_nonce" id="wpsc_purchlogs_update_notes_nonce" value="<?php echo wp_create_nonce( 'wpsc_purchlogs_update_notes' ); ?>" />
				<input type='hidden' name='purchlog_id' value='<?php echo $_GET['purchaselog_id']; ?>' />
				<p><textarea name="purchlog_notes" rows="3" wrap="virtual" id="purchlog_notes" style="width:100%;"><?php if ( isset($_POST['purchlog_notes']) ) { echo stripslashes($_POST['purchlog_notes']); } else { echo wpsc_display_purchlog_notes(); } ?></textarea></p>
				<p><input class="button" type="submit" name="button" id="button" value="Update Notes" /></p>
			</form>
		</div>
		</div>
	</div>
	<?php }

}
/* End Order Notes (by Ben) */
function wpsc_custom_checkout_fields(){
	global $purchlogitem;
	if(!empty($purchlogitem->customcheckoutfields)){
	?>
		<div class="metabox-holder">
			<div id="custom_checkout_fields" class="postbox">
				<h3 class='hndle'>Additional Checkout Fields</h3>
				<div class='inside'>
				<?php
				foreach((array)$purchlogitem->customcheckoutfields as $key=>$value){
					$value['value'] = maybe_unserialize($value['value']);	
					if(is_array($value['value'])){
						?>
						<p><strong><?php echo $key; ?> :</strong> <?php echo implode($value['value'], ','); ?></p>
						<?php
						
												
					}else{
						?>
						<p><strong><?php echo $key; ?> :</strong> <?php echo $value['value']; ?></p>
						<?php
					}
				}
				?>
				</div>
			</div>
		</div>
		<?php
	}
	//exit('<pre>'.print_r($purchlogitem, true).'</pre>');

}

/**
 * @description: determines whether or not to display the product tax or not
 * @return: boolean
**/
function wpec_display_product_tax()
{
   global $purchlogitem;      
   return ($purchlogitem->extrainfo->wpec_taxes_total == 0.00) ? true : false;
}// wpec_display_product_tax

function wpsc_upgrade_purchase_logs() {
	include(WPSC_FILE_PATH.'/wpsc-admin/includes/purchlogs_upgrade.php');
}
?>
