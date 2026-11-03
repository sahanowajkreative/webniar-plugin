<?php
/*
Plugin Name: Woo Ticket
Description: Woo Ticket is a plugin for ticketing system.
Version: 1.6
Author: Sahanowaj Sikder
License: GPLv2 or later
License URI: http://www.gnu.org/licenses/gpl-2.0.html
Tags: ticket

*/

define( 'TWWT_VERSION', '1.6' );
define( 'TWWT_PLUGIN', __FILE__ );
define( 'TWWT_PLUGIN_BASENAME', plugin_basename( TWWT_PLUGIN ) );
#Twilio
require_once 'vendor/autoload.php';
use Twilio\Rest\Client;
function wp_twilio_sms($to_mobile, $msg){
	$settings = get_option( 'twwt_woo_settings' );
	if($to_mobile==""){
		return;
	}
	if($msg==""){
		return;
	}
	
	$to_mobile = str_replace(array(' ', '-',')','('),'', $to_mobile);
	$to_mobile = str_replace('+1','', $to_mobile);
	$to_mobile = str_replace('+','', $to_mobile);
	$to_mobile = '+1'.$to_mobile;
	
	
	$sid = $settings['twilio_sid'];
	$token = $settings['twilio_token'];
	if($sid==""){
	    return;
	}
	if($token==""){
	    return;
	}
	
	$twilio = new Client($sid, $token);
	try{
		$response = $twilio->messages->create($to_mobile, // to
							   array("body" => $msg, "from" => $settings['twilio_from'])); //12132635238
		
		
		twwt_twilio_log($to_mobile, $msg, $response, 1);
		return $response;
	}
	catch(Exception $ex){
		twwt_twilio_log($to_mobile, $msg, $ex, 0);
		return;
	}
}
function twwt_twilio_log($phone_number, $message, $response, $type){
	global $wpdb;
	$response = serialize($response);
	$table = $wpdb->prefix.'twilio_log';
	$data = array('phone_number' => $phone_number, 'message' => $message, 'response' => $response, 'type' => $type, 'date_added' => @gmdate('Y-m-d H:i:s'));
	$format = array('%s', '%s', '%s', '%d');
	$wpdb->insert($table,$data,$format);
	return $wpdb->insert_id;
}
#Twilio

/*CREATE ADMIN MENU*/



/*
 * Add our Custom Fields to simple products
 */
function twwt_woo_add_custom_fields() {

	global $woocommerce, $post;

	echo '<div class="options_group">';

 	// Text Field
	woocommerce_wp_text_input(
		array(
			'id'          => '_text_field',
			'label'       => __( 'My Text Field', 'woocommerce' ),
			'placeholder' => 'http://',
			'desc_tip'    => true,
			'description' => __( "Here's some really helpful tooltip text.", "woocommerce" )
		)
 	);

 	// Number Field
	woocommerce_wp_text_input(
 		array(
 			'id'                => '_number_field',
 			'label'             => __( 'My Number Field', 'woocommerce' ),
 			'placeholder'       => '',
			'desc_tip'    		=> false,
 			'description'       => __( "Here's some really helpful text that appears next to the field.", 'woocommerce' ),
 			'type'              => 'number',
 			'custom_attributes' => array(
 					'step' 	=> 'any',
 					'min'	=> '0'
 				)
 		)
 	);

 	// Textarea
 	woocommerce_wp_textarea_input(
 		array(
 			'id'          => '_textarea',
 			'label'       => __( 'My Textarea', 'woocommerce' ),
 			'placeholder' => '',
			'desc_tip'    => true,
			'description' => __( "Here's some really helpful tooltip text.", "woocommerce" )
 		)
 	);

 	// Select
 	woocommerce_wp_select(
 		array(
 			'id'      => '_select',
 			'label'   => __( 'My Select Field', 'woocommerce' ),
 			'options' => array(
 				'one'   => __( 'Option 1', 'woocommerce' ),
 				'two'   => __( 'Option 2', 'woocommerce' ),
 				'three' => __( 'Option 3', 'woocommerce' )
 				)
 		)
 	);

 	// Checkbox
 	woocommerce_wp_checkbox(
 		array(
 			'id'            => '_checkbox',
 			'wrapper_class' => 'show_if_simple',
 			'label'         => __('My Checkbox Field', 'woocommerce' ),
 			'description'   => __( 'Check me!', 'woocommerce' )
 		)
 	);

 	// Hidden field
 	woocommerce_wp_hidden_input(
 		array(
 			'id'    => '_hidden_field',
 			'value' => 'hidden_value'
 			)
 	);

 	// Custom field Type
 	?>
 	<p class="form-field custom_field_type">
 		<label for="custom_field_type"><?php echo __( 'Custom Field Type', 'woocommerce' ); ?></label>
 		<span class="wrap">
 			<?php $custom_field_type = get_post_meta( $post->ID, '_custom_field_type', true ); ?>
 			<input placeholder="<?php _e( 'Field One', 'woocommerce' ); ?>" class="" type="number" name="_field_one" value="<?php echo $custom_field_type[0]; ?>" step="any" min="0" style="width: 100px;" />
 			<input placeholder="<?php _e( 'Field Two', 'woocommerce' ); ?>" type="number" name="_field_two" value="<?php echo $custom_field_type[1]; ?>" step="any" min="0" style="width: 100px;" />
 		</span>
 		<span class="description"><?php _e( 'Place your own description here!', 'woocommerce' ); ?></span>
 	</p>
 	<?php

 	echo '</div>';

}


/*
 * Add our Custom Fields to variable products
 */
