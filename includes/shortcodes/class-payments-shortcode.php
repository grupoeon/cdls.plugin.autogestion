<?php
/**
 * This is the Payments shortcode.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Payments_Shortcode {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_shortcode( 'cdls_payments', array( $this, 'render' ) );

	}

	public function render() {

		$is_logged_in  = AG()->is_client_logged_in();
		$client_number = API()->client_number();

		ob_start();

		if ( ! $is_logged_in || ! $client_number ) {

			?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Debés estar logueado y tener al menos un alta aprobada para poder realizar pagos.
			</div>
			<?php
			return ob_get_clean();

		}

		?>
		
		<section class="payments-shortcode">
			<?php if ( 'GET' === $_SERVER['REQUEST_METHOD'] ) : ?>
			<form action="" method="post" id="cdls-payments-form" class="cdls-form">
				<section class="fields">
					<div class="mb-3">
						<label for="card_number">Numero de tarjeta:</label>
						<input type="text" data-decidir="card_number" placeholder="XXXXXXXXXXXXXXXX" value="5287456212270000"/>
					</div>
					<div class="mb-3">
						<label for="security_code">Codigo de seguridad:</label>
						<input type="text"  data-decidir="security_code" placeholder="XXX" value="" />
					</div>
					<div class="mb-3">
						<label for="card_expiration_month">Mes de vencimiento:</label>
						<input type="text"  data-decidir="card_expiration_month" placeholder="MM" value="12"/>
					</div>
					<div class="mb-3">
						<label for="card_expiration_year">Año de vencimiento:</label>
						<input type="text"  data-decidir="card_expiration_year" placeholder="AA" value="24"/>
					</div>
					<div class="mb-3">
						<label for="card_holder_name">Nombre del titular:</label>
						<input type="text" data-decidir="card_holder_name" placeholder="TITULAR" value="TITULAR"/>
					</div>
					<div class="mb-3">
						<label for="card_holder_doc_type">Tipo de documento:</label>
						<select data-decidir="card_holder_doc_type">
							<option value="dni">DNI</option>
						</select>
					</div>
					<div class="mb-3">
						<label for="card_holder_doc_type">Numero de documento:</label>
						<input type="text"data-decidir="card_holder_doc_number" placeholder="XXXXXXXXXX" value="27859328"/>
					</div>
					<input type="hidden" name="decidir_token">
					<input type="submit" value="Pagar" />
					</section>
				</form>
			</section>
			<?php else : ?>
				<?php

					$keys_data = array(
						'public_key'  => 'df16c9dedd1c4df684abb8be4adcee27',
						'private_key' => '69407a55e126488dad010a54a4d6c176',
					);

					$ambient   = 'prod';//valores posibles: "test" o "prod"
					$connector = new \Decidir\Connector( $keys_data, $ambient );

					$data = array(
						'site_id'             => '00080617',
						'site_transaction_id' => 'CDLS TEST ' . rand( 0, 1000 ),
						'token'               => $_POST['decidir_token'],
						'customer'            => array(
							'id'         => 'customer',
							'email'      => 'user@mail.test',
							'ip_address' => $_SERVER['REMOTE_ADDR'],
						),
						//'payment_method_id'   => 66,
						'payment_method_id'   => 105,
						'bin'                 => '528745',
						'amount'              => 1.00,
						'currency'            => 'ARS',
						'installments'        => 1,
						'description'         => '',
						'establishment_name'  => 'Caminos de las Sierras',
						'payment_type'        => 'single',
						'sub_payments'        => array(),
					);

					r( $data );

					try {
						$response = $connector->payment()->ExecutePayment( $data );
						r( $response );
					} catch ( \Exception $e ) {
						r( $e->getData() );
					}

					?>
			<?php endif; ?>
		
		<?php

		return ob_get_clean();

	}

}

Payments_Shortcode::instance();
