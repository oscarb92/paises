<?php
/**
 * Account Onhold Email.
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/emails/account-on-hold.php.
 */
defined( 'ABSPATH' ) || exit ;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ) ; ?>

<p>
	<?php
	/* translators: 1: user name 2: blog name */
	printf( wp_kses_post( __( 'Hi %1$s, <br>Your credit limit on %2$s is currently on-hold . Please contact us for more details.', 'credits-for-woocommerce' ) ), esc_html( $user_nicename ), esc_html( $blogname ) )
	?>
</p>

<p><?php esc_html_e( 'Thanks', 'credits-for-woocommerce' ) ; ?></p>

<?php do_action( 'woocommerce_email_footer', $email ) ; ?>
