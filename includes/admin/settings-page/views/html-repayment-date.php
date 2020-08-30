<?php
defined( 'ABSPATH' ) || exit ;
?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="_wc_cs_repayment_month"><?php esc_html_e( 'Repayment Month', 'credits-for-woocommerce' ) ; ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'This option controls whether the generated bill has to be paid within the same month of bill generation/next month of Bill Generation.', 'credits-for-woocommerce' ) ; ?>"></span>
		</label>
	</th>
	<td class="forminp forminp-select">
		<select id="_wc_cs_get_repayment_month" name="_wc_cs_get_repayment_month">
			<option value="this-month" <?php selected( 'this-month', $repayment_month ) ; ?>><?php esc_html_e( 'This Month', 'credits-for-woocommerce' ) ; ?></option>
			<option value="next-month" <?php selected( 'next-month', $repayment_month ) ; ?>><?php esc_html_e( 'Next Month', 'credits-for-woocommerce' ) ; ?></option>
		</select>
	</td>
</tr>
<?php if ( $due_days_in_month > 0 ) { ?>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label for="_wc_cs_get_due_day_of_month"><?php esc_html_e( 'Due Date', 'credits-for-woocommerce' ) ; ?>
				<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'The available repayment dates will be decided based on the value chosen in Billing Date.', 'credits-for-woocommerce' ) ; ?>"></span>
			</label>
		</th>
		<td class="forminp forminp-select">
			<select id="_wc_cs_get_due_day_of_month" name="_wc_cs_get_due_day_of_month">
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
		</td>
	</tr>
<?php } else { ?>
	<tr valign="top">
		<th scope="row" class="titledesc">
			<label></label>
		</th>
		<td class="forminp forminp-select">
			<span><?php esc_html_e( 'No due dates available!!', 'credits-for-woocommerce' ) ; ?></span>
		</td>
	</tr>
<?php } ?>
