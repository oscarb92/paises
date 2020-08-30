<?php
/**
 * Our Admin Functions
 */
defined( 'ABSPATH' ) || exit ;

/**
 * Get our screen ids.
 *
 * @return array
 */
function _wc_cs_get_screen_ids() {
	$wc_cs_screen_id = sanitize_title( __( 'Credits', 'credits-for-woocommerce' ) ) ;
	$screen_ids      = array(
		'wc_cs_credits',
		'wc_cs_adminfunds_txn',
		'wc_cs_vrtualfundstxn',
		$wc_cs_screen_id . '_page_wc_cs_settings',
			) ;

	return apply_filters( 'wc_cs_screen_ids', $screen_ids ) ;
}

/**
 * Create a page and store the ID in an option.
 *
 * @param mixed  $slug Slug for the new page.
 * @param string $option Option name to store the page's ID.
 * @param string $page_title (default: '') Title for the new page.
 * @param string $page_content (default: '') Content for the new page.
 * @param int    $post_parent (default: 0) Parent for the new page.
 * @return int page ID.
 */
function _wc_cs_create_page( $slug, $option = '', $page_title = '', $page_content = '', $post_parent = 0 ) {
	$option_value = '' !== $option ? get_option( $option ) : 0 ;

	if ( $option_value > 0 ) {
		$page_object = get_post( $option_value ) ;

		if ( $page_object && 'page' === $page_object->post_type && ! in_array( $page_object->post_status, array( 'pending', 'trash', 'future', 'auto-draft' ), true ) ) {
			// Valid page is already in place.
			return $page_object->ID ;
		}
	}

	$page_data = array(
		'post_status'    => 'publish',
		'post_type'      => 'page',
		'post_author'    => 1,
		'post_name'      => $slug,
		'post_title'     => $page_title,
		'post_content'   => $page_content,
		'post_parent'    => $post_parent,
		'comment_status' => 'closed',
			) ;

	$page_id = wp_insert_post( $page_data ) ;

	if ( $option ) {
		update_option( $option, $page_id ) ;
	}

	return $page_id ;
}

/**
 * Get WC search field
 * 
 * @param array $args
 * @param bool $echo
 * @return string echo search field
 */
function _wc_cs_search_field( $args = array(), $echo = true ) {

	$args = wp_parse_args( $args, array(
		'class'       => '',
		'id'          => '',
		'name'        => '',
		'type'        => '',
		'action'      => '',
		'placeholder' => '',
		'css'         => 'width: 50%;',
		'multiple'    => true,
		'allow_clear' => true,
		'selected'    => true,
		'options'     => array()
			) ) ;

	ob_start() ;
	?>
	<select 
		id="<?php echo esc_attr( $args[ 'id' ] ) ; ?>" 
		class="<?php echo esc_attr( $args[ 'class' ] ) ; ?>" 
		name="<?php echo esc_attr( '' !== $args[ 'name' ] ? $args[ 'name' ] : $args[ 'id' ]  ) ; ?><?php echo ( $args[ 'multiple' ] ) ? '[]' : '' ; ?>" 
		data-action="<?php echo esc_attr( $args[ 'action' ] ) ; ?>" 
		data-placeholder="<?php echo esc_attr( $args[ 'placeholder' ] ) ; ?>" 
		<?php echo ( $args[ 'allow_clear' ] ) ? 'data-allow_clear="true"' : '' ; ?> 
		<?php echo ( $args[ 'multiple' ] ) ? 'multiple="multiple"' : '' ; ?> 
		style="<?php echo esc_attr( $args[ 'css' ] ) ; ?>">
			<?php
			if ( ! is_array( $args[ 'options' ] ) ) {
				$args[ 'options' ] = ( array ) $args[ 'options' ] ;
			}

			$args[ 'options' ] = array_filter( $args[ 'options' ] ) ;

			foreach ( $args[ 'options' ] as $id ) {
				$option_value = '' ;

				switch ( $args[ 'type' ] ) {
					case 'product':
						$product = wc_get_product( $id ) ;
						if ( $product ) {
							$option_value = wp_kses_post( $product->get_formatted_name() ) ;
						}
						break ;
					case 'customer':
						$user = get_user_by( 'id', $id ) ;
						if ( $user ) {
							$option_value = ( esc_html( $user->display_name ) . '(#' . absint( $user->ID ) . ' &ndash; ' . esc_html( $user->user_email ) . ')' ) ;
						}
						break ;
					default:
						$post = get_post( $id ) ;
						if ( $post ) {
							$option_value = sprintf( '(#%s) %s', $post->ID, wp_kses_post( $post->post_title ) ) ;
						}
						break ;
				}

				if ( $option_value ) {
					?>
				<option value="<?php echo esc_attr( $id ) ; ?>" <?php echo ( $args[ 'selected' ] ) ? 'selected="selected"' : '' ; ?>><?php echo esc_html( $option_value ) ; ?></option>
					<?php
				}
			}
			?>
	</select>
	<?php
	if ( $echo ) {
		ob_end_flush() ;
	} else {
		return ob_get_clean() ;
	}
}
