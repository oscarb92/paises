<?php
defined( 'ABSPATH' ) || exit ;

foreach ( $credit_rules as $rule_id => $rule ) {
	?>
	<tr>
		<td><?php echo esc_html( $rule[ 'name' ] ); ?></td>
		<td><input type="number" name="rule_priority[<?php echo esc_attr( $rule_id ) ; ?>]" value="<?php echo esc_html( $rule[ 'priority' ] ); ?>" min="1" step="1" required="required"></td>
		<td><?php echo wp_kses_post( wc_price( $rule[ 'credit_limit' ] ) ); ?></td>
		<td><?php echo esc_html( $rule[ 'no_of_users' ] ); ?></td>
		<td>
			<a href="#" class="edit button" data-rule_id="<?php echo esc_attr( $rule_id ) ; ?>"><span class="dashicons dashicons-edit-large"></span></a>
			<?php if ( ! $rule[ 'no_of_users' ] ) { ?>
				<a href="#" class="remove button" data-rule_id="<?php echo esc_attr( $rule_id ) ; ?>">X</a>
			<?php } ?>
		</td>
	</tr>  
	<?php
}
?>
