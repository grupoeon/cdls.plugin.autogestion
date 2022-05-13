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

$is_logged_in  = AG()->is_client_logged_in();
$client_number = API()->client_number();
$client_data   = API()->get_client_data();

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

foreach ( $transits as $transit ) {

	if ( ! key_exists( $transit['banda_iso'], $transits_by_domain ) ) {
		$transits_by_domain[ $transit['banda_iso'] ] = array();
	}
	$transits_by_domain[ $transit['banda_iso'] ][] = $transit;
}

$stations   = API()->get_stations();
$categories = API()->get_vehicle_categories();

$directions = array(
	0 => 'Desde Córdoba',
	1 => 'Hacia Córdoba',
);

$client_name = trim( $client_data['nombre'] . ' ' . $client_data['apellido'] . ' ' . $client_data['razon_social'] );


/*
  ┌─────────────────────────────────────────────────────────────────────────┐
  │ START PDF                                                               │
  └─────────────────────────────────────────────────────────────────────────┘
 */

// Extend the TCPDF class to create custom Header and Footer
class MYPDF extends \TCPDF {

	//Page header
	public function Header() {
		// Logo
		$image_file = ROOT_DIR . '/public/logo.png';
		$this->Image( $image_file, 15, 10, 30, '', 'PNG', '', 'T', false, 300, '', false, false, 0, false, false, false );
		// Set font
		$this->SetFont( 'helvetica', 'B', 14 );
		$this->Ln();
		$this->Cell( 0, 5, 'Caminos de las Sierras', 0, false, 'R', 0, '', 0, false, 'M', 'M' );
		$this->Ln();
		$this->SetFont( 'helvetica', 'R', 9 );
		$this->Cell( 0, 5, 'I.V.A. RESPONSABLE INSCRIPTO', 0, false, 'R', 0, '', 0, false, 'M', 'M' );
		$this->Ln();
		$this->Cell( 0, 5, 'C.U.I.T. 30-69297553-3 Ing. Bruto: 27002552-8', 0, false, 'R', 0, '', 0, false, 'M', 'M' );
		$this->Ln();
		$this->Cell( 0, 5, 'INICIO DE ACTIVIDAD: 23/08/98', 0, false, 'R', 0, '', 0, false, 'M', 'M' );
		$this->Ln();
		$this->Cell( 0, 5, 'Oficinas comerciales: Av. Italia 700 - Malagueño - Córdoba', 0, false, 'R', 0, '', 0, false, 'M', 'M' );
		$this->Ln();
		$this->Cell( 0, 5, 'Tel.: 0351 - 4982400 / 10', 0, false, 'R', 0, '', 0, false, 'M', 'M' );

	}

	// Page footer
	public function Footer() {
		// Position at 15 mm from bottom
		$this->SetY( -15 );
		// Set font
		$this->SetFont( 'helvetica', 'I', 8 );
		// Page number
		$this->Cell( 0, 10, 'Página ' . $this->getAliasNumPage() . ' de ' . $this->getAliasNbPages(), 0, false, 'C', 0, '', 0, false, 'T', 'M' );
	}

		// Colored table
	public function TransitsTable( $headers, $transits, $stations, $directions, $categories ) {
		// Colors, line width and bold font
		$this->SetFillColor( 255 );
		$this->SetTextColor( 0 );
		$this->SetDrawColor( 0 );
		$this->SetLineWidth( 0.3 );
		$this->SetFont( 'Helvetica', 'B', 10 );
		$width = array( 40, 60, 30, 20, 30 );
		// Header
		$num_headers = count( $headers );
		for ( $i = 0; $i < $num_headers; ++$i ) {
			$this->Cell( $width[ $i ], 0, $headers[ $i ], 1, 0, 'C', 1 );
		}
		$this->Ln();
		// Color and font restoration
		$this->SetFillColor( 255 );
		$this->SetTextColor( 0 );
		$this->SetFont( '' );
		// Data
		$fill = 255;
		foreach ( $transits as $transit ) {

			$category_id    = $transit['id_categoria'];
			$category_index = array_search(
				$category_id,
				array_column( $categories, 'id' )
			);
			$category       = $categories[ $category_index ];

			$this->Cell( $width[0], 3, $transit['fecha_transito'], 'LR', 0, 'L', $fill );
			$this->Cell( $width[1], 3, $transit['banda_iso'], 'LR', 0, 'L', $fill );
			$this->Cell( $width[2], 3, $category['nombre'], 'LR', 0, 'L', $fill );
			$this->Cell( $width[3], 3, $stations[ $transit['id_estacion'] ], 'LR', 0, 'L', $fill );
			$this->Cell( $width[4], 3, $directions[ $transit['sentido_transito'] ], 'LR', 0, 'L', $fill );
			$this->Ln();
		}
		$this->Cell( array_sum( $w ), 0, '', 'T' );
	}

}

