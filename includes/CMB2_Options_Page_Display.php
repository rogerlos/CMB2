<?php

/**
 * Creates and displays an options page.
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 *
 * @property string $option_key
 * @property string $page
 * @property array  $shared
 * @property array  $default_args
 */
class CMB2_Options_Page_Display {
	
	/**
	 * Options key
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $option_key = '';
	
	/**
	 * The page id, same as menu_slug
	 *
	 * @var string
	 * @since 2.XXX
	 */
	protected $page = '';
	
	/**
	 * Shared cmb->prop values used on this page. This array is merged with passed-in $props
	 *
	 * @var array
	 * @since 2.XXX
	 */
	protected $shared = array(
		'page_columns' => 1,
		'page_format'  => 'simple',
		'reset_button' => '',
		'save_button'  => 'Save',
		'title'        => '',
	);
	
	/**
	 * Default arguments used by page() and page_form(). Reconciled with $shared on construct
	 *
	 * @since 2.XXX
	 * @var array
	 */
	protected $default_args = array();
	
	/**
	 * CMB2_Options_Display constructor.
	 *
	 * @since 2.XXX
	 * @param string $key   The options key
	 * @param string $page  Page slug (also used by menu)
	 * @param array  $props Shared cmb2->prop() values used by the page itself
	 */
	public function __construct( $key, $page, $props ) {
		
		$this->option_key = (string) $key;
		$this->page       = (string) $page;
		
		$props        = ! is_array( $props ) ? (array) $props : $props;
		$this->shared = array_merge( $this->shared, $props );
		
		$this->default_args = $this->merge_default_args();
	}
	
	/**
	 * Returns property, allows checking state of class
	 *
	 * @since  2.XXX
	 * @param  string $property Class property to fetch
	 * @return mixed|null
	 */
	public function __get( $property ) {
		
		return isset( $this->{$property} ) ? $this->{$property} : NULL;
	}
	
	/**
	 * Merges default args. Does not set $this->default_args!
	 *
	 * @since  2.XXX
	 * @param  string $option_key  Defaults to this->option_key
	 * @param  array  $shared      Defaults to this->shared
	 * @return array|mixed|null
	 */
	public function merge_default_args( $option_key = '', $shared = array() ) {
		
		$option_key = empty( $option_key ) || ! is_string( $option_key ) ?
			$this->option_key : $option_key;
		
		$shared = ! is_array( $shared) || empty( $shared ) ?
			$this->shared : array_merge( $this->shared, $shared );
		
		$default_args = array(
			'option_key'     => $option_key,
			'page_format'    => $shared['page_format'],
			'simple_action'  => 'cmb2_options_simple_page',
			'page_nonces'    => TRUE,
			'page_columns'   => $shared['page_columns'],
			'page_metaboxes' => array(
				'top'      => 'edit_form_after_title',
				'side'     => 'side',
				'normal'   => 'normal',
				'advanced' => 'advanced',
			),
			'save_button'    => $shared['save_button'],
			'reset_button'   => $shared['reset_button'],
			'button_wrap'    => true,
			'title'          => $shared['title']
		);
		
		return $default_args;
	}
	
