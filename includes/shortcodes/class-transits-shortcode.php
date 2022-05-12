<?php
/**
 * This is the Transits shortcode.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

defined( 'ABSPATH' ) || die;

class Transits_Shortcode {

	protected static $_instance = null;

	public static function instance() {
		if ( is_null( self::$_instance ) ) {
			self::$_instance = new self();
		}
		return self::$_instance;
	}

	public function __construct() {

		add_shortcode( 'cdls_transits', array( $this, 'render' ) );

		add_filter(
			'template_include',
			function ( $template ) {
				if ( is_page( 'pdf-transito' ) ) {
					return ROOT_DIR . '/includes/transits/transits.php';
				}
				return $template;
			},
			99
		);

	}

	private function get_periods( $client_number ) {

		return DB()->query(
			"SELECT DISTINCT DATE_FORMAT(fecha_transito, '%m/%Y') as periodo FROM transitos WHERE nro_cliente = :nro_cliente ORDER BY fecha_transito DESC;",
			array(
				'nro_cliente' => $client_number,
			)
		);

	}

	private function get_transits( $client_number ) {

		return DB()->query(
			'SELECT transitos.id,dominio,fecha_transito,id_estacion,sentido_transito FROM `transitos`
				JOIN vehiculos
				ON vehiculos.banda_iso = transitos.banda_iso
				WHERE transitos.nro_cliente = :nro_cliente 
				GROUP BY transitos.id
				ORDER BY fecha_transito DESC;
			',
			array(
				'nro_cliente' => $client_number,
			)
		);

	}

	public function render2() {

		$is_logged_in  = AG()->is_client_logged_in();
		$client_number = API()->client_number();

		ob_start();

		if ( ! $is_logged_in || ! $client_number ) {

			?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Debés estar logueado y tener al menos un alta aprobada para ver tus tránsitos.
			</div>
			<?php
			return ob_get_clean();

		}

		$periods = $this->get_periods( $client_number );

		?>

		<table>
			<thead>
				<tr>
					<th>Período de Facturación</th>
					<th>Descargar</th>
				</tr>
			</thead>
			<tbody>

				<?php

				foreach ( $periods as $period ) :

					?>
					<tr>
						<td><?php echo esc_html( $period['periodo'] ); ?></td>
						<td>
							<a href="<?php echo esc_attr( API()->get_pdf_transits_url( $period['periodo'] ) ); ?>" target="_blank">
								<i class="fas fa-download"></i>
							</a>
						</td>
					</tr>
					<?php

				endforeach;

				?>

			</tbody>
		</table>

		<?php

		return ob_get_clean();

	}

	public function render() {

		$is_logged_in  = AG()->is_client_logged_in();
		$client_number = API()->client_number();

		ob_start();

		if ( ! $is_logged_in || ! $client_number ) {

			?>
			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				Debés estar logueado y tener al menos un alta aprobada para ver tus tránsitos.
			</div>
			<?php
			return ob_get_clean();

		}

		if ( $client_number == 23784 ) {
			ob_get_clean();
			return $this->render2();
		}

		$transits = DB()->query(
			'SELECT transitos.id,dominio,fecha_transito,id_estacion,sentido_transito FROM `transitos`
				JOIN vehiculos
				ON vehiculos.banda_iso = transitos.banda_iso
				WHERE transitos.nro_cliente = :nro_cliente 
				GROUP BY transitos.id
				ORDER BY fecha_transito DESC;
			',
			array(
				'nro_cliente' => $client_number,
			)
		);

		$stations = API()->get_stations();

		$directions = array(
			0 => 'Desde Córdoba ⤴',
			1 => '⤵ Hacia Córdoba',
		);

		if ( ! count( $transits ) ) {

			?>

			<div class="alert alert-warning alert-dismissible fade show" role="alert">
				No tienes tránsitos para mostrar (recuerda que sólo puedes visualizar los últimos 4 meses mientras finalizamos la migración de datos).
			</div>

			<?php
			return ob_get_clean();

		}

		?>
		<section class="transits-shortcode" style="overflow-x:auto;">
		<table>
			<thead>
				<tr>
					<th>Fecha</th>
					<th>Dominio</th>
					<th>Estación</th>
					<th>Sentido</th>
				</tr>
			</thead>
			<tbody>

				<?php

				foreach ( $transits as $transit ) :

					?>
					<tr>
						<td><?php echo esc_html( $transit['fecha_transito'] ); ?></td>
						<td><?php echo esc_html( $transit['dominio'] ); ?></td>
						<td><?php echo esc_html( $stations[ $transit['id_estacion'] ] ); ?></td>
						<td><?php echo esc_html( $directions[ $transit['sentido_transito'] ] ); ?></td>
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

Transits_Shortcode::instance();
