<?php

/**
 * Class CMB2_Pages
 * Holds CMB2_Page instances.
 *
 * Uses: None
 * Applies CMB2 Filters:                 None
 * Public methods:
 *     add()                             Add a new CMB2_Page instance
 *     get()                             Retrive by CMB2_Page->page_id
 *     get_all()                         Get all instances
 *     get_by_options_key()              Get all instances sharing the same CMB2_Page->option_key
 *     remove()                          Remove a CMB2_Page instance by page_id
 *     clear()                           Remove all instances
 * Public methods accessed via callback: None
 * Protected methods:                    None
 * Private methods:                      None
 * Magic methods:                        None
 *
 * @since     2.XXX
 *
 * @category  WordPress_Plugin
 * @package   CMB2
 * @author    CMB2 team
 * @license   GPL-2.0+
 * @link      https://cmb2.io
 */
class CMB2_Pages {
	
	/**
	 * Array of all page hookup objects
	 *
	 * @since 2.XXX
	 * @var   CMB2_Page[]
	 */
	protected static $pages = array();
	
	/**
	 * Add a CMB2__Page_Hookup instance object to the registry.
	 *
	 * @since  2.XXX
	 * @param  CMB2_Page $page
	 * @return string
	 */
	public static function add( CMB2_Page $page ) {
		
		if ( ! $page instanceof CMB2_Page ) {
			return FALSE;
		}
		
		self::$pages[ $page->page_id ] = $page;
		
		return $page->page_id;
	}
	
	/**
	 * Retrieve a CMB2_Page_Hookup instance by cmb id
	 *
	 * @since  2.XXX
	 * @param  string $page CMB2_Page instance id
	 * @return CMB2_Page|bool        False or CMB2_Page object instance.
	 */
	public static function get( $page ) {
		
		if ( ! is_string( $page ) || empty( $page ) || empty( self::$pages ) || empty( self::$pages[ $page ] ) ) {
			return FALSE;
		}
		
		return self::$pages[ $page ];
	}
	
	/**
	 * Retrieve all CMB2_Page_Hookup instances registered.
	 *
	 * @since  2.XXX
	 * @return CMB2_Page[] Array of all registered CMB2_Pageinstances.
	 */
	public static function get_all() {
		
		return self::$pages;
	}
	
	/**
	 * Retrieve all CMB2_Page_Hookup instances that have the same options key.
	 *
	 * @since  2.XXX
	 * @param  string $key Key matching options-key
	 * @return bool|CMB2[]  Array of matching CMB2_Page instances
	 */
	public static function get_by_options_key( $key ) {
		
		if ( ! is_string( $key ) ) {
			return FALSE;
		}
		
		$pages = array();
		
		foreach ( self::$pages as $id => $page ) {
			if ( $page->option_key === $key ) {
				$pages[ $id ] = $page;
			}
		}
		
		return $pages;
	}
	
	/**
	 * Remove a CMB2_Page_Hookup instance object from the registry.
	 *
	 * @since  2.XXX
	 * @param  string $page A CMB2_Page instance id.
	 * @return bool
	 */
	public static function remove( $page ) {
		
		if ( ! is_string( $page ) || empty( $page ) ) {
			return FALSE;
		}
		
		if ( array_key_exists( $page, self::$pages ) ) {
			
			unset( self::$pages[ $page ] );
			
			return TRUE;
		}
		
		return FALSE;
	}
	
	/**
	 * Clears all hookups from class.
	 *
	 * @since 2.XXX
	 */
	public static function clear() {
		
		self::$pages = array();
	}
}