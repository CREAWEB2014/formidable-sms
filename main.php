<?php
class Clockwork_Formidable_Plugin extends Clockwork_Plugin {

  protected $plugin_name = 'Formidable';
  protected $language_string = 'formidable_sms';
  protected $prefix = 'clockwork_formidable';
  protected $folder = '';
  
  protected $forms = array();
  
  /**
   * Constructor: setup callbacks and plugin-specific options
   *
   * @author James Inman
   */
  public function __construct() {
    parent::__construct();
    
    // Set the plugin's Clockwork SMS menu to load the contact forms
    $this->plugin_callback = array( $this, 'clockwork_formidable' );
    $this->plugin_dir = basename( dirname( __FILE__ ) );
    
    // Get forms
    $this->forms = $this->get_forms();
    
    // Setup callbacks
    add_filter( 'frm_after_create_entry', array( $this, 'send_sms_notification' ), 30 );
  }
  
  /**
   * Setup the admin navigation
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_navigation() {
    parent::setup_admin_navigation();
  }
  
  /**
   * Setup HTML for the admin <head>
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_head() {
    echo '<link rel="stylesheet" type="text/css" href="' . plugins_url( 'css/clockwork.css', __FILE__ ) . '">';
  }
  
  /**
   * Register the settings for this plugin
   *
   * @return void
   * @author James Inman
   */
  public function setup_admin_init() {
    parent::setup_admin_init();
    
  	register_setting( 'clockwork_formidable', 'clockwork_formidable', array( &$this, 'validate_options' ) );
    add_settings_section( 'clockwork_formidable', __('Default Settings', 'clockwork_formidable'), array( &$this, 'settings_header' ), 'clockwork_formidable' );
  	add_settings_field( 'default_to', __('Send To Number', 'clockwork_formidable'), array( &$this, 'settings_default_to' ), 'clockwork_formidable', 'clockwork_formidable' );
  }
  
  /**
   * Function to provide a callback for the main plugin action page
   *
   * @return void
   * @author James Inman
   */
  public function clockwork_formidable() {
    $this->render_template( 'formidable-options' );
  }
  
  /**
   * Output the header paragraph for the settings
   *
   * @return void
   * @author James Inman
   */
  public function settings_header() {
    echo '<p>' . __( 'Default settings are applied to all your forms unless you set more specific options below.', 'clockwork_formidable' ) . '</p>';
  }
  
  /**
   * Shows the input field for the 'default to' box
   *
   * @return void
   * @author James Inman
   */
  public function settings_default_to() {
  	$options = get_option( 'clockwork_formidable' );
  	echo '<input id="formidable_sms_username" name="clockwork_formidable[default_to]" size="40" type="text" value="' . $options['default_to'] . '" style="padding: 3px;" />';
  }
  
  /**
   * Validates the main options
   *
   * @param string $input 
   * @return void
   * @author James Inman
   */
  public function validate_options( $input ) {
		$options = get_option( 'clockwork_formidable' );
		$options['default_to'] = trim( $input['default_to'] );
    
    foreach( $this->forms as $form )
    {
      $form_options = array();
      if( $_POST['active'][$form->id] == '1' )
      { 
        $form_options['active'] = 1;
        $form_options['to'] = $_POST['to'][$form->id];
        $form_options['message'] = $_POST['message'][$form->id];
      }
      else
      {
        $form_options['active'] = 0;
      }
      
      update_option( 'clockwork_formidable_form_' . $form->id, $form_options );
    }
        
		return $options;
  }
  

  /**
   * Send SMS on contact form submission
   *
   * @param object $form Contact form to send  
   * @return void
   * @author James Inman
   */
  public function send_sms_notification( $entry_id = null, $form_id = null ) {
    $entry = $this->get_entry( $entry_id );
    $form = $this->get_form( $entry->form_id );
    $options = array_merge( get_option( 'clockwork_options' ), get_option( 'clockwork_formidable_form_' . $form->id ) );
    
    $phone = explode( ',', $options['to'] );
    $message = $options['message'];
    
    if( $options['active'] == '1' && !empty( $phone ) )
    {    
      try {
        $clockwork = new WordPressClockwork( $options['api_key'] );
        $messages = array();
        foreach( $phone as $to ) {
          $messages[] = array( 'from' => $options['from'], 'to' => $to, 'message' => $message );          
        }
        $result = $clockwork->send( $messages );
      } catch( ClockworkException $e ) {
        $result = "Error: " . $e->getMessage();
      } catch( Exception $e ) { 
        $result = "Error: " . $e->getMessage();
      }
    }
  }
  
  /**
   * Retrieve an entry from an entry ID
   *
   * @param string $entry_id 
   * @return void
   * @author James Inman
   */
  protected function get_entry( $entry_id ) {
    global $wpdb;
    $entry = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "frm_items WHERE id = %d", $entry_id ) );
    return $entry;
  }
  
  /**
   * Retrieve a form from a form ID
   *
   * @param string $form_id 
   * @return void
   * @author James Inman
   */
  protected function get_form( $form_id ) {
    global $wpdb;
    $form = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM " . $wpdb->prefix . "frm_forms WHERE id = %d", $form_id ) );
    return $form;
  }
  
  /**
   * Retrieve a list of forms
   *
   * @return void
   * @author James Inman
   */
  protected function get_forms() {
    global $wpdb;
    $forms = $wpdb->get_results( "SELECT * FROM " . $wpdb->prefix . "frm_forms WHERE is_template = 0" );
    return $forms;    
  }
  
  /**
   * Check if username and password have been entered
   *
   * @return void
   * @author James Inman
   */
  public function get_existing_username_and_password() { }
  
}

$cp = new Clockwork_Formidable_Plugin();
