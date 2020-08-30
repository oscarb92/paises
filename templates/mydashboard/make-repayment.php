<?php
/**
 * My Dashboard - To Make Bill Payment.
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/mydashboard/make-repayment.php.
 */
defined( 'ABSPATH' ) || exit ;

global $current_user, $wc_credits ;
?>
<div class="wc-cs-mydashboard-content">
	<div class="wc-cs-mydashboard-make-repayment">
		<table>
			<tbody>
				<tr id="statement_date">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'statement_date_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( _wc_cs_format_datetime( $wc_credits->get_last_billed_date(), false ) ) ; ?></td>
				</tr>
				<tr id="due_date">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'due_date_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( _wc_cs_format_datetime( $wc_credits->get_last_billed_due_date(), false ) ) ; ?></td>
				</tr>
				<tr id="payable_amount">
					<td><?php echo esc_html( get_option( WC_CS_PREFIX . 'payable_amount_label' ) ) ; ?></td>
					<td><?php echo wp_kses_post( $wc_credits->get_last_billed_amount() ) ; ?></td>
				</tr>
			</tbody>
			<tfoot>
				<tr>
					<td colspan="2">
						<a href="<?php echo esc_url( wp_nonce_url( add_query_arg( 'do-repayment', $wc_credits->get_id(), wc_get_page_permalink( 'cart' ) ), 'wc-cs-do-repayment', WC_CS_PREFIX . 'nonce' ) ) ; ?>" class="woocommerce-button button wc-cs-make-repayment"><?php echo esc_html( get_option( WC_CS_PREFIX . 'pay_label' ) ) ; ?></a>
					</td>
				</tr>
			</tfoot>
		</table>
	</div>
</div>

