<?php
/**
 * This is the Receipts file processing.
 *
 * @phpcs:disable Squiz.Commenting, Generic.Commenting
 * @phpcs:disable PSR2.Classes.PropertyDeclaration.Underscore
 */
namespace CdlS;

$uid = $_GET['uid'];
$url = 'http://190.96.113.62/telepase/factura/descargarPdf?uid=' . $uid;

$ext      = 'pdf';
$filename = 'Factura Caminos de las Sierras.pdf';

header( "Content-type: application/$ext" );
header( "Content-Disposition: attachment; filename=$filename" );

echo file_get_contents( $url );
