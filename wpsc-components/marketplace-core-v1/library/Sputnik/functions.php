<?php
if ( function_exists('register_sidebar') )
    register_sidebar(array(
        'before_widget' => '<div id="%1$s" class="widget %2$s">',
        'after_widget' => '</div>',
        'before_title' => '<div class="title">',
        'after_title' => '</div>',
    ));

register_sidebars( 1,
	array(
	'name' => 'add-shop-name',
	'before_widget' => '<div id="%1$s">',
	'after_widget' => '</div>',
	'before_title' => '<h6 class="test">',
	'after_title' => '</h6>'
	)
);


register_sidebars( 1,
array(
'name' => 'add-shop-new',
'before_widget' => '<div id="%1$s">',
'after_widget' => '</div>',
'before_title' => '<h6 class="test">',
'after_title' => '</h6>'
)
);


register_sidebars( 1,
	array(
	'name' => 'shiping',
	'before_widget' => '<div id="%1$s">',
	'after_widget' => '</div>',
	'before_title' => '<h6 class="test">',
	'after_title' => '</h6>'
	)
);

register_sidebars( 1,
	array(
		'name' => 'Checkout Page Banner',
		'before_widget' => '<div id="%1$s">',
		'after_widget' => '</div>',
		'before_title' => '<h6 class="test">',
		'after_title' => '</h6>'
	)
);

register_sidebars( 1,
	array(
		'name' => 'Below Coupon Code',
		'before_widget' => '<div id="%1$s">',
		'after_widget' => '</div>',
		'before_title' => '<h6 class="test">',
		'after_title' => '</h6>'
	)
);
?>

<?php

/**
*	Setup Theme post custom fields
**/
include (TEMPLATEPATH . "/lib/theme-post-custom-fields.php");


//Get custom function
include (TEMPLATEPATH . "/lib/custom.lib.php");


//Get custom shortcode
include (TEMPLATEPATH . "/lib/shortcode.lib.php");


/**
*	Setup Menu
**/
include (TEMPLATEPATH . "/lib/menu.lib.php");


/*
	Begin creating admin optinos
*/

$themename = "Kin";
$shortname = "kin";

$categories = get_categories('hide_empty=0&orderby=name');
$wp_cats = array(
	0		=> "Choose a category"
);
foreach ($categories as $category_list ) {
       $wp_cats[$category_list->cat_ID] = $category_list->cat_name;
}

$pages = get_pages(array('parent' => 0));
$wp_pages = array(
	0		=> "Choose a page"
);
foreach ($pages as $page_list ) {
       $wp_pages[$page_list->ID] = $page_list->post_title;
}

$kin_handle = opendir(TEMPLATEPATH.'/css/skins');
$kin_skin_arr = array();

while (false!==($kin_file = readdir($kin_handle))) {
	if ($kin_file != "." && $kin_file != ".." && $kin_file != ".DS_Store") {
		$kin_file_name = basename($kin_file, '.css');
		$kin_name = str_replace('_', ' ', $kin_file_name);

		$kin_skin_arr[$kin_file_name] = $kin_name;
	}
}
closedir($kin_handle);
asort($kin_skin_arr);