// create new PDF document
$pdf = new MYPDF( PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false );

// set document information
$pdf->SetCreator( PDF_CREATOR );
$pdf->SetAuthor( 'Caminos de las Sierras' );
$pdf->SetTitle( "Detalle de Tránsitos - $period - $client_name" );
$pdf->SetSubject( "Detalle de Tránsitos - $period - $client_name" );

// set default header data
$pdf->SetHeaderData( PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE, PDF_HEADER_STRING );

// set header and footer fonts
$pdf->setHeaderFont( array( PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN ) );
$pdf->setFooterFont( array( PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA ) );

// set default monospaced font
$pdf->SetDefaultMonospacedFont( PDF_FONT_MONOSPACED );

// set margins
$pdf->SetMargins( PDF_MARGIN_LEFT, PDF_MARGIN_TOP + 15, PDF_MARGIN_RIGHT );
$pdf->SetHeaderMargin( PDF_MARGIN_HEADER );
$pdf->SetFooterMargin( PDF_MARGIN_FOOTER );

// set auto page breaks
$pdf->SetAutoPageBreak( true, PDF_MARGIN_BOTTOM );

// set image scale factor
$pdf->setImageScale( PDF_IMAGE_SCALE_RATIO );

// set font
$pdf->SetFont( 'Helvetica', 'B', 12 );

$pdf->AddPage();

ob_start();

?>

<div class="details" style="text-align:center;">
	<h1>DETALLE DE TRÁNSITOS</h1>
	<h2 style="font-size: 14px; font-weight:normal;"><b>NOMBRE / RAZÓN SOCIAL:</b> <?php echo esc_html( $client_name ); ?></h2>
	<h2 style="font-size: 14px; font-weight:normal;"><b>Nº CLIENTE:</b> <?php echo esc_html( $client_data['nro_cliente'] ); ?></h2>
	<h2 style="font-size: 14px; font-weight:normal;"><b>PERÍODO:</b> <?php echo esc_html( $period ); ?></h2>
	<h2 style="font-size: 14px; font-weight:normal;">S.E.U.O.</h2>
</div>

<?php

$pdf->writeHTML( ob_get_clean(), true, false, true, false, '' );

foreach ( $transits_by_domain as $iso_band => $transits ) {

	$pdf->AddPage();

	$count          = count( $transits );
	$domain         = $transits[0]['dominio'];
	$domain_and_iso = $transits[0]['dominio'] == $iso_band ? "$iso_band" : "$iso_band | $domain";
	$brand          = $transits[0]['marca'];
	$model          = $transits[0]['modelo'];

	ob_start();

	$pdf->SetFont( 'Helvetica', 'B', 8 );


	?>
	<h1><?php echo "$domain_and_iso | $brand / $model | $count TRÁNSITOS"; ?></h1>
	<br>
	<?php

	$pdf->writeHTML( ob_get_clean(), true, false, true, false, '' );

	$headers = array(
		'Fecha y Hora',
		'Dominio/Banda ISO',
		'Categoría',
		'Estacion',
		'Sentido',
	);

	$pdf->TransitsTable( $headers, $transits, $stations, $directions, $categories );

}

// ---------------------------------------------------------

//Close and output PDF document
$pdf->Output( "$client_name-$period.pdf", 'I' );

//============================================================+
// END OF FILE
//============================================================+
