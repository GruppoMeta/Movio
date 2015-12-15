<?php
/**
 * This file is part of the GLIZY framework.
 * Copyright (c) 2005-2012 Daniele Ugoletti <daniele.ugoletti@glizy.com>
 *
 * For the full copyright and license information, please view the COPYRIGHT.txt
 * file that was distributed with this source code.
 */

define ('K_PATH_CACHE', org_glizy_Paths::getRealPath( 'CACHE' ) );
define ('K_PATH_URL_CACHE', org_glizy_Paths::getRealPath( 'CACHE' ) );
define ('K_PATH_IMAGES', org_glizy_Paths::getRealPath( 'APPLICATION_TEMPLATE' ) );


glz_importApplicationLib('tcpdf/tcpdf.php');
glz_importApplicationLib('tcpdf/config/lang/eng.php');

class org_glizy_template_layoutManager_PDF extends org_glizy_template_layoutManager_PHP
{
	private $pageUrl;
	private $pageTitle;
	function __construct($fileName='', $rootPath='')
	{
		parent::__construct( 'pdf.php', $rootPath );

		$this->pageUrl = GLZ_HOST."/index.php?".__Request::get( '__url__' );
		$this->pageUrl = str_replace( '&printPdf=1', '', $this->pageUrl );
		$this->pageTitle = $this->currentMenu->title;
	}

