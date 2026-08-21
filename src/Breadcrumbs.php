<?php
/**
 * Breadcrumbs Utility Class
 *
 * Provides a lightweight fluent builder for generating accessible HTML
 * breadcrumb navigation. Supports icons, custom separators, ARIA attributes,
 * Schema.org microdata, and WordPress admin styling.
 *
 * @package ArrayPress\Breadcrumbs
 * @since   1.0.0
 * @author  ArrayPress
 * @license GPL-2.0-or-later
 */

declare( strict_types=1 );

namespace ArrayPress\Breadcrumbs;

use JsonSerializable;
use Stringable;

/**
 * Breadcrumbs Class
 *
 * Core operations for building and rendering breadcrumb navigation.
 */
class Breadcrumbs implements JsonSerializable, Stringable {

	/**
	 * The collection of breadcrumb items.
	 *
	 * @var Item[]
	 */
	private array $items = [];

	/**
	 * The separator string between breadcrumb items.
	 *
	 * @var string
	 */
	private string $separator;

	/**
	 * The ARIA label for the nav element.
	 *
	 * @var string
	 */
	private string $aria_label;

	/**
	 * CSS class for the nav wrapper element.
	 *
	 * @var string
	 */
	private string $nav_class;

	/**
	 * CSS class for the ordered list element.
	 *
	 * @var string
	 */
	private string $list_class;

	/**
	 * CSS class for each list item element.
	 *
	 * @var string
	 */
	private string $item_class;

	/**
	 * CSS class for the separator element.
	 *
	 * @var string
	 */
	private string $separator_class;

	/**
	 * CSS class for the currently active item.
	 *
	 * @var string
	 */
	private string $active_class;

	/**
	 * Default configuration values.
	 *
	 * @var array
	 */
	protected static array $defaults = [
		'separator'       => '&#8250;',
		'aria_label'      => 'Breadcrumb',
		'nav_class'       => 'arraypress-breadcrumbs',
		'list_class'      => 'arraypress-breadcrumbs__list',
		'item_class'      => 'arraypress-breadcrumbs__item',
		'separator_class' => 'arraypress-breadcrumbs__separator',
		'active_class'    => 'arraypress-breadcrumbs__item--active',
	];

	/**
	 * Create a new Breadcrumbs instance.
	 *
	 * @param array $config Optional configuration overrides.
	 */
	public function __construct( array $config = [] ) {
		$config = array_merge( self::$defaults, $config );

		$this->separator       = $config['separator'];
		$this->aria_label      = $config['aria_label'];
		$this->nav_class       = $config['nav_class'];
		$this->list_class      = $config['list_class'];
		$this->item_class      = $config['item_class'];
		$this->separator_class = $config['separator_class'];
		$this->active_class    = $config['active_class'];
	}

	/**
	 * Create a new Breadcrumbs instance with fluent API.
	 *
	 * @param array $config Optional configuration overrides.
	 *
	 * @return static New Breadcrumbs instance.
	 */
	public static function create( array $config = [] ): static {
		return new static( $config );
	}

	/** -------------------------------------------------------------------------
	 * Builder Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Add a breadcrumb item.
	 *
	 * @param string      $label      The display text.
	 * @param string|null $url        Optional URL. Null for the current/active item.
	 * @param string|null $icon       Optional icon HTML or dashicon class. A
	 *                                'dashicons-' class is escaped; anything else is
	 *                                emitted as raw HTML, so never pass untrusted
	 *                                input here.
	 * @param array       $attributes Optional extra HTML attributes for the item.
	 *                                Names are validated and event handlers dropped;
	 *                                values are escaped.
	 *
	 * @return static Self for chaining.
	 */
	public function add( string $label, ?string $url = null, ?string $icon = null, array $attributes = [] ): static {
		$this->items[] = new Item( $label, $url, $icon, $attributes );

		return $this;
	}

	/**
	 * Add a home/root breadcrumb item.
	 *
	 * @param string      $label The display text.
	 * @param string      $url   The URL for the home item.
	 * @param string|null $icon  Optional icon HTML or dashicon class. Raw HTML unless
	 *                           it is a 'dashicons-' class; never pass untrusted input.
	 *
	 * @return static Self for chaining.
	 */
	public function add_home( string $label = 'Home', string $url = '/', ?string $icon = null ): static {
		return $this->add( $label, $url, $icon );
	}

	/**
	 * Add the current/active breadcrumb item (no link).
	 *
	 * @param string      $label The display text.
	 * @param string|null $icon  Optional icon HTML or dashicon class. Raw HTML unless
	 *                           it is a 'dashicons-' class; never pass untrusted input.
	 *
	 * @return static Self for chaining.
	 */
	public function add_current( string $label, ?string $icon = null ): static {
		return $this->add( $label, null, $icon );
	}

