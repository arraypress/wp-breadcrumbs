<?php
declare( strict_types=1 );

namespace ArrayPress\Breadcrumbs\Tests;

use ArrayPress\Breadcrumbs\Breadcrumbs;
use ArrayPress\Breadcrumbs\Item;
use DOMDocument;
use DOMXPath;
use PHPUnit\Framework\TestCase;

/**
 * Building and rendering a breadcrumb trail.
 *
 * The output goes straight into an admin page, and every label, URL and
 * attribute in it can come from a caller passing through user input -- a
 * filename, a folder name someone typed. Most of what is asserted here is
 * therefore about escaping rather than markup.
 */
final class BreadcrumbsTest extends TestCase {

	/**
	 * Assert no element in the rendered markup carries an event handler.
	 *
	 * Checking for the substring "onfocus=" is the wrong test: esc_attr()
	 * renders a payload of `a" onfocus="alert(1)` as
	 * `a&quot; onfocus=&quot;alert(1)`, which is perfectly safe and still
	 * contains the substring. What matters is whether the payload broke out of
	 * its quoting and became an attribute, so parse the result and look.
	 *
	 * @param string $html Rendered markup.
	 *
	 * @return void
	 */
	private function assertNoEventHandlers( string $html ): void {
		$document = new DOMDocument();
		$loaded   = $document->loadHTML( '<meta charset="utf-8">' . $html, LIBXML_NOERROR );

		$this->assertTrue( $loaded, 'Rendered markup did not parse' );

		foreach ( ( new DOMXPath( $document ) )->query( '//*' ) as $element ) {
			foreach ( $element->attributes as $attribute ) {
				$this->assertStringStartsNotWith(
					'on',
					$attribute->nodeName,
					"Rendered an event handler: {$attribute->nodeName}"
				);
			}
		}
	}

	private function trail(): Breadcrumbs {
		return Breadcrumbs::create();
	}

	// -- Building ---------------------------------------------------------

	public function test_an_empty_trail_renders_nothing(): void {
		$trail = $this->trail();

		$this->assertTrue( $trail->is_empty() );
		$this->assertSame( '', $trail->render() );
	}

	public function test_items_are_kept_in_order(): void {
		$trail = $this->trail()->add( 'One', '/one' )->add( 'Two', '/two' )->add_current( 'Three' );

		$this->assertSame( 3, $trail->count() );
		$this->assertSame( 'One', $trail->first()->label() );
		$this->assertSame( 'Three', $trail->last()->label() );
	}

	public function test_add_current_makes_an_item_without_a_link(): void {
		$item = $this->trail()->add_current( 'Here' )->first();

		$this->assertFalse( $item->is_link() );
		$this->assertNull( $item->url() );
	}

	public function test_reset_empties_the_trail(): void {
		$trail = $this->trail()->add( 'One', '/one' )->reset();

		$this->assertTrue( $trail->is_empty() );
		$this->assertNull( $trail->first() );
	}

	public function test_add_items_accepts_a_batch(): void {
		$trail = $this->trail()->add_items( [
			[ 'label' => 'One', 'url' => '/one' ],
			[ 'label' => 'Two' ],
		] );

		$this->assertSame( 2, $trail->count() );
	}

	// -- Structure --------------------------------------------------------

	public function test_the_last_item_is_the_current_page(): void {
		$html = $this->trail()->add( 'One', '/one' )->add( 'Two', '/two' )->render();

		$this->assertSame( 1, substr_count( $html, 'aria-current="page"' ) );
		// The last item is current even though it was given a URL.
		$this->assertStringNotContainsString( '<a href="/two"', $html );
		$this->assertStringContainsString( '<a href="/one"', $html );
	}

	public function test_separators_go_between_items_only(): void {
		$html = $this->trail()->add( 'One', '/one' )->add( 'Two', '/two' )->add( 'Three' )->render();

		$this->assertSame( 2, substr_count( $html, 'arraypress-breadcrumbs__separator' ) );
	}

	public function test_positions_are_numbered_from_one(): void {
		$html = $this->trail()->add( 'One', '/one' )->add( 'Two', '/two' )->render();

		$this->assertStringContainsString( 'content="1"', $html );
		$this->assertStringContainsString( 'content="2"', $html );
	}

	public function test_the_trail_carries_schema_markup(): void {
		$html = $this->trail()->add( 'One', '/one' )->add( 'Two' )->render();

		$this->assertStringContainsString( 'https://schema.org/BreadcrumbList', $html );
		$this->assertStringContainsString( 'https://schema.org/ListItem', $html );
		$this->assertStringContainsString( 'itemprop="name"', $html );
	}

	// -- Escaping ---------------------------------------------------------

	public function test_a_label_is_escaped(): void {
		$html = $this->trail()->add( '<script>alert(1)</script>', '/x' )->add( 'End' )->render();

		$this->assertStringNotContainsString( '<script>', $html );
		$this->assertStringContainsString( '&lt;script&gt;', $html );
	}

