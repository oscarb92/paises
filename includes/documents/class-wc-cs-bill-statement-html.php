<?php

defined( 'ABSPATH' ) || exit ;

/**
 * Render Bill Statement as HTML Doc
 * 
 * @class WC_CS_Bill_Statement_HTML
 * @package Class
 */
class WC_CS_Bill_Statement_HTML {

	/**
	 * Logo attachment ID
	 * 
	 * @var int
	 */
	protected $logoAttachment ;

	/**
	 * Get the bill statement going to use.
	 * 
	 * @var WC_CS_Bill_Statement 
	 */
	protected $bill_statement ;

	/**
	 * Get the credits going to use.
	 * 
	 * @var WC_CS_Credits 
	 */
	protected $credits ;

	/**
	 * Get the hash value of statement.
	 * 
	 * @var string 
	 */
	protected $hash ;

	/**
	 * Construct our HTML Doc.
	 * 
	 * @param WC_CS_Bill_Statement|int $bill_statement
	 * @param string $hash
	 * @param WC_CS_Credits $credits
	 */
	public function __construct( $bill_statement = null, $hash = null, $credits = null ) {
		$this->bill_statement = $bill_statement ;
		$this->hash           = $hash ;
		$this->credits        = $credits ;
	}

	/**
	 * Set the logo attachment ID
	 *
	 * @param int $attachment_id
	 */
	public function set_logoAttachment( $attachment_id ) {
		$this->logoAttachment = $attachment_id ;
	}

	/**
	 * Read the bill statement going to use.
	 */
	public function read_bill_statement() {
		if ( is_a( $this->bill_statement, 'WC_CS_Bill_Statement' ) ) {
			if ( is_a( $this->credits, 'WC_CS_Credits' ) ) {
				return ;
			}

			$this->credits = new WC_CS_Credits( $this->bill_statement->get_credits_id() ) ;
			return ;
		}

		try {
			if ( ! is_numeric( $this->bill_statement ) ) {
				if ( is_null( $this->hash ) ) {
					throw new Exception() ;
				}

				$bill_statement_id = _wc_cs_get_bill_statement_id_by_hash( $this->hash ) ;
			} else {
				$bill_statement_id = absint( $this->bill_statement ) ;
			}

			if ( ! $bill_statement_id ) {
				throw new Exception() ;
			}

			$this->bill_statement = new WC_CS_Bill_Statement( $bill_statement_id ) ;

			if ( ! is_a( $this->credits, 'WC_CS_Credits' ) ) {
				$this->credits = new WC_CS_Credits( $this->bill_statement->get_credits_id() ) ;
			}
		} catch ( Exception $e ) {
			$this->bill_statement = null ;
			$this->credits        = null ;
		}
	}

	/**
	 * Prepare the content args.
	 */
	protected function prepare_content() {
		return apply_filters( 'wc_cs_get_bill_statement_content_args', array(
			'user_details'      => $this->credits->get_user_details_html( false ),
			'currency_symbol'   => get_woocommerce_currency_symbol(),
			'statement_date'    => $this->bill_statement->get_date_created(),
			'total_outstanding' => $this->bill_statement->get_total_outstanding(),
			'due_date'          => $this->bill_statement->get_due_date(),
			'prev_amount_due'   => $this->bill_statement->get_prev_amount_due(),
			'other_charges'     => $this->bill_statement->get_other_debits(),
			'transactions'      => $this->credits->get_transactions( 'billed', $this->bill_statement->get_hash() ),
				) ) ;
	}

	/**
	 * Render an HTML file
	 */
	protected function render_content() {
		$this->read_bill_statement() ;

		if ( is_null( $this->bill_statement ) ) {
			return ;
		}

		$attachment_id   = $this->logoAttachment ;
		$has_header_logo = is_numeric( $this->logoAttachment ) && $this->logoAttachment ;
		$data            = $this->prepare_content() ;

		ob_start() ;
		include 'views/html-bill-statement.php' ;
		ob_end_flush() ;
	}

	/**
	 * Generate the HTML Doc.
	 */
	public function generate() {
		// Loads an HTML string
		$this->render_content() ;
		exit() ;
	}

}
