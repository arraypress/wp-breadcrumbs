<?php
/**
 * Breadcrumb Item Value Object
 *
 * Represents a single breadcrumb item with label, optional URL, icon,
 * and extra HTML attributes. Immutable after construction.
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
 * Item Class
 *
 * Represents a single breadcrumb navigation item.
 */
class Item implements JsonSerializable, Stringable {

	/**
	 * The display label text.
	 *
	 * @var string
	 */
	private string $label;

	/**
	 * The URL for the breadcrumb link. Null for the current/active item.
	 *
	 * @var string|null
	 */
	private ?string $url;

	/**
	 * Optional icon HTML or dashicon class.
	 *
	 * @var string|null
	 */
	private ?string $icon;

	/**
	 * Optional extra HTML attributes.
	 *
	 * @var array
	 */
	private array $attributes;

	/**
	 * Create a new breadcrumb item.
	 *
	 * @param string      $label      The display text.
	 * @param string|null $url        Optional URL. Null for the current/active item.
	 * @param string|null $icon       Optional icon HTML or dashicon class.
	 * @param array       $attributes Optional extra HTML attributes.
	 */
	public function __construct( string $label, ?string $url = null, ?string $icon = null, array $attributes = [] ) {
		$this->label      = $label;
		$this->url        = $url;
		$this->icon       = $icon;
		$this->attributes = $attributes;
	}

	/** -------------------------------------------------------------------------
	 * Getters
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the display label.
	 *
	 * @return string The label text.
	 */
	public function label(): string {
		return $this->label;
	}

	/**
	 * Get the URL.
	 *
	 * @return string|null The URL or null if this is the current item.
	 */
	public function url(): ?string {
		return $this->url;
	}

	/**
	 * Get the icon.
	 *
	 * @return string|null The icon HTML or dashicon class.
	 */
	public function icon(): ?string {
		return $this->icon;
	}

	/**
	 * Get extra HTML attributes.
	 *
	 * @return array The attributes array.
	 */
	public function attributes(): array {
		return $this->attributes;
	}

	/**
	 * Check if this item has a URL (is a link).
	 *
	 * @return bool True if the item has a URL.
	 */
	public function is_link(): bool {
		return $this->url !== null;
	}

	/**
	 * Check if this item has an icon.
	 *
	 * @return bool True if the item has an icon.
	 */
	public function has_icon(): bool {
		return $this->icon !== null;
	}

	/** -------------------------------------------------------------------------
	 * Output Methods
	 * ------------------------------------------------------------------------ */

	/**
	 * Get the item data as an array.
	 *
	 * @return array Associative array of item data.
	 */
	public function to_array(): array {
		return [
			'label'      => $this->label,
			'url'        => $this->url,
			'icon'       => $this->icon,
			'attributes' => $this->attributes,
		];
	}

	/** -------------------------------------------------------------------------
	 * Interface Implementations
	 * ------------------------------------------------------------------------ */

	/**
	 * String representation returns the label.
	 *
	 * @return string The label text.
	 */
	public function __toString(): string {
		return $this->label;
	}

	/**
	 * JSON serialization returns the item data array.
	 *
	 * @return array Data for JSON encoding.
	 */
	public function jsonSerialize(): array {
		return $this->to_array();
	}
}
