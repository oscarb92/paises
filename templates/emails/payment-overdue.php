<?php
/**
 * Payment Overdue Email.
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/emails/payment-overdue.php.
 */
defined( 'ABSPATH' ) || exit ;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ) ; ?>

<?php if ( $credits_txn ) { ?>
	<?php if ( $credits->has_status( 'on_hold' ) ) { ?>
		<p>
			<?php
			/* translators: 1: user name 2: last bill date 3: last bill txn start date 4: last bill txn end date 5: blog name 6: debited amount */
			printf( wp_kses_post( __( 'Hi %1$s, <br>Your credit limit bill due amount dated <code>%2$s</code> for the transaction period of <code>%3$s</code> to <code>%4$s</code> has not been paid so far on %5$s and hence late payment fee of <code>%6$s</code> has been charged in your next bill. Your credit pay balance is now onhold. Please pay the bill immediately to continue using your credit balance.', 'credits-for-woocommerce' ) ), esc_html( $user_nicename ), wp_kses_post( _wc_cs_format_datetime( $credits->get_last_billed_date(), false ) ), wp_kses_post( _wc_cs_format_datetime( $bill_statement->get_from_date(), false ) ), wp_kses_post( _wc_cs_format_datetime( $bill_statement->get_to_date(), false ) ), esc_html( $blogname ), wp_kses_post( $credits_txn->get_debited() ) )
			?>
		</p>
	<?php } else { ?>
		<p>
			<?php
			/* translators: 1: user name 2: last bill date 3: last bill txn start date 4: last bill txn end date 5: blog name 6: debited amount */
			printf( wp_kses_post( __( 'Hi %1$s, <br>Your credit limit bill due amount dated <code>%2$s</code> for the transaction period of <code>%3$s</code> to <code>%4$s</code> has not been paid so far on %5$s and hence late payment fee of <code>%6$s</code> has been charged in your next bill.', 'credits-for-woocommerce' ) ), esc_html( $user_nicename ), wp_kses_post( _wc_cs_format_datetime( $credits->get_last_billed_date(), false ) ), wp_kses_post( _wc_cs_format_datetime( $bill_statement->get_from_date(), false ) ), wp_kses_post( _wc_cs_format_datetime( $bill_statement->get_to_date(), false ) ), esc_html( $blogname ), wp_kses_post( $credits_txn->get_debited() ) )
			?>
		</p>
	<?php } ?>

<?php } else { ?>
	<p>
		<?php
		/* translators: 1: user name 2: last bill date 3: last bill txn start date 4: last bill txn end date 5: blog name */
		printf( wp_kses_post( __( 'Hi %1$s, <br>Your credit limit bill due amount dated <code>%2$s</code> for the transaction period of <code>%3$s</code> to <code>%4$s</code> has not been paid so far on %5$s.', 'credits-for-woocommerce' ) ), esc_html( $user_nicename ), wp_kses_post( _wc_cs_format_datetime( $credits->get_last_billed_date(), false ) ), wp_kses_post( _wc_cs_format_datetime( $bill_statement->get_from_date(), false ) ), wp_kses_post( _wc_cs_format_datetime( $bill_statement->get_to_date(), false ) ), esc_html( $blogname ) )
		?>
	</p>
<?php } ?>

<p><?php esc_html_e( 'Thanks', 'credits-for-woocommerce' ) ; ?></p>

<?php do_action( 'woocommerce_email_footer', $email ) ; ?>
