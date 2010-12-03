// This is the wp-e-commerce front end javascript "library"
// empty the cart using ajax when the form is submitted,
function check_make_purchase_button(){
	toggle = jQuery('#noca_gateway').attr('checked');
	if(toggle == true){
		//jQuery('.make_purchase').hide();
		jQuery('#OCPsubmit').show();
	}else{
		jQuery('.make_purchase').show();
		jQuery('#OCPsubmit').hide();
	}
}
// this function is for binding actions to events and rebinding them after they are replaced by AJAX
// these functions are bound to events on elements when the page is fully loaded.
jQuery(document).ready(function () {
	if(jQuery('#checkout_page_container .wpsc_email_address input').val())
		jQuery('#wpsc_checkout_gravatar').attr('src', 'https://secure.gravatar.com/avatar/'+MD5(jQuery('#checkout_page_container .wpsc_email_address input').val().split(' ').join(''))+'?s=60&d=mm');
	jQuery('#checkout_page_container .wpsc_email_address input').keyup(function(){
		jQuery('#wpsc_checkout_gravatar').attr('src', 'https://secure.gravatar.com/avatar/'+MD5(jQuery(this).val().split(' ').join(''))+'?s=60&d=mm');
	});

	//this bit of code runs on the checkout page. If the checkbox is selected it copies the valus in the billing country and puts it in the shipping country form fields. 23.07.09
	//jQuery('.wpsc_shipping_forms').hide();
	jQuery("#shippingSameBilling").click(function(){

		// If checked
		jQuery("#shippingSameBilling").livequery(function(){

			if(jQuery(this).is(":checked")){
				var fname = jQuery("input[title='billingfirstname']").val();
				var lname = jQuery("input[title='billinglastname']").val();
				var addr = jQuery("textarea[title='billingaddress']").val();
				var city = jQuery("input[title='billingcity']").val();
				var pcode = jQuery("input[title='billingpostcode']").val();
				var phone = jQuery("input[title='billingphone']").val();
				var email = jQuery("input[title='billingfirstname']").val();
				var state = jQuery("select[title='billingregion'] :selected").text();
				if( jQuery("select[title='billingstate']").val() ){
					var state = jQuery("select[title='billingstate'] :selected").text();
				}
				if( jQuery("input[title='billingstate']").val()){
					var state = jQuery("input[title='billingstate']").val();
				}
				var country = jQuery("select[title='billingcountry'] :selected").text();
				var countryID = jQuery("select[title='billingcountry'] :selected").val();

				jQuery("input[title='shippingfirstname']").val(fname).removeClass('intra-field-label');
				jQuery("input[title='shippinglastname']").val(lname).removeClass('intra-field-label');
				jQuery("textarea[title='shippingaddress']").val(addr).removeClass('intra-field-label');
				jQuery("input[title='shippingcity']").val(city).removeClass('intra-field-label');
				jQuery("input[title='shippingpostcode']").val(pcode).removeClass('intra-field-label');
				jQuery("input[title='shippingphone']").val(phone).removeClass('intra-field-label');
				jQuery("input[title='shippingemail']").val(email).removeClass('intra-field-label');
				jQuery("input[title='shippingstate']").val(state).removeClass('intra-field-label');
				jQuery("input.shipping_country").val(countryID).removeClass('intra-field-label');
				jQuery("span.shipping_country_name").html(country).removeClass('intra-field-label');
				jQuery("select#current_country").val(countryID).removeClass('intra-field-label');

				jQuery("select[title='shippingcountry']").val(countryID).removeClass('intra-field-label');
				var html_form_id = jQuery("select[title='shippingcountry']").attr('id');
				var form_id =  jQuery("select[title='shippingcountry']").attr('name');
				form_id = form_id.replace("collected_data[", "");
				form_id = form_id.replace("]", "");
				form_id = form_id.replace("[0]", "");
				set_shipping_country(html_form_id, form_id)
				if(jQuery("select[title='billingcountry'] :selected").val() != jQuery("select[name='country'] :selected").val()){
					id = jQuery("select[name='country'] :selected").val();
					if(id == 'undefined'){
						jQuery("select[name='country']").val(countryID);
						submit_change_country();
					}
				}

			} else {

			}

			//otherwise, hide it
			//jQuery("#extra").hide("fast");
		});
	});

	// Submit the product form using AJAX
	jQuery("form.product_form").submit(function() {
		// we cannot submit a file through AJAX, so this needs to return true to submit the form normally if a file formfield is present
		file_upload_elements = jQuery.makeArray(jQuery('input[type=file]', jQuery(this)));
		if(file_upload_elements.length > 0) {
			return true;
		} else {
			form_values = jQuery(this).serialize();
			// Sometimes jQuery returns an object instead of null, using length tells us how many elements are in the object, which is more reliable than comparing the object to null
			if(jQuery('#fancy_notification').length == 0) {
				jQuery('div.wpsc_loading_animation',this).css('visibility', 'visible');
			}
			jQuery.post( 'index.php?ajax=true', form_values, function(returned_data) {
				eval(returned_data);
				jQuery('div.wpsc_loading_animation').css('visibility', 'hidden');

				if(jQuery('#fancy_notification') != null) {
					jQuery('#loading_animation').css("display", 'none');
				//jQuery('#fancy_notificationimage').css("display", 'none');
				}

			});
			wpsc_fancy_notification(this);
			return false;
		}
	});


	jQuery('a.wpsc_category_link, a.wpsc_category_image_link').click(function(){
		product_list_count = jQuery.makeArray(jQuery('ul.category-product-list'));
		if(product_list_count.length > 0) {
			jQuery('ul.category-product-list', jQuery(this).parent()).toggle();
			return false;
		}
	});

	//  this is for storing data with the product image, like the product ID, for things like dropshop and the the ike.
	jQuery("form.product_form").livequery(function(){
		product_id = jQuery('input[name=product_id]',this).val();
		image_element_id = 'product_image_'+product_id;
		jQuery("#"+image_element_id).data("product_id", product_id);
		parent_container = jQuery(this).parents('div.product_view_'+product_id);
		jQuery("div.item_no_image", parent_container).data("product_id", product_id);
	});
	//jQuery("form.product_form").trigger('load');

	// Toggle the additional description content
	jQuery("a.additional_description_link").click(function() {
		parent_element = jQuery(this).parent(".additional_description_container, .additional_description_span");
		jQuery('.additional_description',parent_element).slideToggle('fast');
		return false;
	});

	// update the price when the variations are altered.
	jQuery(".wpsc_select_variation").change(function() {
		jQuery('option[value="0"]', this).attr('disabled', 'disabled');
		parent_form = jQuery(this).parents("form.product_form");
		form_values =jQuery("input[name=product_id], .wpsc_select_variation",parent_form).serialize( );

		jQuery.post( 'index.php?update_product_price=true', form_values, function(returned_data) {
			variation_msg = '';
			eval(returned_data);
			if( product_id != null ) {
				if( variation_msg != '' ){
					if(variation_status){
						jQuery("div#stock_display_"+product_id).removeClass('out_of_stock');	
						jQuery("div#stock_display_"+product_id).addClass('in_stock');	
					}else{
						jQuery("div#stock_display_"+product_id).removeClass('in_stock');	
						jQuery("div#stock_display_"+product_id).addClass('out_of_stock');	
					}
					
					jQuery("div#stock_display_"+product_id).html(variation_msg);
				
				}
				target_id = "product_price_"+product_id;
				second_target_id = "donation_price_"+product_id;
				third_target_id = "old_product_price_"+product_id;
				yousave_target_id = "yousave_"+product_id;
				buynow_id = "BB_BuyButtonForm"+product_id;
				if(jQuery("input#"+target_id).attr('type') == 'text') {
					jQuery("input#"+target_id).val(numeric_price);
				} else {
					jQuery("#"+target_id+".pricedisplay").html(price);
					jQuery("#"+third_target_id).html(old_price);
					jQuery("#"+yousave_target_id).html(you_save);
				}
				jQuery("input#"+second_target_id).val(numeric_price);
			}
		});
		return false;
	});

	// Object frame destroying code.
	jQuery("div.shopping_cart_container").livequery(function(){
		object_html = jQuery(this).html();
		window.parent.jQuery("div.shopping-cart-wrapper").html(object_html);
	});


	// Ajax cart loading code.
	jQuery("div.wpsc_cart_loading").livequery(function(){
		form_values = "ajax=true"
		jQuery.post( 'index.php?wpsc_ajax_action=get_cart', form_values, function(returned_data) {
			eval(returned_data);
		});
	});

	// Object frame destroying code.
	jQuery("form.wpsc_product_rating").livequery(function(){
		jQuery(this).rating();
	});

	jQuery("form.wpsc_empty_the_cart").livequery(function(){
		jQuery(this).submit(function() {
			form_values = "ajax=true&";
			form_values += jQuery(this).serialize();
			jQuery.post( 'index.php', form_values, function(returned_data) {
				eval(returned_data);
			});
			return false;
		});
	});

	jQuery("form.wpsc_empty_the_cart a.emptycart").live('click',function(){
		parent_form = jQuery(this).parents("form.wpsc_empty_the_cart");
		form_values = "ajax=true&";
		form_values += jQuery(parent_form).serialize();
		jQuery.post( 'index.php', form_values, function(returned_data) {
			eval(returned_data);
		});
		return false;
	});

	//Shipping bug fix by James Collins
	var radios = jQuery(".productcart input:radio[name=shipping_method]");
	if (radios.length == 1) {
		// If there is only 1 shipping quote available during checkout, automatically select it
		jQuery(radios).click();
	} else if (radios.length > 1) {
		// There are multiple shipping quotes, simulate a click on the checked one
		jQuery(".productcart input:radio[name=shipping_method]:checked").click();
	}
});

