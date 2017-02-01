<?php 
if (!defined('ABSPATH')) 
exit; // Exit if accessed directly

if (!class_exists('Minbazaar_Registration_Attributes_Front')){ 

	class Minbazaar_Registration_Attributes_Front extends Minbazaar_Registration_Attributes {
		public function __construct() {

			add_action( 'wp_loaded', array( $this, 'front_scripts' ) );
			$this->module_settings = $this->get_module_settings();
			add_action( 'woocommerce_register_form', array($this, 'minbazaar_extra_registration_form_end' ));
			add_action( 'woocommerce_register_form_start', array($this, 'minbazaar_extra_registration_form_start' ) );
			add_action( 'woocommerce_register_post', array($this, 'minbazaar_validate_extra_register_fields'), 10, 3 );
			add_action( 'woocommerce_created_customer', array($this, 'minbazaar_save_extra_register_fields' ));
			add_action('woocommerce_before_my_account', array($this, 'minbazaar_my_profile'));
			add_action( 'init', array($this, 'add_minbua_query_vars' )); 
			 // add_action( 'template_include', array( $this, 'change_template' ) );


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



		function minbazaar_extra_registration_form_start() {

                        ?>

                                <p class="form-row">
                                <label for="first_name"><?php esc_attr_e( 'First Name', 'woocommerce' ); ?>
                                <span class="required">*</span>
                                </label>
                                <input type="text" class="input-text" name="first_name" id="first_name" value="<?php if ( ! empty( $_POST['first_name'] ) ) esc_attr_e( $_POST['first_name'] ); ?>"  />

				<label for="last_name"><?php esc_attr_e( 'Last Name', 'woocommerce' ); ?>
                                <span class="required">*</span>
                                </label>
                                <input type="text" class="input-text" name="last_name" id="last_name" value="<?php if ( ! empty( $_POST['last_name'] ) ) esc_attr_e( $_POST['last_name'] ); ?>" />
                                </p>

                    <?php }

		function minbazaar_extra_registration_form_end() { 

			?>

				<p class="form-row">
				<label for="reg_mobile"><?php esc_attr_e( 'mobile_number', 'woocommerce' ); ?> 
				<span class="required">*</span> 
				</label>
				<input type="text" class="input-text" name="reg_mobile" id="reg_mobile" value="<?php if ( ! empty( $_POST['reg_mobile'] ) ) esc_attr_e( $_POST['reg_mobile'] ); ?>" placeholder="mobile_number" />
				</p>

				<div id="min_otp_value_div" class="input-text" style="display:none">
				<p class="form-row">
				<label for="otp_val"><?php esc_attr_e( 'Enter OTP', 'woocommerce' ); ?>
				<span class="required">*</span>
				</label>
				<input type="input" class="input-text" name="min_otp_value" id="min_otp_value" value="<?php if ( ! empty( $_POST['min_otp_value'] ) ) esc_attr_e( $_POST['min_otp_value'] ); ?>" placeholder="One Time Password" />
				</p>
				</div>
				<div class='inner-otp-error' id='inner-otp-error' style='display:none'> </div>

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


			if (!isset($_POST['first_name']) || empty($_POST['first_name'])) {
                                $validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: First Name is required!', 'woocommerce' ) );
                        }else if( !isset($_POST['last_name']) || empty($_POST['last_name']) ){
                                $validation_errors->add( 'reg_mobile__error', __( '<strong>Error</strong>: Last Name is required!', 'woocommerce' ) );
                        }else if (!isset($_POST['reg_mobile']) || empty($_POST['reg_mobile'])) {
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
			$wpdb->query( $wpdb->prepare("UPDATE ".$wpdb->prefix."minbazaar_otp set used = 1 WHERE mobile_number = '%s'", $mobile));
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
			global $wpdb;
			$otp_count = $wpdb->get_var( $wpdb->prepare("SELECT COUNT(*) FROM ".$wpdb->prefix . "minbazaar_otp WHERE mobile_number = '%s' and otp_value = '%s' and used!=1", $mobile, $otp));

			if($otp_count > 0)
				return true;
			else
				return false;
		}


		function submit_reg_edit_form($user_id) {
				if ( isset( $_POST['reg_mobile'] ) || isset( $_FILES['reg_mobile'] ) ) {
					update_user_meta( $user_id, 'mobile_number', sanitize_text_field( $_POST['reg_mobile'] ) );
				}

				if ( isset( $_POST['first_name'] ) || isset( $_FILES['first_name'] ) ) {
                                update_user_meta( $user_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
                        }

                        if ( isset( $_POST['last_name'] ) || isset( $_FILES['last_name'] ) ) {
                                update_user_meta( $user_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
                        }
		}

		function minbazaar_save_extra_register_fields($customer_id) {

			if ( isset( $_POST['reg_mobile'] ) || isset( $_FILES['reg_mobile'] ) ) {
				update_user_meta( $customer_id, 'mobile_number', sanitize_text_field( $_POST['reg_mobile'] ) );
			}

			if ( isset( $_POST['first_name'] ) || isset( $_FILES['first_name'] ) ) {
                                update_user_meta( $customer_id, 'first_name', sanitize_text_field( $_POST['first_name'] ) );
                        }

			if ( isset( $_POST['last_name'] ) || isset( $_FILES['last_name'] ) ) {
                                update_user_meta( $customer_id, 'last_name', sanitize_text_field( $_POST['last_name'] ) );
                        }
		}




	}

	function add_minbua_query_vars() {
                        add_rewrite_endpoint( 'edit-profile', EP_PERMALINK | EP_PAGES );
                        flush_rewrite_rules();
                }

	function change_template( $template ) {
                if( get_query_var( 'edit-profile') != '' ) {
                        //Check plugin directory
                        $newTemplate = plugin_dir_path( __FILE__ ) . 'view/edit_profile.php';
                        if( file_exists( $newTemplate ) )
                                return $newTemplate;
                }
                return $template;
        }

	function minbazaar_extra_registration_form_edit($user_id) {  ?>

		<?php $this->fmera_show_error_messages(); ?>

			<?php


			$value = get_user_meta( $user_id, 'mobile_number', true );

		?>

			<p class="form-row 15">
			<label for="mobile_number"><?php _e( 'mobile_number', 'woocommerce' ); ?>
			<?php if(1 == 1) { ?> <span class="required">*</span> <?php } ?>
			</label>
			<input type="text" class="input-text" name="mobile_number" id="mobile_number" value="<?php echo $value; ?>" placeholder="Mobile Number" />
			</p>

			<?php }



	function minbazaar_my_profile() { ?> 
		<div class="col2-set addresses">
			<header class="title">
			<?php $profile_url = wc_get_endpoint_url( 'edit-profile', get_current_user_id(), wc_get_page_permalink( 'myaccount' ) ); ?>
			<a class="edit" href="<?php echo $profile_url; ?>">Edit</a>
			</header> 
			</div>                 
			<table class="shop_table shop_table_responsive my_account_orders">
			<tbody> 
			<?php
			$user_id = get_current_user_id();       

			$check = get_user_meta( $user_id, 'mobile_number', true );
			$label = $this->get_fieldByName('mobile_number');
			if($check!='') {                

				$value = get_user_meta( $user_id, 'mobile_number', true );
				?>                      
					<tr class="order" style="text-align:left">
					<td style="width:30%;"><b><?php echo 'Mobile Number'; ?></b></td>
					<td>
					<?php
						echo $value;
				?>
					</td>
					</tr>

					<?php }
		?>

			</tbody>
			</table>

	<?php }

	new Minbazaar_Registration_Attributes_Front();
}



?>
