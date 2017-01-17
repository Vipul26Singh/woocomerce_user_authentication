<?php 

/*
 * Plugin Name:       User Authentication Using Mobile(Free)
 * Plugin URI:        https://github.com/Vipul26Singh/woocomerce_user_authentication
 * Description:       You can avoid fake users registration using this mobile as this will authenticate user mobile number 
 * Version:           0.0.0
 * Author:            Minbazaar 
 * Developed By:      Vipul Singh
 * Author URI:        
 * Support:	      Leave a comment on github 
 * License:           GPL-2.0+
 * License URI:       http://www.gnu.org/licenses/gpl-2.0.txt
 */

if (!defined('ABSPATH')) 
exit; // Exit if accessed directly

/**
 * Check if WooCommerce is active
 **/

if ( !in_array( 'woocommerce/woocommerce.php', apply_filters( 'active_plugins', get_option( 'active_plugins' ) ) ) ) {
	exit;
}

if(!class_exists('Minbazaar_Registration_Attributes')){ 


class Minbazaar_Registration_Attributes{
	public $module_settings = array();
	public $module_default_settings = array();

	 function __construct() {
		$this->module_constants();

		if(is_admin()){
			register_activation_hook( __FILE__, array( $this, 'install_module' ) );
			$this->module_default_settings = $this->get_module_default_settings();
			add_filter( 'extra_plugin_headers', array($this, 'minbazaar_extra_plugin_headers' ));
		} else {
			require_once( MINBUA_PLUGIN_DIR . 'front/minbazaar_registration_attributes_front.php' );
		}
	}

	function minbazaar_extra_plugin_headers($headers) {
		$headers['support'] = 'Support';
		return $headers;	
	}

	public function module_constants() {
		if(!defined('MINBUA_URL'))
			define('MINBUA_URL', plugin_dir_url(__FILE__));

		if(!defined('MINBUA_BASENAME'))
			define('MINBUA_BASENAME', plugin_basename(__FILE__));

		if(!defined('MINBUA_PLUGIN_DIR'))
			define('MINBUA_PLUGIN_DIR', plugin_dir_path(__FILE__));

		if(!defined('MINBUA_OTP_TIMEOUT'))
			define('MINBUA_OTP_TIMEOUT', 120);
	}



	public function set_module_default_settings() {
		$module_settings = get_option( 'minbua_settings' );
		if ( !$module_settings ) {
			update_option( 'minbua_settings', $this->module_default_settings );
		}
	}

	public function get_module_default_settings() {
		$module_default_settings = array (
				'profile_title'  => __( 'Profile Info', 'minbua' ),
				'account_title'  => __( 'Account Info', 'minbua' ),

				); 
		return $module_default_settings;
	}

	public function get_module_settings() {

		$module_settings = get_option( 'minbua_settings' );

		if ( !$module_settings ) {
			update_option( 'minbua_settings', $this->module_default_settings );
			$module_settings = $this->module_default_settings;
		}
		return $module_settings;
	} 
}
}

$minbua = new Minbazaar_Registration_Attributes();



?>

