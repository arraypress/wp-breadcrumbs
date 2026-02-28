<?php
/**
 * Breadcrumbs Helper Functions
 *
 * Provides convenient global functions for common breadcrumb operations.
 * These functions are wrappers around the ArrayPress\Breadcrumbs\Breadcrumbs class.
 *
 * Functions included:
 * - create_breadcrumbs()  - Create a new Breadcrumbs builder instance
 * - render_breadcrumbs()  - Build and render breadcrumbs from an array of items
 * - display_breadcrumbs() - Build and echo breadcrumbs from an array of items
 *
 * @package ArrayPress\Breadcrumbs
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

use ArrayPress\Breadcrumbs\Breadcrumbs;

if ( ! function_exists( 'create_breadcrumbs' ) ) {
	/**
	 * Create a new Breadcrumbs builder instance.
	 *
	 * Returns a fluent builder for constructing breadcrumb navigation.
	 *
	 * Usage:
	 *   create_breadcrumbs()
	 *       ->add( 'Home', '/' )
	 *       ->add( 'Products', '/products' )
	 *       ->add_current( 'Widget' )
	 *       ->display();
	 *
	 * @param array $config Optional configuration overrides.
	 *
	 * @return Breadcrumbs New Breadcrumbs builder instance.
	 */
	function create_breadcrumbs( array $config = [] ): Breadcrumbs {
		return Breadcrumbs::create( $config );
	}
}

if ( ! function_exists( 'render_breadcrumbs' ) ) {
	/**
	 * Build and render breadcrumbs from an array of items.
	 *
	 * A convenience function for rendering breadcrumbs in a single call.
	 * Each item should be an associative array with 'label' (required),
	 * 'url' (optional), 'icon' (optional), and 'attributes' (optional).
	 *
	 * Usage:
	 *   echo render_breadcrumbs( [
	 *       [ 'label' => 'Home', 'url' => '/' ],
	 *       [ 'label' => 'Products', 'url' => '/products' ],
	 *       [ 'label' => 'Widget' ],
	 *   ] );
	 *
	 * @param array $items  Array of item definitions.
	 * @param array $config Optional configuration overrides.
	 *
	 * @return string The rendered breadcrumbs HTML.
	 */
	function render_breadcrumbs( array $items, array $config = [] ): string {
		return Breadcrumbs::create( $config )->add_items( $items )->render();
	}
}

if ( ! function_exists( 'display_breadcrumbs' ) ) {
	/**
	 * Build and echo breadcrumbs from an array of items.
	 *
	 * Same as render_breadcrumbs() but echoes directly instead of returning.
	 *
	 * Usage:
	 *   display_breadcrumbs( [
	 *       [ 'label' => 'Home', 'url' => '/' ],
	 *       [ 'label' => 'Products', 'url' => '/products' ],
	 *       [ 'label' => 'Widget' ],
	 *   ] );
	 *
	 * @param array $items  Array of item definitions.
	 * @param array $config Optional configuration overrides.
	 *
	 * @return void
	 */
	function display_breadcrumbs( array $items, array $config = [] ): void {
		Breadcrumbs::create( $config )->add_items( $items )->display();
	}
}
