<?php
// Set up settings defaults
register_activation_hook(__FILE__, 'twwt_woo_set_options');
function twwt_woo_set_options (){
	$defaults = array(
		'license_key' => '',
		'login_restrict' => 0,
		'login_button_text' => 'Login',
		'enable_notification' => 0,
		'video_show' => 1,
		'winner' => 0,
		'enable_livestrom' => 0,
		'token' => '',
		'owner_id' => '',
		'copy_from_event_id' => '',
		'id' => '',
		'twilio_sid' => '',
		'twilio_token' => '',
		'twilio_from' => '',
	);
	add_option('twwt_woo_settings', $defaults);
}
// Clean up on uninstall
register_activation_hook(__FILE__, 'twwt_woo_deactivate');
function twwt_woo_deactivate(){
	delete_option('twwt_woo_settings');
}


// Render the settings page
class twwt_woo_settings_page {
	// Holds the values to be used in the fields callbacks
	private $options;
			
	// Start up
	public function __construct() {
			add_action( 'admin_menu', array( $this, 'add_plugin_page' ) );
			add_action( 'admin_init', array( $this, 'page_init' ) );
	}
			
	// Add settings page
	public function add_plugin_page() {
		//add_submenu_page('edit.php?post_type=twwt_woo', 'Settings',  'Settings', 'manage_options', 'twabc-settings', array($this,'create_admin_page'));
		add_menu_page( 'Woo Ticket', 'Woo Ticket', 'administrator', 'wooticket', array($this,'create_admin_page'), 'dashicons-tickets-alt' );
	}
			
			
	// Options page callback
	public function create_admin_page() {
		// Set class property
		$this->options = get_option( 'twwt_woo_settings' );
		if(!$this->options){
			twwt_woo_set_options ();
			$this->options = get_option( 'twwt_woo_settings' );
		}
		?>
		<div class="wrap">
		
					 
				<form method="post" action="options.php">
				<?php
						settings_fields( 'twwt_woo_settings' );   
						do_settings_sections( 'twwt_woo-settings' );
						submit_button(); 
				?>
				</form>
		</div>
		<?php
	}
			
