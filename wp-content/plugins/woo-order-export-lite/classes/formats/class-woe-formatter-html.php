<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

include_once 'abstract-class-woe-formatter-plain-format.php';

/**
 * Class WOE_Formatter_PDF
 *
 * Using CSV formatter as basis. Works like CSV (even creates csv file) but after finish,
 * fetches data from file and paste them to PDF as table
 */
class WOE_Formatter_Html extends WOE_Formatter_Plain_Format {

     protected $css;
     protected $table_header_row;

     public function __construct(
		$mode,
		$filename,
		$settings,
		$format,
		$labels,
		$field_formats,
		$date_format,
		$offset
	) {
		parent::__construct( $mode, $filename, $settings, $format, $labels, $field_formats, $date_format, $offset );

		$this->css = $this->get_prepared_css();
	}

	public function start( $data = '' ) {
		$data = $this->make_header( $data );
		$data = apply_filters( "woe_{$this->format}_header_filter", $data );
		$this->prepare_array( $data );
		parent::start( $data );

		$this->table_header_row = $data;

		if ( $this->mode != 'preview' ) {

		    $this->set_table_header_row($this->table_header_row);

		    if( $this->settings['custom_css'] )
			$this->css['style'] = '';

		    fwrite( $this->handle, '<html><head><style type="text/css">'.$this->css['style'].$this->settings['custom_css'].'</style></head><body>' );
		}

		if ( ! empty( $this->settings['display_column_names'] ) AND $data ) {
			if ( $this->mode == 'preview' ) {
			    $this->rows[] = $data;
			} else {

				if ( $this->settings['header_text'] ) {
				    fwrite( $this->handle, '<div class="header">' . $this->settings['header_text'] . '</div>' );
				}

				fwrite( $this->handle, '<table>' );

				do_action( "woe_before_{$this->format}_print_header", $this->handle, $data, $this );

				if ( ! apply_filters( "woe_{$this->format}_custom_output_func", false, $this->handle, $data, true ) ) {
				    fwrite( $this->handle, '<thead><tr><th>' . join( '</th><th>', $data ) . "</th></tr></thead>\n" );
				}

				do_action( "woe_{$this->format}_print_header", $this->handle, $data, $this );
			}
		}
	}

	public function output( $rec ) {
		$rows = parent::output( $rec );
		foreach ( $rows as $row ) {
			$this->prepare_array( $row );
			if ( $this->has_output_filter ) {
				$row = apply_filters( "woe_{$this->format}_output_filter", $row, $this );
				if ( ! $row ) {
					continue;
				}
			}

			if ( $this->mode == 'preview' ) {
				$this->rows[] = $row;
			} else {

				if ( ! apply_filters( "woe_{$this->format}_custom_output_func", false, $this->handle, $row, false ) ) {

				    fwrite( $this->handle, '<tr><td>' . join( '</td><td>', $row ) . "</td></tr>\n" );

				}
			}
		}

	}

	public function finish() {
		$this->try_apply_summary_report_fields();

		if ( $this->mode == 'preview' ) {

			if( $this->settings['custom_css'] ) {
				foreach( $this->css['inline'] as $k=>$v)
					$this->css['inline'][$k]  = '';
			}

			$this->rows = apply_filters( "woe_{$this->format}_preview_rows", $this->rows );

			fwrite( $this->handle, '<div style="'.$this->css['inline']['body'].'">' );

			if ( $this->settings['header_text'] ) {
			    fwrite( $this->handle, '<div class="header" style="'.$this->css['inline']['header'].'">' . $this->settings['header_text'] . '</div>' );
			}

			fwrite( $this->handle, '<table>' );

			if ( count( $this->rows ) < 2 ) {
				$this->rows[] = array( '<td colspan=10 style="'.$this->css['inline']['td'].'"><b>' . __( 'No results', 'woo-order-export-lite' ) .'</b></td>' );
			}
			foreach ( $this->rows as $num => $rec ) {
				if ( $num == 0 AND ! empty( $this->settings['display_column_names'] ) ) {
				    fwrite( $this->handle,
					'<thead><tr><th style="'.$this->css['inline']['th'].'">' . join( '</th><th style="'.$this->css['inline']['th'].'">', $rec ) . "</th></tr></thead>\n" );
				} else {
					fwrite( $this->handle, '<tr><td style="'.$this->css['inline']['td'].'">' . join( '</td><td style="'.$this->css['inline']['td'].'">', $rec ) . "</td></tr>\n" );
				}
			}

			if (! empty( $this->settings['display_column_names'] ) && ! empty( $this->settings['repeat_header_last_line'] ) && $this->table_header_row) {
			    fwrite( $this->handle, '<tfoot><tr><th style="'.$this->css['inline']['th'].'">' . join( '</th><th style="'.$this->css['inline']['th'].'">', $this->table_header_row ) . "</th></tr></tfoot>\n" );
			}

			fwrite( $this->handle, '</table>' );

			if ( $this->settings['footer_text'] ) {
			    fwrite( $this->handle, '<div class="footer" style="'.$this->css['inline']['footer'].'">' . $this->settings['footer_text'] . '</div>' );
			}

			fwrite( $this->handle, '</div>' );

		} else {
			do_action( "woe_{$this->format}_print_footer", $this->handle, $this );

			$this->table_header_row = $this->get_table_header_row();

			if (! empty( $this->settings['display_column_names'] ) && ! empty( $this->settings['repeat_header_last_line'] ) && $this->table_header_row) {
			    if ( ! apply_filters( "woe_{$this->format}_custom_output_func", false, $this->handle, $this->table_header_row, true ) ) {
				fwrite( $this->handle, '<tfoot><tr><th>' . join( '</th><th>', $this->table_header_row ) . "</th></tr></tfoot>\n" );
			    }
			}

			fwrite( $this->handle, '</table>' );

			if ( $this->settings['footer_text'] ) {
			    fwrite( $this->handle, '<div class="footer">' . $this->settings['footer_text'] . '</div>' );
			}

			fwrite( $this->handle, '</body></html>' );
		}
		parent::finish();
	}

