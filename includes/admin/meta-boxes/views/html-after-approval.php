<?php
defined( 'ABSPATH' ) || exit ;
?>
<table class="widefat striped wc-cs-site-activity">
	<tbody>
		<tr id="request_status">
			<td><?php esc_html_e( 'Status', 'credits-for-woocommerce' ) ; ?></td>
			<td>
				<select name="request_status">
					<?php
					foreach ( _wc_cs_get_after_approval_credits_statuses() as $status_name => $display_name ) {
						?>
						<option value="<?php echo esc_attr( $status_name ) ; ?>" <?php selected( $status_name, WC_CS_PREFIX . $credits->get_status( 'edit' ) ) ; ?>><?php echo esc_html( $display_name ) ; ?></option>
						<?php
					}
					?>
				</select>
			</td>
		</tr>
		<tr id="approved_credits">
			<td><?php esc_html_e( 'Approved Credits', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_approved_credits() ) ; ?></td>
		</tr>
		<tr id="modify_credits_limit">
			<td><?php esc_html_e( 'Modify Credits', 'credits-for-woocommerce' ) ; ?></td>
			<td><input type="checkbox" name="modify_credits_limit" value="yes"></td>
		</tr>
		<tr id="new_credits_limit">
			<td><?php esc_html_e( 'New Credits', 'credits-for-woocommerce' ) ; ?></td>
			<td><input type="number" name="new_credits_limit" step="0.01" min="0.01"></td>
		</tr>
		<tr id="total_outstanding">
			<td><?php esc_html_e( 'Total Outstanding', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( ( ( '' !== $credits->get_last_billed_status() || $credits->get_total_outstanding_amount( 'edit' ) > 0 ) ? $credits->get_total_outstanding_amount() : '-' ) ) ; ?></td>
		</tr>
		<tr id="available_credits">
			<td><?php esc_html_e( 'Available Credits', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_available_credits() ) ; ?></td>
		</tr>
		<tr id="last_bill_date">
			<td><?php esc_html_e( 'Last Bill Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( _wc_cs_format_datetime( $credits->get_last_billed_date() ) ) ; ?></td>
		</tr>
		<tr id="last_bill_amount">
			<td><?php esc_html_e( 'Last Bill Amount', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( ( '' !== $credits->get_last_billed_status() ? $credits->get_last_billed_amount() : '-' ) ) ; ?></td>
		</tr>
		<tr id="due_date">
			<td><?php esc_html_e( 'Due Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( _wc_cs_format_datetime( $credits->get_last_billed_due_date() ) ) ; ?></td>
		</tr>
		<tr id="modify_due_date">
			<td><?php esc_html_e( 'Modify Due Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><input type="checkbox" name="modify_due_date" <?php checked( 'yes', $modify_due_date ) ; ?> value="yes"></td>
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
		<tr id="last_payment_date">
			<td><?php esc_html_e( 'Last Payment Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( _wc_cs_format_datetime( $credits->get_last_payment_date() ) ) ; ?></td>
		</tr>        
		<tr id="next_billing_date">
			<td><?php esc_html_e( 'Next Billing Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( _wc_cs_format_datetime( $credits->get_next_bill_date() ) ) ; ?></td>
		</tr>
		<tr id="modify_billing_date">
			<td><?php esc_html_e( 'Modify Next Billing Date', 'credits-for-woocommerce' ) ; ?></td>
			<td><input type="checkbox" name="modify_billing_date" <?php checked( 'yes', $modify_billing_date ) ; ?> value="yes"></td>
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
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><button class="button-primary save-after-approval"><?php esc_html_e( 'Save', 'credits-for-woocommerce' ) ; ?></button></td>
		</tr>
	</tfoot>
</table>