$options = array (

//Begin admin header
array(
		"name" => $themename." Options",
		"type" => "title"
),
//End admin header


//Begin first tab "General"
array(
		"name" => "General",
		"type" => "section"
)
,

array( "type" => "open"),

array( "name" => "Skins",
	"desc" => "Select the skin for the theme",
	"id" => $shortname."_skin",
	"type" => "select",
	"options" => $kin_skin_arr,
	"std" => "white"
),

array( "name" => "Your Logo (Image URL)",
	"desc" => "Enter the URL of image that you want to use as the logo",
	"id" => $shortname."_logo",
	"type" => "text",
	"std" => "",
),
array( "name" => "Google Analytics Domain ID ",
	"desc" => "Get analytics on your site. Simply give us your Google Analytics Domain ID (something like UA-123456-1)",
	"id" => $shortname."_ga_id",
	"type" => "text",
	"std" => ""

),
array( "name" => "Custom Favicon",
	"desc" => "A favicon is a 16x16 pixel icon that represents your site; paste the URL to a .ico image that you want to use as the image",
	"id" => $shortname."_favicon",
	"type" => "text",
	"std" => "",
),

array( "type" => "close"),
//End first tab "General"

//Begin fourth tab "Homepage"
array( "name" => "Homepage",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Homepage category",
	"desc" => "Choose a category from which content show on Homepage",
	"id" => $shortname."_home_cat",
	"type" => "select",
	"options" => $wp_cats,
	"std" => "Choose a category"
),
array( "type" => "close"),
//End fourth tab "Homepage"


//Begin second tab "Gallery"
array( "name" => "Gallery",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Gallery sort by",
	"desc" => "Select sorting type for contents in Gallery",
	"id" => $shortname."_gallery_sort",
	"type" => "select",
	"options" => array(
		'DESC' => 'Newest First',
		'ASC' => 'Oldest First',
	),
	"std" => "ASC"
),
array( "name" => "Gallery scroll speed",
	"desc" => "Enter speed number of Gallery scrolling (Larger number for faster speed)",
	"id" => $shortname."_gallery_slider_speed",
	"type" => "text",
	"size" => "20px",
	"std" => "5",
),
array( "name" => "Auto scroll",
	"desc" => "Select if you want to enable or disable auto scroll feature",
	"id" => $shortname."_gallery_auto_scroll",
	"type" => "select",
	"options" => array(
		1 => 'Enable',
		0 => 'Disable',
	),
	"std" => 1
),
array( "name" => "Gallery image width (in pixels)",
	"desc" => "Enter number of width for Gallery image",
	"id" => $shortname."_gallery_width",
	"type" => "text",
	"size" => "40px",
	"std" => "424",
),
array( "name" => "Gallery image height (in pixels)",
	"desc" => "Enter number of height for Gallery image",
	"id" => $shortname."_gallery_height",
	"type" => "text",
	"size" => "40px",
	"std" => "640",
),

array( "type" => "close"),
//End second tab "Gallery"


//Begin second tab "Blog"
array( "name" => "Blog",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Choose page for blog",
	"desc" => "Choose a page from which your blog posts to display",
	"id" => $shortname."_blog_page",
	"type" => "select",
	"options" => $wp_pages,
	"std" => "Choose a page"),

array( "name" => "Blog category",
	"desc" => "Choose a category from which content show as Blog posts",
	"id" => $shortname."_blog_cat",
	"type" => "select",
	"options" => $wp_cats,
	"std" => "Choose a category"
),
array( "type" => "close"),
//End second tab "Blog"


//Begin fourth tab "Contact"
array( "name" => "Contact",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Choose page for contact form",
	"desc" => "Choose a page from which your contact form to display",
	"id" => $shortname."_contact_page",
	"type" => "select",
	"options" => $wp_pages,
	"std" => "Choose a page"),
array( "name" => "Your email address",
	"desc" => "Enter which email address will be sent from contact form",
	"id" => $shortname."_contact_email",
	"type" => "text",
	"std" => ""

),
//End fourth tab "Contact"

//Begin fifth tab "Footer"
array( "type" => "close"),
array( "name" => "Footer",
	"type" => "section"),
array( "type" => "open"),

array( "name" => "Footer text",
	"desc" => "Enter footer text ex. copyright description",
	"id" => $shortname."_footer_text",
	"type" => "textarea",
	"std" => ""

),
//End fifth tab "Footer"


array( "type" => "close")

);



function kin_add_admin() {

global $themename, $shortname, $options;

if ( isset($_GET['page']) && $_GET['page'] == basename(__FILE__) ) {

	if ( isset($_REQUEST['action']) && 'save' == $_REQUEST['action'] ) {

		foreach ($options as $value)
		{
			update_option( $value['id'], $_REQUEST[ $value['id'] ] );
		}

foreach ($options as $value) {
	if( isset( $_REQUEST[ $value['id'] ] ) ) {
		if($value['id'] != $shortname."_sidebar0")
		{
			update_option( $value['id'], $_REQUEST[ $value['id'] ]  );
		}
		elseif(isset($_REQUEST[ $value['id'] ]) && !empty($_REQUEST[ $value['id'] ]))
		{
			//get last sidebar serialize array
			$current_sidebar = get_option($shortname."_sidebar");
			$current_sidebar[ $_REQUEST[ $value['id'] ] ] = $_REQUEST[ $value['id'] ];

			update_option( $shortname."_sidebar", $current_sidebar );
		}
	}
	else
	{
		delete_option( $value['id'] );
	}
}


	header("Location: admin.php?page=functions.php&saved=true");

}
else if( isset($_REQUEST['action']) && 'reset' == $_REQUEST['action'] ) {

	foreach ($options as $value) {
		delete_option( $value['id'] ); }

	header("Location: admin.php?page=functions.php&reset=true");

}
}

add_menu_page($themename, $themename, 'administrator', basename(__FILE__), 'kin_admin');
}

