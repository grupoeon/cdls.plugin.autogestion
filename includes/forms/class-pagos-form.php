<?php
/**
 * This is the payments form.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */

namespace CdlS;

defined( 'ABSPATH' ) || die;

class Pagos_Form extends Form {

	/**
	 * @phpcs:disable WordPress.Security.EscapeOutput.OutputNotEscaped
	 */
	public function build() {

		echo $this->form->open(
			$this->form->id,
			$this->form->name,
			'',
			'POST',
			'class="cdls-form"'
		);

		if ( ! API()->is_client_data_complete()['valid'] ) {
			$this->error_message( MSG()::COMPLETE_PROFILE_INFO, true );
		}

		$payment = DB()->get_payment( API()->client_number(), $_GET['id'] );

		if ( $payment ) {

			$payments = array( $payment );
			$total    = 0;

			?>

			<h1>Pagos a Realizar</h1>

			<table>
				<thead>
					<th>Tipo</th>
					<th>Identificador</th>
					<th>Fecha</th>
					<th>Saldo</th>
				</thead>
				<tbody>
					<?php
					foreach ( $payments as $payment ) :
						$total += floatval( $payment['saldo'] );
						?>
						<tr>
							<td>Factura</td>
							<td>Nº <?php echo esc_html( $payment['nro_factura'] ); ?></td>
							<td><?php echo esc_html( $payment['vencimiento_factura'] ); ?></td>
							<td><?php echo esc_html( $this->format_money( $payment['saldo'] ) ); ?></td>
						</tr>
					<?php endforeach; ?>
					<tr style="background:rgba(0,0,0,.05);">
						<td>Total</td>
						<td colspan="3"><b><?php echo esc_html( $this->format_money( $total ) ); ?></b></td>
					</tr>
				</tbody>
			</table>

			<?php

		} else {
			$this->warning_message( 'Ingrese al apartado de facturas o infracciones para iniciar un pago.', true );
			return;
		}

		?>
		<section class="section">
			<h1>Datos del medio de pago</h1>
			<section class="fields">
			<?php $this->output_form_fields(); ?>
			</section>
			<?php echo $this->form->input_hidden( 'cdls_form_id', $this->form->id ); ?>
			<br>
			<?php
			echo $this->form->input_submit(
				array(
					'type'  => 'submit',
					'name'  => 'is_submit',
					'value' => 'Realizar Pago',
					'id'    => 'is_submit',
				)
			);
			?>
		</section>
		<?php

		echo $this->form->close();

	}

	private function format_money( $float ) {
		return '$ ' . number_format( $float, 2 );
	}

	public function output_form_fields() {

		echo $this->form->select(
			array(
				'name'     => 'medio_de_pago',
				'label'    => 'Medio de Pago',
				'options'  => API()->get_payment_methods( array( 1, 3 ), 'codigo_decidir' ),
				'selected' => 'Seleccione',
			)
		);

		echo $this->form->text(
			array(
				'name'   => 'nombre',
				'label'  => 'Nombre del Titular',
				'string' => 'data-decidir="card_holder_name"',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'documento',
				'label'  => 'DNI del Titular',
				'string' => 'data-decidir="card_holder_doc_number"',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'nro_tarjeta',
				'label'  => 'Número de la tarjeta',
				'string' => 'data-decidir="card_number"',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'vencimiento_tarjeta_mes',
				'label'  => 'Vencimiento Mes (MM)',
				'string' => 'placeholder="ej. 04" data-decidir="card_expiration_month"',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'vencimiento_tarjeta_ano',
				'label'  => 'Vencimiento Año (AA)',
				'string' => 'placeholder="ej. 28" data-decidir="card_expiration_year"',
			)
		);

		echo $this->form->number(
			array(
				'name'   => 'codigo_tarjeta',
				'label'  => 'Código de Seguridad',
				'string' => 'placeholder="ej. 123" data-decidir="security_code"',
			)
		);

		?>

		<input type="hidden" name="tipo_documento" data-decidir="card_holder_doc_type" value="dni">

		<?php

		echo $this->form->hidden(
			array(
				'token_decidir' => '',
				'tarjeta_bin'   => '',
			)
		);

	}

	public static function get_validation_rules( $data ) {

		return array_merge_recursive(
			array(
				'required'      => array( 'medio_de_pago', 'nombre', 'documento', 'nro_tarjeta', 'vencimiento_tarjeta_ano', 'vencimiento_tarjeta_mes', 'codigo_tarjeta', 'token_decidir' ),
				'in'            => array(
					array(
						'medio_de_pago',
						array_keys(
							API()->get_payment_methods( array( 1, 3 ), 'codigo_decidir' )
						),
					),
				),
				'numeric'       => array( 'documento', 'nro_tarjeta' ),
				'lengthBetween' => array(
					array( 'documento', 7, 11 ),
					array( 'codigo_tarjeta', 3, 4 ),
					array( 'nro_tarjeta', 14, 16 ),
				),
				'lengthMax'     => array(
					array( 'nombre', 50 ),
					array( 'vencimiento_tarjeta_ano', 2 ),
					array( 'vencimiento_tarjeta_mes', 2 ),
				),
			)
		);

	}

	public static function get_validation_labels() {
		return array(
			'medio_de_pago'           => 'Medio de Pago',
			'vencimiento_tarjeta_ano' => 'Vencimiento Tarjeta Año',
		);
	}

