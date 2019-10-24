<?php
if ( ! class_exists( 'WOE_FPDF' ) ) {
	require( 'class-woe-fpdf.php' );
}

class WOE_PDF_MC_Table extends WOE_FPDF {
	protected $widths;
	protected $aligns;

	protected $table_header = array();

	protected $header_props = array();
	protected $footer_props = array();
	protected $table_header_props = array();
	protected $table_row_props = array();
	protected $table_props = array();

	protected $stretch_buffer = array();
	protected $stretch_buffer_params = array();

	protected $default_props = array(
		'header'       => array(
			'title'      => '',
			'style'      => 'B',
			'size'       => 5,
			'text_color' => array( 0, 0, 0 ),
			'logo'       => array(
				'source' => '',
				'width'  => 0,
				'height' => 0,
				'align'  => 'R',
			),
		),
		'table'        => array(
			'stretch'      => false,
			'column_width' => array(),
			'solid_width'  => array(),
		),
		'table_header' => array(
			'style'            => '',
			'size'             => 5,
			'text_color'       => array( 0, 0, 0 ),
			'background_color' => array( 255, 255, 255 ),
			'repeat'           => true,
		),
		'table_row'    => array(
			'style'            => '',
			'size'             => 5,
			'text_color'       => array( 0, 0, 0 ),
			'background_color' => array( 255, 255, 255 ),
		),
		'footer'       => array(
			'title'           => '',
			'style'           => 'B',
			'size'            => 5,
			'text_color'      => array( 0, 0, 0 ),
			'pagination_type' => '',
		),
	);

	public function setProperties( $props ) {
		foreach ( $this->default_props as $key => $default_props ) {
			if ( ! empty( $props[ $key ] ) && is_array( $props[ $key ] ) ) {

				$name = $key . '_props';
				if ( ! isset( $this->$name ) ) {
					continue;
				}

				$this->$name = array_merge( $default_props, $props[ $key ] );
			}
		}
	}

	public function setHeaderProperty( $props ) {
		$this->header_props = array_merge( $this->default_props['header'], $props );
	}

	public function addTableHeader( $header ) {
		$this->table_header = $header;
		$this->changeBrushToDraw( 'table_header' );
		$this->Row( $header );
	}

	public function setTableHeaderProperty( $props ) {
		$this->table_header_props = array_merge( $this->default_props['table_header'], $props );
	}

	public function setTableRowProperty( $props ) {
		$this->table_row_props = array_merge( $this->default_props['table_header'], $props );
	}

	public function setFooterProperty( $props ) {
		$this->footer_props = array_merge( $this->default_props['footer'], $props );
	}

	public function Header() {
		if ( ! empty( $this->header_props['title'] ) ) {
			$this->changeBrushToDraw( 'header' );
			$this->Cell( 0, 0, $this->header_props['title'], 0, 0, 'C' );
			$this->Ln( 2 );
		}

		if ( $this->drawLogo() ) {
			$this->Ln( 1 );
		}
	}

	protected function drawLogo() {
		$source = $this->header_props['logo']['source'];
		$width  = $this->header_props['logo']['width'];
		$height = $this->header_props['logo']['height'];
		$align  = $this->header_props['logo']['align'];

		if ( ! $source || ! $height ) {
			return false;
		}

		$height = $this->validateHeight( $height );
		if ( ! $width ) {
			list( $img_width, $img_height, $type, $attr ) = getimagesize( $source );
			$width = $height * $img_width / $img_height;
		}
		$width = $this->validateWidth( $width );

		if ( $align == 'R' ) {
			$x = $this->GetPageWidth() - $this->getRightMargin() - $width;
		} elseif ( $align == 'C' ) {
			$x = ( $this->GetPageWidth() - $width ) / 2;
		} else {
			$x = $this->getLeftMargin();
		}

		$type = strtoupper( pathinfo( $source, PATHINFO_EXTENSION ) );

		$this->Image( $source, $x, $this->GetY(), $width, $height, $type );
		$this->Ln( $height );

		return true;
	}

	public function Footer() {
		$this->SetY( - 15 );

		$this->changeBrushToDraw( 'footer' );

		if ( ! empty( $this->footer_props['title'] ) ) {
			// Title
			$this->Cell( 0, 0, $this->footer_props['title'], 0, 0, 'C' );
			// Line break
			$this->Ln( 10 );
		}

		// Position at 1.5 cm from bottom
		$this->SetY( - 15 );

		if ( ! empty( $this->footer_props['pagination'] ) ) {
			// Page number
			$align = in_array( $this->footer_props['pagination'], array(
				'L',
				'C',
				'R',
			) ) ? $this->footer_props['pagination'] : false;
			if ( $align ) {
				$this->Cell( 0, 10, 'Page ' . $this->PageNo() . '/{nb}', 0, 0, $align );
			}
		}
	}