function kin_add_init() {

$file_dir=get_bloginfo('template_directory');
wp_enqueue_style("functions", $file_dir."/functions/functions.css", false, "1.0", "all");
wp_enqueue_script("rm_script", $file_dir."/functions/rm_script.js", false, "1.0");

}
function kin_admin() {

global $themename, $shortname, $options;
$i=0;

if ( isset($_REQUEST['saved']) &&  $_REQUEST['saved'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings saved.</strong></p></div>';
if ( isset($_REQUEST['reset']) &&  $_REQUEST['reset'] ) echo '<div id="message" class="updated fade"><p><strong>'.$themename.' settings reset.</strong></p></div>';

?>



	<div class="wrap rm_wrap">
	<h2><?php echo $themename; ?> Settings</h2>

	<div class="rm_opts">
	<form method="post"><?php foreach ($options as $value) {
switch ( $value['type'] ) {

case "open":
?> <?php break;

case "close":
?>

	</div>
	</div>
	<br />


	<?php break;

case "title":
?>
	<br />



<?php break;

case 'text':

	//if sidebar input then not show default value
	if($value['id'] != $shortname."_sidebar0")
	{
		$default_val = get_settings( $value['id'] );
	}
	else
	{
		$default_val = '';
	}
?>

	<div class="rm_input rm_text"><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
	<input name="<?php echo $value['id']; ?>"
		id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>"
		value="<?php if ($default_val != "") { echo stripslashes(get_settings( $value['id'])  ); } else { echo $value['std']; } ?>"
		<?php if(!empty($value['size'])) { echo 'style="width:'.$value['size'].'"'; } ?> />
		<small><?php echo $value['desc']; ?></small>
	<div class="clearfix"></div>

	<?php
	if($value['id'] == $shortname."_sidebar0")
	{
		$current_sidebar = get_option($shortname."_sidebar");

		if(!empty($current_sidebar))
		{
	?>
		<ul id="current_sidebar" class="rm_list">

	<?php
		foreach($current_sidebar as $sidebar)
		{
	?>

			<li id="<?=$sidebar?>"><?=$sidebar?> ( <a href="/wp-admin/admin.php?page=functions.php" class="sidebar_del" rel="<?=$sidebar?>">Delete</a> )</li>

	<?php
		}
	?>

		</ul>

	<?php
		}
	}
	?>

	</div>
	<?php
break;

case 'password':
?>

	<div class="rm_input rm_text"><label for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
	<input name="<?php echo $value['id']; ?>"
		id="<?php echo $value['id']; ?>" type="<?php echo $value['type']; ?>"
		value="<?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id'])  ); } else { echo $value['std']; } ?>"
		<?php if(!empty($value['size'])) { echo 'style="width:'.$value['size'].'"'; } ?> />
	<small><?php echo $value['desc']; ?></small>
	<div class="clearfix"></div>

	</div>
	<?php
break;

case 'textarea':
?>

	<div class="rm_input rm_textarea"><label
		for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>
	<textarea name="<?php echo $value['id']; ?>"
		type="<?php echo $value['type']; ?>" cols="" rows=""><?php if ( get_settings( $value['id'] ) != "") { echo stripslashes(get_settings( $value['id']) ); } else { echo $value['std']; } ?></textarea>
	<small><?php echo $value['desc']; ?></small>
	<div class="clearfix"></div>

	</div>

	<?php
break;

case 'select':
?>

	<div class="rm_input rm_select"><label
		for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>

	<select name="<?php echo $value['id']; ?>"
		id="<?php echo $value['id']; ?>">
		<?php foreach ($value['options'] as $key => $option) { ?>
		<option
		<?php if (get_settings( $value['id'] ) == $key) { echo 'selected="selected"'; } ?>
			value="<?php echo $key; ?>"><?php echo $option; ?></option>
		<?php } ?>
	</select> <small><?php echo $value['desc']; ?></small>
	<div class="clearfix"></div>
	</div>
	<?php
break;

case "checkbox":
?>

	<div class="rm_input rm_checkbox"><label
		for="<?php echo $value['id']; ?>"><?php echo $value['name']; ?></label>

	<?php if(get_option($value['id'])){ $checked = "checked=\"checked\""; }else{ $checked = "";} ?>
	<input type="checkbox" name="<?php echo $value['id']; ?>"
		id="<?php echo $value['id']; ?>" value="true" <?php echo $checked; ?> />


	<small><?php echo $value['desc']; ?></small>
	<div class="clearfix"></div>
	</div>
	<?php break;
case "section":

$i++;

?>

	<div class="rm_section">
	<div class="rm_title">
	<h3><img src="<?php bloginfo('template_directory')?>/functions/images/trans.png" class="inactive" alt=""><?php echo $value['name']; ?></h3>
	<span class="submit"><input name="save<?php echo $i; ?>" type="submit"
		value="Save changes" /> </span>
	<div class="clearfix"></div>
	</div>
	<div class="rm_options"><?php break;

}
}
?> <input type="hidden" name="action" value="save" />
	</form>
	<form method="post"><!-- p class="submit">
<input name="reset" type="submit" value="Reset" />
<input type="hidden" name="action" value="reset" />
</p --></form>
	</div>


	<?php
}

add_action('admin_init', 'kin_add_init');
add_action('admin_menu', 'kin_add_admin');

/*
	End creating admin options
*/

function zao_customizer() {
	add_theme_support( 'widget-customizer' );
	add_theme_support( 'html5', array( 'search-form' ) );
}

add_action( 'after_setup_theme', 'zao_customizer' );

//Make widget support shortcode
add_filter('widget_text', 'do_shortcode');

function cart_has_kit( $purchase_id ) {

	$log = new WPSC_Purchase_Log( $purchase_id );
	$cart = $log->get_cart_contents();
	$names = wp_list_pluck( $cart, 'name' );

	return ( bool ) array_filter( $names, 'fingerprint_check' );
}

function fingerprint_check( $product_name ) {
	return stristr( $product_name, 'print' );
}

add_action( 'wpsc_before_submit_checkout', 'paypal_post_var_fix' );

function paypal_post_var_fix() {

	if ( 'wpsc_merchant_paypal_pro' != $_POST['custom_gateway'] ) {

		if ( isset( $_POST['card_number'] ) )
			unset( $_POST['card_number'] );

		if ( isset( $_POST['expiry'] ) )
			unset( $_POST['expiry'] );

		if ( isset( $_POST['card_code'] ) )
			unset( $_POST['card_code'] );

		if ( isset( $_POST['cctype'] ) )
			unset( $_POST['cctype'] );
	}
}

function add_bulk_export_button() {
	submit_button( 'Bulk Export Selected Orders', 'primary', 'bulk_export_orders_to_pdf', false );
}

add_action( 'wpsc_sales_log_extra_tablenav'      , 'add_bulk_export_button' );
add_action( 'wpsc-product_page_wpsc-edit-coupons', 'show_bulk_importer' );

function show_bulk_importer() {
	global $wpdb;

	$currency_data = $wpdb->get_row( "SELECT `symbol`,`symbol_html`,`code` FROM `" . WPSC_TABLE_CURRENCY_LIST . "` WHERE `id`='" . esc_attr( get_option( 'currency_type' ) ) . "' LIMIT 1", ARRAY_A );
	$currency_sign = ! empty( $currency_data['symbol'] ) ? $currency_data['symbol_html'] : $currency_data['code'];
	?>
	<div class="wrap">
		<h2>Bulk Import Coupons</h2>
		<p>Sometimes, stores need create a <strong><em>TON</em></strong> of coupons, and fast.  Sometimes, it's because of a deal on Living Social, Groupon or another daily deals site.  Sometimes, they've generated custom codes on their busienss cards.  Either way, here's how you can do that.</p>
		<p>First, enter a list of coupons in the area below.  Just make sure each code is on its own line.  Then, set the conditions for the coupon.  Last step?  Profit!</p>
		<div id='bulk_add_coupon_box' class='' >
			<form name='bulk_add_coupon' method='post' action=''>
				<table class='add-coupon'>
					<tr>
						<strong>Coupon Codes</strong><br />
						<textarea name="bulk_codes" style="width:50%"></textarea>
					</tr>
					<tr>
						<th><?php esc_html_e( 'Discount', 'wpsc' ); ?></th>
						<th><?php esc_html_e( 'Start', 'wpsc' ); ?></th>
						<th><?php esc_html_e( 'Expiry', 'wpsc' ); ?></th>
					</tr>
					<tr>
						<td>
							<input type='text' value='' size='3' name='add_discount' />
							<select name='add_discount_type'>
								<option value='0' ><?php echo esc_html( $currency_sign ) ?></option>
								<option value='1' ><?php _ex( '%', 'Percentage sign as discount type in coupons page', 'wpsc' ); ?></option>
								<option value='2' ><?php esc_html_e( 'Free shipping', 'wpsc' ); ?></option>
							</select>
						</td>
						<td>
							<input type='text' class='pickdate' size='11' value="<?php echo date('Y-m-d'); ?>" name='add_start' />
						</td>
						<td>
							<input type='text' class='pickdate' size='11' name='add_end' value="<?php echo (date('Y')+1) . date('-m-d') ; ?>">
						</td>
						<td>
							<input type='submit' value='<?php esc_attr_e( 'Bulk Add Coupons', 'wpsc' ); ?>' name='submit_coupon' class='button-primary' />
						</td>
					</tr>

					<tr>
						<td colspan='3' scope="row">
							<p>
								<span class='input_label'><?php esc_html_e( 'Active', 'wpsc' ); ?></span><input type='hidden' value='0' name='add_active' />
								<input type='checkbox' value='1' checked='checked' name='add_active' />
								<span class='description'><?php esc_html_e( 'Activate coupon on creation.', 'wpsc' ) ?></span>
							</p>
						</td>
					</tr>

					<tr>
						<td colspan='3' scope="row">
							<p>
								<span class='input_label'><?php esc_html_e( 'Use Once', 'wpsc' ); ?></span><input type='hidden' value='0' name='add_use-once' />
								<input type='checkbox' value='1' name='add_use-once' />
								<span class='description'><?php esc_html_e( 'Deactivate coupon after it has been used.', 'wpsc' ) ?></span>
							</p>
						</td>
					</tr>

					<tr>
						<td colspan='3' scope="row">
							<p>
								<span class='input_label'><?php esc_html_e( 'Apply On All Products', 'wpsc' ); ?></span><input type='hidden' value='0' name='add_every_product' />
								<input type="checkbox" value="1" name='add_every_product'/>
								<span class='description'><?php esc_html_e( 'This coupon affects each product at checkout.', 'wpsc' ) ?></span>
							</p>
						</td>
					</tr>

					<tr><td colspan='3'><span id='table_header'><?php esc_html_e( 'Conditions', 'wpsc' ); ?></span></td></tr>
					<tr>
						<td colspan="8">
						<div class='coupon_condition' >
							<div class='first_condition'>
								<select class="ruleprops" name="rules[property][]">
									<option value="item_name" rel="order"><?php esc_html_e( 'Item name', 'wpsc' ); ?></option>
									<option value="item_quantity" rel="order"><?php esc_html_e( 'Item quantity', 'wpsc' ); ?></option>
									<option value="total_quantity" rel="order"><?php esc_html_e( 'Total quantity', 'wpsc' ); ?></option>
									<option value="subtotal_amount" rel="order"><?php esc_html_e( 'Subtotal amount', 'wpsc' ); ?></option>
									<?php echo apply_filters( 'wpsc_coupon_rule_property_options', '' ); ?>
								</select>

								<select name="rules[logic][]">
									<option value="equal"><?php esc_html_e( 'Is equal to', 'wpsc' ); ?></option>
									<option value="greater"><?php esc_html_e( 'Is greater than', 'wpsc' ); ?></option>
									<option value="less"><?php esc_html_e( 'Is less than', 'wpsc' ); ?></option>
									<option value="contains"><?php esc_html_e( 'Contains', 'wpsc' ); ?></option>
									<option value="not_contain"><?php esc_html_e( 'Does not contain', 'wpsc' ); ?></option>
									<option value="begins"><?php esc_html_e( 'Begins with', 'wpsc' ); ?></option>
									<option value="ends"><?php esc_html_e( 'Ends with', 'wpsc' ); ?></option>
									<option value="category"><?php esc_html_e( 'In Category', 'wpsc' ); ?></option>
								</select>

								<span><input type="text" name="rules[value][]"/></span>
								<script>
									var bulk_coupon_number=1;
									function bulk_add_another_property(this_button){
										var new_property='<div class="coupon_condition">\n'+
											'<div> \n'+
											'<select class="ruleprops" name="rules[property][]"> \n'+
											'<option value="item_name" rel="order"><?php echo esc_js( __( 'Item name', 'wpsc' ) ); ?></option> \n'+
											'<option value="item_quantity" rel="order"><?php echo esc_js( __( 'Item quantity', 'wpsc' ) ); ?></option>\n'+
											'<option value="total_quantity" rel="order"><?php echo esc_js( __( 'Total quantity', 'wpsc' ) ); ?></option>\n'+
											'<option value="subtotal_amount" rel="order"><?php echo esc_js( __( 'Subtotal amount', 'wpsc' ) ); ?></option>\n'+
											'<?php echo apply_filters( 'wpsc_coupon_rule_property_options', '' ); ?>'+
											'</select> \n'+
											'<select name="rules[logic][]"> \n'+
											'<option value="equal"><?php echo esc_js( __( 'Is equal to', 'wpsc' ) ); ?></option> \n'+
											'<option value="greater"><?php echo esc_js( __( 'Is greater than', 'wpsc' ) ); ?></option> \n'+
											'<option value="less"><?php echo esc_js( __( 'Is less than', 'wpsc' ) ); ?></option> \n'+
											'<option value="contains"><?php echo esc_js( __( 'Contains', 'wpsc' ) ); ?></option> \n'+
											'<option value="not_contain"><?php echo esc_js( __( 'Does not contain', 'wpsc' ) ); ?></option> \n'+
											'<option value="begins"><?php echo esc_js( __( 'Begins with', 'wpsc' ) ); ?></option> \n'+
											'<option value="ends"><?php echo esc_js( __( 'Ends with', 'wpsc' ) ); ?></option> \n'+
											'</select> \n'+
											'<span> \n'+
											'<input type="text" name="rules[value][]"/> \n'+
											'</span>  \n'+
											'<img height="16" width="16" class="delete" alt="<?php esc_attr_e( 'Delete', 'wpsc' ); ?>" src="<?php echo WPSC_CORE_IMAGES_URL; ?>/cross.png" onclick="jQuery(this).parent().remove();"/></div> \n'+
											'</div> ';

										jQuery('#bulk_add_coupon_box .coupon_condition:last').after(new_property);
										bulk_coupon_number++;
									}
								</script>
							</div>
						</div>
					</tr>

					<tr>
						<td>
							<a class="wpsc_coupons_condition_add" onclick="bulk_add_another_property(jQuery(this));">
								<?php esc_html_e( 'Add New Condition', 'wpsc' ); ?>
							</a>
						</td>
					</tr>
				</table>
			</form>
		</div>
	</div>
	<?php
}

function process_bulk_coupons() {
	global $wpdb;

	if ( ! isset( $_POST['bulk_codes'] ) || empty( $_POST['bulk_codes'] ) )
		return;

	$coupon_codes = trim( $_POST['bulk_codes'] );

	if ( empty( $_POST['bulk_codes'] ) )
		return;

	$coupon_codes  = $_POST['bulk_codes'];
	$discount      = (double)$_POST['add_discount'];
	$discount_type = (int)$_POST['add_discount_type'];
	$use_once      = (int)(bool)$_POST['add_use-once'];
	$every_product = (int)(bool)$_POST['add_every_product'];
	$is_active     = (int)(bool)$_POST['add_active'];
	$start_date    = date( 'Y-m-d', strtotime( $_POST['add_start'] ) ) . " 00:00:00";
	$end_date      = date( 'Y-m-d', strtotime( $_POST['add_end'] ) ) . " 00:00:00";
	$rules         = $_POST['rules'];

	foreach ( $rules as $key => $rule ) {
		foreach ( $rule as $k => $r ) {
			$new_rule[$k][$key] = $r;
		}
	}

	foreach ( $new_rule as $key => $rule ) {
		if ( '' == $rule['value'] ) {
			unset( $new_rule[$key] );
		}
	}

	$codes = explode( PHP_EOL, $coupon_codes );

	foreach ( $codes as $coupon_code ) {
		$coupon_code = trim( $coupon_code );

		if ( empty( $coupon_code ) )
			continue;

			$insert = $wpdb->insert(
				    WPSC_TABLE_COUPON_CODES,
				    array(
						'coupon_code' => $coupon_code,
						'value' => $discount,
						'is-percentage' => $discount_type,
						'use-once' => $use_once,
						'is-used' => 0,
						'active' => $is_active,
						'every_product' => $every_product,
						'start' => $start_date,
						'expiry' => $end_date,
						'condition' => serialize( $new_rule )
				    ),
				    array(
						'%s',
						'%f',
						'%d',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s',
						'%s'
				    )
				);
	}
}

add_action( 'load-wpsc-product_page_wpsc-edit-coupons', 'process_bulk_coupons' );


/**
 * Hooks into WPEC bulk actions to print PDFs of transaction reports.
 *
 * @return type
 */
function wpsc_bulk_pdf_exporter( $action ) {

	if ( ! isset( $_REQUEST['bulk_export_orders_to_pdf'] ) || ! isset( $_REQUEST['post'] ) || ( isset( $_REQUEST['action'] ) && 'delete' === $_REQUEST['action'] ) )
		return;

	//Loops through checked IDs with wpsc_generate_pdf_sales_log().  Fingers crossed that it actually does something.
	$post_ids = array_map( 'absint', $_REQUEST['post'] );

	if ( ! $post_ids )
		return;

	if ( count( $post_ids ) < 2 )
		wpsc_generate_pdf_sales_log( $post_ids[0] );

	$file_paths = array();
	foreach ( $post_ids as $id ) {
		$file_paths[] = wpsc_generate_pdf_sales_log( $id, 'F' );
	}

	$zip = wpsc_create_zip( $file_paths, '/home/shirmich/public_html/wp-content/purchase-logs.zip', true );

	if ( $zip ) {
		header( 'Location: http://www.jookandnona.com/wp-content/purchase-logs.zip' );
		exit;
	}
}

add_action( 'wpsc_sales_log_process_bulk_action', 'wpsc_bulk_pdf_exporter' );

/**
 * If the user attempts to download multiple files, we need to create a ZIP of them
 *
 * @param type $files
 * @param type $destination
 * @param type $overwrite
 * @return type
 */
function wpsc_create_zip( $files = array(), $destination = '', $overwrite = false ) {
  //if the zip file already exists and overwrite is false, return false
  if(file_exists($destination) && !$overwrite) { return false; }
  //vars
  $valid_files = array();
  //if files were passed in...
  if(is_array($files)) {
    //cycle through each file
    foreach($files as $file) {
      //make sure the file exists
      if(file_exists($file)) {
        $valid_files[$file] = basename( $file );
      }
    }
  }
  //if we have good files...
  if(count($valid_files)) {
    //create the archive
    $zip = new ZipArchive();
    if($zip->open($destination,$overwrite ? ZIPARCHIVE::OVERWRITE : ZIPARCHIVE::CREATE) !== true) {
      return false;
    }
    //add the files
    foreach($valid_files as $path => $file) {
      $zip->addFile($path,$file);
    }
    //debug
    //echo 'The zip archive contains ',$zip->numFiles,' files with a status of ',$zip->status;

    //close the zip -- done!
    $zip->close();

    //check to make sure the file exists
    return file_exists( $destination ) ? $destination : false;
  }
  else
  {
    return false;
  }
}

function zao_search_products( $query ) {
	if ( $query->is_search() ) {
		$query->set( 'post_type', 'wpsc-product' );
	}
}

add_action( 'pre_get_posts', 'zao_search_products' );

function zao_show_custom_message_in_cart() {
		global $wpsc_cart;

		$item = $wpsc_cart->cart_items[ wpsc_the_cart_item_key() ];

		if ( ! empty( $item->custom_message ) ) {
			echo '<em class="wpsc_upsells_cart_note">Engraving: ( ' . esc_html( $item->custom_message ) . ' )</em>';
		}

}

add_action( 'wpsc_after_checkout_cart_item_name', 'zao_show_custom_message_in_cart', 15 );
