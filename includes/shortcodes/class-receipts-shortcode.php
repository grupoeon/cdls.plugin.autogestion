<?php
/**
 * This is the Receipts shortcode.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Receipts_Shortcode {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_shortcode( 'cdls_receipts', array( $this, 'render' ) );

	}

	public function render() {

		$is_logged_in  = AG()->is_client_logged_in();
		$client_number = API()->client_number();

		ob_start();

		if ( ! $is_logged_in || ! $client_number ) {

			?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Debés estar logueado y tener al menos un alta aprobada para ver tus facturas.
			</div>
			<?php
			return ob_get_clean();

		}

		$receipts = DB()->query(
			'SELECT * FROM `facturas`
				WHERE nro_cliente = :nro_cliente 
				ORDER BY vencimiento_factura DESC,
						nro_factura DESC
			',
			array(
				'nro_cliente' => $client_number,
			)
		);

		if ( ! count( $receipts ) ) {

			?>

			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				No tienes facturas para mostrar (recuerda que sólo puedes visualizar los últimos 2 meses mientras finalizamos la migración de datos).
			</div>

			<?php
			return ob_get_clean();

		}

		?>
		<section class="receipts-shortcode" style="overflow-x:auto;">
		<table>
			<thead>
				<tr>
					<th>Nº Factura</th>
					<th>Tipo</th>
					<th>Vencimiento</th>
					<th>Monto</th>
					<th>Descargar</th>
				</tr>
			</thead>
			<tbody>

				<?php

				foreach ( $receipts as $receipt ) :

					?>
					<tr>
						<td><?php echo esc_html( $receipt['nro_factura'] ); ?></td>
						<td><?php echo esc_html( $receipt['tipo_factura'] ); ?></td>
						<td><?php echo esc_html( $receipt['vencimiento_factura'] ); ?></td>
						<td><?php echo esc_html( $receipt['monto_factura'] ); ?></td>
						<td>
							<a href="<?php echo esc_html( API()->get_receipt_url( $receipt ) ); ?>">
								<i class="fas fa-download"></i>
							</a>
						</td>
					</tr>
					<?php

				endforeach;

				?>

			</tbody>
		</table>
		</section>
		
		<?php

		return ob_get_clean();

	}

}

Receipts_Shortcode::instance();
