<?php 
if (!defined('ABSPATH')) 
exit; // Exit if accessed directly

if (!class_exists('Minbazaar_Registration_Attributes_Front')){ 

class Minbazaar_Registration_Attributes_Front extends Minbazaar_Registration_Attributes {
	public function __construct() {

		add_action( 'wp_loaded', array( $this, 'front_scripts' ) );
		$this->module_settings = $this->get_module_settings();
		add_action( 'woocommerce_register_form', array($this, 'minbazaar_extra_registration_form_end' ));
		add_action( 'woocommerce_register_post', array($this, 'minbazaar_validate_extra_register_fields'), 10, 3 );
		add_action( 'woocommerce_created_customer', array($this, 'minbazaar_save_extra_register_fields' ));
		/**add_action( 'init', array($this, 'add_fmera_query_vars' ));
		add_action( 'template_include', array( $this, 'change_template' ) );
		**/

		if (isset($_POST['action'])) {
			if ($_POST['action'] == 'SubmitRegForm') {
				$this->submit_reg_edit_form($_POST['user_id']);
			} 
		}
	}

	public function front_scripts() {
		wp_enqueue_script( 'jquery-ui');
                wp_enqueue_script( 'minbua-front-jsssssss', plugins_url( '/js/javascript.js', __FILE__ ), array('jquery'), false );
	
                wp_enqueue_style( 'minbua-front-css', plugins_url( '/css/minbua_style_front.css', __FILE__ ), false );
                wp_enqueue_style( 'jquery-ui-css');
        }


	function minbazaar_extra_registration_form_end() { 

				?>

					<p class="form-row">
						<label for="reg_mobile"><?php esc_attr_e( 'Mobile Number', 'woocommerce' ); ?> 
					 		<span class="required">*</span> 
						</label>
						<input type="text" class="input-text" name="reg_mobile" id="reg_mobile" value="<?php if ( ! empty( $_POST['reg_mobile'] ) ) esc_attr_e( $_POST['reg_mobile'] ); ?>" placeholder="Mobile Number" />
					</p>
					
					<div id="min_otp_value_div" class="input-text" style="display:none">
						<p class="form-row">
							<label for="otp_val"><?php esc_attr_e( 'Enter OTP', 'woocommerce' ); ?>
								<span class="required">*</span>
							</label>
        						<input type="input" class="input-text" name="min_otp_value" id="min_otp_value" value="<?php if ( ! empty( $_POST['min_otp_value'] ) ) esc_attr_e( $_POST['min_otp_value'] ); ?>" placeholder="One Time Password" />
						</p>
    					</div>
					<div class='inner-otp-error woocommerce-error' id='inner-otp-error' style='display:none'> </div>

    					<div id="min_progress_bar_div" style="display:none">
        					<p class="input-text">Wait for some time before resending One time passsword</p>
        					<div  class="w3-progress-container">
          						<div id="min_progress_bar" class="w3-progressbar w3-green" style="width:1%"></div>
        					</div>
    					</div>
					<div id="min_request_otp_div">
                                                <button type="button" name="min_request_otp" id="min_request_otp" class="btn btn-default button button-medium" onclick="min_perform_otp_task()">
                                                        <span id="initial_button_text" >Validate Mobile <i class="fa fa-chevron-right right"></i></span>
							<span id="final_button_text" style="display:none">Resend OTP <i class="fa fa-chevron-right right"></i></span>
                                                </button>
                                        </div>
    					<br>

															
	<?php }




	function minbazaar_validate_extra_register_fields($username, $email, $validation_errors) {


			if (!isset($_POST['reg_mobile']) || empty($_POST['reg_mobile'])) {
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile Number is required!', 'woocommerce' ) );
			}else if( strlen($_POST['reg_mobile']) > 10 || !preg_match('/^[0-9]{10}$/', $_POST['reg_mobile']) ){
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile Number is not valid', 'woocommerce' ) );
			}else if( $this->mobile_already_exists($_POST['reg_mobile'])){
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile Number already registered', 'woocommerce' ) );
			}else if(!isset($_POST['min_otp_value'])){
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Please Validate Mobile before procceeding', 'woocommerce' ) );	
			}else if(!$this->valid_otp($_POST['reg_mobile'], $_POST['min_otp_value'])){
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile authentication failed. Please enter correct OTP', 'woocommerce' ) );
			}else{
				$this->delete_otp($_POST['reg_mobile']);
			}
	}

	function delete_otp($mobile){
                global $wpdb;
                $wpdb->query( $wpdb->prepare("DELETE FROM ".$wpdb->minbazaar_otp." WHERE mobile_number = '%s' or transaction_date < DATE_SUB(NOW(), INTERVAL 5 HOUR)", $mobile));
        }

	function mobile_already_exists($mobile){
		global $wpdb;
		$user_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."usermeta WHERE meta_key = 'mobile_number' and meta_value = '%s'", $mobile));


		if($user_count > 0)
			return true;
		else
			return false;
	}

	function valid_otp($mobile, $OTP){
		return true;
                global $wpdb;
                $otp_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->minbazaar_otp . "WHERE mobile_number = '%s' and otp_value = '%s'", $mobile, $otp));

                if($otp_count > 0)
                        return true;
                else
                        return false;
        }


	function submit_reg_edit_form($user_id) {
			if ( isset( $_POST[$field->field_name] ) && empty( $_POST[$field->field_name] ) && ($field->is_required == 1)) {
				$this->fmera_errors()->add( $field->field_name.'_error', __( $field->field_label.' is required!', 'woocommerce' ) );
			} else {
				if ( isset( $_POST['reg_mobile'] ) || isset( $_FILES['reg_mobile'] ) ) {
					update_user_meta( $user_id, 'mobile_number', sanitize_text_field( $_POST['reg_mobile'] ) );
				}
			}
	}

	function minbazaar_save_extra_register_fields($customer_id) {

			if ( isset( $_POST['reg_mobile'] ) || isset( $_FILES['reg_mobile'] ) ) {
				update_user_meta( $customer_id, 'mobile_number', sanitize_text_field( $_POST['reg_mobile'] ) );
			}
	}




}
}


new Minbazaar_Registration_Attributes_Front();

?>
