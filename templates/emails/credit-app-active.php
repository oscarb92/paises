<?php
/**
 * Credit Application Active Email.
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/emails/credit-app-active.php.
 */
defined( 'ABSPATH' ) || exit ;
?>

<?php do_action( 'woocommerce_email_header', $email_heading, $email ) ; ?>

<p>
	<?php
	/* translators: 1: user name 2: blog name 3: login url */
	printf( wp_kses_post( __( 'Hi %1$s, <br>Your credit request on %2$s has been approved. Please login to the %3$s to get more details about on your credit limit.', 'credits-for-woocommerce' ) ), esc_html( $user_nicename ), esc_html( $blogname ), '<a href="' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '">' . esc_url( wc_get_page_permalink( 'myaccount' ) ) . '</a>' )
	?>
</p>

<p><?php esc_html_e( 'Thanks', 'credits-for-woocommerce' ) ; ?></p>

<?php do_action( 'woocommerce_email_footer', $email ) ; ?>
