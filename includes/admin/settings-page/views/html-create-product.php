<?php
defined( 'ABSPATH' ) || exit ;
?>
<th scope="row" class="titledesc">
	<label for="create_product"><?php esc_html_e( 'Product Name', 'credits-for-woocommerce' ); ?></label>
</th>
<td class="forminp forminp-select">
	<input type="text" name="product_title" id="product_title" placeholder="<?php esc_attr_e( 'Enter the product title', 'credits-for-woocommerce' ) ; ?>"></br></br>
	<button class="button _wc_cs_create_product" data-product_title="<?php echo esc_attr( $product_title ) ; ?>" data-field_type_selector="<?php echo esc_attr( $field_type_selector ) ; ?>" data-display_field_id="<?php echo esc_attr( "_wc_cs_get_selected_product_{$slug}" ) ; ?>" id="<?php echo esc_attr( "_wc_cs_create_product_{$slug}" ) ; ?>"><?php esc_html_e( 'Create Product', 'credits-for-woocommerce' ) ; ?><span class="spinner" style="display:none;"></span></button>
</td>
