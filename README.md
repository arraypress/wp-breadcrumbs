# WordPress Breadcrumbs

A lightweight fluent builder for generating accessible HTML breadcrumb navigation in WordPress. Supports Schema.org
microdata, ARIA attributes, icons (including dashicons), custom separators, and flexible configuration.

## Installation

```bash
composer require arraypress/wp-breadcrumbs
```

## Usage

### Fluent Builder

```php
use ArrayPress\Breadcrumbs\Breadcrumbs;

Breadcrumbs::create()
    ->add( 'Home', '/', 'dashicons-admin-home' )
    ->add( 'Products', '/products/' )
    ->add_current( 'Widget Pro' )
    ->display();
```

### One-Liners

```php
// Render from an array of items
echo render_breadcrumbs( [
    [ 'label' => 'Home', 'url' => '/' ],
    [ 'label' => 'Blog', 'url' => '/blog/' ],
    [ 'label' => 'My Post' ],
] );

// Echo directly
display_breadcrumbs( [
    [ 'label' => 'Dashboard', 'url' => admin_url() ],
    [ 'label' => 'Settings' ],
] );
```

### Helper Function

```php
// Create a builder via helper
create_breadcrumbs()
    ->add( 'Buckets', $buckets_url, 'dashicons-cloud' )
    ->add( $bucket_name, $bucket_url )
    ->add_current( $prefix )
    ->display();
```

### With Icons

```php
// WordPress dashicons (auto-detected by prefix)
Breadcrumbs::create()
    ->add( 'Home', '/', 'dashicons-admin-home' )
    ->add( 'Media', '/media/', 'dashicons-admin-media' )
    ->add_current( 'image.jpg' )
    ->display();

// Raw HTML icons (SVG, img, etc.)
Breadcrumbs::create()
    ->add( 'Home', '/', '<svg>...</svg>' )
    ->add_current( 'Page' )
    ->display();
```

### Custom Configuration

```php
Breadcrumbs::create( [
    'separator'  => '/',
    'aria_label' => 'File navigation',
    'nav_class'  => 'my-breadcrumbs',
] )
    ->add( 'Root', '/' )
    ->add( 'Documents', '/docs/' )
    ->add_current( 'readme.md' )
    ->display();

// Or configure via fluent methods
Breadcrumbs::create()
    ->separator( '&raquo;' )
    ->aria_label( 'Site navigation' )
    ->nav_class( 'custom-nav' )
    ->add( 'Home', '/' )
    ->add_current( 'About' )
    ->display();
```

### Bulk Items

```php
$items = [
    [ 'label' => 'Home', 'url' => '/', 'icon' => 'dashicons-admin-home' ],
    [ 'label' => 'Products', 'url' => '/products/' ],
    [ 'label' => 'Widgets', 'url' => '/products/widgets/' ],
    [ 'label' => 'Widget Pro' ],
];

Breadcrumbs::create()->add_items( $items )->display();
```

### Inspection

```php
$breadcrumbs = Breadcrumbs::create()
    ->add( 'Home', '/' )
    ->add( 'Blog', '/blog/' )
    ->add_current( 'Post' );

$breadcrumbs->count();      // 3
$breadcrumbs->has_items();   // true
$breadcrumbs->is_empty();    // false
$breadcrumbs->first();       // Item { label: 'Home', url: '/' }
$breadcrumbs->last();        // Item { label: 'Post', url: null }
$breadcrumbs->items();       // [ Item, Item, Item ]
```

### Array & JSON Output

```php
$breadcrumbs = Breadcrumbs::create()
    ->add( 'Home', '/' )
    ->add_current( 'About' );

$breadcrumbs->to_array();          // Array of item data
echo json_encode( $breadcrumbs );  // JSON serializable
echo (string) $breadcrumbs;        // Rendered HTML
```

### Item Value Object

```php
use ArrayPress\Breadcrumbs\Item;

$item = new Item( 'Products', '/products/', 'dashicons-cart' );

$item->label();       // 'Products'
$item->url();         // '/products/'
$item->icon();        // 'dashicons-cart'
$item->attributes();  // []
$item->is_link();     // true
$item->has_icon();    // true
$item->to_array();    // [ 'label' => ..., 'url' => ..., ... ]
```