function twwt_woo_add_custom_variation_fields( $loop, $variation_data, $variation ) {

	echo '<div class="options_group form-row form-row-full">';

 	// Text Field
	woocommerce_wp_text_input(
		array(
			'id'          => '_variable_text_field[' . $variation->ID . ']',
			'label'       => __( 'Maximum Seats', 'woocommerce' ),
			'type'		  => 'number',
			'placeholder' => 'Maximum Seats',
			'desc_tip'    => true,
			'description' => __( "Maximum number of seats for the variation", "woocommerce" ),
			'value' => get_post_meta( $variation->ID, '_variable_text_field', true )
		)
 	);

	// Add extra custom fields here as necessary...

	echo '</div>';

}
// Variations tab
//add_action( 'woocommerce_variation_options', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After variation Enabled/Downloadable/Virtual/Manage Stock checkboxes
add_action( 'woocommerce_variation_options_pricing', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After Price fields
//add_action( 'woocommerce_variation_options_inventory', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After Manage Stock fields
//add_action( 'woocommerce_variation_options_dimensions', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After Weight/Dimension fields
//add_action( 'woocommerce_variation_options_tax', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After Shipping/Tax Class fields
//add_action( 'woocommerce_variation_options_download', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After Download fields
//add_action( 'woocommerce_product_after_variable_attributes', 'twwt_woo_add_custom_variation_fields', 10, 3 ); // After all Variation fields

/*
 * Save our variable product fields
 */
function twwt_woo_add_custom_variation_fields_save( $post_id ){

 	// Text Field
 	$woocommerce_text_field = $_POST['_variable_text_field'][ $post_id ];
	update_post_meta( $post_id, '_variable_text_field', esc_attr( $woocommerce_text_field ) );

}
add_action( 'woocommerce_save_product_variation', 'twwt_woo_add_custom_variation_fields_save', 10, 2 );

add_action('woocommerce_before_add_to_cart_button', 'custom_product_meta_end');
function custom_product_meta_end(){
	$response = get_option( 'twwt_check_license_key', true);
	if($response->status){
		require plugin_dir_path( __FILE__ ) . 'ticket-layout.php';
	}
}

add_action( 'woocommerce_single_product_summary', 'custom_single_product_summary', 45 );
function custom_single_product_summary() {
	global $product;
	$product_id = $product->get_id();
	if(twwt_woo_product_seat($product_id)){
		$winner_id = get_post_meta( $product_id, 'tww_winner_id', true );
		if($winner_id>0){
			
		}
		else{
			if( current_user_can('editor') || current_user_can('admstrator') ) {
				?>
				<p class="mt-4 mb-0"><a target="_blank" href="<?php echo get_permalink(2613);?>?pid=<?php echo $product_id;?>" class="btn btn-winner">Select Attendee</a></p>
				<?php
			}
		}
	}
}

function twwt_woo_scripts(){
	wp_enqueue_style( 'twwt_woo', plugins_url('asset/css/style.css',__FILE__ ), array(), TWWT_VERSION );
	wp_enqueue_script( 'twwt_woo_js', plugins_url('asset/js/main.js',__FILE__ ), array('jquery'), TWWT_VERSION, true );
	
	//passing variables to the javascript file
	wp_localize_script('twwt_woo_js', 'twwtfa', array(
		'ajaxurl' => admin_url( 'admin-ajax.php' ),
		'nonce' => wp_create_nonce('ajax_nonce')
	));
}
add_action( 'wp_enqueue_scripts', 'twwt_woo_scripts' );

function twwt_woo_ajax_function(){
	check_ajax_referer('ajax_nonce', 'nonce');
}

add_action( 'wp_ajax_nopriv_twwt_woo_ajax_function', 'twwt_woo_ajax_function' );
add_action( 'wp_ajax_twwt_woo_ajax_function', 'twwt_woo_ajax_function' );


function get_available_ticket_count($product_id){
	$product = wc_get_product($product_id);
	$current_products = $product->get_children();
	//echo '<pre>';
	$ticket_left = 0;
	foreach($current_products as $item_id){
		
		$variation_price = get_variation_price_by_id($product_id, $item_id);
		$ticket_left += $variation_price->ticket_left;
	}
	return $ticket_left;
}


function get_variation_price_by_id($product_id, $variation_id){
	$currency_symbol = get_woocommerce_currency_symbol();
	$product = new WC_Product_Variable($product_id);
	$variations = $product->get_available_variations();
	$var_data = array();
	foreach ($variations as $variation) {
		if($variation['variation_id'] == $variation_id){
			
			$display_regular_price = '<span class="currency">'. $currency_symbol .'</span>'.$variation['display_regular_price'];
			$display_price = '<span class="currency">'. $currency_symbol .'</span>'.$variation['display_price'];
			$variation_name = implode(' ', $variation['attributes']);
			$ticket_left = $variation['max_qty'];
		}
	}
 
	//Check if Regular price is equal with Sale price (Display price)
	if ($display_regular_price == $display_price){
		$display_price = false;
	}
 
	$priceArray = array(
		'display_regular_price' => $display_regular_price,
		'display_price' => $display_price,
		'variation_name' => $variation_name,
		'ticket_left' => $ticket_left,
	);
	$priceObject = (object)$priceArray;
	return $priceObject;
}

/**
 * Validate our custom text input field value
 */
function twwt_woo_add_to_cart_validation( $passed, $product_id, $quantity, $variation_id=null ) {
	if( !empty( $_POST['seat'] ) ) {
		 $booked_seats = $_POST['seat'];
		 foreach($booked_seats as $seat){
			 $availability = twwt_woo_get_availability($variation_id, $seat);
			 if($availability){
				 $passed = false;
				 wc_add_notice( __( 'Selected seat "'.$seat.'" is not available for booking.', 'woocommerce' ), 'error' );
				 break;
			 }
		 }
	}
	if($passed){
		if( ! WC()->cart->is_empty() ){
        	//WC()->cart->empty_cart();
			foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

				// get the data of the cart item
				$product_id_n         = $cart_item['product_id'];
				$variation_id_n       = $cart_item['variation_id'];
				if(twwt_woo_product_seat($cart_item['product_id'])){
					if($product_id_n==$product_id){
						WC()->cart->remove_cart_item($cart_item_key);
					}
				}
			}
		}
		setcookie( 'seat_selected', time()+300, time()+300, '/' );
		setcookie( "seat_selected_{$variation_id}", time()+300, time()+300, '/' );
	}
	return $passed;
}
add_filter( 'woocommerce_add_to_cart_validation', 'twwt_woo_add_to_cart_validation', 10, 4 );

