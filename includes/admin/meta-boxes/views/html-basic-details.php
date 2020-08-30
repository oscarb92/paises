<?php
defined( 'ABSPATH' ) || exit ;
?>
<div class="inside">    
	<table class="widefat striped wc-cs-basic-details">
		<tbody>
			<?php
			foreach ( WC_CS_Form_Fields::get_fields() as $field_key => $args ) {
				if ( empty( $args[ 'label' ] ) ) {
					continue ;
				}

				if ( 'file_attachments' === $field_key ) {
					?>
					<tr>
						<td colspan="2">
							<?php include 'html-file-attachments.php' ; ?>
						</td>
					</tr>
					<?php
				} else {
					$prop = str_replace( 'billing_', '', $field_key ) ;
					?>
					<tr>
						<td><?php echo esc_html( $args[ 'label' ] ) ; ?></td>
						<td><?php echo is_callable( array( $credits, "get_user_{$prop}" ) ) && $credits->{"get_user_{$prop}"}() ? esc_html( $credits->{"get_user_{$prop}"}() ) : '-' ; ?></td>
					</tr>
					<?php
				}
			}
			?>
		</tbody>
	</table>
</div>
