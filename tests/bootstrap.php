<?php
/**
 * Test bootstrap.
 *
 * Stands in for the handful of WordPress escaping functions this package uses.
 * They are the real implementations' behaviour, not no-ops: the point of most
 * of these tests is that output is escaped, and a stub that returned its input
 * unchanged would make every one of them pass regardless.
 */

declare( strict_types=1 );

require_once __DIR__ . '/../vendor/autoload.php';

if ( ! function_exists( 'esc_attr' ) ) {
	function esc_attr( $text ): string {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_html' ) ) {
	function esc_html( $text ): string {
		return htmlspecialchars( (string) $text, ENT_QUOTES, 'UTF-8' );
	}
}

if ( ! function_exists( 'esc_url' ) ) {
	function esc_url( $url ): string {
		$url = trim( (string) $url );

		// WordPress drops anything whose scheme is not allowed, which is what
		// stops javascript: and data: URLs reaching an href.
		if ( preg_match( '#^\s*(javascript|data|vbscript)\s*:#i', $url ) ) {
			return '';
		}

		return htmlspecialchars( $url, ENT_QUOTES, 'UTF-8' );
	}
}
