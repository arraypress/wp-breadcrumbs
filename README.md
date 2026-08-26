# Breadcrumbs

Build a breadcrumb trail that search engines can read and screen readers can
navigate.

## What it does

Breadcrumbs look like a list of links until you need them done properly: a
`<nav>` with a label, the current page marked with `aria-current` rather than
just styled differently, and Schema.org microdata so Google shows the trail
in results instead of a bare URL.

This builds that markup from the items you add, so the accessibility and the
structured data come for free rather than being remembered.

## Features

* Build a trail by adding items, with the current page marked correctly
* Get Schema.org BreadcrumbList microdata without writing any of it
* Mark the current item with `aria-current`, not just a CSS class
* Add a dashicon to any item
* Set your own classes on the nav, the list, the items and the separator
* Render from an array, when the trail is already assembled elsewhere

## Installation

```bash
composer require arraypress/wp-breadcrumbs
```

## Quick start

```php
use ArrayPress\Breadcrumbs\Breadcrumbs;

Breadcrumbs::create()
	->add( 'Home', '/', 'dashicons-admin-home' )
	->add( 'Products', '/products/' )
	->add_current( 'Widget Pro' )
	->display();
```

`add_current()` rather than a third `add()` — the last crumb is the page you
are on, so it is not a link and is announced as the current one.

When the trail already exists as data:

```php
echo render_breadcrumbs( [
	[ 'label' => 'Home', 'url' => '/' ],
	[ 'label' => 'Blog', 'url' => '/blog/' ],
	[ 'label' => 'This post' ],
] );
```

## Requirements

* PHP 8.3 or later
* WordPress 7.1 or later

## License

GPL-2.0-or-later