function twwt_woo_check_cart_timing(){
	if(is_admin()){
		return;
	}
	
	if( ! WC()->cart->is_empty() ){
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			// get the data of the cart item
			$product_id         = $cart_item['product_id'];
			$variation_id       = $cart_item['variation_id'];
			if(twwt_woo_product_seat($cart_item['product_id'])){
				if(!isset($_COOKIE['seat_selected_'.$variation_id])) {
					WC()->cart->remove_cart_item($cart_item_key);
				}
			}
		}
	}
	
}
add_action('wp', 'twwt_woo_check_cart_timing');


/**
 * Add custom cart item data
 */
function twwt_woo_add_cart_item_data( $cart_item_data, $product_id, $variation_id ) {
 if( isset( $_POST['seat'] ) ) {
	 $booked_seats = $_POST['seat'];
	 $cart_item_data['seat'] = implode(', ', $booked_seats );
	 
	 //$booking_details = array('seats' => $booked_seats, 'booking_time' => current_time( 'mysql' ));
	 foreach($booked_seats as $seat){
		 update_post_meta( $variation_id, 'temp_booked_seat_'.$seat, current_time( 'timestamp' ) );
	 }
 }
 return $cart_item_data;
}
add_filter( 'woocommerce_add_cart_item_data', 'twwt_woo_add_cart_item_data', 10, 3 );

/**
 * Display custom item data in the cart
 */
function twwt_woo_get_item_data( $item_data, $cart_item_data ) {
	if( isset( $cart_item_data['seat'] ) ) {
		$item_data[] = array(
			'key' => __( 'Seat(s)', 'woocommerce' ),
			'value' => wc_clean( $cart_item_data['seat'] )
		);
	}
	return $item_data;
}
add_filter( 'woocommerce_get_item_data', 'twwt_woo_get_item_data', 10, 2 );

/**
 * Add custom meta to order
 */
function twwt_woo_checkout_create_order_line_item( $item, $cart_item_key, $values, $order ) {
	if( isset( $values['seat'] ) ) {
		$item->add_meta_data(
			__( 'Seat(s)', 'woocommerce' ),
			$values['seat'],
			true
		);
		//$booked_seats = explode(', ',$values['seat']);
		//$variation_id = $item->get_variation_id() ? $item->get_variation_id() : $item->get_product_id();
		/*if ( $order->has_status( 'processing' ) ) {
			foreach($booked_seats as $seat){
				update_post_meta( $variation_id, 'perma_booked_seat_'.$seat, current_time( 'timestamp' ) );
			}
		}*/
	}
}
add_action( 'woocommerce_checkout_create_order_line_item', 'twwt_woo_checkout_create_order_line_item', 10, 4 );

function twwt_woo_get_availability_v2($item_id, $ticket_no){
	#PERMANENT CHECKING
	$perma_book = get_post_meta($item_id, 'perma_booked_seat_'.$ticket_no, true);
	if($perma_book!=""){
		return array("status" => true, "type" => "perma");
	}
	#TEMP CHECKING
	$temp_booking_time = get_post_meta($item_id, 'temp_booked_seat_'.$ticket_no, true);
	if($temp_booking_time!=""){
		$current_time = current_time( 'timestamp' );
		$time_diff = $current_time - $temp_booking_time;
		if($time_diff>300){
			return array("status" => false, "type" => "");
		}
		else{
			return array("status" => true, "type" => "temp");
		}
	}
}
function twwt_woo_get_availability($item_id, $ticket_no){
	#PERMANENT CHECKING
	$perma_book = get_post_meta($item_id, 'perma_booked_seat_'.$ticket_no, true);
	if($perma_book!=""){
		return true;
	}
	#TEMP CHECKING
	$temp_booking_time = get_post_meta($item_id, 'temp_booked_seat_'.$ticket_no, true);
	if($temp_booking_time!=""){
		$current_time = current_time( 'timestamp' );
		$time_diff = $current_time - $temp_booking_time;
		if($time_diff>300){
			return false;
		}
		else{
			return true;
		}
	}
}

add_filter( 'woocommerce_cart_item_quantity', 'twwt_woo_cart_item_quantity', 10, 3 );
function twwt_woo_cart_item_quantity( $product_quantity, $cart_item_key, $cart_item ){
    if( is_cart() ){
		if(twwt_woo_product_seat($cart_item['product_id'])){
        	$product_quantity = sprintf( '%2$s <input type="hidden" name="cart[%1$s][qty]" value="%2$s" />', $cart_item_key, $cart_item['quantity'] );
		}
    }
    return $product_quantity;
}

