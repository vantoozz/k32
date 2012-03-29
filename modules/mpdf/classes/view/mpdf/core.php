<?php defined('SYSPATH') or die('No direct script access.');
/**
 * Render a view as a PDF.
 *
 * @packge     Kohana-mPDF
 * @author     Woody Gilk <woody.gilk@kohanaphp.com>
 * @author     Sergei Gladkovskiy <smgladkovskiy@gmail.com>
 * @copyright  (c) 2009 Woody Gilk
 * @license    MIT
 */
abstract class View_mPDF_Core extends View {

	public static function factory($file = NULL, array $data = NULL)
	{
		return new View_MPDF($file, $data);
	}

	public function render($file = NULL)
	{
		// Render the HTML normally
		$html = parent::render($file);

		// Render the HTML to a PDF
		$mpdf = new mPDF('UTF-8', 'A4');

		$mpdf->WriteHTML($html);

		return $mpdf->output();

	}

} // End View_MPDF