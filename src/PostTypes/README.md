# Post types

`TheFrosty\WpUtilities\PostTypes\AbstractPostType` registers a custom post type with sensible defaults, generated
labels and a REST/rewrite slug, using WordPress' own `register_post_type()`.

> **v4:** `jjgrainger/posttypes` is no longer a dependency (see [#275](https://github.com/thefrosty/wp-utilities/pull/275)).
> Post types are registered natively and `registerPostType()` returns `void`. See [Migrating from v3](#migrating-from-v3).

## Quick start

```php
namespace MyPlugin\PostTypes;

use TheFrosty\WpUtilities\PostTypes\AbstractPostType;

class Book extends AbstractPostType
{
    public const array|string|null POST_TYPE = 'book';
    public const string|null SLUG = 'book';

    public function __construct()
    {
        $this->setNames(['singular' => 'Book', 'plural' => 'Books']);
        $this->setArgs(['supports' => ['title', 'editor', 'thumbnail']]);
        $this->setLabels(['menu_name' => 'Library']);
        parent::__construct();
    }

    public function addHooks(): void
    {
        $this->registerPostType();
    }
}
```

```php
use TheFrosty\WpUtilities\Plugin\PluginFactory;

PluginFactory::create('my-plugin')
    ->add(new \MyPlugin\PostTypes\Book())
    ->initialize();
```

`AbstractPostType` is a hook provider (it extends `AbstractHookProvider`), so it's added to the plugin like any other.

## Required pieces

| Piece | Purpose |
|---|---|
| `POST_TYPE` constant | The post type key passed to `register_post_type()` (max. 20 characters). |
| `SLUG` constant | Used to derive the default rewrite slug & `rest_base` (see [Slugs](#slugs)). |
| `setNames()` | `singular` / `plural` names used to generate the labels. `name` and `slug` default to the constants. |
| `setArgs()` | Your `register_post_type()` args, merged over the [defaults](#default-args). |
| `setLabels()` | Labels that override the generated ones. Must not be empty: set at least one (e.g. `menu_name`). |

Call the three setters **before** `parent::__construct()`. The constructor throws
`TheFrosty\WpUtilities\Exceptions\Exception` when a constant is missing (`Undefined constants.`) or a setter wasn't
called with a non-empty array (`Required class properties not set.`).

## Registration

`registerPostType()` hooks `register_post_type()` to `init` (priority 10). If `init` is running or has already
fired, the post type is registered immediately instead. Taxonomies register at priority 9, so they exist first.

## Default args

`setArgs()` merges your args over these defaults with `wp_parse_args()` (a shallow merge: passing `rewrite` replaces
the whole default `rewrite` array):

| Arg | Default |
|---|---|
| `public` | `true` |
| `exclude_from_search` | `false` |
| `publicly_queryable` | `true` |
| `show_ui` | `true` |
| `show_in_nav_menus` | `false` |
| `show_in_admin_bar` | `false` |
| `show_in_rest` | `true` |
| `rest_base` | the [URL slug](#slugs) |
| `capability_type` | `'post'` |
| `hierarchical` | `false` |
| `has_archive` | `true` |
| `query_var` | `true` |
| `can_export` | `true` |
| `supports` | `['title']` |
| `rewrite` | `['slug' => the URL slug]` |
| `delete_with_user` | `false` |

## Labels

Every label you don't set is generated from the names, so the `Book` example above registers with:

| Label | Value |
|---|---|
| `name`, `all_items` | Books |
| `singular_name` | Book |
| `menu_name` | Library *(set explicitly)* |
| `add_new` | Add New |
| `add_new_item` | Add New Book |
| `edit_item` / `new_item` / `view_item` | Edit Book / New Book / View Book |
| `search_items` | Search Books |
| `not_found` / `not_found_in_trash` | No Books found / No Books found in Trash |
| `parent_item_colon` | Parent Book: |

Without `singular`/`plural`, the names are derived from `POST_TYPE`: `book_review` becomes "Book Review" and
"Book Reviews"; a trailing `y` becomes `ies` (`story` becomes "Stories").

Passing `labels` in `setArgs()` skips the generation entirely: those labels are used as-is.

## Slugs

The rewrite slug and `rest_base` come from `URL_SLUG` when set, otherwise from `SLUG` in camelCase without
separators (`book_review` becomes `bookReview`):

```php
public const string|null SLUG = 'book_review';
public const string|null URL_SLUG = 'reviews'; // /reviews/my-review/ and /wp-json/wp/v2/reviews
```

## Menu icon

Override `getSvg()` with an inline SVG and pass `buildBase64DataImage()` as the `menu_icon`. The SVG is sized to
20&times;20 with a black fill so WordPress can recolour it for the admin colour scheme:

```php
public function __construct()
{
    $this->setNames(['singular' => 'Book', 'plural' => 'Books']);
    $this->setArgs(['menu_icon' => $this->buildBase64DataImage()]);
    $this->setLabels(['menu_name' => 'Library']);
    parent::__construct();
}

protected function getSvg(): string
{
    return '<svg viewBox="0 0 20 20"><path d="M3 2h11a3 3 0 0 1 3 3v13H6a3 3 0 0 1-3-3z"/></svg>';
}
```

## Taxonomies

Register a taxonomy for the post type with an [`AbstractTaxonomy`](../Taxonomies/README.md) whose `POST_TYPE` is the
post type, or pass existing taxonomies in the args (`'taxonomies' => ['category']`).

## Admin list table columns & filters

Use [`WpAdmin\Columns\AbstractColumns`](../WpAdmin/Columns/AbstractColumns.php) for custom columns and
[`WpAdmin\RestrictManagePosts`](../WpAdmin/RestrictManagePosts.php) for meta filters on `edit.php`.

## Migrating from v3

v3 built a `PostTypes\PostType` (from `jjgrainger/posttypes` v2) and `registerPostType()` returned it. In v4 the
library calls WordPress directly:

| v3 | v4 |
|---|---|
| `$postType = $this->registerPostType();` | `$this->registerPostType();` (returns `void`) |
| `->icon('dashicons-book')` on the returned object | `setArgs(['menu_icon' => 'dashicons-book'])` |
| `->taxonomy('genre')` | `setArgs(['taxonomies' => ['genre']])`, or an `AbstractTaxonomy` with `POST_TYPE = 'book'` |
| `->columns()->add(…)` / `->filters(…)` | `WpAdmin\Columns\AbstractColumns` / `WpAdmin\RestrictManagePosts` |
| `jjgrainger/posttypes` installed via wp-utilities | Not installed any more: add `"jjgrainger/posttypes": "^2.2"` to your own `composer.json` if you use its API directly |

Subclasses that only set constants, names, args and labels (and call `registerPostType()`) need no changes: the
same defaults and generated labels are used.