	private function get_site_transaction_id() {

		$receipt_id = wp_unslash( $_GET['id'] );

		return bin2hex( openssl_random_pseudo_bytes( 2 ) )
				. '-' . bin2hex( openssl_random_pseudo_bytes( 2 ) )
				. '-' . bin2hex( openssl_random_pseudo_bytes( 2 ) )
				. '-' . str_pad( $receipt_id, 9, '0', STR_PAD_LEFT );

	}

	/**
	 * @phpcs:disable WordPress.Security.NonceVerification.Missing
	 * @phpcs:disable WordPress.Security.ValidatedSanitizedInput.InputNotValidated
	 * @phpcs:disable WordPress.PHP.DisallowShortTernary.Found
	 */
	public function submit() {

		$client_data         = API()->get_client_data();
		$token_decidir       = wp_unslash( $_POST['token_decidir'] );
		$bin                 = intval( wp_unslash( $_POST['tarjeta_bin'] ) );
		$medio_de_pago       = intval( wp_unslash( $_POST['medio_de_pago'] ) );
		$site_transaction_id = $this->get_site_transaction_id();

		$payment = DB()->get_payment( API()->client_number(), wp_unslash( $_GET['id'] ) );

		if ( ! $payment ) {
			return;
		}

		$keys_data = array(
			'public_key'  => 'df16c9dedd1c4df684abb8be4adcee27',
			'private_key' => '69407a55e126488dad010a54a4d6c176',
		);

		// Possible values: "test" o "prod".
		$ambient   = 'prod';
		$connector = new \Decidir\Connector( $keys_data, $ambient );

		$data = array(
			'site_id'             => '00080617',
			'site_transaction_id' => $site_transaction_id,
			'token'               => $token_decidir,
			'customer'            => array(
				'id'         => $client_data['nro_cliente'],
				'email'      => $client_data['correo'],
				'ip_address' => $_SERVER['REMOTE_ADDR'],
			),
			//'payment_method_id'   => 66,
			'payment_method_id'   => $medio_de_pago,
			'bin'                 => strval( $bin ),
			'amount'              => floatval( $payment['saldo'] ),
			'currency'            => 'ARS',
			'installments'        => 1,
			'description'         => '',
			'establishment_name'  => 'Caminos de las Sierras',
			'payment_type'        => 'single',
			'sub_payments'        => array(),
		);

		try {
			$response      = $connector->payment()->ExecutePayment( $data );
			$status        = $response->getStatus();
			$decidir_id    = $response->getId();
			$error_message = $response->getStatus_details()->error
								? $response->getStatus_details()->error['reason']['description']
								: '';

			$payment_id = DB()->insert(
				'
			INSERT INTO pagos ( id_tipo_pago, nro_cliente, id_medio_de_pago, monto, decidir_id, decidir_site_transaction_id, decidir_status, decidir_error_message ) 
			VALUES (1, :nro_cliente, :medio_de_pago, :monto, :id, :site_transaction_id, :status, :error_message)
			',
				array(
					'nro_cliente'         => $client_data['nro_cliente'],
					'medio_de_pago'       => $medio_de_pago,
					'site_transaction_id' => $site_transaction_id,
					'monto'               => $payment['saldo'],
					'status'              => $status,
					'id'                  => $decidir_id,
					'error_message'       => $error_message,
				)
			);

			$payment_id = DB()->insert(
				'
			INSERT INTO pagos_facturas ( id_pago, orden_venta ) 
			VALUES (:payment_id,:orden_venta)
			',
				array(
					'payment_id'  => $payment_id,
					'orden_venta' => $payment['orden_venta'],
				)
			);

			if ( 'approved' === $status ) {

				DB()->insert(
					'UPDATE facturas_morosas
						SET abonado = 1
						WHERE orden_venta = :orden_venta',
					array(
						'orden_venta' => $payment['orden_venta'],
					)
				);

				$nro_cliente          = $client_data['nro_cliente'];
				$correo_cliente       = $client_data['correo'];
				$nombre_cliente       = $client_data['nombre'] ?: '-';
				$apellido_cliente     = $client_data['apellido'] ?: '-';
				$razon_social_cliente = $client_data['razon_social'] ?: '-';
				$orden_venta          = $payment['orden_venta'];
				$saldo                = $payment['saldo'];
				$date                 = TIME()->now();

				wp_mail(
					'cobranzas@camsierras.com.ar',
					"PAGO WEB | CLIENTE Nº $nro_cliente | ORDEN VENTA Nº $orden_venta",
					"
						(Este es un mensaje automático)
						Ingresó un nuevo pago a través del sitio.

						<b>Número de cliente:</b> $nro_cliente
						<b>Correo del cliente:</b> $correo_cliente
						<b>Nombre:</b> $nombre_cliente
						<b>Apellido:</b> $apellido_cliente
						<b>Razón Social:</b> $razon_social_cliente

						<b>Orden venta:</b> $orden_venta
						<b>Monto:</b> $ $saldo
						<b>Código de operación (Decidir):</b> $site_transaction_id
						<b>Fecha del pago:</b> $date
					",
					array( 'Reply-To: ' . $client_data['correo'] )
				);

				$this->success_message( 'Tu pago se procesó correctamente.' );

			} else {
				// rejected.

				$this->error_message(
					'Ocurrió un error al procesar tu pago. Error: ' . $error_message
				);
			}

			return;
		} catch ( \PDOException $e ) {
			$this->error_message( 'Ocurrió un error al procesar tu pago.' );
		} catch ( \Exception $e ) {
			$this->error_message( 'Ocurrió un error al procesar tu pago.' );
		}

	}

	public function current_user_can_submit() {
		return AG()->is_client_logged_in() && API()->is_client_data_complete()['valid'];
	}

}