	public function test_a_label_cannot_break_out_of_an_attribute(): void {
		$this->assertNoEventHandlers(
			$this->trail()->add( 'a" onmouseover="alert(1)', '/x' )->add( 'End' )->render()
		);
	}

	public function test_a_url_is_escaped(): void {
		$html = $this->trail()->add( 'Link', '/path?a=1&b=2' )->add( 'End' )->render();

		$this->assertStringContainsString( 'a=1&amp;b=2', $html );
	}

	/**
	 * A folder name arriving from storage can be anything, and it reaches the
	 * href as a path segment.
	 */
	public function test_a_javascript_url_does_not_reach_the_href(): void {
		$html = $this->trail()->add( 'Click', 'javascript:alert(1)' )->add( 'End' )->render();

		$this->assertStringNotContainsString( 'javascript:', $html );
	}

	public function test_a_config_class_is_escaped(): void {
		$this->assertNoEventHandlers(
			Breadcrumbs::create( [ 'nav_class' => 'x" onload="alert(1)' ] )->add( 'One' )->render()
		);
	}

	// -- Caller-supplied attributes ---------------------------------------

	public function test_a_plain_attribute_is_rendered(): void {
		$html = $this->trail()->add( 'One', '/one', null, [ 'data-bucket' => 'my-bucket' ] )->add( 'End' )->render();

		$this->assertStringContainsString( 'data-bucket="my-bucket"', $html );
	}

	public function test_an_attribute_value_is_escaped(): void {
		$this->assertNoEventHandlers(
			$this->trail()->add( 'One', '/one', null, [ 'data-x' => 'a" onfocus="alert(1)' ] )->add( 'End' )->render()
		);
	}

	/**
	 * esc_attr() escapes attribute *values* and is the wrong tool for a name:
	 * it leaves spaces and '=' untouched, so this key would otherwise render
	 * as three separate attributes, one of them an event handler.
	 */
	public function test_an_attribute_name_cannot_smuggle_in_a_second_attribute(): void {
		$html = $this->trail()
			->add( 'One', '/one', null, [ 'x onfocus=alert(1) autofocus' => 'y' ] )
			->add( 'End' )
			->render();

		$this->assertStringNotContainsString( 'autofocus', $html );
		$this->assertNoEventHandlers( $html );
	}

	/**
	 * Nothing in a breadcrumb needs an event handler, and esc_attr() considers
	 * one entirely safe because no character in it requires escaping.
	 */
	public function test_event_handler_attributes_are_refused(): void {
		$html = $this->trail()
			->add( 'One', '/one', null, [ 'onmouseover' => 'alert(1)', 'onclick' => 'alert(2)' ] )
			->add( 'End' )
			->render();

		$this->assertNoEventHandlers( $html );
	}

	public function test_a_malformed_attribute_name_is_dropped(): void {
		$html = $this->trail()
			->add( 'One', '/one', null, [ '9bad' => 'x', '' => 'y', 'has space' => 'z' ] )
			->add( 'End' )
			->render();

		$this->assertStringNotContainsString( '9bad', $html );
		$this->assertStringNotContainsString( 'has space', $html );
	}

	// -- Icons ------------------------------------------------------------

	public function test_a_dashicon_becomes_a_span(): void {
		$html = $this->trail()->add( 'One', '/one', 'dashicons-database' )->add( 'End' )->render();

		$this->assertStringContainsString( 'class="dashicons dashicons-database"', $html );
		$this->assertStringContainsString( 'aria-hidden="true"', $html );
	}

	public function test_a_dashicon_class_is_escaped(): void {
		$this->assertNoEventHandlers(
			$this->trail()->add( 'One', '/one', 'dashicons-x" onload="alert(1)' )->add( 'End' )->render()
		);
	}

	// -- Output forms -----------------------------------------------------

	public function test_display_echoes_what_render_returns(): void {
		$trail = $this->trail()->add( 'One', '/one' )->add( 'Two' );

		ob_start();
		$trail->display();

		$this->assertSame( $trail->render(), (string) ob_get_clean() );
	}

	public function test_string_casting_renders( ): void {
		$trail = $this->trail()->add( 'One', '/one' )->add( 'Two' );

		$this->assertSame( $trail->render(), (string) $trail );
	}

	public function test_json_serialization_returns_the_items(): void {
		$trail = $this->trail()->add( 'One', '/one' )->add_current( 'Two' );

		$decoded = json_decode( (string) json_encode( $trail ), true );

		$this->assertCount( 2, $decoded );
		$this->assertSame( 'One', $decoded[0]['label'] );
		$this->assertNull( $decoded[1]['url'] );
	}

	public function test_an_item_stringifies_to_its_label(): void {
		$this->assertSame( 'Label', (string) new Item( 'Label', '/x' ) );
	}
}