	function apply(&$regionContent)
	{
		$templateSource = parent::apply( $regionContent );
		$xml = new SimpleXMLElement( utf8_encode( $templateSource ) );

		// TCPDF stuff:
		// create new PDF document
		$pdf = new GlizyPDF( (string)$xml['orientation'] == 'portrait' ? 'P' : 'L' , PDF_UNIT, (string)$xml['size'], true, 'UTF-8', false);
		// set document information
		$pdf->SetCreator( PDF_CREATOR );
		$pdf->SetAuthor( __Config::get( 'APP_NAME' ) );
		$pdf->SetTitle( $this->currentMenu->title );
		$pdf->SetSubject( '' );
		$pdf->SetFooterText( $this->replacePlaceHolders( $xml->footer ) );

		// set default header data
		$logo =  $xml->header['logo'];
		$logoUrl =  ''.$logo;
		$logoWidth = 0;

		if ( file_exists( $logoUrl ) )
		{
			$logo = str_replace( org_glizy_Paths::get( 'APPLICATION_TEMPLATE' ), '', $logo );
			if ( strpos( $logo, 'application/mediaArchive' ) !== false )
			{
				$logo = '../../../../'.$logo;
			}
			$imageSize = getImageSize( $logoUrl );
			$logoWidth = $imageSize[0] * ( (float)$xml['logoScale'] / 5 );
		}
		else
		{
			$logo = '';
		}
		$pdf->SetHeaderData( $logo, $logoWidth, '', '' );


		// set header and footer fonts
		$pdf->setHeaderFont(Array( (string)$xml->header[ 'font' ], '', (int)$xml->header[ 'fontSize' ] ));
		$pdf->setFooterFont(Array( (string)$xml->footer[ 'font' ], '', (int)$xml->footer[ 'fontSize' ] ));

		// set default monospaced font
		$pdf->SetDefaultMonospacedFont(PDF_FONT_MONOSPACED);

		//set margins
		$margin = explode( ',', $xml[ 'margin' ] );
		$marginHeaderFooter = explode( ',', $xml[ 'marginHeaderFooter' ] );
		$pdf->SetMargins( $margin[3], $margin[0], $margin[1]);
		$pdf->SetHeaderMargin( $marginHeaderFooter[0] );
		$pdf->SetFooterMargin( $marginHeaderFooter[1]);

		//set auto page breaks
		$pdf->SetAutoPageBreak(TRUE, $margin[2]);

		//set image scale factor
		$pdf->setImageScale( (float)$xml['imageScale'] );

		//set some language-dependent strings
		$pdf->setLanguageArray($l);

		// set font
		$pdf->SetFont( (string)$xml->content[ 'font' ], '', (int)$xml->content[ 'fontSize' ]);

		// add a page
		$pdf->AddPage();

		$styles = $xml->styles;
		$content = $xml->content;
		$this->fixImages( $content, $xml[ 'stripImages' ] );
		$this->fixHtml( $content );

		$html = <<<EOF
<style>
    $styles

h1 {
    font-size: 24pt;
    text-decoration: underline;
}
h2 {
    font-size: 20pt;
}
h3 {
    font-size: 15pt;
}
h4 {
    font-size: 10pt;
}
h5 {
    font-size: 10pt;
}
</style>
$content
EOF;

		$tags = array( 	'ul' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
						'ol' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
						'dt' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
						'dl' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
						'dd' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
						'div' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0)),
						'p' => array(0 => array('h' => '', 'n' => 1), 1 => array('h' => '', 'n' => 0.5)),
						'h2' => array(0 => array('h' => '', 'n' => 1), 1 => array('h' => '', 'n' => 0.5)),
						'h3' => array(0 => array('h' => '', 'n' => 1), 1 => array('h' => '', 'n' => 0.5)),
						'h4' => array(0 => array('h' => '', 'n' => 1), 1 => array('h' => '', 'n' => 0.5)),
						'h5' => array(0 => array('h' => '', 'n' => 0), 1 => array('h' => '', 'n' => 0.5))
					);
		$pdf->setHtmlVSpace($tags);

		// output the HTML content
		$pdf->writeHTML($html, true, false, true, false, '');
		// reset pointer to the last page
		$pdf->lastPage();
		//Close and output PDF document
		$pdf->Output( $this->currentMenu->title.'.pdf', 'I');
		exit;
	}

	private function fixImages( &$html, $strip )
	{
		if ( $strip == "true" )
		{
			$html = preg_replace( '/<img[^>]+\>/i', '', $html );
		}
		else
		{
			$pattern = '/<\s*img [^\>]+src\s*=\s*[\""\']?([^\""\'\s>]*)/i';
			preg_match_all( $pattern, $html, $matches, PREG_PATTERN_ORDER );
			for($i=0; $i<count($matches[0]); $i++)
			{
				$replacement = $this->imageSrc( $matches[1][$i] );
				if( $replacement )
				{
					$html = str_replace( $matches[0][$i], $replacement, $html);
				}
			}
		}
	}

	private function fixHtml( &$html )
	{
		$html = str_replace( array( '<dl', '</dl>', '<dt>', '</dt>', '<dd>', '</dd>', '<div', '</div>' ),
							 array( '<p', '</p>', '', ': ', '', '<br>', '<div', '</div>' ),
							 $html );

		$html = str_replace( array( 'href="index.php?', 'href="getFile.php?' ), array( 'href="'.GLZ_HOST.'/index.php?', 'href="'.GLZ_HOST.'/getFile.php?' ), $html );

		preg_match_all ("/a[\s]+[^>]*?href[\s]?=[\s\"\']+(.*?)[\"\']+(.*?)>([^<]+|.*?)?<\/a>/",  $html, $matches );
		$links = '';
		$row = 0;
		for( $i = 0; $i < count( $matches[ 0 ] ); $i++ )
		{
			if ( strpos( $matches[ 0 ][ $i ], 'getImage.php' ) !== false ) continue;
			$row++;
			$html = str_replace( $matches[ 2 ][ $i ].'>'.$matches[ 3 ][ $i ].'</a>', $matches[ 2 ][ $i ].'>'.$matches[ 3 ][ $i ].'</a><span style="font-size: small"> ['.$row.'] </span>', $html );
			$links .= '- ['.$row.'] '.$matches[ 1 ][ $i ].'<br>';
		}
		if ( !empty( $links ) )
		{
			$html .= '<i style="page-break-before: always; font-size: small;"><p><b>Collegamenti</b><br>'.$links.'</p></i>';
		}
	}

	private function imageSrc( $url )
	{
		if ( strpos( $url, 'getImage.php' ) !== false )
		{
			$url = str_replace( array( 'getImage.php?', '&amp;' ), array( '', '&' ), $url );
			$chunks = explode( '&', $url );
			$image = array();
			foreach( $chunks as $key => $chunk )
			{
				list( $k, $v ) = explode( '=', $chunk );
				$image[ $k ] = $v;
			}

			// controllo
			if( !isset($image['id']) ) return "";
			$media = org_glizycms_mediaArchive_MediaManager::getMediaById( $image['id'] );
			if( isset($image['w']) && isset($image['h']) )
			{
				$mediaInfo = $media->getResizeImage( $image['w'], $image['h'] );
			}
			else
			{
				$mediaInfo = $media->getImageInfo();
			}
			return "<img src=\"".$mediaInfo['fileName'];
		}

	}

	private function replacePlaceHolders( $text )
	{
		$text = (string)$text;
		$text = str_replace( '##url##', $this->pageUrl, (string)$text );
		$text = str_replace( '##title##', $this->pageTitle, $text );
		return $text;
	}
}


class GlizyPDF extends TCPDF
{
	private $footerText;

	public function SetFooterText( $footerText )
	{
		$this->footerText = $footerText;
	}

	public function Footer()
	{
		parent::Footer();
		$this->SetY(-13);
		//$this->SetFont( 'helvetica', 'I', 8 );
		$this->Cell(0, 10, $this->footerText, 0, false, 'L', 0, '', 0, false, 'T', 'M');
    }
}