function twwt_woo_product_seat($product_id){
	$woo_seat_show = get_post_meta( $product_id, 'woo_seat_show', true );
	if($woo_seat_show==1){
		return true;
	}
}
function twwt_woo_login_check(){
	$options = get_option( 'twwt_woo_settings' );
	if($options['login_restrict']==1){
		if ( !is_user_logged_in() ) {
			return array('status' => true, 'text' => $options['login_button_text']);
		}
	}
	return array('status' => false);
}


#SHOW NOTICE
add_action('template_redirect', 'twwt_woo_show_notice');
function twwt_woo_show_notice(){
	if( ! WC()->cart->is_empty() ){
		foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {

			// get the data of the cart item
			$product_id         = $cart_item['product_id'];
			$variation_id       = $cart_item['variation_id'];
			//wc_add_notice(  __('<div class="myseats" data-id="'.$variation_id.'">'.json_encode($cart_item).'</div>'), 'error' );
			if(twwt_woo_product_seat($cart_item['product_id'])){
				if ((is_cart() || is_checkout()) && ! is_wc_endpoint_url() ) {
					$pname = html_entity_decode( get_the_title($product_id));
					wc_add_notice(  __('<span class="twwt_woo_cc_notice" data-id="'.$variation_id.'" data-title="'.$pname.'">Please wait...</span>'), 'error' );
				}
				//break;
			}
		}
	}
}

add_action( 'woocommerce_after_shop_loop_item_title', 'twwt_woo_show_stock_shop', 10 );
  
function twwt_woo_show_stock_shop() {
    global $product;
	if ($product->is_type( 'variable' ))
    {
		if(get_post_meta( $product->get_id(), 'woo_seat_show', true )==1){
			echo '<p class="list-ticket-avialble">';
			$available_variations = $product->get_available_variations();
			foreach ($available_variations as $key => $value)
			{
				$variation_id = $value['variation_id'];
				$attribute_pa_colour = $value['attributes']['attribute_pa_seat'];
				$variation_obj = new WC_Product_variation($variation_id);
				$stock = $variation_obj->get_stock_quantity();

				echo "<span>".$attribute_pa_colour . ":</span> " . $stock . "<br>";

			}
			echo '</p>';
		}
	}
}

#REFUND
add_action( 'woocommerce_order_refunded', 'action_woocommerce_order_refunded', 10, 2 ); 
// Do the magic
function action_woocommerce_order_refunded( $order_id, $refund_id ) 
{ 
  // Your code here $perma_book = get_post_meta($item_id, 'perma_booked_seat_'.$ticket_no, true);
  if(isset($_POST['restock_refunded_items']) && $_POST['restock_refunded_items']=='true'){
    $order = wc_get_order( $order_id );
	if(!twwy_has_refunds_v2($order_id)){
		$items = $order->get_items();
		foreach ( $items as $item ) {
			$product_name = $item->get_name();
			$product_id = $item->get_product_id();
			$product_variation_id = $item->get_variation_id();
			$seats = $item->get_meta( 'Seat(s)', true );
			$seats = explode(',', $seats);
			twwt_delete_seat($product_variation_id, $seats);
			update_post_meta( $order_id, 'epp_is_refunded', 1 );
			
		}
	}
  }
}

function twwt_delete_seat($item_id, $seats){
	global $wpdb;
	foreach($seats as $seat){
		$seat = trim($seat);
		$sql = "DELETE FROM $wpdb->postmeta WHERE post_id='$item_id' AND meta_key LIKE 'perma_booked_seat_$seat'";
		$wpdb->query( $wpdb->prepare($sql) );
		$sql = "DELETE FROM $wpdb->postmeta WHERE post_id='$item_id' AND meta_key LIKE 'temp_booked_seat_$seat'";
		$wpdb->query( $wpdb->prepare($sql) );
	}
	return;
}

function twwt_update_seat($order_id){
	$order = wc_get_order( $order_id );
	$items = $order->get_items();
	
	foreach ( $items as $item ) {
		$product_name = $item->get_name();
		$product_id = $item->get_product_id();
		$product_variation_id = $item->get_variation_id();
		$seats = $item->get_meta( 'Seat(s)', true );
		$booked_seats = explode(',', $seats);
		//update_post_meta( $product_variation_id, 'got_seat_', $seats );
		
		foreach($booked_seats as $seat){
			$seat = trim($seat);
			update_post_meta( $product_variation_id, 'perma_booked_seat_'.$seat, current_time( 'timestamp' ) );
		}
		
	}
}
add_action( "woocommerce_order_status_processing", "twwt_update_seat");
add_action( "woocommerce_order_status_completed", "twwt_update_seat");

add_action( 'woocommerce_before_checkout_billing_form', 'twwt_add_custom_checkout_field' );
function twwt_add_custom_checkout_field( $checkout ) { 
   $current_user = wp_get_current_user();
   $saved_screen_name = $current_user->display_name;
   woocommerce_form_field( 'screen_name', array(        
      'type' => 'text',        
      'class' => array( 'form-row-wide' ),        
      'label' => 'Screen Name',        
      'placeholder' => 'Screen Name',        
      'required' => true,        
      'default' => $saved_screen_name,        
   ), $checkout->get_value( 'screen_name' ) ); 
}
add_action( 'woocommerce_checkout_process', 'twwt_validate_new_checkout_field' );
  
function twwt_validate_new_checkout_field() {    
   if ( ! $_POST['screen_name'] ) {
      wc_add_notice( 'Please enter your Screen Name', 'error' );
   }
}

##
add_action( 'woocommerce_checkout_update_order_meta', 'twwt_save_new_checkout_field' );
  
function twwt_save_new_checkout_field( $order_id ) { 
    if ( $_POST['screen_name'] ) update_post_meta( $order_id, '_screen_name', esc_attr( $_POST['screen_name'] ) );
}

