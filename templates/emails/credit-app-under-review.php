<?php
/**
 * Credit Application Under Review Email.
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/emails/credit-app-under-review.php.
 */
defined( 'ABSPATH' ) || exit ;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ) ; ?>

<p>
	<?php
	/* translators: 1: user name 2: blog name */
	printf( wp_kses_post( __( 'Hi %1$s, <br>Your credit request on %2$s is currently being reviewed. Please check back after some time.', 'credits-for-woocommerce' ) ), esc_html( $user_nicename ), esc_html( $blogname ) )
	?>
</p>

<p><?php esc_html_e( 'Thanks', 'credits-for-woocommerce' ) ; ?></p>

<?php do_action( 'woocommerce_email_footer', $email ) ; ?>
