<?php
/**
 * My Dashboard
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/mydashboard/dashboard.php.
 */
defined( 'ABSPATH' ) || exit ;

global $current_user, $wc_credits ;
?>

<div class="wc-cs-mydashboard-content">
	<div class="wc-cs-mydashboard-profile">
		<table>
			<tbody>
				<tr id="approved_credits">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'approved_credits_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( $wc_credits->get_approved_credits() ) ; ?></td>
				</tr>     
				<tr id="total_outstanding">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'total_outstanding_amount_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( ( ( '' !== $wc_credits->get_last_billed_status() || $wc_credits->get_total_outstanding_amount( 'edit' ) > 0 ) ? $wc_credits->get_total_outstanding_amount() : '-' ) ) ; ?></td>
				</tr>	
				<tr id="available_credits">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'available_credits_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( $wc_credits->get_available_credits() ) ; ?></td>
				</tr>
				<tr id="last_bill_date">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'last_bill_date_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( _wc_cs_format_datetime( $wc_credits->get_last_billed_date(), false ) ) ; ?></td>
				</tr>
				<tr id="last_bill_amount">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'last_bill_amount_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( ( '' !== $wc_credits->get_last_billed_status() ? $wc_credits->get_last_billed_amount() : '-' ) ) ; ?></td>
				</tr>
				<tr id="due_date">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'due_date_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( _wc_cs_format_datetime( $wc_credits->get_last_billed_due_date(), false ) ) ; ?></td>
				</tr>
				<tr id="last_payment_date">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'last_payment_date_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( _wc_cs_format_datetime( $wc_credits->get_last_payment_date(), false ) ) ; ?></td>
				</tr>                
				<tr id="next_billing_date">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'next_billing_date_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( _wc_cs_format_datetime( $wc_credits->get_next_bill_date(), false ) ) ; ?></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2">
						<?php foreach ( $dashboard->get_endpoints() as $endpoint => $label ) : ?>
							<a href="<?php echo esc_url( $dashboard->get_endpoint_url( $endpoint ) ) ; ?>" class="woocommerce-button button <?php echo esc_attr( $endpoint ) ; ?>"><?php echo esc_html( $label ) ; ?></a>
						<?php endforeach ; ?>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>
<?php