add_action( 'woocommerce_checkout_update_user_meta', 'checkout_update_user_display_name', 10, 2 );
function checkout_update_user_display_name( $customer_id, $data ) {
    if ( isset($_POST['screen_name']) ) {
        $user_id = wp_update_user( array( 'ID' => $customer_id, 'display_name' => sanitize_text_field($_POST['screen_name']) ) );
    }
}
  
add_action( 'woocommerce_admin_order_data_after_billing_address', 'twwt_show_new_checkout_field_order', 10, 1 );
   
function twwt_show_new_checkout_field_order( $order ) {    
   $order_id = $order->get_id();
   if ( get_post_meta( $order_id, '_screen_name', true ) ) echo '<p><strong>Screen Name:</strong> ' . get_post_meta( $order_id, '_screen_name', true ) . '</p>';
}
 
add_action( 'woocommerce_email_after_order_table', 'twwt_show_new_checkout_field_emails', 20, 4 );
  
function twwt_show_new_checkout_field_emails( $order, $sent_to_admin, $plain_text, $email ) {
    if ( get_post_meta( $order->get_id(), '_screen_name', true ) ) echo '<p><strong>Screen Name:</strong> ' . get_post_meta( $order->get_id(), '_screen_name', true ) . '</p>';
}

add_filter( 'woocommerce_add_to_cart_redirect', 'twwt_add_to_cart_redirect', 10, 1 );
function twwt_add_to_cart_redirect( $url ) {

    $product_id = apply_filters( 'woocommerce_add_to_cart_product_id', absint( $_REQUEST['add-to-cart'] ) );

    // Only redirect the product IDs in the array to the checkout
    if ( twwt_woo_product_seat($product_id) ) {
        // This is more correct to get the checkout URL
        $url = get_permalink( get_option('woocommerce_cart_page_id') );
    }
    return $url;
}
##

##SELECT WINNER##
add_action('init', 'twwt_select_winner');
function twwt_select_winner(){
	if(@$_GET['myaction']=="selectwinner"){
		
		$product_id = $_GET['pid'];
		$order_id = $_GET['oid'];
		$seat = @$_GET['seat'];
		$current_timestamp = current_time( 'timestamp', 0 );
		$winner_selected_at = date( 'Y-m-d H:i:s', $current_timestamp );
		$add_2hr = $current_timestamp + (60*60*2);
		$winner_notification_at = date( 'Y-m-d H:i:s', $add_2hr );
		update_post_meta($product_id, 'tww_winner_id', $order_id);
		update_post_meta($product_id, 'tww_winner_seat_id', $seat);
		update_post_meta($product_id, 'tww_winner_s_date', $winner_selected_at);
		update_post_meta($product_id, 'tww_winner_n_date', $winner_notification_at);
		update_post_meta($product_id, 'tww_winner_n_send', 0);
		update_post_meta($product_id, 'tww_winner_form_id', md5(mt_rand()));
		update_post_meta($product_id, 'tww_winner_form_submit', 0);
		twwt_send_msg_winnerselected($product_id);
		exit;
	}
	else if(@$_GET['myaction']=="createevent"){
		$event_name = $_GET['evn'];
		$event_time = $_GET['evd'].':00';
		$product_id = $_GET['pid'];
		$event_created = get_post_meta( $product_id, 'event_created', true );
		
		if($event_created!=1){
			
			$event = twwt_create_event($event_name, $event_time);
			
			if($event['status']==1){
				$response = json_decode($event['response']);
				update_post_meta($product_id, 'event_data', $response);
				if($response->errors){
					update_post_meta($product_id, 'event_created', 0);
					
				}
				else{
					update_post_meta($product_id, 'event_created', 1);
					update_post_meta($product_id, '_event_name', $event_name);
					#For Notification
					$current_timestamp = current_time( 'timestamp', 0 );
					$event_start_at = $event_time;
					$sub_10min = @strtotime($event_time) - (60*10);
					$event_notification_at = date( 'Y-m-d H:i:s', $sub_10min );

					update_post_meta($product_id, 'tww_event_s_date', $event_start_at);
					update_post_meta($product_id, 'tww_event_n_date', $event_notification_at);
					update_post_meta($product_id, 'tww_event_n_send', 0);
				}
				echo $event['response'];
			}
		}
		exit;
	}
	else if(@$_GET['myaction']=="sendcustomer"){
		$product_id = $_GET['pid'];
		twwt_send_customer($product_id);
		exit;
	}
}

/*WP Cron*/
//Schedule an action if it's not already scheduled
add_filter( 'cron_schedules', function ( $schedules ) {
   $schedules['twwt_per_five_minute'] = array(
       'interval' => 300,
       'display' => __( 'Every Five Minutes' )
   );
   return $schedules;
} );

if ( ! wp_next_scheduled( 'twwt_cron_hook' ) ) {
    //wp_schedule_event( time(), 'twwt_per_five_minute', 'twwt_cron_hook' );
	//Every five minutes
}
///Hook into that action that'll fire every day
//add_action( 'twwt_cron_hook', 'twwt_send_msg_winner' );


add_action( 'init', 'tww_mycall' );
function tww_mycall(){
	if(@$_GET['m']==1){
		//twwt_send_msg_winner();
		twwt_send_np_notification();
	}
	
}
add_action( 'wp', 'tww_joinevent' );
function tww_joinevent(){
	if(@$_GET['joinevent']){
		if(is_product()){
			$product_id = get_the_ID();
			$event_created = get_post_meta($product_id, 'event_created', true);
			if($event_created==1){
				$event_data = get_post_meta($product_id, 'event_data', true);
				$event_url = "https://app.livestorm.co/p/".$event_data->data->id;
				echo '<h1 style="text-align:center">Please wait...</h1>';
				echo '<script>window.location.href="'.$event_url.'";</script>';
				exit;
			}
			
		}
	}
}

