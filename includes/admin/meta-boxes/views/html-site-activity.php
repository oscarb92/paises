<?php
defined( 'ABSPATH' ) || exit ;
?>
<table class="widefat striped wc-cs-site-activity">
	<tbody>
		<tr>
			<td><?php esc_html_e( 'Member Since', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo $user ? esc_html( $user->user_registered ) : '' ; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Total No.Of Orders Placed', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo is_numeric( $credits->get_total_orders_placed_by_user( 'edit' ) ) ? esc_html( $credits->get_total_orders_placed_by_user() ) : '-' ; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Total Amount Spent on Site', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_total_amount_spent_by_user() ) ; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Highest Order Value', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_highest_order_value_by_user() ) ; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Lowest Order Value', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_lowest_order_value_by_user() ) ; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Average Monthly Amount Spent', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_avg_monthly_amount_spent_by_user() ) ; ?></td>
		</tr>
		<tr>
			<td><?php esc_html_e( 'Average Yearly Amount Spent', 'credits-for-woocommerce' ) ; ?></td>
			<td><?php echo wp_kses_post( $credits->get_avg_yearly_amount_spent_by_user() ) ; ?></td>
		</tr>
	</tbody>
	<tfoot>
		<tr>
			<td colspan="2"><button class="button-primary check-site-activity"><?php esc_html_e( 'Check', 'credits-for-woocommerce' ) ; ?></button></td>
		</tr>
	</tfoot>
</table>