	/**
	 * Display options-page output. Called from CMB2_Options_Hookup.
	 *
	 * @since  2.XXX
	 * @param  array  $inserted_args Inserted argument array; keys should be in
	 * @return string                Formatted HTML
	 */
	public function page( $inserted_args = array() ) {
		
		$inserted_args = ! is_array( $inserted_args ) ? (array) $inserted_args : $inserted_args;
		$args = array_replace_recursive( $this->default_args, $inserted_args );
		
		$before_html = $after_html = '';
		
		// add formatting class to non-post-type options pages
		$wrapclass = $args['page_format'] !== 'post' ? ' cmb2-options-page' : '';
		
		$html = '<div class="wrap' . $wrapclass . ' options-' . $args['option_key'] . '">';
		
		if ( $args['title'] ) {
			$html .= '<h1 class="wp-heading-inline">' . wp_kses_post( $args['title'] ) . '</h1>';
		}
		
		/**
		 * 'cmb2_options_page_before' filter.
		 * Allows inserting content before the page form, below the title. Any content passed in should be formatted
		 * HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_page_before', $before_html, $this );
		
		// the form itself, built potentially from multiple option boxes
		$html .= $this->page_form( $args );
		
		/**
		 * 'cmb2_options_page_after' filter.
		 * Allows inserting content after the page form. Content should be formatted HTML ready to be echoed to page.
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_page_after', $after_html, $this );
		
		// close wrapper
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Options page form. Adds post-style structure for pages where that is desired.
	 *
	 * @since  2.XXX
	 * @param  array $inserted_args Allows injecting the page_form arguments
	 * @return string               Formatted HTML
	 */
	public function page_form( $inserted_args = array() ) {
		
		$inserted_args = ! is_array( $inserted_args ) ? (array) $inserted_args : $inserted_args;
		$args          = array_replace_recursive( $this->default_args, $inserted_args );
		
		$id = 'cmb2-option-' . $args['option_key'];
		$top_html = $bottom_html = '';
		
		/**
		 * 'cmb2_options_form_id' filter
		 * Change the ID of the form. If returned empty, will revert to default.
		 *
		 * @since 2.XXX
		 */
		$form_id = apply_filters( 'cmb2_options_form_id', $id, $this );
		
		// No empty IDs
		$form_id = empty( $form_id ) ? $id : $form_id;
		
		// opening form tag
		$html = '<form action="' . esc_url( admin_url( 'admin-post.php' ) ) . '" '
		        . 'method="POST" id="' . esc_attr( $form_id ) . '" '
		        . 'enctype="multipart/form-data" encoding="multipart/form-data">';
		
		/**
		 * 'cmb2_options_form_top' filter
		 * Insert HTML content just after form opening tag
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_form_top', $top_html, $this );
		
		// Submit action hidden field
		$html .= '<input type="hidden" name="action" value="' . esc_attr( $args['option_key'] ) . '">';
		
		// Add form, using the type specified
		$html .= $args['page_format'] !== 'post' ?
			$this->page_form_simple( $args['simple_action'] ) :
			$this->page_form_post( $args['page_nonces'], $args['page_columns'], $args['page_boxes'] );
		
		// Get save button
		$html .= $this->save_button( $args['save_button'], $args['reset_button'], $args['button_wrap'] );
		
		/**
		 * 'cmb2_options_form_bottom' filter
		 * Insert HTML content just before form closing tag
		 *
		 * @since 2.XXX
		 */
		$html .= apply_filters( 'cmb2_options_form_bottom', $bottom_html, $this );
		
		$html .= '</form>';
		
		return $html;
	}
	
	/**
	 * Simple options page layout; the format originally output by CMB2_Options_Hookup.
	 * Note multiple metaboxes in this format visually appear as one large metabox
	 *
	 * @since  2.XXX
	 * @param  string  $action  Allows injecting the action, or removing it
	 * @param  string  $page    Defaults to $this->page
	 * @return string           Formatted HTML
	 */
	public function page_form_simple( $action = 'cmb2_options_simple_page', $page = '' ) {
		
		$html   = '';
		$action = ! is_string( $action ) || empty( $action ) ? ''          : $action;
		$page   = ! is_string( $page )   || empty( $page )   ? $this->page : $page;
		
		if ( $action ) {
			ob_start();
			do_action( $action, $page );
			$html = ob_get_clean();
		}
		
		return $html;
	}
	
	/**
	 * Post-editor style options page.
	 *
	 * Contexts 'before_permalink', 'after_title', 'after_editor' not supported.
	 *
	 * @since  2.XXX
	 * @param  bool  $nonces Whether to return nonce fields
	 * @param  int   $cols   Number of columns, defaults to $shared['page_columns']
	 * @param  array $boxes  Array of strings to populate meta/context box locations. Merged with defaults.
	 * @return string        Formatted HTML
	 */
	public function page_form_post( $nonces = TRUE, $cols = 0, $boxes = array() ) {
		
		$default_boxes = array(
			'top'      => 'edit_form_after_title',
			'side'     => 'side',
			'normal'   => 'normal',
			'advanced' => 'advanced',
		);
		$boxes         = is_array( $boxes ) ? $boxes : (array) $boxes;
		$loc           = array_merge( $default_boxes, $boxes );
		
		$cols = intval( $cols ) < 1 ? $this->shared['page_columns'] : intval( $cols );
		
		$html = '';
		
		// nonce fields
		$html .= $this->page_form_post_nonces( $nonces );
		
		// form_top context boxes
		$html .= $this->page_form_post_context_boxes( $loc['top'] );
		
		// main post area
		$html .= '<div id="poststuff">';
		$html .= '<div id="post-body" class="metabox-holder columns-' . $cols . '">';
		
		// optional sidebar
		$html .= $this->page_form_post_sidebar( $cols, $loc['side'] );
		
		// main column
		$html .= '<div id="postbox-container-' . $cols . '" class="postbox-container">';
		
		$html .= $this->page_form_post_meta_boxes( $loc['normal'] );
		$html .= $this->page_form_post_meta_boxes( $loc['advanced'] );
		
		$html .= '</div>';
		$html .= '</div>';
		
		return $html;
	}
	
	/**
	 * Adds optional sidebar to post-style form
	 *
	 * @since  2.XXX
	 * @param  int    $cols Allow injection of columns for testing. Uses shared columns if not set
	 * @param  string $side Allow injection of side box location
	 * @return string
	 */
	public function page_form_post_sidebar( $cols = 0, $side = 'side' ) {
		
		$html = '';
		$cols = intval( $cols ) < 1 ? $this->shared['page_columns'] : intval( $cols );
		
		if ( $cols == 2 ) {
			
			$html .= '<div id="postbox-container-1" class="postbox-container">';
			
			$html .= $this->page_form_post_meta_boxes( $side );
			
			$html .= '</div>';
		}
		
		return $html;
	}
	
	/**
	 * Returns WP nonce fields for post form
	 *
	 * @since  2.XXX
	 * @param  bool $nonces Whether to return nonce fields
	 * @return string
	 */
	public function page_form_post_nonces( $nonces = TRUE ) {
		
		$html = '';
		
		$html .= $nonces ? wp_nonce_field( 'meta-box-order', 'meta-box-order-nonce', FALSE, FALSE ) : '';
		$html .= $nonces ? wp_nonce_field( 'closedpostboxes', 'closedpostboxesnonce', FALSE, FALSE ) : '';
		
		return $html;
	}
	
	/**
	 * Returns CMB2 context boxes for a given location. Checks against allowed locations
	 *
	 * @since  2.XXX
	 * @param  string $location
	 * @return string
	 */
	public function page_form_post_context_boxes( $location = '' ) {
		
		$html = '';
		$ok   = array( 'edit_form_after_title' );
		
		if ( empty( $location ) || ! is_string( $location ) || ! in_array( $location, $ok ) ) {
			return $html;
		}
		
		ob_start();
		do_action( $location, $this->page );
		$html .= ob_get_clean();
		
		return $html;
	}
	
	/**
	 * Returns WP do_meta_boxes output for a given location. Checks against allowable locations.
	 *
	 * @since  2.XXX
	 * @param  string $location Meta box context
	 * @return string
	 */
	public function page_form_post_meta_boxes( $location = '' ) {
		
		$html = '';
		$ok   = array( 'side', 'normal', 'advanced' );
		
		if ( empty( $location ) || ! is_string( $location ) || ! in_array( $location, $ok ) ) {
			return $html;
		}
		
		ob_start();
		do_meta_boxes( $this->page, $location, NULL );
		$html .= ob_get_clean();
		
		return $html;
	}
	
	/**
	 * Save button. Optionally adds a reset button. Can set either or both.
	 *
	 * @since  2.XXX
	 * @param  string $save_button  Text of saved button, uses shared['save_button'] as default
	 * @param  string $reset_button Text of reset button, uses shared['reset_button'] as default
	 * @param  bool   $button_wrap  Whether to add a wrapper or not
	 * @return string               Formatted HTML
	 */
	public function save_button( $save_button = '', $reset_button = '', $button_wrap = TRUE ) {
		
		$save_button = empty( $save_button ) ? $this->shared['save_button'] : $save_button;
		$save_button = is_string( $save_button ) && ! empty( $save_button ) ? __( $save_button, 'cmb2' ) : '';
		
		$reset_button = empty( $reset_button ) ? $this->shared['reset_button'] : $reset_button;
		$reset_button = is_string( $reset_button ) && ! empty( $reset_button ) ? __( $reset_button, 'cmb2' ) : '';
		
		if ( ! $save_button && ! $reset_button ) {
			return '';
		}
		
		// Bundle pieces to be nice to users of filter below
		$pieces = array(
			'button_wrap'  => $button_wrap ?
				'<p class="cmb-submit-wrap clear">%s%s</p>' : '%s%s',
			'reset_button' => $reset_button ?
				get_submit_button( esc_attr( $reset_button ), 'secondary', 'reset-cmb', FALSE ) : '',
			'save_button'  => $save_button ?
				get_submit_button( esc_attr( $save_button ), 'primary', 'submit-cmb', FALSE ) : '',
		);
		
		// final HTML
		$html = sprintf( $pieces['button_wrap'], $pieces['reset_button'], $pieces['save_button'] );
		
		/**
		 * 'cmb2_options_page_save_html' filter
		 * Allows filtering of the save button HTML. Include pieces for easier parsing of the concatenated
		 * html string.
		 *
		 * @since 2.XXX
		 */
		$html = apply_filters( 'cmb2_options_page_save_html', $html, $pieces, $this->page );
		
		return is_string( $html ) && ! empty( $html ) ? $html : '';
	}
}