	public function addRow( $data, $widths = null, $h = null ) {
		$this->changeBrushToDraw( 'table_row' );
		$this->Row( $data, $widths, $h );
	}

	protected function Row( $data, $widths = null, $h = null ) {
		if ( ! $data ) {
			return;
		}

		$widths = ! $widths ? $this->getRowWidths( $data ) : $widths;
		$h      = ! $h ? $this->getRowHeight( $widths, $data ) : $h;

		//Issue a page break first if needed
		$this->CheckPageBreak( $h );

		$columns_count = $this->getColumnCountInPage( $widths );
		if ( $extra_data = array_slice( $data, $columns_count ) ) {
			$this->stretch_buffer[]        = $extra_data;
			$this->stretch_buffer_params[] = array(
				'widths' => array_slice( $widths, $columns_count ),
				'height' => $h,
			);
		}
		$data = array_slice( $data, 0, $columns_count );


		//Draw the cells of the row
		for ( $i = 0; $i < count( $data ); $i ++ ) {
			$w = $widths[ $i ];
			$a = isset( $this->aligns[ $i ] ) ? $this->aligns[ $i ] : 'L';
			//Save the current position
			$x = $this->GetX();
			$y = $this->GetY();
			//Draw the border
			$this->Rect( $x, $y, $w, $h, 'DF' );

			if ( isset( $data[ $i ]['type'], $data[ $i ]['value'] ) && 'image' === $data[ $i ]['type'] && file_exists( $data[ $i ]['value'] ) ) {
				$source = $data[ $i ]['value'];
				$type   = strtoupper( pathinfo( $source, PATHINFO_EXTENSION ) );
				$this->Image( $source, $x + 1 / 2, $y + 1 / 2, $w - 1, $h - 1, $type );
			} elseif ( ! is_array( $data[ $i ] ) ) {
				//Print the text
				$this->MultiCell( $w, 5, $data[ $i ], 0, $a );
			}

			//Put the position to the right of the cell
			$this->SetXY( $x + $w, $y );
		}
		//Go to the next line
		$this->Ln( $h );
	}

	protected function getColumnCountInPage( $widths ) {
		$count = count( $widths );
		if ( $this->table_props['stretch'] ) {
			$sum_width  = 0;
			$page_width = $this->GetPageWidth() - $this->getLeftMargin() - $this->getRightMargin();
			$count      = 0;
			foreach ( $widths as $width ) {
				if ( $sum_width + $width > $page_width ) {
					break;
				}
				$sum_width += $width;
				$count ++;
			}
		}

		return $count;
	}

	/**
	 * Calculate the width for every column of the row
	 *
	 * @param $row
	 *
	 * @return array
	 */
	protected function getRowWidths( $row ) {
		if ( $this->table_props['stretch'] ) {
			$widths = array();
			for ( $i = 0; $i < count( $row ); $i ++ ) {
				$width = isset( $this->table_props['column_width'][ $i ] ) ? $this->table_props['column_width'][ $i ] : $this->table_props['column_width'][ $i % count( $this->table_props['column_width'] ) ];

				$widths[ $i ] = $this->validateWidth( $width );
			}

		} else {
			$widths = array_fill( 0, count( $row ), ( $this->GetPageWidth() - $this->getLeftMargin() - $this->getRightMargin() ) / count( $row ) );
		}

		if ( $this->table_props['solid_width'] ) {
			foreach ( $this->table_props['solid_width'] as $position => $width ) {
				$widths[ $position ] = $this->validateWidth( $width );
			}
		}

		return $widths;
	}

	public function GetPageWidth() {
		return $this->flt_current_width;
	}

	public function GetPageHeight() {
		return $this->flt_current_height;
	}

	public function getLeftMargin() {
		return $this->int_left_margin;
	}

	public function getRightMargin() {
		return $this->int_right_margin;
	}

	protected function validateWidth( $width, $min_width = 5 ) {
		$max_width = $this->GetPageWidth() - $this->getLeftMargin() - $this->getRightMargin() - 50;
		if ( $width < $min_width ) {
			$width = $min_width;
		} elseif ( $width > $max_width ) {
			$width = $max_width;
		}

		return $width;
	}

