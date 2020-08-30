<?php
defined( 'ABSPATH' ) || exit ;
?>
<div class="wrap woocommerce">
	<form method="post" id="mainform" action="" enctype="multipart/form-data">
		<div class="icon32 icon32-woocommerce-settings" id="icon-woocommerce"><br /></div>
		<h2 class="nav-tab-wrapper woo-nav-tab-wrapper">
			<?php
			$_tabs = apply_filters( 'wc_cs_settings_tabs_array', array() ) ;

			foreach ( $_tabs as $slug => $label ) {
				echo '<a href="' . esc_url( admin_url( 'admin.php?page=wc_cs_settings&tab=' . esc_attr( $slug ) ) ) . '" class="nav-tab ' . ( $current_tab == $slug ? 'nav-tab-active' : '' ) . '">' . esc_html( $label ) . '</a>' ;
			}
			do_action( 'wc_cs_settings_tabs' ) ;
			?>
		</h2>
		<?php
		switch ( $current_tab ) :
			default:
				do_action( 'wc_cs_sections_' . $current_tab ) ;
				do_action( 'wc_cs_settings_' . $current_tab ) ;
				break ;
		endswitch ;
		?>
		<?php if ( apply_filters( 'wc_cs_submit_' . $current_tab, true ) ) : ?>
			<p class="submit">
				<?php if ( ! isset( $GLOBALS[ 'hide_save_button' ] ) ) : ?>
					<input name="save" class="button-primary" type="submit" value="<?php esc_attr_e( 'Save changes', 'credits-for-woocommerce' ) ; ?>" />
				<?php endif ; ?>
				<input type="hidden" name="subtab" id="last_tab" />
				<?php wp_nonce_field( 'wc-cs-settings', WC_CS_PREFIX . 'nonce' ) ; ?>
			</p>
		<?php endif ; ?>
	</form>
	<?php if ( apply_filters( 'wc_cs_reset_' . $current_tab, true ) ) : ?>
		<form method="post" id="reset_mainform" action="" enctype="multipart/form-data" style="float: left; margin-top: -52px; margin-left: 159px;">
			<input name="reset" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Reset', 'credits-for-woocommerce' ) ; ?>"/>
			<input name="reset_all" class="button-secondary" type="submit" value="<?php esc_attr_e( 'Reset All', 'credits-for-woocommerce' ) ; ?>"/>
			<?php wp_nonce_field( 'wc-cs-reset-settings', WC_CS_PREFIX . 'nonce' ) ; ?>
		</form>    
	<?php endif ; ?>
</div>
