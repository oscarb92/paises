<?php
defined( 'ABSPATH' ) || exit ;
?>
<table class="wc-cs-file-attachments" style="width:100%;">
	<thead>
		<tr>
			<th><?php esc_html_e( 'S.no', 'credits-for-woocommerce' ) ; ?></th>
			<th><?php esc_html_e( 'Attachments', 'credits-for-woocommerce' ) ; ?></th>
			<th><?php esc_html_e( 'Download', 'credits-for-woocommerce' ) ; ?></th>
		</tr>
	</thead>
	<tbody>
		<?php foreach ( $credits->get_attachments() as $count => $attachment_id ) { ?>
			<?php
			$file_path = wp_get_attachment_url( $attachment_id ) ;

			if ( ! $file_path ) {
				continue ;
			}

			$filename = basename( $file_path ) ;

			if ( strstr( $filename, '?' ) ) {
				$filename = current( explode( '?', $filename ) ) ;
			}

			$download_link = add_query_arg( array( 'download_file' => $attachment_id, WC_CS_PREFIX . 'nonce' => wp_create_nonce( 'wc-cs-download-user-docs' ) ), admin_url( "post={$credits->get_id()}&action=edit" ) ) ;
			?>
			<tr>
				<td><?php echo esc_html( ++ $count ) ; ?>.</td>
				<td class="file-attached"><?php echo esc_html( $filename ) ; ?></td>
				<td class="file-download"><a href="<?php echo esc_url( $download_link ) ; ?>"><span class="dashicons dashicons-download"></span></a></td>
			</tr>
		<?php } ?>
	</tbody>
</table>
