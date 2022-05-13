<?php
/**
 * This generates the transits PDF for a specific period.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

use \Dompdf\Dompdf;

defined( 'ABSPATH' ) || die;

$period = $_GET['period'] ?: '';

if ( ! $period ) {
	return;
}

// $is_logged_in  = AG()->is_client_logged_in();
$is_logged_in  = true;
$client_number = API()->client_number( 12720293 );
$client_data   = API()->get_client_data( 12720293 );
$client_number = 6064;

/**
 * 23784 65911 6064
 */

if ( ! $is_logged_in || ! $client_number ) {

	?>
	<div class="alert alert-warning alert-dismissible fade show" role="alert">
		Debés estar logueado y tener al menos un alta aprobada para ver tus tránsitos.
	</div>
	<?php
	return;

}

$start = microtime( true );

$transits = DB()->query(
	"SELECT * FROM (
        SELECT transitos.id,transitos.id_categoria,dominio,transitos.banda_iso,marca,modelo,fecha_transito,id_estacion,sentido_transito,
                DATE_FORMAT(fecha_transito, '%m/%Y') as periodo
            FROM `transitos`
            JOIN vehiculos
            ON vehiculos.banda_iso = transitos.banda_iso
            WHERE transitos.nro_cliente = :nro_cliente
            GROUP BY transitos.id
        ) as derivada
            WHERE periodo = :periodo
            ORDER BY banda_iso,fecha_transito ASC
    ",
	array(
		'nro_cliente' => $client_number,
		'periodo'     => $period,
	)
);


$transits_by_domain = array();

var_dump( 'got transits ' . ( microtime( true ) - $start ) );

foreach ( $transits as $transit ) {

	if ( ! key_exists( $transit['banda_iso'], $transits_by_domain ) ) {
		$transits_by_domain[ $transit['banda_iso'] ] = array();
	}
	$transits_by_domain[ $transit['banda_iso'] ][] = $transit;
}

var_dump( 'parsed transits ' . ( microtime( true ) - $start ) );

$stations   = API()->get_stations();
$categories = API()->get_vehicle_categories();

$directions = array(
	0 => 'Desde Córdoba',
	1 => 'Hacia Córdoba',
);

$client_name = trim( $client_data['nombre'] . ' ' . $client_data['apellido'] . ' ' . $client_data['razon_social'] );

ob_start();

?>

<style> 
	* {
		font-family: sans-serif;
		font-size: .9rem;
	}
	table {
		width: 100%;
	}

	th {
		text-align: left;
	}
	
	.break-after {
		page-break-after: always;
	}
	.details {
		display: flex;
	}

	.header {
		margin-bottom: 2rem;
		border-top: 5px double black;
		border-bottom: 5px double black;
		text-align: center;
		position: fixed;
	}

	.header-image {
		width: 50%;
	}

	.header img {
		width: 10rem;
	}

	h1,
	h2,
	small {
		font-size: .8rem;
		margin: 0;
		font-weight: normal;
		font-family: sans-serif;
	}

	h1 {
		font-weight: bold;
		font-size: 1.25rem;
		margin-bottom: .5rem;
	}

	.header h1 {
		margin: 0;
	}

	h2 {
		font-size: 1rem;
	}

	small {
		display: block;
	}

	.details {
		margin-bottom: 2rem;
	}

	.domain {
		padding: 1rem;
	}
	
	.domain h1 {
		border-bottom: 1px solid black;
	}
	
	body {
		margin-top: 10rem;
	}
</style>

<table class="header">
	<thead>
		<tr>
			<th class="header-image"><img src="https://caminosdelassierras.com.ar/wp-content/uploads/2022/02/logo-1.png"/></th>
			<th><h1>Caminos de las Sierras</h1>
			<h2>Caminos de las Sierras S.A.</h2>
			<small>Oficinas comerciales: Av. Italia 700 - Malagueño - Córdoba</small>
			<small>Tel.: 0351 - 4982400 / 10</small>
			<small>I.V.A. RESPONSABLE INSCRIPTO</small>
			<small>C.U.I.T. 30-69297553-3 Ing. Bruto: 27002552-8</small>
			<small>INICIO DE ACTIVIDAD: 23/08/98</small></th>
		</tr>
	</thead>
</table>

<div class="details">
	<h1 style="margin:0;">Detalle de tránsitos <small style="display:inline-block;">(S.E.U.O.)</small></h1>
	<h2 style="margin:0;"><b>Nombre / Razón Social:</b> <?php echo esc_html( $client_name ); ?></h2>
	<h2 style="margin:0;"><b>Nº de Cliente:</b> <?php echo esc_html( $client_data['nro_cliente'] ); ?></h2>
	<h2 style="margin:0;"><b>Período:</b> <?php echo esc_html( $period ); ?></h2>
</div>

<?php foreach ( $transits_by_domain as $iso_band => $transits ) : ?>

	<?php
		$count          = count( $transits );
		$domain         = $transits[0]['dominio'];
		$domain_and_iso = $transits[0]['dominio'] == $iso_band ? "$iso_band" : "$iso_band | $domain";
		$brand          = $transits[0]['marca'];
		$model          = $transits[0]['modelo'];
	?>

<div class="domain break-after">
	<h1><?php echo "$domain_and_iso | $brand/$model | $count TRÁNSITOS"; ?></h1>
	<table>
		<thead>
			<tr>
				<th>Fecha y Hora</th>
				<th>Dominio/Banda ISO</th>
				<th>Categoría</th>
				<th>Estación</th>
				<th>Sentido</th>
			</tr>
		</thead>
		<tbody>

			<?php foreach ( $transits as $transit ) : ?>
				<?php
					$category_id    = $transit['id_categoria'];
					$category_index = array_search(
						$category_id,
						array_column( $categories, 'id' )
					);
					$category       = $categories[ $category_index ];
				?>
				<tr>
					<td><?php echo esc_html( $transit['fecha_transito'] ); ?></td>
					<td><?php echo esc_html( $transit['banda_iso'] ); ?></td>
					<td><?php echo esc_html( $category['nombre'] ); ?></td>
					<td><?php echo esc_html( $stations[ $transit['id_estacion'] ] ); ?></td>
					<td><?php echo esc_html( $directions[ $transit['sentido_transito'] ] ); ?></td>
				</tr>
				<?php

			endforeach;

			?>

		</tbody>
	</table>

</div>

<?php endforeach; ?>

<?php

$html = ob_get_clean();

var_dump( 'rendered tarnsits ' . ( microtime( true ) - $start ) );
return;

$dompdf = new Dompdf( array( 'enable_remote' => true ) );
$dompdf->loadHtml( $html );

$dompdf->setPaper( 'A4' );

$dompdf->render();

$dompdf->stream(
	null,
	array(
		'Attachment' => 0,
	)
);
