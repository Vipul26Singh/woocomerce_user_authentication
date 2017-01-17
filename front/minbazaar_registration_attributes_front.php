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

		/**if (isset($_POST['action'])) {
			if ($_POST['action'] == 'SubmitRegForm') {
				$this->submit_reg_edit_form($_POST['user_id']);
			} 
		}**/
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
															
	<?php }



	function fme_extra_registration_form_edit($user_id) {  ?>

		<h3><?php echo esc_attr_e($this->module_settings['profile_title']); ?></h3>
			<?php $this->fmera_show_error_messages(); ?>

			<?php 

			$fields = $this->get_fields();
		foreach ($fields as $field) { 

			$value = get_user_meta( $user_id, $field->field_name, true );

			if($field->field_type == 'text' && $field->is_hide == 0) {
				?>

					<p class="form-row <?php echo $field->width; ?>">
					<label for="<?php echo $field->field_name; ?>"><?php _e( $field->field_label, 'woocommerce' ); ?> 
					<?php if($field->is_required == 1) { ?> <span class="required">*</span> <?php } ?>
					</label>
					<input type="text" class="input-text" name="<?php echo $field->field_name; ?>" id="<?php echo $field->field_name; ?>" value="<?php echo $value; ?>" placeholder="<?php echo $field->field_placeholder; ?>" />
					</p>

					<?php } else if($field->field_type == 'textarea' && $field->is_hide == 0) { ?>

						<p class="form-row <?php echo $field->width; ?>">
							<label for="<?php echo $field->field_name; ?>"><?php _e( $field->field_label, 'woocommerce' ); ?> 
							<?php if($field->is_required == 1) { ?> <span class="required">*</span> <?php } ?>
							</label>
							<textarea name="<?php echo $field->field_name; ?>" id="<?php echo $field->field_name; ?>" class="input-text" cols="5" rows="2" placeholder="<?php echo $field->field_placeholder; ?>"><?php echo $value; ?></textarea>

							</p>

							<?php } else if($field->field_type == 'select' && $field->is_hide == 0) { ?>

								<p class="form-row <?php echo $field->width; ?>">
									<label for="<?php echo $field->field_name; ?>"><?php _e( $field->field_label, 'woocommerce' ); ?> 
									<?php if($field->is_required == 1) { ?> <span class="required">*</span> <?php } ?>
									</label>
									<select name="<?php echo $field->field_name; ?>" id="<?php echo $field->field_name; ?>">
									<?php $options = $this->getSelectOptions($field->field_id);
								foreach($options as $option) {
									?>
										<option value="<?php echo $option->meta_key; ?>"  <?php if($option->meta_key == $value) { echo "selected"; } ?>>
										<?php echo $option->meta_value; ?>
										</option>

										<?php } ?>

										</select>

										</p>

										<?php } else if($field->field_type == 'checkbox' && $field->is_hide == 0) { ?>

											<p class="form-row <?php echo $field->width; ?>">

												<input type="checkbox" name="<?php echo $field->field_name; ?>" value="<?php echo $value; ?>" <?php if($value == 1) { echo "checked"; } ?> class="input-checkbox">

												<?php esc_attr_e( $field->field_label, 'woocommerce' ); ?> 
												<?php if($field->is_required == 1) { ?> <span class="required">*</span> <?php } ?>

												</p>

												<?php } else if($field->field_type == 'radioselect' && $field->is_hide == 0) { ?>

													<p class="form-row <?php echo $field->width; ?>">
														<label for="<?php echo $field->field_name; ?>"><?php esc_attr_e( $field->field_label, 'woocommerce' ); ?> 
														<?php if($field->is_required == 1) { ?> <span class="required">*</span> <?php } ?>
														</label>
														<?php $options = $this->getSelectOptions($field->field_id);
													foreach($options as $option) {
														?>

															<input type="radio" name="<?php echo $field->field_name; ?>" value="<?php echo $option->meta_key; ?>" <?php if($option->meta_key == $value) { echo "checked"; } ?> class="input-checkbox"> <?php echo $option->meta_value; ?>

															<?php } ?>
															</p>

															<?php } ?> 

															<?php } ?>

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
		return false;
	}

	function fmera_errors() {
		static $wp_error; // Will hold global variable safely
		return isset($wp_error) ? $wp_error : ($wp_error = new WP_Error(null, null, null));
	}

	function fmera_show_error_messages() {
		if($codes = $this->fmera_errors()->get_error_codes()) {
			echo '<ul class="woocommerce-error">';
			// Loop error codes and display errors
			foreach($codes as $code){
				$message = $this->fmera_errors()->get_error_message($code);
				echo '<li>' . $message . '</li>';
			}
			echo '</ul>';
		}	
	}

	function submit_reg_edit_form($user_id) {
		$fields = $this->get_fields();
		foreach ($fields as $field) { 

			if ( isset( $_POST[$field->field_name] ) && empty( $_POST[$field->field_name] ) && ($field->is_required == 1)) {
				$this->fmera_errors()->add( $field->field_name.'_error', __( $field->field_label.' is required!', 'woocommerce' ) );
			} else {



				if ( isset( $_POST[$field->field_name] ) || isset( $_FILES[$field->field_name] ) ) {



					update_user_meta( $user_id, $field->field_name, sanitize_text_field( $_POST[$field->field_name] ) );


				}


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


	function get_fieldByName($name) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->fmera_fields." WHERE field_name = %s", $name));      
		return $result;
	}

	function get_OptionByid($name, $id) {
		global $wpdb;

		$result = $wpdb->get_row( $wpdb->prepare( "SELECT * FROM ".$wpdb->fmera_meta." WHERE meta_key = %s AND field_id = %d", $name, $id));      
		return $result;
	}



	function getSelectOptions($id) {
		global $wpdb;

		$result = $wpdb->get_results( $wpdb->prepare( "SELECT * FROM ".$wpdb->fmera_meta." WHERE field_id = %d", $id));      
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