## HTML Output

The rendered HTML follows semantic best practices:

```html

<nav class="arraypress-breadcrumbs" aria-label="Breadcrumb">
    <ol class="arraypress-breadcrumbs__list" itemscope itemtype="https://schema.org/BreadcrumbList">
        <li class="arraypress-breadcrumbs__item" itemprop="itemListElement" itemscope
            itemtype="https://schema.org/ListItem">
            <a href="/" itemprop="item">
                <span class="dashicons dashicons-admin-home" aria-hidden="true"></span>
                <span itemprop="name">Home</span>
            </a>
            <meta itemprop="position" content="1">
        </li>
        <li class="arraypress-breadcrumbs__separator" aria-hidden="true">&#8250;</li>
        <li class="arraypress-breadcrumbs__item arraypress-breadcrumbs__item--active" itemprop="itemListElement"
            itemscope itemtype="https://schema.org/ListItem" aria-current="page">
            <span itemprop="name">About</span>
            <meta itemprop="position" content="2">
        </li>
    </ol>
</nav>
```

## Methods

### Builder

| Method          | Returns  | Description                       |
|-----------------|----------|-----------------------------------|
| `create()`      | `static` | Factory method (static)           |
| `add()`         | `static` | Add an item with label, url, icon |
| `add_home()`    | `static` | Add a home/root item              |
| `add_current()` | `static` | Add the current page (no link)    |
| `add_items()`   | `static` | Add multiple items from array     |
| `reset()`       | `static` | Remove all items                  |

### Configuration

| Method              | Returns  | Description                |
|---------------------|----------|----------------------------|
| `separator()`       | `static` | Set separator string       |
| `aria_label()`      | `static` | Set nav ARIA label         |
| `nav_class()`       | `static` | Set nav element CSS class  |
| `list_class()`      | `static` | Set ol element CSS class   |
| `item_class()`      | `static` | Set li element CSS class   |
| `separator_class()` | `static` | Set separator li CSS class |
| `active_class()`    | `static` | Set active item CSS class  |

### Getters

| Method        | Returns  | Description            |
|---------------|----------|------------------------|
| `items()`     | `Item[]` | All breadcrumb items   |
| `count()`     | `int`    | Number of items        |
| `has_items()` | `bool`   | Whether items exist    |
| `is_empty()`  | `bool`   | Whether no items exist |
| `first()`     | `?Item`  | First item or null     |
| `last()`      | `?Item`  | Last item or null      |

### Rendering

| Method      | Returns  | Description             |
|-------------|----------|-------------------------|
| `render()`  | `string` | Get breadcrumbs as HTML |
| `display()` | `void`   | Echo breadcrumbs HTML   |

### Output

| Method       | Returns | Description          |
|--------------|---------|----------------------|
| `to_array()` | `array` | Items as data arrays |

### Item Methods

| Method         | Returns   | Description                    |
|----------------|-----------|--------------------------------|
| `label()`      | `string`  | Display text                   |
| `url()`        | `?string` | URL or null for current item   |
| `icon()`       | `?string` | Icon HTML or dashicon class    |
| `attributes()` | `array`   | Extra HTML attributes          |
| `is_link()`    | `bool`    | Whether item has a URL         |
| `has_icon()`   | `bool`    | Whether item has an icon       |
| `to_array()`   | `array`   | Item data as associative array |

### Configuration Defaults

| Option            | Default                                | Description           |
|-------------------|----------------------------------------|-----------------------|
| `separator`       | `&#8250;` (›)                          | Item separator        |
| `aria_label`      | `Breadcrumb`                           | Nav ARIA label        |
| `nav_class`       | `arraypress-breadcrumbs`               | Nav CSS class         |
| `list_class`      | `arraypress-breadcrumbs__list`         | List CSS class        |
| `item_class`      | `arraypress-breadcrumbs__item`         | Item CSS class        |
| `separator_class` | `arraypress-breadcrumbs__separator`    | Separator CSS class   |
| `active_class`    | `arraypress-breadcrumbs__item--active` | Active item CSS class |

## Requirements

- PHP 7.4+
- WordPress 5.0+

## License

GPL-2.0-or-later
