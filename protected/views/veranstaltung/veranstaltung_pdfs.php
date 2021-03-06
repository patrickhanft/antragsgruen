<?php

/**
 * @var VeranstaltungController $this
 * @var Veranstaltung $veranstaltung
 * @var array|array[] $antraege
 * @var Sprache $sprache
 */


header('Content-type: application/pdf; charset=UTF-8');


$cached = Yii::app()->cache->get("pdf_" . $veranstaltung->id);
if ($cached !== false) {
	echo $cached;
} else {


// Muss am Anfang stehen, ansonsten zerhaut's die Zeilenumbrüche; irgendwas mit dem internen Encoding
	foreach ($antraege as $antraege2) foreach ($antraege2 as $antrag) {
		/** @var Antrag $antrag */
		if (!in_array($antrag->status, IAntrag::$STATI_UNSICHTBAR)) $absae = $antrag->getParagraphs();
	}


// create new PDF document
	$pdf = new AntragsgruenPDF(PDF_PAGE_ORIENTATION, PDF_UNIT, PDF_PAGE_FORMAT, true, 'UTF-8', false);

// set document information
	$pdf->SetCreator(PDF_CREATOR);
	$pdf->SetTitle($veranstaltung->name);
//$pdf->SetSubject($sprache->get("Antrag") . " " . $model->revision_name . ": " . $model->name);
//$pdf->SetKeywords('TCPDF, PDF, example, test, guide');

// set default header data
//$pdf->SetHeaderData(PDF_HEADER_LOGO, PDF_HEADER_LOGO_WIDTH, PDF_HEADER_TITLE . ' 006', PDF_HEADER_STRING);

// set header and footer fonts
//$pdf->setHeaderFont(Array(PDF_FONT_NAME_MAIN, '', PDF_FONT_SIZE_MAIN));
//$pdf->setFooterFont(Array(PDF_FONT_NAME_DATA, '', PDF_FONT_SIZE_DATA));

// set default monospaced font
	$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

	$pdf->setPrintHeader(false);
	$pdf->setPrintFooter(true);

//set margins
	$pdf->SetMargins(25, 40, 25);
//$pdf->SetHeaderMargin(PDF_MARGIN_HEADER);
//$pdf->SetFooterMargin(PDF_MARGIN_FOOTER);

//set auto page breaks
	$pdf->SetAutoPageBreak(TRUE, PDF_MARGIN_BOTTOM - 5);

//set image scale factor
	$pdf->setImageScale(PDF_IMAGE_SCALE_RATIO);


	$first = true;
	foreach ($antraege as $antraege2) foreach ($antraege2 as $antrag) {
		/** @var Antrag $antrag */
		if (!in_array($antrag->status, IAntrag::$STATI_UNSICHTBAR)) {
			$initiatorinnen     = array();
			$unterstuetzerInnen = array();
			foreach ($antrag->antragUnterstuetzerInnen as $unt) {
				if ($unt->rolle == IUnterstuetzerInnen::$ROLLE_INITIATORIN) $initiatorinnen[] = $unt->person->name;
				if ($unt->rolle == IUnterstuetzerInnen::$ROLLE_UNTERSTUETZERIN) $unterstuetzerInnen[] = $unt->person;
			}

			$this->widget("AntragPDFWidget", array(
				"sprache"        => $sprache,
				"antrag"         => $antrag,
				"pdf"            => $pdf,
				"initiatorinnen" => implode(", ", $initiatorinnen),
				"header"         => ($antrag->veranstaltung->url_verzeichnis != "ltwby13-programm" || $first),
			));
			$first = false;
		}
	}


	$pdftext = $pdf->Output('Antraege.pdf', 'S');


	Yii::app()->cache->set("pdf_" . $veranstaltung->id, $pdftext);
	echo $pdftext;
}