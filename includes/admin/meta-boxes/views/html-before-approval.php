<?php
defined( 'ABSPATH' ) || exit ;

$eligible_limit = ! $credits->is_manual() ? WC_CS_Credit_Line_Rules::get_eligible_credit_limit_by_user( $credits->get_user_id(), $credits->get_rule_applied() ) : '' ;
?>
<table class="widefat striped wc-cs-site-activity">
	<tbody>
		<tr id="request_status">
			<td><?php esc_html_e( 'Status', 'credits-for-woocommerce' ) ; ?></td>
			<td>
				<select name="request_status">
					<?php
					foreach ( _wc_cs_get_before_approval_credits_statuses() as $status_name => $display_name ) {
						?>
						<option value="<?php echo esc_attr( $status_name ) ; ?>" <?php selected( $status_name, WC_CS_PREFIX . $credits->get_status( 'edit' ) ) ; ?>><?php echo esc_html( $display_name ) ; ?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<?php if ( _wc_cs()->funding_via_real_money() ) { ?>
			<tr id="available_credits_in_admin_acc">
				<td><?php esc_html_e( 'Available Funds in Admin Account', 'credits-for-woocommerce' ) ; ?></td>
				<td><?php echo wp_kses_post( WC_CS_Admin_Funds::get_available_funds() ) ; ?></td>
			</tr>
		<?php } ?>
		<tr id="new_credits_limit">
			<td><?php esc_html_e( 'Eligible Credits', 'credits-for-woocommerce' ) ; ?></td>
			<td>
				<input type="number" name="new_credits_limit" value="<?php echo esc_attr( $eligible_limit ) ; ?>" step="0.01" min="0.01" readonly="readonly"><a href="#" class="edit-credits"><?php esc_html_e( 'Edit', 'credits-for-woocommerce' ) ; ?></a>
				<input type="hidden" name="eligible_limit" value="<?php echo esc_attr( $eligible_limit ) ; ?>">
			</td>
		</tr>
		<tr id="use_global_billing_date">
			<td><?php esc_html_e( 'Use Global Billing Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><input type="checkbox" name="use_global_billing_date" <?php checked( 'yes', $use_global_billing_date ) ; ?> value="yes"></td>
		</tr>
		<tr id="new_billing_date">
			<td><?php esc_html_e( 'Billing Date', 'credits-for-woocommerce' ) ; ?></td>
			<td>
				<select name="billing_day_of_month">
					<?php for ( $day = 1 ; $day <= 27 ; $day ++ ) : ?>
						<?php
						if ( in_array( $day, $billing_days_excluded ) ) {
							continue ;
						}
						?>
						<option value="<?php echo esc_attr( $day ) ; ?>" <?php selected( $day, $selected_billing_day ) ; ?>><?php echo esc_html( _wc_cs_get_number_suffix( $day ) ) ; ?></option>
					<?php endfor ; ?>
				</select>
				<span><?php esc_html_e( 'of Every Month', 'credits-for-woocommerce' ) ; ?></span>
			</td>
		</tr>
		<tr id="use_global_due_date">
			<td><?php esc_html_e( 'Use Global Due Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><input type="checkbox" name="use_global_due_date" <?php checked( 'yes', $use_global_due_date ) ; ?> value="yes"></td>
		</tr>
		<tr id="new_due_date">
			<td><?php esc_html_e( 'Due Date', 'credits-for-woocommerce' ) ; ?></td>
			<td>
				<select name="get_repayment_month">
					<option value="this-month" <?php selected( 'this-month', $repayment_month ) ; ?>><?php esc_html_e( 'This Month', 'credits-for-woocommerce' ) ; ?></option>
					<option value="next-month" <?php selected( 'next-month', $repayment_month ) ; ?>><?php esc_html_e( 'Next Month', 'credits-for-woocommerce' ) ; ?></option>
				</select>
				<?php if ( $due_days_in_month > 0 ) { ?>
					<select name="due_day_of_month">
						<?php for ( $day = $due_start_day_in_month ; $day <= $due_days_in_month ; $day ++ ) : ?>
							<?php
							if ( in_array( $day, $due_days_excluded ) ) {
								continue ;
							}
							?>
							<option value="<?php echo esc_attr( $day ) ; ?>" <?php selected( $day, $selected_due_day ) ; ?>><?php echo esc_html( _wc_cs_get_number_suffix( $day ) ) ; ?></option>
						<?php endfor ; ?>
					</select>
					<span><?php esc_html_e( 'of Every Month', 'credits-for-woocommerce' ) ; ?></span>
				<?php } else { ?>
					<span><?php esc_html_e( 'No due dates available!!', 'credits-for-woocommerce' ) ; ?></span>
				<?php } ?>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><button class="button-primary save-before-approval"><?php esc_html_e( 'Save', 'credits-for-woocommerce' ) ; ?></button></td>
		</tr>
	</tfoot>
</table>
