<?php
function wpec_options_taxes()
{
   $wpec_taxes_controller = new wpec_taxes_controller;
   $wpec_taxes_options = $wpec_taxes_controller->wpec_taxes->wpec_taxes_get_options();

   //include standard shopping cart notifications
   wpsc_settings_page_update_notification();
?>
   <form name='wpec_taxes_options' id='wpec_taxes_options' method='post' action=''>
   <div class="wrap">
      <h2><?php echo __('Tax Settings'); ?></h2>
      <p>
         <label for='wpec_taxes_enabled'>
            <input <?php if($wpec_taxes_options['wpec_taxes_enabled']){echo 'checked="checked"';} ?> type="checkbox" id='wpec_taxes_enabled' name='wpsc_options[wpec_taxes_enabled]' />
            <?php echo __('Turn tax on'); ?>
         </label>
      </p>
      <p>
         <label for='wpec_taxes_inprice1'>
            <input <?php if($wpec_taxes_options['wpec_taxes_inprice']=='exclusive'){echo 'checked="checked"';} ?> type="radio" value='exclusive' id='wpec_taxes_inprice1' name='wpsc_options[wpec_taxes_inprice]' />
            <?php echo __('Product prices are tax exclusive - add tax to the price during checkout'); ?>
         </label>
      </p>
      <p>
         <label for='wpec_taxes_inprice2'>
            <input <?php if($wpec_taxes_options['wpec_taxes_inprice']=='inclusive'){echo 'checked="checked"';} ?> type="radio" value='inclusive' id='wpec_taxes_inprice2' name='wpsc_options[wpec_taxes_inprice]' />
            <?php echo __("Product prices are tax inclusive - during checkout the total price doesn't increase but tax is shown as a line item"); ?>
         </label>
      </p>
      <h4><?php echo __('Product Specific Tax'); ?></h4>
      <p>
         <label for='wpec_taxes_product_1'>
            <input <?php if($wpec_taxes_options['wpec_taxes_product']=='add'){echo 'checked="checked"';} ?> type="radio" value='add' id='wpec_taxes_product_1' name='wpsc_options[wpec_taxes_product]' />
            <?php echo __('Add per product tax to tax percentage if product has a specific tax rate'); ?>
         </label>
      </p>
      <p>
         <label for='wpec_taxes_product_2'>
            <input <?php if($wpec_taxes_options['wpec_taxes_product']=='replace'){echo 'checked="checked"';} ?> type="radio" value='replace' id='wpec_taxes_product_2' name='wpsc_options[wpec_taxes_product]' />
            <?php echo __('Replace tax percentage with product specific tax rate'); ?>
         </label>
      </p>

      <h4><?php echo __('Tax Logic'); ?></h4>
      <p>
         <label for='wpec_taxes_logic_1'>
            <input <?php if($wpec_taxes_options['wpec_taxes_logic']=='billing_shipping'){echo 'checked="checked"';} ?> type="radio" value='billing_shipping' id='wpec_taxes_logic_1' name='wpsc_options[wpec_taxes_logic]' />
            <?php echo __('Apply tax when Billing and Shipping Country is the same as Shops base location'); ?>
         </label>
         <div id='billing_shipping_preference_container' style='margin-left: 20px;'>
            <p>
               <label for='wpec_billing_preference'>
                  <input <?php if($wpec_taxes_options['wpec_taxes_logic']=='billing_shipping'&&$wpec_taxes_options['wpec_billing_shipping_preference']=='billing_address'){echo 'checked="checked"';} ?> type="radio" value='billing_address' id='wpec_billing_preference' name='wpsc_options[wpec_billing_shipping_preference]' />
                  <?php echo __('Apply tax to Billing Address'); ?>
               </label>
            </p>
            <p>
               <label for='wpec_shipping_preference'>
                  <input <?php if($wpec_taxes_options['wpec_taxes_logic']=='billing_shipping'&&$wpec_taxes_options['wpec_billing_shipping_preference']=='shipping_address'){echo 'checked="checked"';} ?> type="radio" value='shipping_address' id='wpec_shipping_preference' name='wpsc_options[wpec_billing_shipping_preference]' />
                  <?php echo __('Apply tax to Shipping Address'); ?>
               </label>
            </p>
         </div>
      </p>
      <p>
         <label for='wpec_taxes_logic_2'>
            <input <?php if($wpec_taxes_options['wpec_taxes_logic']=='billing'){echo 'checked="checked"';} ?> type="radio" value='billing' id='wpec_taxes_logic_2' name='wpsc_options[wpec_taxes_logic]' />
         <?php echo __('Apply tax when Billing Country is the same as Shops base location'); ?>
         </label>
      </p>
      <p>
         <label for='wpec_taxes_logic_3'>
            <input <?php if($wpec_taxes_options['wpec_taxes_logic']=='shipping'){echo 'checked="checked"';} ?> type="radio" value='shipping' id='wpec_taxes_logic_3' name='wpsc_options[wpec_taxes_logic]' />
         <?php echo __('Apply tax when Shipping Country is the same as Shops base location'); ?>
         </label>
      </p>
      <div id='metabox-holder' class="metabox-holder">
         <div id='wpec-taxes-rates-container' class='postbox'>
            <h3 class='hndle' style='cursor: default'><?php echo __('Tax Rates'); ?></h3>
            <div id='wpec-taxes-rates' class='inside'>
               <!--Start Taxes Output-->
               <?php
                     /**
                      * Add New Tax Rate - should add another paragraph with the
                        another key specified for the input array
                      * Delete - Should remove the given paragraph from the page
                        and either ajax delete it from the DB or mark it for
                        deletion and process it after the changes are made.
                      * Selecting a Country - should automatically populate the
                        regions select box. Selecting a different country should
                        remove the region select box. If the user selects a
                        different country with regions it shouldn't matter because
                        the code should automatically add the region select in.
                      *  - Allow users to define tax for entire country even if regions exist.
                      * Shipping Tax - needs to be per region or per tax rate.
                        Remove the setting from the main Tax Settings area.
                      * Constraints -
                        1. Should not allow a user to add more than one
                           tax rate for the same area.
                        2. If a country tax rate is specified and then a region tax
                           rate, the region tax rate takes precedence.
                     **/
                     
                  //if tax is included warn about shipping
                  if($wpec_taxes_controller->wpec_taxes_isincluded())
                  {
                     echo '<p>'.__('Note: Tax is not applied to shipping when product prices are tax inclusive.').'</p>';
                  }// if
                  
                  //get current tax rates
                  $tax_rates = $wpec_taxes_controller->wpec_taxes->wpec_taxes_get_rates();
                  $tax_rate_count = 0;
                  if(!empty($tax_rates))
                  {
                     foreach($tax_rates as $tax_rate)
                     {
                        echo $wpec_taxes_controller->wpec_taxes_build_form($tax_rate_count, $tax_rate);
                        $tax_rate_count++;
                     }// foreach
                  }// if
               ?>
               <!--End Taxes Output-->
               <p>
                  <a id="add_taxes_rate" href="#"><?php echo __('Add New Tax Rate'); ?></a>
               </p>
            </div>
         </div>
         <div id='wpec-taxes-bands-container' class='postbox'>
            <h3 class='hndle' style='cursor: default'><?php echo __('Tax Bands'); ?></h3>
            <div id='wpec-taxes-bands' class='inside'>
			
               <?php
					echo '<p>'.__('Note: Tax Bands are special tax rules you can create and apply on a per-product basis.').'</p>';

                  //echo message regarding inclusive tax
                  if(!$wpec_taxes_controller->wpec_taxes_isincluded())
                  {
                     echo '<p>'.__('Note: Tax Bands do not take affect when product prices are tax exclusive.').'</p>';
                  }// if
                  
                  $tax_bands = $wpec_taxes_controller->wpec_taxes->wpec_taxes_get_bands();
                  $tax_band_count = 0;
                  if(!empty($tax_bands))
                  {
                     foreach($tax_bands as $tax_band)
                     {
                        echo $wpec_taxes_controller->wpec_taxes_build_form($tax_band_count, $tax_band, 'bands');
                        $tax_band_count++;
                     }// foreach
                  }// if
               ?>
               <p>
                  <a id="add_taxes_band" href="#"><?php echo __('Add New Tax Band'); ?></a>
               </p>
            </div>
         </div><!--wpec-taxes-bands-container-->
      </div><!--metabox-holder-->
      <div class="submit">
         <input type='hidden' name='wpec_admin_action' value='submit_taxes_options' />
         <?php wp_nonce_field('update-options', 'wpsc-update-options'); ?>
         <input type="submit" class='button-primary' value="Save Changes" name="submit_taxes" />
      </div>
   </div>
   </form>
<?php
}// wpec_options_taxes
?>