	protected function validateHeight( $height, $min_height = 5 ) {
		$max_height = $this->GetPageHeight() - $this->int_top_margin - $this->int_break_margin - 50;
		if ( $height < $min_height ) {
			$height = $min_height;
		} elseif ( $height > $max_height ) {
			$height = $max_height;
		}

		return $height;
	}

	/**
	 * Calculate the height of the row
	 *
	 * @param $widths
	 * @param $row
	 *
	 * @return int|mixed
	 */
	protected function getRowHeight( $widths, $row ) {
		$nb = 0;
		for ( $i = 0; $i < count( $row ); $i ++ ) {
			// do not calculate height for non string values
			$value = is_string( $row[ $i ] ) ? $row[ $i ] : 0;
			$nb    = max( $nb, $this->NbLines( $widths[ $i ], $value ) );
		}

		return 5 * $nb;
	}

	public function CheckPageBreak( $h ) {
		//If the height h would cause an overflow, add a new page immediately
		if ( $this->GetY() + $h > $this->flt_page_break_trigger ) {
			$this->flush_buffer();

			$this->AddPage( $this->str_current_orientation );
			if ( $this->table_header_props['repeat'] && $this->table_header ) {
				$this->changeBrushToDraw( 'table_header' );
				$this->Row( $this->table_header );
				$this->changeBrushToDraw( 'table_row' );
			}
		}
	}

	public function output_to_destination( $dest = '', $name = '', $isUTF8 = false ) {
		$this->flush_buffer();
		// Output PDF to some destination
		$this->Close();
		$output = parent::output();
		if ( strlen( $name ) == 1 && strlen( $dest ) != 1 ) {
			// Fix parameter order
			$tmp  = $dest;
			$dest = $name;
			$name = $tmp;
		}
		if ( $dest == '' ) {
			$dest = 'I';
		}
		if ( $name == '' ) {
			$name = 'doc.pdf';
		}
		switch ( strtoupper( $dest ) ) {
			case 'I':
				// Send to standard output
				if ( PHP_SAPI != 'cli' ) {
					// We send to a browser
					header( 'Content-Type: application/pdf' );
					header( 'Content-Disposition: inline; ' . $this->_httpencode( 'filename', $name, $isUTF8 ) );
					header( 'Cache-Control: private, max-age=0, must-revalidate' );
					header( 'Pragma: public' );
				}
				echo $output;
				break;
			case 'D':
				// Download file
				header( 'Content-Type: application/x-download' );
				header( 'Content-Disposition: attachment; ' . $this->_httpencode( 'filename', $name, $isUTF8 ) );
				header( 'Cache-Control: private, max-age=0, must-revalidate' );
				header( 'Pragma: public' );
				echo $output;
				break;
			case 'F':
				// Save to local file
				if ( ! file_put_contents( $name, $output ) ) {
					throw new WOE_FPDF_Exception( 'Unable to create output file: ' . $name );
				}
				break;
			case 'S':
				// Return as a string
				return $output;
			default:
				throw new WOE_FPDF_Exception( 'Incorrect output destination: ' . $dest );
		}

		return '';
	}

	protected function _httpencode( $param, $value, $isUTF8 ) {
		// Encode HTTP header field parameter
		if ( $this->_isascii( $value ) ) {
			return $param . '="' . $value . '"';
		}
		if ( ! $isUTF8 ) {
			$value = utf8_encode( $value );
		}
		if ( strpos( $_SERVER['HTTP_USER_AGENT'], 'MSIE' ) !== false ) {
			return $param . '="' . rawurlencode( $value ) . '"';
		} else {
			return $param . "*=UTF-8''" . rawurlencode( $value );
		}
	}

	protected function _isascii( $s ) {
		// Test if string is ASCII
		$nb = strlen( $s );
		for ( $i = 0; $i < $nb; $i ++ ) {
			if ( ord( $s[ $i ] ) > 127 ) {
				return false;
			}
		}

		return true;
	}

	protected function flush_buffer() {
		while ( $this->stretch_buffer ) {
			$this->AddPage( $this->str_current_orientation );

			$buffer                      = $this->stretch_buffer;
			$stretch_buffer_params       = $this->stretch_buffer_params;
			$this->stretch_buffer        = array();
			$this->stretch_buffer_params = array();

			if ( $this->table_header ) {
				$this->changeBrushToDraw( 'table_header' );
				$params = array_shift( $stretch_buffer_params );
				$this->Row( array_shift( $buffer ), $params['widths'], $params['height'] );
				$this->changeBrushToDraw( 'table_row' );
			}

			foreach ( $buffer as $index => $row ) {
				$params = $stretch_buffer_params[ $index ];
				$this->addRow( $row, $params['widths'], $params['height'] );
			}
		}
	}

