<?php

/**
 * Rules Tab.
 * 
 * @class WC_CS_Settings_Rules
 * @package Class
 */
class WC_CS_Settings_Rules extends WC_CS_Abstract_Settings {

	/**
	 * WC_CS_Settings_Rules constructor.
	 */
	public function __construct() {

		$this->id            = 'rules' ;
		$this->label         = __( 'Rules', 'credits-for-woocommerce' ) ;
		$this->custom_fields = array(
			'get_rules',
				) ;
		$this->settings      = $this->get_settings() ;
		$this->init() ;

		add_filter( 'wc_cs_reset_' . $this->id, '__return_false' ) ;
		add_action( 'admin_footer', array( $this, 'rule_preview_template' ) ) ;
	}

	/**
	 * Get settings array.
	 *
	 * @return array
	 */
	public function get_settings() {
		global $current_section ;

		return apply_filters( 'wc_cs_get_' . $this->id . '_settings', array(
			array(
				'name' => __( 'Rules', 'credits-for-woocommerce' ),
				'type' => 'title',
				'id'   => $this->prefix . 'rules_settings'
			),
			array( 'type' => $this->get_custom_field_type( 'get_rules' ) ),
			array( 'type' => 'sectionend', 'id' => $this->prefix . 'rules_settings' ),
				) ) ;
	}

	/**
	 * Custom type field.
	 */
	public function get_rules() {
		$credit_rules = get_option( WC_CS_PREFIX . 'credit_line_rules', array() ) ;
		?>
		<tr valign="top">
			<td class="wc_cs_rules_wrapper">
				<table class="widefat striped fixed wc_cs_rules" cellspacing="0" >
					<thead>
						<tr>
							<th><?php esc_html_e( 'Rule Name', 'credits-for-woocommerce' ) ; ?></th>
							<th><?php esc_html_e( 'Rule Priority', 'credits-for-woocommerce' ) ; ?>&nbsp;<?php echo wc_help_tip( esc_html( 'The Rule with the lowest priority will apply first. For example, if 3 rules are created with priority set as 1, 2 and 3 respectively, then Rule 1 is the highest priority and Rule 3 is the lowest priority.', 'credits-for-woocommerce' ) ) ; ?></th>
							<th><?php esc_html_e( 'Credit Limit', 'credits-for-woocommerce' ) ; ?></th>
							<th><?php esc_html_e( 'Number of Users', 'credits-for-woocommerce' ) ; ?></th>
							<th><?php esc_html_e( 'Action', 'credits-for-woocommerce' ) ; ?></th>
						</tr>
					</thead>
					<tbody>                
						<?php
						if ( ! empty( $credit_rules ) ) {
							include 'views/html-rules.php' ;
						}
						?>

					</tbody>
					<tfoot>
						<tr>
							<th colspan="5">
								<a href="#" class="add button button-primary"><?php esc_html_e( 'Add Rule', 'credits-for-woocommerce' ) ; ?></a>
							</th>
						</tr>
					</tfoot>
				</table>
			</td>
		</tr>
		<?php
	}

	/**
	 * Template for rule preview.
	 */
	public function rule_preview_template() {
		$group_id = 0 ;
		$group    = array(
			array(
				'name'         => '',
				'priority'     => '',
				'credit_limit' => 0,
				'no_of_users'  => 0,
				'criteria'     => array()
			) ) ;
		?>
		<script type="text/template" id="tmpl-wc-cs-modal-preview-rule">
			<?php include 'views/html-preview-rule.php' ; ?>
		</script>
		<?php
	}

	/**
	 * Save custom settings.
	 */
	public function custom_types_save( $posted ) {
		$credit_rules = get_option( WC_CS_PREFIX . 'credit_line_rules', array() ) ;

		if ( empty( $credit_rules ) || ! isset( $posted[ 'rule_priority' ] ) || empty( $posted[ 'rule_priority' ] ) ) {
			return ;
		}

		foreach ( $posted[ 'rule_priority' ] as $rule_id => $priority ) {
			if ( isset( $credit_rules[ $rule_id ] ) ) {
				$credit_rules[ $rule_id ][ 'priority' ] = absint( $priority ) ;
			}
		}

		$sorted = array() ;
		foreach ( $credit_rules as $rule_id => $rule ) {
			$sorted[ $rule_id ] = absint( $rule[ 'priority' ] ) ;
		}

		asort( $sorted ) ;

		$sorted_rules = array() ;
		if ( ! empty( $sorted ) ) {
			foreach ( $sorted as $rule_id => $priority ) {
				if ( isset( $credit_rules[ $rule_id ] ) ) {
					$sorted_rules[ $rule_id ] = $credit_rules[ $rule_id ] ;
				}
			}
		}

		update_option( WC_CS_PREFIX . 'credit_line_rules', $sorted_rules ) ;
	}

}

return new WC_CS_Settings_Rules() ;
