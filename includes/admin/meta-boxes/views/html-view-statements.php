<?php
defined( 'ABSPATH' ) || exit ;
?>
<table class="wc-cs-view-statements">
	<tbody>
		<tr>
			<td><?php esc_html_e( 'Select Period', 'credits-for-woocommerce' ) ; ?></td>
			<td>
				<select name="selected_month">
					<?php foreach ( _wc_cs_get_months() as $month => $month_label ) { ?>
						<option value="<?php echo esc_attr( $month ) ; ?>"><?php echo esc_html( $month_label ) ; ?></option>
					<?php } ?>
				</select>
			</td>
			<td>
				<select name="selected_year">
					<?php foreach ( _wc_cs_get_years() as $yr ) { ?>
						<option value="<?php echo esc_attr( $yr ) ; ?>"><?php echo esc_html( $yr ) ; ?></option>
					<?php } ?>
				</select>
			</td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td><button class="button-primary view-statements"><?php esc_html_e( 'View', 'credits-for-woocommerce' ) ; ?></button></td>
		</tr>
	</tfoot>
</table>
