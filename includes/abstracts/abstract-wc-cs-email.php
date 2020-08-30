<?php

/**
 * Email Class.
 * 
 * @abstract WC_CS_Abstract_Email
 * @extends WC_Email
 */
abstract class WC_CS_Abstract_Email extends WC_Email {

	/**
	 * Email supports.
	 *
	 * @var array Supports
	 */
	public $supports = array( 'mail_to_admin' ) ;

	/**
	 * Constructor.
	 */
	public function __construct() {
		$this->template_base = WC_CS_TEMPLATE_PATH ;

		// Call WC_Email constuctor
		parent::__construct() ;
	}

	/**
	 * Check email supports the given type.
	 *
	 * @param string $type
	 * @return bool
	 */
	public function supports( $type ) {
		return in_array( $type, $this->supports ) ;
	}

	/**
	 * Maybe trigger the sending of this email.
	 */
	public function maybe_trigger() {
		if ( $this->is_enabled() && $this->get_recipient() ) {
			$this->send( $this->get_recipient(), $this->get_subject(), $this->get_content(), $this->get_headers(), $this->get_attachments() ) ;
		}
	}

	/**
	 * Get valid recipients.
	 *
	 * @return string
	 */
	public function get_recipient() {
		$recipient = '' ;
		if ( $this->supports( 'recipient' ) ) {
			$recipient = $this->get_option( 'recipient', get_option( 'admin_email' ) ) ;
		} else if ( $this->supports( 'mail_to_admin' ) && 'yes' === $this->get_option( 'mail_to_admin' ) ) {
			$recipient = get_option( 'admin_email' ) ;
		}

		if ( '' === $this->recipient ) {
			$this->recipient = $recipient ;
		} else {
			$this->recipient .= ', ' ;
			$this->recipient .= $recipient ;
		}

		return parent::get_recipient() ;
	}

	/**
	 * Get email type.
	 *
	 * @return string
	 */
	public function get_email_type() {
		return class_exists( 'DOMDocument' ) ? 'html' : '' ;
	}

	/**
	 * Format date to display.
	 *
	 * @param int|string $date
	 * @return string
	 */
	public function format_date( $date = '' ) {
		return _wc_cs_format_datetime( $date, false ) ;
	}

	/**
	 * Get content args.
	 *
	 * @return array
	 */
	public function get_content_args() {

		return array(
			'blogname'      => $this->get_blogname(),
			'site_url'      => home_url(),
			'email_heading' => $this->get_heading(),
			'sent_to_admin' => false,
			'plain_text'    => false,
			'email'         => $this,
				) ;
	}

	/**
	 * Get content HTMl.
	 *
	 * @return string
	 */
	public function get_content_html() {
		ob_start() ;
		_wc_cs_get_template( $this->template_html, $this->get_content_args() ) ;
		return ob_get_clean() ;
	}

	/**
	 * Get content plain.
	 *
	 * @return string
	 */
	public function get_content_plain() {
		return '' ;
	}

	/**
	 * Display form fields
	 */
	public function init_form_fields() {
		$this->form_fields = array(
			'enabled' => array(
				'title'   => __( 'Enable/Disable', 'credits-for-woocommerce' ),
				'type'    => 'checkbox',
				'label'   => __( 'Enable this email notification', 'credits-for-woocommerce' ),
				'default' => 'yes'
			) ) ;

		if ( $this->supports( 'recipient' ) ) {
			$this->form_fields = array_merge( $this->form_fields, array(
				'recipient' => array(
					'title'       => __( 'Recipient(s)', 'credits-for-woocommerce' ),
					'type'        => 'text',
					/* translators: 1: admin email */
					'description' => sprintf( __( 'Enter recipients (comma separated) for this email. Defaults to %1$s.', 'credits-for-woocommerce' ), '<code>' . esc_attr( get_option( 'admin_email' ) ) . '</code>' ),
					'placeholder' => '',
					'default'     => '',
					'desc_tip'    => true,
				) ) ) ;
		}

		$this->form_fields = array_merge( $this->form_fields, array(
			'subject' => array(
				'title'       => __( 'Email Subject', 'credits-for-woocommerce' ),
				'type'        => 'text',
				/* translators: 1: email subject */
				'description' => sprintf( __( 'Defaults to <code>%1$s</code>', 'credits-for-woocommerce' ), $this->subject ),
				'placeholder' => '',
				'default'     => ''
			),
			'heading' => array(
				'title'       => __( 'Email Heading', 'credits-for-woocommerce' ),
				'type'        => 'text',
				/* translators: 1: email heading */
				'description' => sprintf( __( 'Defaults to <code>%1$s</code>', 'credits-for-woocommerce' ), $this->heading ),
				'placeholder' => '',
				'default'     => ''
			) ) ) ;

		if ( $this->supports( 'mail_to_admin' ) ) {
			$this->form_fields = array_merge( $this->form_fields, array(
				'mail_to_admin' => array(
					'title'   => __( 'Send Email to Admin', 'credits-for-woocommerce' ),
					'type'    => 'checkbox',
					'default' => 'no'
				) ) ) ;
		}
	}

}
