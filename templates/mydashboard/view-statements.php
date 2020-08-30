<?php
/**
 * My Dashboard - To View their Statements
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/mydashboard/view-statements.php.
 */
defined( 'ABSPATH' ) || exit ;

global $current_user, $wc_credits ;
?>
<div class="wc-cs-mydashboard-content">
	<div class="wc-cs-mydashboard-view-statements">
		<label for="view_statements"><?php echo esc_html( get_option( WC_CS_PREFIX . 'select_period_label' ) ) ; ?></label>
		<select name="selected_month">
			<?php foreach ( _wc_cs_get_months() as $month => $month_label ) { ?>
				<option value="<?php echo esc_attr( $month ) ; ?>"><?php echo esc_html( $month_label ) ; ?></option>
			<?php } ?>
		</select>
		<select name="selected_year">
			<?php foreach ( _wc_cs_get_years() as $yr ) { ?>
				<option value="<?php echo esc_attr( $yr ) ; ?>"><?php echo esc_html( $yr ) ; ?></option>
			<?php } ?>
		</select>
		<div class="wc-cs-dashboard-button">
			<button class="woocommerce-button button view-statements"><?php echo esc_html( get_option( WC_CS_PREFIX . 'view_statements_link_label' ) ) ; ?></button>
		</div>
	</div>
</div>