	/**
	 * Add multiple items at once from an array.
	 *
	 * Each item should be an associative array with keys:
	 * - 'label' (required)
	 * - 'url' (optional)
	 * - 'icon' (optional)
	 * - 'attributes' (optional)
	 *
	 * @param array $items Array of item definitions.
	 *
	 * @return static Self for chaining.
	 */
	public function add_items( array $items ): static {
		foreach ( $items as $item ) {
			if ( is_array( $item ) && isset( $item['label'] ) ) {
				$this->add(
					$item['label'],
					$item['url'] ?? null,
					$item['icon'] ?? null,
					$item['attributes'] ?? []
				);
			}
		}

		return $this;
	}

	/**
	 * Remove all items and start fresh.
	 *
	 * @return static Self for chaining.
	 */
	public function reset(): static {
		$this->items = [];

		return $this;
	}

	/** -------------------------------------------------------------------------
	 * Configuration Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Set the separator between items.
	 *
	 * @param string $separator The separator HTML string. Emitted raw so markup and
	 *                          entities work, so never pass untrusted input here.
	 *
	 * @return static Self for chaining.
	 */
	public function separator( string $separator ): static {
		$this->separator = $separator;

		return $this;
	}

	/**
	 * Set the ARIA label for the nav element.
	 *
	 * @param string $label The ARIA label.
	 *
	 * @return static Self for chaining.
	 */
	public function aria_label( string $label ): static {
		$this->aria_label = $label;

		return $this;
	}

	/**
	 * Set the CSS class for the nav wrapper.
	 *
	 * @param string $class The CSS class.
	 *
	 * @return static Self for chaining.
	 */
	public function nav_class( string $class ): static {
		$this->nav_class = $class;

		return $this;
	}

	/**
	 * Set the CSS class for the ordered list.
	 *
	 * @param string $class The CSS class.
	 *
	 * @return static Self for chaining.
	 */
	public function list_class( string $class ): static {
		$this->list_class = $class;

		return $this;
	}

	/**
	 * Set the CSS class for list items.
	 *
	 * @param string $class The CSS class.
	 *
	 * @return static Self for chaining.
	 */
	public function item_class( string $class ): static {
		$this->item_class = $class;

		return $this;
	}

	/**
	 * Set the CSS class for separator elements.
	 *
	 * @param string $class The CSS class.
	 *
	 * @return static Self for chaining.
	 */
	public function separator_class( string $class ): static {
		$this->separator_class = $class;

		return $this;
	}

	/**
	 * Set the CSS class for the active item.
	 *
	 * @param string $class The CSS class.
	 *
	 * @return static Self for chaining.
	 */
	public function active_class( string $class ): static {
		$this->active_class = $class;

		return $this;
	}

	/** -------------------------------------------------------------------------
	 * Getters
	 * ------------------------------------------------------------------------ */

	/**
	 * Get all breadcrumb items.
	 *
	 * @return Item[] Array of breadcrumb items.
	 */
	public function items(): array {
		return $this->items;
	}

	/**
	 * Get the number of breadcrumb items.
	 *
	 * @return int Item count.
	 */
	public function count(): int {
		return count( $this->items );
	}

	/**
	 * Check if there are any items.
	 *
	 * @return bool True if items exist.
	 */
	public function has_items(): bool {
		return ! empty( $this->items );
	}

	/**
	 * Check if the breadcrumbs are empty.
	 *
	 * @return bool True if no items exist.
	 */
	public function is_empty(): bool {
		return empty( $this->items );
	}

	/**
	 * Get the first item.
	 *
	 * @return Item|null The first item or null.
	 */
	public function first(): ?Item {
		return $this->items[0] ?? null;
	}

	/**
	 * Get the last item.
	 *
	 * @return Item|null The last item or null.
	 */
	public function last(): ?Item {
		$count = count( $this->items );

		return $count > 0 ? $this->items[ $count - 1 ] : null;
	}

	/** -------------------------------------------------------------------------
	 * Rendering Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Render the breadcrumbs as an HTML string.
	 *
	 * Generates a semantic nav > ol > li structure with ARIA attributes
	 * and Schema.org BreadcrumbList microdata.
	 *
	 * @return string The rendered HTML.
	 */
	public function render(): string {
		if ( $this->is_empty() ) {
			return '';
		}

		$items_html = [];
		$total      = count( $this->items );

		foreach ( $this->items as $index => $item ) {
			$is_last   = ( $index === $total - 1 );
			$is_active = $is_last || $item->url() === null;
			$position  = $index + 1;

			$items_html[] = $this->render_item( $item, $position, $is_active );

			if ( ! $is_last ) {
				$items_html[] = $this->render_separator();
			}
		}

		return sprintf(
			'<nav class="%s" aria-label="%s"><ol class="%s" itemscope itemtype="https://schema.org/BreadcrumbList">%s</ol></nav>',
			esc_attr( $this->nav_class ),
			esc_attr( $this->aria_label ),
			esc_attr( $this->list_class ),
			implode( '', $items_html )
		);
	}

	/**
	 * Render and echo the breadcrumbs.
	 *
	 * @return void
	 */
	public function display(): void {
		echo $this->render();
	}

