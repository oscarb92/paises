<?php
defined( 'ABSPATH' ) || exit ;
?>
<thead>
	<tr>
		<th><?php esc_html_e( 'Field Name', 'credits-for-woocommerce' ) ; ?></th>
		<th><?php esc_html_e( 'Field Status', 'credits-for-woocommerce' ) ; ?></th>
		<th><?php esc_html_e( 'Mandatory', 'credits-for-woocommerce' ) ; ?></th>
	</tr>
</thead>
<tbody>                
	<?php foreach ( WC_CS_Form_Fields::get_fields() as $field_key => $value ) : ?>
		<?php
		if ( empty( $value[ 'label' ] ) ) {
			continue ;
		}
		?>
		<tr>
			<td><?php echo esc_html( $value[ 'label' ] ) ; ?></td>
			<td>
				<input type="checkbox" value="yes" name="<?php echo esc_attr( "_wc_cs_{$field_key}_enabled" ) ; ?>" <?php checked( 'yes', get_option( "_wc_cs_{$field_key}_enabled" ) ) ; ?>
				<?php
				if ( isset( $value[ 'default_required' ] ) ) {
					?>
						   onclick="return false"<?php } ?>/>
			</td>
			<td>
				<input type="checkbox" value="yes" name="<?php echo esc_attr( "_wc_cs_{$field_key}_is_mandatory" ) ; ?>" <?php checked( 'yes', get_option( "_wc_cs_{$field_key}_is_mandatory" ) ) ; ?>
				<?php
				if ( isset( $value[ 'default_required' ] ) ) {
					?>
						   onclick="return false"<?php } ?>/>
			</td>
		</tr>
	<?php endforeach ; ?>
</tbody>