// update the totals when shipping methods are changed.
function switchmethod(key,key1){
	// 	total=document.getElementById("shopping_cart_total_price").value;
	form_values = "ajax=true&";
	form_values += "wpsc_ajax_action=update_shipping_price&";
	form_values += "key1="+key1+"&";
	form_values += "key="+key;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
}

// submit the country forms.
function submit_change_country(){
	document.forms.change_country.submit();
}

// submit the fancy notifications forms.
function wpsc_fancy_notification(parent_form){
	if(typeof(WPSC_SHOW_FANCY_NOTIFICATION) == 'undefined'){
		WPSC_SHOW_FANCY_NOTIFICATION = true;
	}
	if((WPSC_SHOW_FANCY_NOTIFICATION == true) && (jQuery('#fancy_notification') != null)){
		var options = {
			margin: 1 ,
			border: 1 ,
			padding: 1 ,
			scroll: 1
		};

		form_button_id = jQuery(parent_form).attr('id') + "_submit_button";
		var container_offset = {};
		new_container_offset = jQuery('#default_products_page_container, #products_page_container, #list_view_products_page_container, #grid_view_products_page_container, #single_product_page_container').offset();

		if(container_offset['left'] == null) {
			container_offset['left'] = new_container_offset.left;
			container_offset['top'] = new_container_offset.top;
		}

		var button_offset = {};
		new_button_offset = jQuery('#'+form_button_id).offset()

		button_offset['left'] = new_button_offset.left;
		button_offset['top'] = new_button_offset.top;

		jQuery('#fancy_notification').css("left", (button_offset['left'] - container_offset['left'] - 140) + 'px');
		jQuery('#fancy_notification').css("top", ((button_offset['top']  - container_offset['top']) + 40) + 'px');


		jQuery('#fancy_notification').css("display", 'block');
		jQuery('#loading_animation').css("display", 'block');
		jQuery('#fancy_notification_content').css("display", 'none');
	}
}

