<?php 
if (!defined('ABSPATH')) 
exit; // Exit if accessed directly

if (!class_exists('Minbazaar_Registration_Attributes_Front')){ 

class Minbazaar_Registration_Attributes_Front extends Minbazaar_Registration_Attributes {
	public function __construct() {

		//add_action( 'wp_loaded', array( $this, 'front_scripts' ) );
		$this->module_settings = $this->get_module_settings();
		add_action( 'woocommerce_register_form', array($this, 'minbazaar_extra_registration_form_end' ));
		add_action( 'woocommerce_register_post', array($this, 'minbazaar_validate_extra_register_fields'), 10, 3 );
		add_action( 'woocommerce_created_customer', array($this, 'minbazaar_save_extra_register_fields' ));
		//add_action('woocommerce_before_my_account', array($this, 'fme_my_profile'));
		/**add_action( 'init', array($this, 'add_fmera_query_vars' ));
		add_action( 'template_include', array( $this, 'change_template' ) );
		**/

		if (isset($_POST['action'])) {
			if ($_POST['action'] == 'SubmitRegForm') {
				$this->submit_reg_edit_form($_POST['user_id']);
			} 
		}
	}


	function minbazaar_extra_registration_form_end() { 
			$fields = $this->get_fields();

				?>

					<p class="form-row">
					<label for="reg_mobile"><?php esc_attr_e( 'Mobile Number', 'woocommerce' ); ?> 
					 <span class="required">*</span> 
					</label>
					<input type="text" class="input-text" name="reg_mobile" id="reg_mobile" value="<?php if ( ! empty( $_POST['reg_mobile'] ) ) esc_attr_e( $_POST['reg_mobile'] ); ?>" placeholder="Mobile Number" />
					</p>
					<div id="min_request_otp_div">
        <button type="button" name="min_request_otp" id="min_request_otp" class="btn btn-default button button-medium" onclick="min_perform_otp_task()">
                    <span>Validate Mobile<i class="fa fa-chevron-right right"></i></span>
                </button>
        <input type="hidden" id="min_timeout_otp" name="min_timeout_otp" value={$mobilenumberregister_otp_validity}>
    </div>

															
	<?php }




	function minbazaar_validate_extra_register_fields($username, $email, $validation_errors) {


			if (isset($_POST['reg_mobile']) && empty($_POST['reg_mobile'])) {
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile Number is required!', 'woocommerce' ) );
			}else if(isset($_POST['reg_mobile']) && ( strlen($_POST['reg_mobile']) > 10 || !preg_match('/^[0-9]{10}$/', $_POST['reg_mobile']) )){
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile Number is not valid', 'woocommerce' ) );
			}else if(isset($_POST['reg_mobile']) && $this->mobile_already_exists($_POST['reg_mobile'])){
				$validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Mobile Number already registered', 'woocommerce' ) );
			}
	}

	function mobile_already_exists($mobile){
		global $wpdb;
		$user_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix."usermeta WHERE meta_key = 'Mobile Number' and meta_value = '%s'", $mobile));


		if($user_count > 0)
			return true;
		else
			return false;
	}


	function submit_reg_edit_form($user_id) {
			if ( isset( $_POST[$field->field_name] ) && empty( $_POST[$field->field_name] ) && ($field->is_required == 1)) {
				$this->fmera_errors()->add( $field->field_name.'_error', __( $field->field_label.' is required!', 'woocommerce' ) );
			} else {
				if ( isset( $_POST['reg_mobile'] ) || isset( $_FILES['reg_mobile'] ) ) {
					update_user_meta( $user_id, 'Mobile Number', sanitize_text_field( $_POST['reg_mobile'] ) );
				}
			}
	}

	function minbazaar_save_extra_register_fields($customer_id) {

		$fields = $this->get_fields();
			if ( isset( $_POST['reg_mobile'] ) || isset( $_FILES['reg_mobile'] ) ) {
				update_user_meta( $customer_id, 'Mobile Number', sanitize_text_field( $_POST['reg_mobile'] ) );
			}
	}



	function get_fields() {
		global $wpdb;

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->fmera_fields." WHERE field_type!='' AND type = %s ORDER BY length(sort_order), sort_order", 'registration'));      
		return $result;
	}



	function fme_my_profile() { ?>
		<div class="col2-set addresses">
			<header class="title">
			<h3><?php echo esc_attr_e($this->module_settings['profile_title']); ?></h3>
			<?php $profile_url = wc_get_endpoint_url( 'edit-profile', get_current_user_id(), wc_get_page_permalink( 'myaccount' ) ); ?>
			<a class="edit" href="<?php echo $profile_url; ?>">Edit</a>
			</header>
			</div>
			<table class="shop_table shop_table_responsive my_account_orders">
			<tbody>
			<?php 
			$user_id = get_current_user_id();
		$fields =  $this->get_fields();
		foreach ($fields as $field) {

			$check = get_user_meta( $user_id, $field->field_name, true );
			$label = $this->get_fieldByName($field->field_name);
			if($check!='') {

				$value = get_user_meta( $user_id, $field->field_name, true );
				?>
					<tr class="order" style="text-align:left">
					<td style="width:30%;"><b><?php echo $label->field_label; ?></b></td>
					<td>
					<?php 
					if($label->field_type=='checkbox' && $value==1) { 
						echo "Yes";
					} else if($label->field_type=='checkbox' && $value==0) {
						echo "No";
					} else if($label->field_type=='select' || $label->field_type=='radioselect') { 
						$meta = $this->get_OptionByid($value, $label->field_id);
						echo $meta->meta_value;
					} else
					{
						echo $value;
					}
				?>
					</td>
					</tr>

					<?php }
		}

		?>

			</tbody>
			</table>

			<?php }


}
}


new Minbazaar_Registration_Attributes_Front();

?>