	// Register and add settings
	public function page_init() {		
		register_setting(
				'twwt_woo_settings', // Option group
				'twwt_woo_settings', // Option name
				array( $this, 'sanitize' ) // Sanitize
		);
		
        // Sections
		add_settings_section(
				'twwt_woo_settings_behaviour', // ID
				'Woo Ticket Basic Settings', // Title
				array( $this, 'twwt_woo_settings_behaviour_header' ), // Callback
				'twwt_woo-settings' // Page
		);
		// Sections #2
		add_settings_section(
				'twwt_woo_settings_livestrom', // ID
				'Livestrom Settings', // Title
				array( $this, 'twwt_woo_settings_livestrom_header' ), // Callback
				'twwt_woo-settings' // Page
		);
		// Sections #3
		add_settings_section(
				'twwt_woo_settings_twilio', // ID
				'Twilio Settings', // Title
				array( $this, 'twwt_woo_settings_twilio_header' ), // Callback
				'twwt_woo-settings' // Page
		);
		
        
		// Behaviour Fields
		add_settings_field(
				'license_key', // ID
				'License Key', // Title
				array( $this, 'license_key_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_behaviour' // Section
		);
		add_settings_field(
				'login_restrict', // ID
				'Login Restriction', // Title
				array( $this, 'login_restrict_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_behaviour' // Section
		);
		add_settings_field(
				'login_button_text', // ID
				'Login Button Text', // Title
				array( $this, 'login_button_text_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_behaviour' // Section
		);
		add_settings_field(
				'winner', // ID
				'Enable Winner Selection', // Title
				array( $this, 'winner_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_behaviour' // Section
		);
		add_settings_field(
				'enable_notification', // ID
				'Enable Notification', // Title
				array( $this, 'enable_notification_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_behaviour' // Section
		);
		add_settings_field(
				'video_show', // ID
				'Show Video', // Title
				array( $this, 'video_show_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_behaviour' // Section
		);
		add_settings_field(
				'enable_livestrom', // ID
				'Enable Livestrom', // Title
				array( $this, 'enable_livestrom_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_livestrom' // Section
		);
		add_settings_field(
				'token', // ID
				'Token', // Title
				array( $this, 'token_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_livestrom' // Section
		);
		add_settings_field(
				'owner_id', // ID
				'Owner ID', // Title
				array( $this, 'owner_id_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_livestrom' // Section
		);
		add_settings_field(
				'copy_from_event_id', // ID
				'Copy From Event ID', // Title
				array( $this, 'copy_from_event_id_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_livestrom' // Section
		);
		
		add_settings_field(
				'twilio_sid', // ID
				'SID', // Title
				array( $this, 'twilio_sid_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_twilio' // Section
		);
		add_settings_field(
				'twilio_token', // ID
				'Token', // Title
				array( $this, 'twilio_token_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_twilio' // Section
		);
		add_settings_field(
				'twilio_from', // ID
				'From Number', // Title
				array( $this, 'twilio_from_callback' ), // Callback
				'twwt_woo-settings', // Page
				'twwt_woo_settings_twilio' // Section
		);
			 
	}
			
	// Sanitize each setting field as needed -  @param array $input Contains all settings fields as array keys
	public function sanitize( $input ) {
		$new_input = array();
		foreach($input as $key => $var){
			$new_input[$key] = sanitize_text_field( $input[$key] );
			if($key=="license_key"){
				delete_transient( 'twwt_license_key_request' );
				twwt_check_license_key($input[$key]);
				
			}
		}
		return $new_input;
	}
			
	// Print the Section text
	public function twwt_woo_settings_behaviour_header() {
            echo '<p>'.__('Settings for Woo Ticket.', 'twwt_woo-settings').'</p>';
	}
	public function twwt_woo_settings_livestrom_header() {
            echo '<p>'.__('Settings for Livestrom.', 'twwt_woo-settings').'</p>';
	}
	public function twwt_woo_settings_twilio_header() {
            echo '<p>'.__('Settings for Twilio.', 'twwt_woo-settings').'</p>';
	}
	public function license_key_callback(){
		echo '<input type="text" id="license_key" name="twwt_woo_settings[license_key]" size="50" value="'.$this->options['license_key'].'" />';
	}
	public function login_restrict_callback() {
			if(isset( $this->options['login_restrict'] ) && $this->options['login_restrict'] == 1){
			$yes = ' selected="selected"';
			$no = '';
		} else {
			$yes = '';
			$no = ' selected="selected"';
		}
		echo '<select id="login_restrict" name="twwt_woo_settings[login_restrict]">
			<option value="1"'.$yes.'>Yes</option>
			<option value="0"'.$no.'>No</option>
		</select>';
	}
	public function login_button_text_callback(){
		echo '<input type="text" id="login_button_text" name="twwt_woo_settings[login_button_text]" size="50" value="'.$this->options['login_button_text'].'" />';
	}
	public function winner_callback(){
		if(isset( $this->options['winner'] ) && $this->options['winner'] == 1){
			$yes = ' selected="selected"';
			$no = '';
		} else {
			$yes = '';
			$no = ' selected="selected"';
		}
		echo '<select id="winner" name="twwt_woo_settings[winner]">
			<option value="1"'.$yes.'>Yes</option>
			<option value="0"'.$no.'>No</option>
		</select>';
	}
	public function enable_notification_callback(){
		if(isset( $this->options['enable_notification'] ) && $this->options['enable_notification'] == 1){
			$yes = ' selected="selected"';
			$no = '';
		} else {
			$yes = '';
			$no = ' selected="selected"';
		}
		echo '<select id="enable_notification" name="twwt_woo_settings[enable_notification]">
			<option value="1"'.$yes.'>Yes</option>
			<option value="0"'.$no.'>No</option>
		</select>';
	}
	public function video_show_callback(){
		if(isset( $this->options['video_show'] ) && $this->options['video_show'] == 1){
			$yes = ' selected="selected"';
			$no = '';
		} else {
			$yes = '';
			$no = ' selected="selected"';
		}
		echo '<select id="video_show" name="twwt_woo_settings[video_show]">
			<option value="1"'.$yes.'>To All</option>
			<option value="0"'.$no.'>Purchased Customer</option>
		</select>';
	}
	public function enable_livestrom_callback(){
		if(isset( $this->options['enable_livestrom'] ) && $this->options['enable_livestrom'] == 1){
			$yes = ' selected="selected"';
			$no = '';
		} else {
			$yes = '';
			$no = ' selected="selected"';
		}
		echo '<select id="enable_livestrom" name="twwt_woo_settings[enable_livestrom]">
			<option value="1"'.$yes.'>Yes</option>
			<option value="0"'.$no.'>No</option>
		</select>';
	}
	public function token_callback(){
		echo '<input type="password" id="token" name="twwt_woo_settings[token]" value="'.$this->options['token'].'" style="width:100%;" />';
	}
	public function owner_id_callback(){
		echo '<input type="text" id="owner_id" name="twwt_woo_settings[owner_id]" size="50" value="'.$this->options['owner_id'].'" />';
	}
	public function copy_from_event_id_callback(){
		echo '<input type="text" id="copy_from_event_id" name="twwt_woo_settings[copy_from_event_id]" size="50" value="'.$this->options['copy_from_event_id'].'" />';
	}
	public function twilio_sid_callback(){
		echo '<input type="text" id="twilio_sid" name="twwt_woo_settings[twilio_sid]" size="50" value="'.$this->options['twilio_sid'].'" />';
	}
	public function twilio_token_callback(){
		echo '<input type="text" id="twilio_token" name="twwt_woo_settings[twilio_token]" size="50" value="'.$this->options['twilio_token'].'" />';
	}
	public function twilio_from_callback(){
		echo '<input type="text" id="twilio_from" name="twwt_woo_settings[twilio_from]" size="50" value="'.$this->options['twilio_from'].'" />';
	}
}

if( is_admin() ){
		$twwt_woo_settings_page = new twwt_woo_settings_page();
}