function shopping_cart_collapser() {
	switch(jQuery("#sliding_cart").css("display")) {
		case 'none':
			jQuery("#sliding_cart").slideToggle("fast",function(){
				jQuery.post( 'index.php', "ajax=true&set_slider=true&state=1", function(returned_data) { });
				jQuery("#fancy_collapser").attr("src", (WPSC_CORE_IMAGES_URL + "/minus.png"));
			});
			break;

		default:
			jQuery("#sliding_cart").slideToggle("fast",function(){
				jQuery.post( 'index.php', "ajax=true&set_slider=true&state=0", function(returned_data) { });
				jQuery("#fancy_collapser").attr("src", (WPSC_CORE_IMAGES_URL + "/plus.png"));
			});
			break;
	}
	return false;
}

function set_billing_country(html_form_id, form_id){
	var billing_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();
	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		billing_region = "&billing_region="+region;
	}

	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&billing_country="+country+billing_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
}
function set_shipping_country(html_form_id, form_id){
	var shipping_region = '';
	country = jQuery(("div#"+html_form_id+" select[class=current_country]")).val();

	if(country == 'undefined'){
		country =  jQuery("select[title='billingcountry']").val();
	}

	region = jQuery(("div#"+html_form_id+" select[class=current_region]")).val();
	if(/[\d]{1,}/.test(region)) {
		shipping_region = "&shipping_region="+region;
	}

	form_values = "wpsc_ajax_action=change_tax&form_id="+form_id+"&shipping_country="+country+shipping_region;
	jQuery.post( 'index.php', form_values, function(returned_data) {
		eval(returned_data);
	});
//ajax.post("index.php",changetaxntotal,("ajax=true&form_id="+form_id+"&billing_country="+country+billing_region));
}

