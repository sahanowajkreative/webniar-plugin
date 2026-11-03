<?php
add_action( 'add_meta_boxes', 'twwt_add_post_meta_boxes' );


function twwt_add_post_meta_boxes() {
	add_meta_box(
		'twwt-wc-product-customer-list',
		 __( 'Customer List', 'woocommerce' ),
		'twwt_add_post_meta_boxes_callback',
		'product',
		'normal',
		'default'
	);
	add_meta_box(
		'twwt-wc-product-seat',
		 __( 'Woo Ticket', 'woocommerce' ),
		'twwt_add_post_meta_boxes_seat_callback',
		'product',
		'side',
		'default'
	);
}

function twwt_add_post_meta_boxes_seat_callback(){
	global $post;

	// Nonce field to validate form request came from current site
	wp_nonce_field( basename( __FILE__ ), 'product_fields' );
	// Get the location data if it's already been entered
	$woo_seat_show = get_post_meta( $post->ID, 'woo_seat_show', true );
	$selected = "";
	if($woo_seat_show==1){
		$selected = "selected";
		
	}
	else{
		echo '<style>#twwt-wc-product-customer-list{display:none;}</style>';
	}

	// Output the field
	echo '<select name="woo_seat_show"class="widefat">';
	echo '<option value="0">Disable</option>';
	echo '<option value="1" '.$selected.'>Enable</option>';
	echo '</select>';
}

function twwt_save_product_seat_meta( $post_id, $post ) {
	// Return if the user doesn't have edit permissions.
	if ( ! current_user_can( 'edit_post', $post_id ) ) {
		return $post_id;
	}
	// Verify this came from the our screen and with proper authorization,
	// because save_post can be triggered at other times.
	if ( ! isset( $_POST['woo_seat_show'] ) || ! wp_verify_nonce( $_POST['product_fields'], basename(__FILE__) ) ) {
		return $post_id;
	}
	
	// check autosave
  if ( wp_is_post_autosave( $post_id ) ){
	  return 'autosave';
  }

  //check post revision
  if ( wp_is_post_revision( $post_id ) ){
      return 'revision';
  }
	$key = 'woo_seat_show';
	$value = $_POST['woo_seat_show'];
	if ( get_post_meta( $post_id, $key, true ) ) {
		// If the custom field already has a value, update it.
		update_post_meta( $post_id, $key, $value );
	} else {
		// If the custom field doesn't have a value, add it.
		add_post_meta( $post_id, $key, $value);
	}

	if ( ! $value ) {
		// Delete the meta key if there's no value
		delete_post_meta( $post_id, $key );
	}
}
add_action( 'save_post', 'twwt_save_product_seat_meta', 1, 2 );