	public function NbLines( $w, $txt ) {
		//Computes the number of lines a MultiCell of width w will take
		if ( $w == 0 ) {
			$w = $this->GetPageWidth() - $this->getRightMargin() - $this->flt_position_x;
		}
		$wmax     = ( $w - 2 * $this->int_cell_margin ) * 1000;
		$s        = str_replace( "\r", '', $txt );
		$text_len = strlen( $s );
		if ( $text_len > 0 and $s[ $text_len - 1 ] == "\n" ) {
			$text_len --;
		}
		$sep          = - 1;
		$ch_index     = 0;
		$j            = 0;
		$l            = 0;
		$line_counter = 1;
		while ( $ch_index < $text_len ) {
			$char = $s[ $ch_index ];
			if ( $char == "\n" ) {
				$ch_index ++;
				$sep = - 1;
				$j   = $ch_index;
				$l   = 0;
				$line_counter ++;
				continue;
			}
			if ( $char == ' ' ) {
				$sep = $ch_index;
			}
//			$l += $arr_character_width[ $char ];
			$l += $this->GetStringWidth( $char ) * 1000;

			if ( $l > $wmax ) {
				if ( $sep == - 1 ) {
					if ( $ch_index == $j ) {
						$ch_index ++;
					}
				} else {
					$ch_index = $sep + 1;
				}
				$sep = - 1;
				$j   = $ch_index;
				$l   = 0;
				$line_counter ++;
			} else {
				$ch_index ++;
			}
		}

		return $line_counter;
	}

	public function SetAligns( $a ) {
		//Set the array of column alignments
		$this->aligns = $a;
	}

	protected function changeBrushToDraw( $what ) {
		if ( ! in_array( $what, array_keys( $this->default_props ) ) ) {
			return false;
		}

		$name = $what . '_props';
		if ( ! isset( $this->$name ) ) {
			return false;
		}
		$props = $this->$name;

		$defaults = array(
			'font_family'      => $this->str_current_font_family,
			'font_style'       => $props['style'],
			'font_size'        => $props['size'],
			'font_color'       => $this->getTextColor( $props ),
			'background_color' => $this->getFillColor( $props ),
		);
		$args = apply_filters( 'woe_formatter_pdf_change_brush_to_draw_arguments', $defaults, $what );
		$args = array_merge($defaults, $args);



		$this->SetFont( $args['font_family'], $args['font_style'], $args['font_size'] );
		if ( ! empty( $args['font_color'] ) ) {
			$color = $args['font_color'];
			$this->SetTextColor( $color[0], $color[1], $color[2] );
		}
		if ( ! empty( $args['background_color'] ) ) {
			$color = $args['background_color'];
			$this->SetFillColor( $color[0], $color[1], $color[2] );
		}

		return true;
	}

	private function getTextColor($props) {
		$color = ! empty( $props['text_color'] ) ? $props['text_color'] : null;

		if ( $color ) {
			$color = $this->convert_color( $color );
		}

		return $color;
	}

	private function getFillColor($props) {
		$color = ! empty( $props['background_color'] ) ? $props['background_color'] : null;

		if ( $color ) {
			$color = $this->convert_color( $color );
		}

		return $color;
	}

	private function loadTextColor( $props ) {
		$color = ! empty( $props['text_color'] ) ? $props['text_color'] : array();

		if ( $color ) {
			$color = $this->convert_color( $color );
			$this->SetTextColor( $color[0], $color[1], $color[2] );
		}
	}

	private function loadFillColor( $props ) {
		$color = ! empty( $props['background_color'] ) ? $props['background_color'] : array();

		if ( $color ) {
			$color = $this->convert_color( $color );
			$this->SetFillColor( $color[0], $color[1], $color[2] );
		}
	}

	private function convert_color( $array ) {
		for ( $i = 0; $i < 2; $i ++ ) {
			$array[ $i ] = ( isset( $array[ $i ] ) && is_numeric( $array[ $i ] ) && $array[ $i ] >= 0 && $array[ $i ] <= 255 ) ? $array[ $i ] : null;
		}

		return array_slice( $array, 0, 3 );
	}

}