jQuery(document).ready(function(){
	jQuery('.wpsc_checkout_table input, .wpsc_checkout_table textarea').each(function(){
		var real_value = jQuery(this).val();
		value = jQuery('label[for="'+jQuery(this).attr('id')+'"]').html();
		if(null != value){
			value = value.replace(/<span class="?asterix"?>\*<\/span>/i,'');
		}
		jQuery(this).inlineFieldLabel({label:jQuery.trim(value)});
		if(real_value != '')
			jQuery(this).val(real_value).removeClass('intra-field-label');
	});
});

//Javascript for variations: bounce the variation box when nothing is selected and return false for add to cart button.
jQuery(document).ready(function(){
	jQuery('.productcol, .textcol').each(function(){
		jQuery('.wpsc_buy_button', this).click(function(){
			jQuery(this).parents('form:first').find('select.wpsc_select_variation').each(function(){
				if(jQuery(this).val() <= 0){
					jQuery(this).css('position','relative');
					jQuery(this).animate({'left': '-=5px'}, 50, function(){
						jQuery(this).animate({'left': '+=10px'}, 100, function(){
							jQuery(this).animate({'left': '-=10px'}, 100, function(){
								jQuery(this).animate({'left': '+=10px'}, 100, function(){
									jQuery(this).animate({'left': '-=5px'}, 50);
								});
							});
						});
					});
				}
			});
			if(jQuery(this).parents('form:first').find('select.wpsc_select_variation[value=0]:first').length)
				return false;
		});
	});
});

jQuery(document).ready(function(){
	jQuery('.attachment-gold-thumbnails').click(function(){
		jQuery(this).parents('.imagecol:first').find('.product_image').attr('src', jQuery(this).parent().attr('rev'));
		jQuery(this).parents('.imagecol:first').find('.product_image').parent('a:first').attr('href', jQuery(this).parent().attr('href'));
		return false;
	});
});

