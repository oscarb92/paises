<?php
defined( 'ABSPATH' ) || exit ;
?>
<div class="wc-backbone-modal wc_cs_credit_line_rule_preview_wrapper">
	<div class="wc-backbone-modal-content">
		<section class="wc-backbone-modal-main" role="main">
			<header class="wc-backbone-modal-header">
				<h1><?php esc_html_e( 'Edit Credit Line Rule', 'credits-for-woocommerce' ) ; ?></h1>
				<button class="modal-close modal-close-link dashicons dashicons-no-alt">
					<span class="screen-reader-text"><?php esc_html_e( 'Close modal panel', 'credits-for-woocommerce' ) ; ?></span>
				</button>
			</header>
			<article>
				<div class="wc_cs_credit_line_rule_wrapper">                    
					<p class="wc_cs_rule_name_field">
						<span><?php esc_html_e( 'Rule Name', 'credits-for-woocommerce' ) ; ?></span>
						<input type="text" name="name" value="{{ data.data.name }}">
					</p>
					<div class="wc_cs_rule_criteria_wrapper" 
						 data-group="
						 <?php
							ob_start() ;
							include 'html-add-criteria-options-group.php' ;
							echo esc_attr( ob_get_clean() ) ;
							?>
						 " 
						 data-row="
						 <?php
							ob_start() ;
							include 'html-add-criteria-options-row.php' ;
							echo esc_attr( ob_get_clean() ) ;
							?>
						 "
						 >
						<h2><?php esc_html_e( 'Criteria', 'credits-for-woocommerce' ) ; ?></h2>
						<span class="criteria_options_groups_inside">
							{{{ data.criteria_options_groups }}}
						</span>
					</div>
					<p class="wc_cs_rule_credit_line_field">
						<span><?php esc_html_e( 'Credit Limit', 'credits-for-woocommerce' ) ; ?></span>
						<input type="text" name="credit_limit" value="{{ data.data.credit_limit }}">
					</p>
					<input type="hidden" name="no_of_users" value="{{ data.data.no_of_users }}"/>
										<input type="hidden" name="priority" value="{{ data.data.priority }}"/>
					<input type="hidden" name="rule_id" value="{{ data.rule_id }}"/>
				</div>
			</article>
			<footer>                
				<div class="inner">
					<button id="btn-ok" class="save_rule button button-primary"><?php esc_html_e( 'Save Rule', 'credits-for-woocommerce' ) ; ?></button>
				</div>
			</footer>
		</section>
	</div>
</div>
<div class="wc-backbone-modal-backdrop modal-close"></div>
