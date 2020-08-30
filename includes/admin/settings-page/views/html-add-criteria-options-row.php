<?php
defined( 'ABSPATH' ) || exit ;

$rule_actions = array(
	'user_as'                                     => __( 'Is a', 'credits-for-woocommerce' ),
	'user_not_as'                                 => __( 'Is not a', 'credits-for-woocommerce' ),
	'user_registered_for'                         => __( 'Is registered for', 'credits-for-woocommerce' ),
	'user_total_orders_amt_less_than_r_eql_to'    => __( 'Has previously purchased less than or equal to', 'credits-for-woocommerce' ),
	'user_total_orders_amt_more_than_r_eql_to'    => __( 'Has previously purchased more than or equal to', 'credits-for-woocommerce' ),
	'user_placed_orders_count_less_than_r_eql_to' => __( 'Has placed number of orders less than or equal to', 'credits-for-woocommerce' ),
	'user_placed_orders_count_more_than_r_eql_to' => __( 'Has placed number of orders more than or equal to', 'credits-for-woocommerce' )
		) ;

$registered_period = array(
	'days'   => __( 'Day(s)', 'credits-for-woocommerce' ),
	'months' => __( 'Month(s)', 'credits-for-woocommerce' ),
	'years'  => __( 'Year(s)', 'credits-for-woocommerce' ),
		) ;

foreach ( $group as $row_id => $row ) :
	?>
	<p class="wc_cs_rule_criteria_options_row" data-row="<?php echo esc_attr( $row_id ) ; ?>">
		<select name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][action]" id="action">
			<?php foreach ( $rule_actions as $key => $label ) : ?>
				<option value="<?php echo esc_attr( $key ) ; ?>" <?php selected( $key, ( isset( $row[ 'action' ] ) ? $row[ 'action' ] : '' ) ) ; ?>><?php echo esc_html( $label ) ; ?></option>
			<?php endforeach ; ?>
		</select>
		<select name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][user_role]" id="user_role">
			<?php foreach ( _wc_cs_get_user_roles() as $role_key => $role_name ) : ?>
				<option value="<?php echo esc_attr( $role_key ) ; ?>" <?php selected( $role_key, ( isset( $row[ 'user_role' ] ) ? $row[ 'user_role' ] : '' ) ) ; ?>><?php echo esc_html( $role_name ) ; ?></option>
			<?php endforeach ; ?>
		</select>
		<span id="registered_period_group" style="display:none">            
			<select name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][registered_period_compare]" id="registered_period_compare">
				<option value="less-than" <?php selected( 'less-than', ( isset( $row[ 'registered_period_compare' ] ) ? $row[ 'registered_period_compare' ] : '' ) ) ; ?>><?php esc_html_e( 'Less than', 'credits-for-woocommerce' ) ; ?></option>
				<option value="more-than" <?php selected( 'more-than', ( isset( $row[ 'registered_period_compare' ] ) ? $row[ 'registered_period_compare' ] : '' ) ) ; ?>><?php esc_html_e( 'More than', 'credits-for-woocommerce' ) ; ?></option>
			</select>
			<input type="number" name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][registered_period_interval]" min="1" id="registered_period_interval" value="<?php echo esc_attr( isset( $row[ 'registered_period_interval' ] ) ? $row[ 'registered_period_interval' ] : 1  ) ; ?>">
			<select name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][registered_period]" id="registered_period">
				<?php foreach ( $registered_period as $key => $label ) : ?>
					<option value="<?php echo esc_attr( $key ) ; ?>" <?php selected( $key, ( isset( $row[ 'registered_period' ] ) ? $row[ 'registered_period' ] : '' ) ) ; ?>><?php echo esc_html( $label ) ; ?></option>
				<?php endforeach ; ?>
			</select>
		</span>
		<input type="number" name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][orders_amount]" id="orders_amount" value="<?php echo esc_attr( isset( $row[ 'orders_amount' ] ) ? $row[ 'orders_amount' ] : ''  ) ; ?>" style="display: none; width: 35%" placeholder="<?php esc_attr_e( 'Enter order amount', 'credits-for-woocommerce' ) ; ?>">
		<input type="number" name="criteria[<?php echo esc_attr( $group_id ) ; ?>][<?php echo esc_attr( $row_id ) ; ?>][orders_count]" id="orders_count" value="<?php echo esc_attr( isset( $row[ 'orders_count' ] ) ? $row[ 'orders_count' ] : ''  ) ; ?>" style="display: none; width: 35%" placeholder="<?php esc_attr_e( 'Enter number of orders', 'credits-for-woocommerce' ) ; ?>">
		<a href="#" class="add_and_options button"><?php esc_html_e( 'AND', 'credits-for-woocommerce' ) ; ?></a>
		<a href="#" class="remove_options button"><?php esc_html_e( 'X', 'credits-for-woocommerce' ) ; ?></a>
	</p>
<?php endforeach ; ?>
