<?php
defined( 'ABSPATH' ) || exit ;
?>
<div class="wc_cs_rule_criteria_options_group" data-group="<?php echo esc_attr( $group_id ) ; ?>">
	<?php include 'html-add-criteria-options-row.php' ; ?>
	<p class="wc_cs_rule_criteria_options_or">
		<a href="#" class="add_or_group button"><?php esc_html_e( 'OR', 'credits-for-woocommerce' ) ; ?></a>
	</p>
</div>