	protected function prepare_array( &$arr ) {
		if ( apply_filters( "woe_stop_csv_injection", true ) ) {
			$arr = array_map( array( $this, 'stop_csv_injection' ), $arr );
		}

		if ( ! in_array( $this->encoding, array( '', 'utf-8', 'UTF-8' ) ) ) {
			$arr = array_map( array( $this, 'encode_value' ), $arr );
		}
	}

	protected function stop_csv_injection( $value ) {
		$formula_chars = array( "=", "+", "-", "@" );
		if ( in_array( substr( $value, 0, 1 ), $formula_chars ) ) {
			$value = " " . $value;
		}

		return $value;
	}

	protected function encode_value( $value ) {
		return iconv( 'UTF-8', $this->encoding, $value );
	}

	protected function get_prepared_css() {

	    $default_css = array();

	    if ($this->settings['font_size']) {
		$default_css['font-size'] = $this->settings['font_size'] . 'px';
	    }

	    $default_header_css = array();

	    if ( $this->settings['header_text_color'] ) {
		$default_header_css['color'] = $this->settings['header_text_color'];
	    }

	    $default_footer_css = array();

	    if ( $this->settings['footer_text_color'] ) {
		$default_footer_css['color'] = $this->settings['footer_text_color'];
	    }

	    $align = "left";

	    switch($this->settings['cols_align']) {
		case 'L':
		    $align = "left";
		    break;
		case 'R':
		    $align = "right";
		    break;
		case 'C':
		    $align = "center";
		    break;
	    }

	    $default_th_css = array('text-align' => $align, 'font-weight' => 'bold');
	    $default_td_css = array('text-align' => $align);

	    if ( $this->settings['table_header_text_color'] ) {
		$default_th_css['color'] = $this->settings['table_header_text_color'];
	    }

	    if ( $this->settings['table_header_background_color'] ) {
		$default_th_css['background-color'] = $this->settings['table_header_background_color'];
	    }

	    if ( $this->settings['table_row_text_color'] ) {
		$default_td_css['color'] = $this->settings['table_row_text_color'];
	    }

	    if ( $this->settings['table_row_background_color'] ) {
		$default_td_css['background-color'] = $this->settings['table_row_background_color'];
	    }

	    $css = array();

	    $default = array(
		'body'	 => $default_css,
		'header' => $default_header_css,
		'footer' => $default_footer_css,
		'th'	 => $default_th_css,
		'td'	 => $default_td_css,
	    );

	    foreach ($default as $key => $tmp) {

		$tmp_css = array();

		foreach ($tmp as $p => $s) {
		    $tmp_css[] = sprintf('%s: %s', $p, $s);
		}

		$css[$key] = implode('; ', $tmp_css);
	    }

	    $style = '
		body, .header, .footer, table th, table td {'.$css['body'].'}
		table th {'.$css['th'].'}
		table td {'.$css['td'].'}
		.header {'.$css['header'].'}
		.footer {'.$css['footer'].'}
	    ';

	    return array(
		'style' => $style,
		'inline'=> $css,
	    );
	}

	public function get_table_header_row() {
	    return get_transient($this->get_transient_key_table_header_row());
	}

	public function set_table_header_row($row) {
	    return set_transient($this->get_transient_key_table_header_row(), $row, 5 * MINUTE_IN_SECONDS);
	}

	public function get_transient_key_table_header_row() {
	    return 'woocommerce-order-file-'. str_replace('/', '-', $this->filename) .'-html-table-header-row';
	}

}