<?php
defined( 'ABSPATH' ) || exit ;
?>
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="_wc_cs_get_billing_day_of_month"><?php esc_html_e( 'Billing Date', 'credits-for-woocommerce' ) ; ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'A bill will be generated every month on a date set in this option. The Bill will have all the expenses made by the user using the credit limit during last month.', 'credits-for-woocommerce' ) ; ?>"></span>
		</label>
	</th>
	<td class="forminp forminp-select">
		<select id="_wc_cs_get_billing_day_of_month" name="_wc_cs_get_billing_day_of_month">
			<?php for ( $day = $billing_start_day_in_month ; $day <= $billing_days_in_month ; $day ++ ) : ?>
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
<tr valign="top">
	<th scope="row" class="titledesc">
		<label for="_wc_cs_get_threshold_day_of_month"><?php esc_html_e( 'Threshold Date', 'credits-for-woocommerce' ) ; ?>
			<span class="woocommerce-help-tip" data-tip="<?php esc_html_e( 'The User\'s First Billing month will be decided based on the value set in this option. For example consider the threshold value is 15 and If an user is getting approved on 16 December, their first bill will be generated only on Februry 15 and not on January 15.', 'credits-for-woocommerce' ) ; ?>"></span>
		</label>
	</th>
	<td class="forminp forminp-select">
		<select id="_wc_cs_get_threshold_day_of_month" name="_wc_cs_get_threshold_day_of_month">
			<?php for ( $day = $threshold_start_day_in_month ; $day <= $threshold_days_in_month ; $day ++ ) : ?>
				<?php
				if ( in_array( $day, $threshold_days_excluded ) ) {
					continue ;
				}
				?>
				<option value="<?php echo esc_attr( $day ) ; ?>" <?php selected( $day, $selected_threshold_day ) ; ?>><?php echo esc_html( _wc_cs_get_number_suffix( $day ) ) ; ?></option>
			<?php endfor ; ?>
		</select>
		<span><?php esc_html_e( 'of Every Month', 'credits-for-woocommerce' ) ; ?></span>
	</td>
</tr>
