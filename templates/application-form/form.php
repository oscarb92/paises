<?php
/**
 * Credits Application Form
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/application-form/form.php.
 */
defined( 'ABSPATH' ) || exit ;

global $current_user ;
?>
<form name="wc_cs_new_user" class="wc-cs-mydashboard" method="post" enctype="multipart/form-data">
	<h4><?php esc_html_e( 'User Details', 'credits-for-woocommerce' ) ; ?></h4>
	<div class="wc-cs-user-fields-wrapper">
		<?php
		foreach ( $fields as $field_key => $field ) {
			woocommerce_form_field( $field_key, $field, WC_CS_Form_Fields::get_value( $field_key ) ) ;
		}
		?>
	</div>
	<div class="wc-cs-dashboard-button">
		<input type="submit" name="credit_app_submitted" value="<?php esc_html_e( 'Submit', 'credits-for-woocommerce' ) ; ?>"/>
		<?php wp_nonce_field( 'wc-cs-credit-app-submited', WC_CS_PREFIX . 'nonce' ) ; ?> 
	</div>
</form>
