<?php
add_action( 'post_submitbox_misc_actions', 'add_notfication_sender_button', 10, 2 );

function add_notfication_sender_button( $post_obj ) {
	//post or whatever custom post type you'd like to add the text
	if ( 'product' === $post_obj->post_type && 'publish' === $post_obj->post_status ) {
		$post_id = $post_obj->ID;
		$notification_sent = get_post_meta($post_id, 'twwt_np_notification_sent', true);
		if($notification_sent==1){
		}
		else{
			$np_post_id = get_option( 'twwt_np_notification_auto_start_post_id' );
			if($np_post_id>0){
				?>
                <div class="misc-pub-section"><p>Notify your customer about the arrival of new webinar/event.</p>
                <p>Please wait a process is already runing.</p></div>
                <?php
			}
			else{
			?>
            <div class="misc-pub-section"><p >Notify your customer about the arrival of new webinar/event.</p>
            <p ><label><input type="checkbox" name="twwt_np_notification_sent" value="1" />Send Notification</label></p></div>
            <?php
			}
		}
	}
}

add_action('save_post', 'save_post_send_notfication');

function save_post_send_notfication($post_id){
	if (defined('DOING_AUTOSAVE') && DOING_AUTOSAVE) {
        return;
    }
    if (!current_user_can('edit_post', $post_id)) {
        return;
    }
	
    if (isset($_POST['twwt_np_notification_sent'])) {
		add_option('twwt_np_notification_auto_start_post_id', $post_id);
        update_post_meta($post_id, 'twwt_np_notification_sent', 1);
    }
}

//add_action( 'twwt_cron_hook', 'twwt_send_np_notification' );
function twwt_send_np_notification(){
	
	$np_post_id = get_option( 'twwt_np_notification_auto_start_post_id' );
	
	if($np_post_id>0){
		$product_name = get_the_title($np_post_id);
		$url = get_the_permalink($np_post_id);
		
		$customers = get_users( array( 'role' => 'customer' ) );
		foreach($customers as $customer){
			$opt_notification = get_user_meta( $customer->ID, 'twwy_opt_notification' , true );
			//if($opt_notification==1){
				$email = $customer->data->user_email;

				$phone = get_user_meta( $customer->ID, 'billing_phone' , true );
				$first_name = get_user_meta( $customer->ID, 'first_name' , true );
				$unsubscribe_notification_email = get_user_meta( $customer->ID, 'unsubscribe_notification_email' , true );
				//echo $email.' '.$phone.'<br>';
				if($unsubscribe_notification_email==1){

				}
				else{
					twwt_npo_email_send($email, $first_name, $product_name, $url);
				}

				####
				$twilio_msg = "Hello, {$first_name}, A new webinar is live: {$product_name}. Book your seat at this link: {$url}";
				wp_twilio_sms($phone, $twilio_msg);
				sleep(1);
			//} //END IF OPTIN
		}
		
		delete_option( 'twwt_np_notification_auto_start_post_id' );
	}
}

function twwt_npo_email_send($email, $first_name, $product_name, $url){
	global $woocommerce;
	$mailer = $woocommerce->mailer();
	
	
	$email_heading = 'A new webinar is live '.$product_name;
	ob_start();
	wc_get_template( 'emails/email-header.php', array( 'email_heading' => $email_heading ) );
	echo "<p>Hello {$first_name},<br>";
	echo 'A new webinar is live: '.$product_name.'</p>';
	echo "<p>Book your seat at this link: {$url}</p>
<p>Thank you,<br>
Red Lion Tactics</p>";
	wc_get_template( 'emails/email-footer.php' );
	echo '<p style="text-align:center;margin-top:0;">If you would prefer not receiving our emails Please <a href="'.home_url('/?unsub=email&email='.urlencode($email)).'">unsubscribe</a></p>';
	$message = ob_get_clean();
	$subject = $email_heading;
	return $mailer->send( $email, $subject, $message);
	//return true;
}

add_action('init', 'mt_unsubscribe');
function mt_unsubscribe(){
    if(@$_GET['unsub']=="email"){
        $email = $_GET['email'];
        $user = get_user_by( 'email', $email );
        $user_id = $user->ID;
        update_user_meta( $user_id, 'unsubscribe_notification_email', 1);
        if ( wp_redirect( home_url('/?alertmsg='.urlencode('You are successfully unsubscribed.')) ) ) {
            exit;
        }
    }
}