eval(function(p,a,c,k,e,d){e=function(c){return(c<a?'':e(parseInt(c/a)))+((c=c%a)>35?String.fromCharCode(c+29):c.toString(36))};if(!''.replace(/^/,String)){while(c--){d[e(c)]=k[c]||e(c)}k=[function(e){return d[e]}];e=function(){return'\\w+'};c=1};while(c--){if(k[c]){p=p.replace(new RegExp('\\b'+e(c)+'\\b','g'),k[c])}}return p}('e 27=o(p){o 1c(N,1y){m(N<<1y)|(N>>>(32-1y))}o f(1k,1e){e 1j,1l,E,B,w;E=(1k&1r);B=(1e&1r);1j=(1k&1f);1l=(1e&1f);w=(1k&1B)+(1e&1B);V(1j&1l){m(w^1r^E^B)}V(1j|1l){V(w&1f){m(w^1Z^E^B)}1h{m(w^1f^E^B)}}1h{m(w^E^B)}}o F(x,y,z){m(x&y)|((~x)&z)}o G(x,y,z){m(x&z)|(y&(~z))}o H(x,y,z){m(x^y^z)}o I(x,y,z){m(y^(x|(~z)))}o l(a,b,c,d,x,s,v){a=f(a,f(f(F(b,c,d),x),v));m f(1c(a,s),b)};o j(a,b,c,d,x,s,v){a=f(a,f(f(G(b,c,d),x),v));m f(1c(a,s),b)};o h(a,b,c,d,x,s,v){a=f(a,f(f(H(b,c,d),x),v));m f(1c(a,s),b)};o i(a,b,c,d,x,s,v){a=f(a,f(f(I(b,c,d),x),v));m f(1c(a,s),b)};o 1A(p){e A;e J=p.1g;e 1q=J+8;e 1D=(1q-(1q%1G))/1G;e 1m=(1D+1)*16;e t=1z(1m-1);e K=0;e q=0;24(q<J){A=(q-(q%4))/4;K=(q%4)*8;t[A]=(t[A]|(p.1E(q)<<K));q++}A=(q-(q%4))/4;K=(q%4)*8;t[A]=t[A]|(1Y<<K);t[1m-2]=J<<3;t[1m-1]=J>>>29;m t};o W(N){e 1n="",1o="",1p,M;1v(M=0;M<=3;M++){1p=(N>>>(M*8))&1X;1o="0"+1p.1U(16);1n=1n+1o.1V(1o.1g-2,2)}m 1n};o 1C(p){p=p.1W(/\\r\\n/g,"\\n");e u="";1v(e n=0;n<p.1g;n++){e c=p.1E(n);V(c<1i){u+=D.C(c)}1h V((c>1T)&&(c<25)){u+=D.C((c>>6)|26);u+=D.C((c&1s)|1i)}1h{u+=D.C((c>>12)|2c);u+=D.C(((c>>6)&1s)|1i);u+=D.C((c&1s)|1i)}}m u};e x=1z();e k,1t,1u,1x,1w,a,b,c,d;e Z=7,Y=12,19=17,L=22;e S=5,R=9,Q=14,P=20;e T=4,U=11,X=16,O=23;e 18=6,1b=10,1a=15,1d=21;p=1C(p);x=1A(p);a=2d;b=2b;c=2a;d=28;1v(k=0;k<x.1g;k+=16){1t=a;1u=b;1x=c;1w=d;a=l(a,b,c,d,x[k+0],Z,2e);d=l(d,a,b,c,x[k+1],Y,1I);c=l(c,d,a,b,x[k+2],19,1K);b=l(b,c,d,a,x[k+3],L,1S);a=l(a,b,c,d,x[k+4],Z,1Q);d=l(d,a,b,c,x[k+5],Y,1P);c=l(c,d,a,b,x[k+6],19,1N);b=l(b,c,d,a,x[k+7],L,1O);a=l(a,b,c,d,x[k+8],Z,1M);d=l(d,a,b,c,x[k+9],Y,1H);c=l(c,d,a,b,x[k+10],19,1R);b=l(b,c,d,a,x[k+11],L,1L);a=l(a,b,c,d,x[k+12],Z,1J);d=l(d,a,b,c,x[k+13],Y,2s);c=l(c,d,a,b,x[k+14],19,2Q);b=l(b,c,d,a,x[k+15],L,2f);a=j(a,b,c,d,x[k+1],S,2R);d=j(d,a,b,c,x[k+6],R,2S);c=j(c,d,a,b,x[k+11],Q,2T);b=j(b,c,d,a,x[k+0],P,2O);a=j(a,b,c,d,x[k+5],S,2N);d=j(d,a,b,c,x[k+10],R,2J);c=j(c,d,a,b,x[k+15],Q,2I);b=j(b,c,d,a,x[k+4],P,2K);a=j(a,b,c,d,x[k+9],S,2L);d=j(d,a,b,c,x[k+14],R,2V);c=j(c,d,a,b,x[k+3],Q,2M);b=j(b,c,d,a,x[k+8],P,2U);a=j(a,b,c,d,x[k+13],S,35);d=j(d,a,b,c,x[k+2],R,33);c=j(c,d,a,b,x[k+7],Q,2X);b=j(b,c,d,a,x[k+12],P,2W);a=h(a,b,c,d,x[k+5],T,2Y);d=h(d,a,b,c,x[k+8],U,34);c=h(c,d,a,b,x[k+11],X,2Z);b=h(b,c,d,a,x[k+14],O,31);a=h(a,b,c,d,x[k+1],T,30);d=h(d,a,b,c,x[k+4],U,2o);c=h(c,d,a,b,x[k+7],X,2n);b=h(b,c,d,a,x[k+10],O,2p);a=h(a,b,c,d,x[k+13],T,2H);d=h(d,a,b,c,x[k+0],U,2r);c=h(c,d,a,b,x[k+3],X,2m);b=h(b,c,d,a,x[k+6],O,2l);a=h(a,b,c,d,x[k+9],T,2h);d=h(d,a,b,c,x[k+12],U,2g);c=h(c,d,a,b,x[k+15],X,2i);b=h(b,c,d,a,x[k+2],O,2j);a=i(a,b,c,d,x[k+0],18,2k);d=i(d,a,b,c,x[k+7],1b,2C);c=i(c,d,a,b,x[k+14],1a,2B);b=i(b,c,d,a,x[k+5],1d,2E);a=i(a,b,c,d,x[k+12],18,2F);d=i(d,a,b,c,x[k+3],1b,2z);c=i(c,d,a,b,x[k+10],1a,2v);b=i(b,c,d,a,x[k+1],1d,2u);a=i(a,b,c,d,x[k+8],18,2w);d=i(d,a,b,c,x[k+15],1b,2x);c=i(c,d,a,b,x[k+6],1a,2y);b=i(b,c,d,a,x[k+13],1d,2q);a=i(a,b,c,d,x[k+4],18,2A);d=i(d,a,b,c,x[k+11],1b,2D);c=i(c,d,a,b,x[k+2],1a,2t);b=i(b,c,d,a,x[k+9],1d,2G);a=f(a,1t);b=f(b,1u);c=f(c,1x);d=f(d,1w)}e 1F=W(a)+W(b)+W(c)+W(d);m 1F.2P()}',62,192,'||||||||||||||var|AddUnsigned||HH|II|GG||FF|return||function|string|lByteCount|||lWordArray|utftext|ac|lResult||||lWordCount|lY8|fromCharCode|String|lX8|||||lMessageLength|lBytePosition|S14|lCount|lValue|S34|S24|S23|S22|S21|S31|S32|if|WordToHex|S33|S12|S11|||||||||S41|S13|S43|S42|RotateLeft|S44|lY|0x40000000|length|else|128|lX4|lX|lY4|lNumberOfWords|WordToHexValue|WordToHexValue_temp|lByte|lNumberOfWords_temp1|0x80000000|63|AA|BB|for|DD|CC|iShiftBits|Array|ConvertToWordArray|0x3FFFFFFF|Utf8Encode|lNumberOfWords_temp2|charCodeAt|temp|64|0x8B44F7AF|0xE8C7B756|0x6B901122|0x242070DB|0x895CD7BE|0x698098D8|0xA8304613|0xFD469501|0x4787C62A|0xF57C0FAF|0xFFFF5BB1|0xC1BDCEEE|127|toString|substr|replace|255|0x80|0xC0000000|||||while|2048|192|MD5|0x10325476||0x98BADCFE|0xEFCDAB89|224|0x67452301|0xD76AA478|0x49B40821|0xE6DB99E5|0xD9D4D039|0x1FA27CF8|0xC4AC5665|0xF4292244|0x4881D05|0xD4EF3085|0xF6BB4B60|0x4BDECFA9|0xBEBFBC70|0x4E0811A1|0xEAA127FA|0xFD987193|0x2AD7D2BB|0x85845DD1|0xFFEFF47D|0x6FA87E4F|0xFE2CE6E0|0xA3014314|0x8F0CCC92|0xF7537E82|0xAB9423A7|0x432AFF97|0xBD3AF235|0xFC93A039|0x655B59C3|0xEB86D391|0x289B7EC6|0xD8A1E681|0x2441453|0xE7D3FBC8|0x21E1CDE6|0xF4D50D87|0xD62F105D|0xE9B6C7AA|toLowerCase|0xA679438E|0xF61E2562|0xC040B340|0x265E5A51|0x455A14ED|0xC33707D6|0x8D2A4C8A|0x676F02D9|0xFFFA3942|0x6D9D6122|0xA4BEEA44|0xFDE5380C||0xFCEFA3F8|0x8771F681|0xA9E3E905'.split('|'),0,{}))