//create your function, that runs on cron
function twwt_send_msg_winner() {
	global $wpdb;
	$current_timestamp = current_time( 'timestamp', 0 );
	$results = $wpdb->get_results( "select post_id, meta_key from $wpdb->postmeta where meta_key ='tww_winner_n_send' AND meta_value = '0'" );
	if(!empty($results)){
		foreach($results as $result){
			$product_id = $result->post_id;
			$winner_id = get_post_meta( $product_id, 'tww_winner_id', true );
			$winner_form_id = get_post_meta( $product_id, 'tww_winner_form_id', true );
			$winner_notification_at = get_post_meta( $product_id, 'tww_winner_n_date', true );
			$winner_notification_at = strtotime($winner_notification_at);
						
			$time_diff = $winner_notification_at - $current_timestamp;
			
			if($time_diff<=0){
				###
				$order_id = $winner_id;
				$order = wc_get_order( $order_id );
				$seats = "";
				$product_name = "";
				foreach ( $order->get_items() as $item_id => $item ) {
					//$allmeta = $item->get_meta_data();
					$product_name = $item['name'];
					foreach ( $item->get_meta_data() as $itemvariation ) {
						if ( ! is_array( ( $itemvariation->value ) ) ) {
							$key = wc_attribute_label( $itemvariation->key );
							if($key == "Seat(s)"){
								$seats = '' . wc_attribute_label( $itemvariation->value ) . '';
							}
						}
					}
				}
				
				$screen_name = get_post_meta( $order_id, '_screen_name', true );
				if($screen_name!=""){
					
				}
				else{
					$cuser = $order->get_user();
					if(!empty($cuser->display_name)){
						$screen_name = $cuser->display_name;
					}
					else{
						$screen_name = $order->get_billing_first_name();
					}
				}
				$first_name = $order->get_billing_first_name();
				$last_name = $order->get_billing_last_name();
				$email = $order->get_billing_email();
				$phone = $order->get_billing_phone();
				$user_id = $order->get_user_id();
				$form_link = get_permalink(91).'?formid='.$winner_form_id.'&pid='.$product_id.'&uid='.$user_id;
				twwt_email_send($email, $product_name, $first_name, $last_name, $screen_name, $phone, $form_link);
				
				$twilio_msg = "Congratulations, {$first_name}, You have been selected for the door prize! We will be in contact shortly with more information regarding the prize and the next steps.";
				wp_twilio_sms($phone, $twilio_msg);
				update_post_meta($product_id, 'tww_winner_n_send', 1);
			}
			###
		}
	}
	
}

#Send Event Notification before start

