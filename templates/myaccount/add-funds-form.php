<?php
/**
 * Funds Addition Form
 *
 * This template can be overridden by copying it to yourtheme/credits-for-woocommerce/myaccount/add-funds-form.php.
 */
defined( 'ABSPATH' ) || exit ;
?>
<form name="wc_cs_view_add_funds" class="wc-cs-view-add-funds" method="post" enctype="multipart/form-data">
	<table>
		<tr>
			<th><?php esc_html_e( 'Amount to Add', 'credits-for-woocommerce' ) ; ?></th>
			<td>:</td>
			<td>
				<input type="number" name="fund_amount" min="<?php echo esc_attr( $min_amt ) ; ?>" max="<?php echo esc_attr( $max_amt ) ; ?>" step="0.01" value=""/>
			</td>
		</tr> 
		<tr>
			<td>
				<input type="submit" name="add_funds" value="<?php esc_html_e( 'Add Amount', 'credits-for-woocommerce' ) ; ?>"/>
				<?php wp_nonce_field( 'wc-cs-view-add-funds', WC_CS_PREFIX . 'nonce' ) ; ?>
			</td>
		</tr>  
	</table>
</div>
