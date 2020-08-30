<?php
/**
 * My Dashboard - To View Unbilled Transactions
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/mydashboard/view-unbilled-txns.php.
 */
defined( 'ABSPATH' ) || exit ;

global $current_user, $wc_credits ;
?>
<div class="wc-cs-mydashboard-content">
	<div class="wc-cs-mydashboard-view-unbilled-txns">
		<table>
			<thead>
				<tr>
					<th><?php esc_html_e( 'Activity', 'credits-for-woocommerce' ) ; ?></th>
					<th><?php esc_html_e( 'Credit', 'credits-for-woocommerce' ) ; ?></th>
					<th><?php esc_html_e( 'Debit', 'credits-for-woocommerce' ) ; ?></th>
					<th><?php esc_html_e( 'Date', 'credits-for-woocommerce' ) ; ?></th>
				</tr>
			</thead>
			<tbody>
				<?php if ( $dashboard->is_credits_user() ) : ?>
					<?php if ( $dashboard->get_credits()->get_transactions( 'unbilled' ) ) : ?>
						<?php foreach ( $dashboard->get_credits()->get_transactions( 'unbilled' ) as $txn ) : ?>
							<tr>
								<td data-title="<?php esc_html_e( 'Activity', 'credits-for-woocommerce' ) ; ?>"><?php echo wp_kses_post( $txn->get_activity() ) ; ?></td>
								<td data-title="<?php esc_html_e( 'Credit', 'credits-for-woocommerce' ) ; ?>"><?php echo wp_kses_post( $txn->get_credited() ) ; ?></td>
								<td data-title="<?php esc_html_e( 'Debit', 'credits-for-woocommerce' ) ; ?>"><?php echo wp_kses_post( $txn->get_debited() ) ; ?></td>
								<td data-title="<?php esc_html_e( 'Date', 'credits-for-woocommerce' ) ; ?>"><?php echo wp_kses_post( _wc_cs_format_datetime( $txn->get_date_created() ) ) ; ?></td>
							</tr>
						<?php endforeach ; ?>
					<?php else : ?>
						<tr>
							<td><?php esc_html_e( 'No Transactions Found', 'credits-for-woocommerce' ) ; ?></td>
						</tr>
					<?php endif ; ?>
				<?php endif ; ?>
			</tbody>
		</table>
	</div>
</div>

