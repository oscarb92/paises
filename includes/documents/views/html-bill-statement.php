<!DOCTYPE html>
<html>
	<title><?php echo wp_title() ; ?></title>
	<meta charset="UTF-8">
	<meta name="viewport" content="width=device-width, initial-scale=1">
	<head>
		<script>
			function printStatement() {
				window.print() ;
			}
		</script>
		<style type="text/css">
			html,
			body{
				width:100%;
				margin:0;
				padding:0;
				float:left;
			}
			.wc-cs-statement-wrapper {
				width:100%;
				margin:0;
				padding:0;
				float:left;
			}
			header {
				min-height: 50px;
				color: #000;
				width:100%;
				text-align: center;
				float:left;
				padding:20px 0px;
			}
			.wc-cs-statement-header-container{
				width:80%;
				margin:0 auto;
				background:#fff;
			}
			header table td {
				text-align:left;
			}
			header table td img {
				width:200px;
				height:auto;
				margin:0;
			}
			header table td h2 {
				text-align: right;
				color: #000;
				font-size:18px;
				margin:0;
			}
			header h2 {
				text-align: center;
				color: #000;
				font-size:18px;
				margin-top:20px;
				line-height: 35px;
			}
			footer {
				margin-top:10px;
				height: 100px;
				color: #000;
				text-align: center;
				line-height: 35px;
				width:100%;
				float:left;            
			}
			main {
				width:100%;
				margin:0;
				padding:0;
				float:left;
			}
			footer table tr td span {
				display:block;
				text-align:right;
			}
			header .wc-cs-statement-header-container{
				border-bottom:1px solid #ccc;
			}
			footer .wc-cs-statement-header-container {
				border-top:1px solid #ccc;
			}
			.wc-cs-transaction-table tbody tr:nth-child(2n){
				background:#fafafa;
			}
			.wc-cs-print-button {
				position: fixed;
				top:10px;
				right:10px
			}
			.wc-cs-print-button button {
				background:#ea4a86 url('<?php echo esc_attr( WC_CS_URL ) ; ?>/assets/images/printer-5-16.png') no-repeat left 7px center;
				color:#fff;
				font-size:14px;
				padding:10px 20px 10px 30px;
				border-radius: 5px;
				border:none;
				font-weight:bold;
				cursor:pointer;
			}
			.wc-cs-print-button button:hover {
				box-shadow:0 0 3px #000;
			}
			@media print {
				.wc-cs-print-button {
					display:none;
				}
				header{
					padding-top:0;
				}
				.wc-cs-statement-header-container {
					width:100%;
				}
				footer {
					display:none;
				}
			}
		</style>
	</head>
	<body>
		<div class="wc-cs-print-button">
			<button onclick="printStatement()"><?php esc_html_e( 'Print Statement', 'credits-for-woocommerce' ) ; ?></button>
		</div>
		<div class="wc-cs-statement-wrapper">
			<header>
				<div class="wc-cs-statement-header-container">
					<?php
					if ( $has_header_logo ) {
						?>
						<table style="border-collapse:collapse;width:100%;">
							<tr>
								<td style="width:50%;"><?php echo wp_get_attachment_image( $attachment_id, array( 200, 100 ) ) ; ?></td>
								<td style="width:50%;"><h2><?php esc_html_e( 'Bill Statement', 'credits-for-woocommerce' ) ; ?></h2></td>
							</tr>
						</table>
						<?php
					} else {
						?>
						<h2><?php esc_html_e( 'Bill Statement', 'credits-for-woocommerce' ) ; ?></h2>
						<?php
					}
					?>
				</div>
			</header>
			<main>
				<div class="wc-cs-statement-header-container">
					<table style="border-collapse:collapse;width:100%;">
						<tr>
							<td style="border:1px solid #ccc;width:50%;padding-left:15px;">
								<strong style="margin-top:10px;margin-left:10px;"><?php echo wp_kses_post( wpautop( wptexturize( $data[ 'user_details' ] ) ) ) ; ?></strong>
								<p style="font-size:12px;"></p>
							</td>
							<td style="border:1px solid #ccc;width:50%;">
								<table style="border-collapse:collapse;width:100%;" cellpadding="12" cellspacing="0">
									<tr>
										<td style="width:45%;"><?php esc_html_e( 'Statement Date', 'credits-for-woocommerce' ) ; ?></td>
										<td style="width:10%;">:</td>
										<td style="width:45%;"><?php echo wp_kses_post( _wc_cs_format_datetime( $data[ 'statement_date' ], false ) ) ; ?></td>
									</tr>                        
									<tr>
										<td style="width:45%;"><?php esc_html_e( 'Total Amount Due', 'credits-for-woocommerce' ) ; ?></td>
										<td style="width:10%;">:</td>
										<td style="width:45%;"><?php echo wp_kses_post( $data[ 'total_outstanding' ] ) ; ?></td>
									</tr>
									<tr>
										<td style="width:45%;"><?php esc_html_e( 'Remember to Pay By', 'credits-for-woocommerce' ) ; ?></td>
										<td style="width:10%;">:</td>
										<td style="width:45%;"><?php echo wp_kses_post( _wc_cs_format_datetime( $data[ 'due_date' ], false ) ) ; ?></td>
									</tr>                            
								</table>
							</td>
						</tr>
					</table>
					<table cellpadding="2" style="width:100%;">
						<tr>
							<td></td>
						</tr>
						<tr>
							<td style="background-color:#b07612;color:#fff;padding:10px 0px 10px 10px"><strong><?php esc_html_e( 'My Summary', 'credits-for-woocommerce' ) ; ?></strong></td>
						</tr>
						<tr>
							<td></td>
						</tr>
					</table>
					<table cellpadding="5" style="width:100%;" >
						<tr>
							<td style="border:1px solid #ccc;text-align:center;width:21.25%;"><?php esc_html_e( 'Previous Amount Due ', 'credits-for-woocommerce' ) ; ?></td>
							<td style="text-align:center;width:5%;"></td>                
							<td style="border:1px solid #ccc;text-align:center;width:21.25%;"><?php esc_html_e( 'Purchases & Other Charges', 'credits-for-woocommerce' ) ; ?></td>
							<td style="text-align:center;width:5%;"></td>							
							<td style="border:1px solid #ccc;text-align:center;width:21.25%;"><?php esc_html_e( 'Total Amount Due', 'credits-for-woocommerce' ) ; ?></td>
						</tr>
						<tr>                
							<td style="border:1px solid #ccc;text-align:center;background-color:#f1f1f1;"><?php echo wp_kses_post( $data[ 'prev_amount_due' ] ) ; ?></td>
							<td style="text-align:center;">+</td>
							<td style="border:1px solid #ccc;text-align:center;background-color:#f1f1f1;"><?php echo wp_kses_post( $data[ 'other_charges' ] ) ; ?></td>							
							<td style="text-align:center;">=</td>
							<td style="border:1px solid #ccc;text-align:center;background-color:#f1f1f1;"><?php echo wp_kses_post( $data[ 'total_outstanding' ] ) ; ?></td>
						</tr>
						<tr>
							<td colspan="4"></td>
						</tr>
					</table>
					<table cellpadding="7" style="border-collapse:collapse;width:100%;" class="wc-cs-transaction-table">
						<tr>
							<th style="border:1px solid #ccc;background-color:#b07612;color:#fff;width:20%;text-align:center;"><?php esc_html_e( 'Date', 'credits-for-woocommerce' ) ; ?></th>
							<th style="border:1px solid #ccc;background-color:#b07612;color:#fff;width:20%;text-align:center;"><?php esc_html_e( 'Activity', 'credits-for-woocommerce' ) ; ?></th>
							<th style="border:1px solid #ccc;background-color:#b07612;color:#fff;width:20%;text-align:center;"><?php /* translators: 1 : curruency symbol */ esc_html_e( sprintf( 'Credit(%1$s)', $data[ 'currency_symbol' ] ), 'credits-for-woocommerce' ) ; ?></th>
							<th style="border:1px solid #ccc;background-color:#b07612;color:#fff;width:20%;text-align:center;"><?php /* translators: 1 : currency symbol */ esc_html_e( sprintf( 'Debit(%1$s)', $data[ 'currency_symbol' ] ), 'credits-for-woocommerce' ) ; ?></th>
						</tr>
						<tbody>
							<?php if ( ! empty( $data[ 'transactions' ] ) ) { ?>
								<?php foreach ( $data[ 'transactions' ] as $txn ) : ?>
									<tr>
										<td style="border:1px solid #ccc;"><?php echo wp_kses_post( _wc_cs_format_datetime( $txn->get_date_created() ) ) ; ?></td>
										<td style="border:1px solid #ccc;"><?php echo wp_kses_post( $txn->get_activity() ) ; ?></td>
										<td style="border:1px solid #ccc;"><?php echo wp_kses_post( $txn->get_credited() ) ; ?></td>
										<td style="border:1px solid #ccc;"><?php echo wp_kses_post( $txn->get_debited() ) ; ?></td>
									</tr>
								<?php endforeach ; ?>
							<?php } else { ?>
								<tr>
									<td colspan="4" style="border:1px solid #ccc;"><?php esc_html_e( 'No transactions available.', 'credits-for-woocommerce' ) ; ?></td>
								</tr>
							<?php } ?>
						</tbody>
					</table>
				</div>
			</main>
			<footer>
				<div class="wc-cs-statement-header-container">
					<table style="border-collapse:collapse;width:100%;text-align: center" cellpadding="7" >
						<tr>
							<td><?php esc_html_e( 'Copyright &copy;', 'credits-for-woocommerce' ) ; ?> <?php echo esc_html( _wc_cs_prepare_datetime( _wc_cs_get_time( 'timestamp' ), 'Y' ) ) ; ?></td>

						</tr>
					</table>
				</div>
			</footer>
		</div>
	</body>
</html>