function twwt_add_post_meta_boxes_callback() {
	global $post;
	global $wpdb;
	
	$options = get_option( 'twwt_woo_settings' );
	$winner_selection = $options['winner'];
	$enable_livestrom = $options['enable_livestrom'];
	
	$product_id = $post->ID;
	$random_arr = array();
	
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
		$winner_id = get_post_meta( $product_id, 'tww_winner_id', true );
		?>
        <table style="width:100%" class="wp-list-table widefat fixed striped table-view-list pages">
        	<thead>
            	<tr><th width="56">Order ID</th>
                <th>Screen Name</th>
                <th>Fist Name</th>
                <th>Last Name</th>
                <th>Email</th>
                <th width="100">Phone</th>
                <th width="100">Seats</th>
                </tr>
            </thead>
        <?php
		foreach($order_ids as $order_id){
			$random_arr[] = $order_id;
			 $order = wc_get_order( $order_id );
			 if(!twwy_has_refunds_v2($order_id)){
			 $seats = "";
			 foreach ( $order->get_items() as $item_id => $item ) {
				 //$allmeta = $item->get_meta_data();
				 if( $item->get_product_id() == $product_id){
					 foreach ( $item->get_meta_data() as $itemvariation ) {
						if ( ! is_array( ( $itemvariation->value ) ) ) {
							$key = wc_attribute_label( $itemvariation->key );
							if($key == "Seat(s)"){
								$seats = '' . wc_attribute_label( $itemvariation->value ) . '';
							}
						}
					}
				 }
			 }
			 ?>
             <tr <?php if($winner_id==$order_id){echo 'bgcolor="#d1e4dd"';}?>>
             	<td><a target="_blank" href="post.php?post=<?php echo $order_id;?>&action=edit"><?php echo $order_id;?></a></td>
                <td><?php 
				
				$screen_name = get_post_meta( $order_id, '_screen_name', true );
				if($screen_name!=""){
					echo $screen_name;
				}
				else{
					$cuser = $order->get_user();
					if(!empty($cuser->display_name)){
						echo $cuser->display_name;
					}
					else{
						echo $order->get_billing_first_name();
					}
				}
				if($winner_id==$order_id){echo '<i class="dashicons dashicons-awards"></i>';}
				?></td>
                <td><?php echo $order->get_billing_first_name();?></td>
                <td><?php echo $order->get_billing_last_name();?></td>
                <td><?php echo $order->get_billing_email();?></td>
                <td><?php echo $order->get_billing_phone();?></td>
                <td><?php echo $seats;?></td>
               
             </tr>
             <?php
			 }
			 
		}
		?>
        </table>
        <div class="notice" id="tww_notice">
        </div>
        <?php 
		##echo '<pre>';
		##print_r( get_post_meta( $product_id, 'event_data', true ) );
		##echo '</pre>';
		if($winner_selection==1){
		$winner_selected_at = get_post_meta( $product_id, 'tww_winner_s_date', true );
		$winner_notification_at = get_post_meta( $product_id, 'tww_winner_n_date', true );
		
		
		shuffle($random_arr);
		?>
        <?php if($winner_id>0){
			echo '<p><strong>Winner selected at:</strong> '.$winner_selected_at.'<br>';
			echo '<strong>Notification sent at:</strong> '.$winner_notification_at.'</p>';
		}
		else{ ?>
        
        <p>
        <a href="<?php echo get_permalink(2613);?>?pid=<?php echo $product_id;?>" target="_blank" class="button button-primary">Select Attendee</a>
        </p>
        <script>
		function SelectWinner(){
			jQuery("#btn_select_winner").html('Please wait...').attr('disabled', 'disabled');
			jQuery.get('<?php echo home_url('/');?>?myaction=selectwinner&pid=<?php echo $product_id;?>&oid=<?php echo $random_arr[0];?>', function(data){
				location.reload();
			});
		}
		</script>
        
        <?php } 
		}
		?>
        <?php 
		if($enable_livestrom==1){
			$event_data = get_post_meta( $product_id, 'event_data', true );
			if(!empty($event_data)){
			echo '<p><strong>Event Name:</strong> '.$event_data->data->attributes->title.'<br>';
			echo '<strong>Event Time:</strong> '.date("Y-m-d H:i:s", $event_data->included[0]->attributes->estimated_started_at).'</p>';
			}
			if(get_post_meta( $product_id, 'event_created', true )==1){
			}
			else{
				?>
				<h3>Send Webinar to LiveStorm.</h3>
				<p><input type="text" size="50" id="tww_event_name" placeholder="Event Name" /></p>
				<p><input type="text" size="50" id="tww_event_time" placeholder="Starting Time" /> <br /><strong>Timezone:</strong> <?php echo get_option('timezone_string');?></p>
				
				<p>
				<button type="button" class="button button-primary" onclick="SendCustomer();" id="btn_send_customer">Send Webinar to LiveStorm</button>
				</p>
			   <script>
				jQuery(document).ready(function(e) {
					jQuery(document).ready(function(){
						var date = new Date();
						jQuery('#tww_event_time').datetimepicker({ dateFormat: 'yy-mm-dd', controlType: 'select', minDate:new Date(new Date(date).setHours(date.getHours() + 1)) });
					});
				});
				function SendCustomer(){
					var _n = jQuery('#tww_event_name').val();
					var _t = jQuery('#tww_event_time').val();
					if(_n==""){
						alert('Please enter event name');
						jQuery('#tww_event_name').focus();
						return false;
					}
					if(_t==""){
						alert('Please enter event time');
						jQuery('#tww_event_time').focus();
						return false;
					}
					
					
					jQuery("#btn_send_customer").html('Sending...').attr('disabled', 'disabled');
					jQuery.get('<?php echo home_url('/');?>?myaction=createevent&pid=<?php echo $product_id;?>&evn='+encodeURIComponent(_n)+'&evd='+encodeURIComponent(_t), function(data){
						var obj = JSON.parse(data);
						if(obj.errors){
							//alert(obj.errors[0].title);
							jQuery("#tww_notice").html(obj.errors[0].title).removeClass('notice-success').addClass('notice-error');
							jQuery("#btn_send_customer").html('Send Webinar to LiveStorm').removeAttr('disabled');
							return;
						}
						else{
							jQuery("#tww_notice").html('Event successfully created. Registering a participant to the event.').removeClass('notice-error').addClass('notice-success');
							SendCustomerToLiveStrom(<?php echo $product_id;?>);
						}
						///location.reload();
					});
				}
				
			   </script> 
				<?php
			}
		}
		?>
        
        <?php
	}
	else{
		echo '<p>No one bought this webinar.</p>';
	}
	?>
    <script>
	function SendCustomerToLiveStrom(_product_id){
		//var _lC = setInterval(function(){
			jQuery.get('<?php echo home_url('/');?>?myaction=sendcustomer&pid=<?php echo $product_id;?>', function(data){
				if(data==0){
					//clearInterval(_lC);
					location.reload();
				}
				else{
					setTimeout(function(){
						SendCustomerToLiveStrom(_product_id);
					},1000);
				}
			});
		//},1000);
	}
	</script>
    <?php
}
?>