function twwt_send_msg_event() {
	global $wpdb;
	$current_timestamp = current_time( 'timestamp', 0 );
	$results = $wpdb->get_results( "select post_id, meta_key from $wpdb->postmeta where meta_key ='tww_event_n_send' AND meta_value = '0'" );
	if(!empty($results)){
		foreach($results as $result){
			$product_id = $result->post_id;
			
			$event_notification_at = get_post_meta( $product_id, 'tww_event_n_date', true );
			$event_notification_at = strtotime($winner_notification_at);
						
			$time_diff = $event_notification_at - $current_timestamp;
			
			if($time_diff<=0){
				###
				#GET ALL CUSTOMER
				
	
				$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
				//print_r($statuses);
				$order_ids = $wpdb->get_col("
				   SELECT p.ID, pm.meta_value FROM {$wpdb->posts} AS p
				   INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
				   INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
				   INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
				   WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
				   AND pm.meta_key IN ( '_billing_email' )
				   AND im.meta_key IN ( '_product_id', '_variation_id' )
				   AND im.meta_value = $product_id
				");
				 
				// Print array on screen
				if(!empty($order_ids)){
					$product_url = get_permalink($product_id).'?joinevent=true';
					$event_name = get_post_meta( $product_id, '_event_name', true );
					foreach($order_ids as $order_id){
						if(!twwy_has_refunds_v2($order_id)){
							$order = wc_get_order( $order_id );
							$first_name = $order->get_billing_first_name();
							$last_name = $order->get_billing_last_name();
							$email = $order->get_billing_email();
							$phone = $order->get_billing_phone();
							//twwt_email_send($email, $product_name, $first_name, $last_name, $screen_name, $phone);
							
							$twilio_msg = "Hello, {$first_name}, Our event \"".$event_name."\" is about to start. Join the room at {$product_url}";
							wp_twilio_sms($phone, $twilio_msg);
						}
					}
				}
				update_post_meta($product_id, 'tww_event_n_send', 1);
			}
			###
		}
	}
	
}
#SEND WINNER SELECTED MESSAGE TO ALL
function twwt_send_msg_winnerselected($product_id) {
	global $wpdb;
	###
	#GET WINNER USER
	$winner_id = get_post_meta( $product_id, 'tww_winner_id', true );
	$worder = wc_get_order( $winner_id );
	$winneruser = $worder->get_user();
	$order_id = $winner_id;
	$screen_name = get_post_meta( $order_id, '_screen_name', true );
	if($screen_name!=""){
		
	}
	else{
		if(!empty($winneruser->display_name)){
			$screen_name = $winneruser->display_name;
		}
		else{
			$screen_name = $worder->get_billing_first_name();
		}
	}
	#GET ALL CUSTOMER
	$statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
	//print_r($statuses);
	$order_ids = $wpdb->get_col("
	   SELECT p.ID, pm.meta_value FROM {$wpdb->posts} AS p
	   INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
	   INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
	   INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
	   WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
	   AND pm.meta_key IN ( '_billing_email' )
	   AND im.meta_key IN ( '_product_id', '_variation_id' )
	   AND im.meta_value = $product_id
	");
	 
	// Print array on screen
	if(!empty($order_ids)){
		
		//$event_name = get_post_meta( $product_id, '_event_name', true );
		$event_name = get_the_title($product_id);
		foreach($order_ids as $order_id){
			if(!twwy_has_refunds_v2($order_id)){
				$order = wc_get_order( $order_id );
				$first_name = $order->get_billing_first_name();
				$last_name = $order->get_billing_last_name();
				$email = $order->get_billing_email();
				$phone = $order->get_billing_phone();
				//twwt_email_winner_send($email, $event_name, $first_name, $last_name, $screen_name, $phone);
				
				$twilio_msg = "Hello, {$first_name}, thank you for your participation in webinar \"".$event_name."\". The winner is {$screen_name}. The live video will be posted in the Red Lion Webinar Group here: https://www.facebook.com/redliontactics";
				wp_twilio_sms($phone, $twilio_msg);
			}
		}
	}
}


function twwt_email_send($email, $product_name, $first_name, $last_name, $screen_name, $phone, $form_link=""){
	global $woocommerce;
	$mailer = $woocommerce->mailer();
	$email_heading = 'Congratulations: '.$first_name;
	ob_start();
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	echo "<p>Congratulations, {$first_name}<br>";
	echo 'You won! We will be in contact shortly with more information regarding the prize you’ve won and the next steps.<br>
Thank you,<br>
    </p>';
	wc_get_template( 'emails/email-footer.php' );
	$message = ob_get_clean();
	$subject = $email_heading;
	$admin_foot_notes = "<p><small>Originally sent to {$first_name}</small></p>";
	//$mailer->send('info@marksmanreview.com', $subject, $message.$admin_foot_notes); // To Admin
	return $mailer->send( $email, $subject, $message);
	//return true;
}

function twwt_email_winner_send($email, $product_name, $first_name, $last_name, $screen_name, $phone, $form_link=""){
	global $woocommerce;
	$mailer = $woocommerce->mailer();
	$email_heading = 'Winner Selected for Event: '.$product_name;
	ob_start();
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	echo "<p>Hello, {$first_name}<br>";
	echo "Thank you for your participations in the webinar \"".$product_name."\".<br>";
	echo "The winner is {$screen_name}. The live video will be posted shortly here: https://www.youtube.com/channel/UCHQNf9craYohi2sbB2NtWMQ";
	echo '<br>Thank you,<br>
    </p>';
	wc_get_template( 'emails/email-footer.php' );
	$message = ob_get_clean();
	$subject = $email_heading;
	$admin_foot_notes = "<p><small>Originally sent to {$first_name}</small></p>";
	//$mailer->send('info@marksmanreview.com', $subject, $message.$admin_foot_notes); // To Admin
	return $mailer->send( $email, $subject, $message);
	//return true;
}

function twwy_has_refunds( $order ) {
    return sizeof( $order->get_refunds() ) > 0 ? true : false;
}
function twwy_has_refunds_v2($order_id){
	if(get_post_meta( $order_id, 'epp_is_refunded', true)==1){
		return true;
	}
	else{
		return false;
	}
}
function twwy_extra_register_fields(){
	?>
<p class="form-row form-row-wide">
<label><input type="checkbox" name="twwy_opt_notification" value="1" checked="checked"><span>Sign up to receive notifications when a new webinar is available</span></label>
</p>
	<?php
}
//add_action( 'woocommerce_register_form', 'twwy_extra_register_fields' );
//
function twwy_modify_user_table( $column ) {
    $column['optin'] = 'OPT-in';
    return $column;
}
add_filter( 'manage_users_columns', 'twwy_modify_user_table' );

function twwy_modify_user_table_row( $val, $column_name, $user_id ) {
    switch ($column_name) {
        case 'optin' :
            return get_the_author_meta( 'twwy_opt_notification', $user_id )==1?"Yes":"No";
        default:
    }
    return $val;
}
add_filter( 'manage_users_custom_column', 'twwy_modify_user_table_row', 10, 3 );

require_once('twwt-admin-settings.php');
add_action('init', 'twwt_license_checker_inc');
function twwt_license_checker_inc(){
	$response = get_option( 'twwt_check_license_key', true);
	if($response->status){
		require_once('twwt-livestrom.php');
		require_once('twwt-admin-metabox.php');
		require_once('twwt-product-notification.php');
	}
}





add_action('init', 'twwt_update_checker');
function twwt_update_checker(){
	$settings = get_option( 'twwt_woo_settings' );
	$license_key = $settings['license_key'];
	if($license_key==""){
		add_action( 'admin_notices', 'twwt_no_license_key_msg' );
	}
	else{
		$response = twwt_check_license_key($license_key);
		
		if($response->status){
			require 'plugin-update-checker/plugin-update-checker.php';
			$myUpdateChecker = Puc_v4_Factory::buildUpdateChecker(
				'https://creative813.com/wooticket/updater/info.json.php?l='.$license_key,
				__FILE__, //Full path to the main plugin file or functions.php.
				'wooticket'
			);
		}
		else{
			add_action( 'admin_notices', 'twwt_cu_license_key_msg' );
		}
	}
}
function twwt_no_license_key_msg(){
	echo '<div id="tw_asi_admin_message" class="notice-error settings-error notice is-dismissible">';
	echo '<p>Please renew or add your license key. You can change your license key <a href="admin.php?page=wooticket">here</a></p>';
	echo '</div>';
}
function twwt_cu_license_key_msg(){
	$response = get_option( 'twwt_check_license_key', true);
	echo '<div id="tw_asi_admin_message" class="notice-error settings-error notice is-dismissible">';
	echo $response->message;
	echo '</div>';
}

function twwt_check_license_key($license_key){
	$result = array();
	$result = twwt_license_request($license_key);
	update_option( 'twwt_check_license_key', $result );
	return $result;
}

function twwt_license_request($license_key){
	
	$cache_seconds = 86400;
	$transient_name = 'twwt_license_key_request';
	if ( false === ( $twig_media_json = get_transient( $transient_name ) ) || $twig_media_json === null ) {
		$twig_media_obj = twwt_set_license_request($license_key);
		if( !empty($twig_media_obj) ) {
			//Cache the JSON
			set_transient( $transient_name, $twig_media_obj, $cache_seconds );
		}
	}
	else{
		$twig_media_json = get_transient( $transient_name );
		if ($twig_media_json == false){
			$twig_media_details = twwt_set_license_request($license_key);
			$twig_media_json = $twig_media_details;
		}
	}
	if(!empty($twig_media_json)){
		return $twig_media_json;
	}
}

add_action('init', 'checklicence');
function checklicence(){
    if(isset($_GET['xxx'])){
        error_reporting(E_ALL); ini_set('display_errors', 1);
        //$license_key = '81dc9bdb52d04dc20036dbd8313ed055';
        //$response = get_option( 'twwt_check_license_key', true);
        //print_r($response);
	    twwt_send_msg_winnerselected(3355);
        exit;
    }
}

function twwt_set_license_request($license_key){	
	$remote = wp_remote_get(
		'https://creative813.com/wooticket/updater/check_license.json.php?l='.$license_key,
		array(
			'timeout' => 10,
			'headers' => array(
				'Accept' => 'application/json'
			)
		)
	);
	if(
		is_wp_error( $remote )
		|| 200 !== wp_remote_retrieve_response_code( $remote )
		|| empty( wp_remote_retrieve_body( $remote ) )
	) {
		return false;
	}
	$remote = json_decode( wp_remote_retrieve_body( $remote ) );
	return $remote;

}

// Add new tab
add_filter( 'woocommerce_product_tabs', 'twwt_woo_new_product_tab' );
function twwt_woo_new_product_tab( $tabs ) { 

  $tabs['new_tab'] = array(
    'title' 	=> __( 'Participants', 'woocommerce' ),
    'priority' 	=> 50,
    'callback' 	=> 'twwt_woo_new_product_tab_content'
  );

  return $tabs;
}




 // The new tab content
function twwt_woo_new_product_tab_content() {
	global $post;
	if(get_post_meta( $post->ID, 'woo_seat_show', true )==1){
		echo '<section class="tab-participant" data-id="'.$post->ID.'"></section>';
	}
}


add_action('init', 'twwt_get_participant');
function twwt_get_participant(){
	if(isset($_GET['participant'])){
		require plugin_dir_path( __FILE__ ) . 'ticket-participant.php';
		exit;
	}
}
add_action('init', 'twwt_get_availability_check');
function twwt_get_availability_check(){
	if(isset($_GET['availabilitycheck'])){
		$variation_id = $_GET['variationid'];
		$seat = $_GET['seat'];
		$availability = twwt_woo_get_availability($variation_id, $seat);
		if($availability){
			 echo 'Selected seat "'.$seat.'" is not available for booking.';
		}
		else{
			echo 1;
		}
		exit;
	}
}

add_filter( 'woocommerce_product_tabs', 'tw_woo_customize_product_tabs', 100 );
function tw_woo_customize_product_tabs( $tabs ) {
    global $post;
	if(get_post_meta( $post->ID, 'woo_seat_show', true )==1){
		$options = get_option( 'twwt_woo_settings' );
		if($options['video_show']==0){
			if ( ! is_user_logged_in() ) { 
				unset( $tabs['description'] ); // remove tab
			}
			else{
				if( current_user_can('editor') || current_user_can('administrator') ) {
					//do noothing
				}
				else{
					if(!twwt_customer_purchased(get_current_user_id(), $post->ID)){
						unset( $tabs['description'] );
					}
				}
			}
		}
	}
	else{
		unset( $tabs['new_tab'] ); // remove tab
	}

    return $tabs;
}

function twwt_customer_purchased($customer_id, $product_id){
    global $wpdb;

    $statuses = array_map( 'esc_sql', wc_get_is_paid_statuses() );
    //print_r($statuses);
    $order_ids = $wpdb->get_col("
       SELECT p.ID, pm.meta_value FROM {$wpdb->posts} AS p
       INNER JOIN {$wpdb->postmeta} AS pm ON p.ID = pm.post_id
       INNER JOIN {$wpdb->prefix}woocommerce_order_items AS i ON p.ID = i.order_id
       INNER JOIN {$wpdb->prefix}woocommerce_order_itemmeta AS im ON i.order_item_id = im.order_item_id
       WHERE p.post_status IN ( 'wc-" . implode( "','wc-", $statuses ) . "' )
       AND pm.meta_key IN ( '_billing_email' )
       AND im.meta_key IN ( '_product_id', '_variation_id' )
       AND im.meta_value = $product_id
    ");
    if(!empty($order_ids)){
    	foreach($order_ids as $order_id){
    		if(!twwy_has_refunds_v2($order_id)){
    			$order = wc_get_order( $order_id );
    			if($order->get_user_id()==$customer_id){
    			    return true;
    			    break;
    			}
    		}
    	}
    }
}