	/** -------------------------------------------------------------------------
	 * Output Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get all items as an array.
	 *
	 * @return array Array of item data arrays.
	 */
	public function to_array(): array {
		return array_map( fn( Item $item ) => $item->to_array(), $this->items );
	}

	/** -------------------------------------------------------------------------
	 * Interface Implementations
	 * ------------------------------------------------------------------------ */

	/**
	 * String representation returns the rendered HTML.
	 *
	 * @return string The rendered breadcrumbs HTML.
	 */
	public function __toString(): string {
		return $this->render();
	}

	/**
	 * JSON serialization returns the items array.
	 *
	 * @return array Data for JSON encoding.
	 */
	public function jsonSerialize(): array {
		return $this->to_array();
	}

	/** -------------------------------------------------------------------------
	 * Internal Helpers
	 * ------------------------------------------------------------------------ */

	/**
	 * Render a single breadcrumb item.
	 *
	 * @param Item $item     The breadcrumb item.
	 * @param int  $position The position (1-based) for Schema.org.
	 * @param bool $active   Whether this is the active/current item.
	 *
	 * @return string The rendered list item HTML.
	 */
	protected function render_item( Item $item, int $position, bool $active ): string {
		$classes = $this->item_class;
		if ( $active ) {
			$classes .= ' ' . $this->active_class;
		}

		$extra_attrs = self::build_attributes( $item->attributes() );

		$icon_html = '';
		if ( $item->has_icon() ) {
			$icon_html = self::render_icon( $item->icon() );
		}

		if ( $item->is_link() && ! $active ) {
			$inner = sprintf(
				'<a href="%s" itemprop="item">%s<span itemprop="name">%s</span></a>',
				esc_url( $item->url() ),
				$icon_html,
				esc_html( $item->label() )
			);
		} else {
			$inner = sprintf(
				'%s<span itemprop="name">%s</span>',
				$icon_html,
				esc_html( $item->label() )
			);
		}

		return sprintf(
			'<li class="%s" itemprop="itemListElement" itemscope itemtype="https://schema.org/ListItem"%s%s>%s<meta itemprop="position" content="%d"></li>',
			esc_attr( $classes ),
			$active ? ' aria-current="page"' : '',
			$extra_attrs,
			$inner,
			$position
		);
	}

	/**
	 * Render the separator element.
	 *
	 * @return string The separator HTML.
	 */
	protected function render_separator(): string {
		return sprintf(
			'<li class="%s" aria-hidden="true">%s</li>',
			esc_attr( $this->separator_class ),
			$this->separator
		);
	}

	/**
	 * Render an icon, supporting both raw HTML and WordPress dashicon classes.
	 *
	 * @param string $icon Icon HTML or dashicon class name.
	 *
	 * @return string The rendered icon HTML.
	 */
	protected static function render_icon( string $icon ): string {
		if ( str_starts_with( $icon, 'dashicons-' ) ) {
			return sprintf(
				'<span class="dashicons %s" aria-hidden="true"></span> ',
				esc_attr( $icon )
			);
		}

		return $icon . ' ';
	}

	/**
	 * Build an HTML attributes string from an associative array.
	 *
	 * @param array $attributes Key/value pairs of attributes.
	 *
	 * @return string The attributes string (with leading space if non-empty).
	 */
	protected static function build_attributes( array $attributes ): string {
		if ( empty( $attributes ) ) {
			return '';
		}

		$parts = [];
		foreach ( $attributes as $key => $value ) {
			$key = self::sanitize_attribute_name( (string) $key );

			if ( '' === $key ) {
				continue;
			}

			$parts[] = sprintf( '%s="%s"', $key, esc_attr( $value ) );
		}

		return empty( $parts ) ? '' : ' ' . implode( ' ', $parts );
	}

	/**
	 * Validate an HTML attribute name, or reject it.
	 *
	 * esc_attr() escapes attribute *values*; it is the wrong tool for a name.
	 * It leaves spaces and '=' untouched, so a key of
	 * `x onfocus=alert(1) autofocus` renders as three separate attributes, and
	 * it considers `onmouseover` entirely safe because nothing in it requires
	 * escaping. Either turns caller-supplied attributes into script execution.
	 *
	 * Names are therefore checked against the HTML attribute-name grammar, and
	 * event handlers refused outright.
	 *
	 * @param string $name Raw attribute name.
	 *
	 * @return string The name if acceptable, or '' to drop the attribute.
	 */
	protected static function sanitize_attribute_name( string $name ): string {
		$name = strtolower( trim( $name ) );

		// Letters, digits, hyphen, underscore and colon only, starting with a
		// letter. Nothing matching this can contain whitespace, '=', '/', a
		// quote or '>', so it cannot break out of its own attribute.
		if ( 1 !== preg_match( '/^[a-z][a-z0-9_:-]*$/', $name ) ) {
			return '';
		}

		// Event handlers execute script, and no breadcrumb needs one.
		if ( str_starts_with( $name, 'on' ) ) {
			return '';
		}

		return $name;
	}

}
