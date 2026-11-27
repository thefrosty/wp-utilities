# Taxonomies

`TheFrosty\WpUtilities\Taxonomies\AbstractTaxonomy` registers a custom taxonomy for one or more post types, with
sensible defaults, generated labels and a REST/rewrite slug, using WordPress' own `register_taxonomy()`.

> **v4:** `jjgrainger/posttypes` is no longer a dependency (see [#275](https://github.com/thefrosty/wp-utilities/pull/275)).
> Taxonomies are registered natively and `registerTaxonomy()` returns `void`. See [Migrating from v3](#migrating-from-v3).

## Quick start

`AbstractTaxonomy` isn't a hook provider itself: implement `WpHooksInterface` and call `registerTaxonomy()` from
`addHooks()`.

```php
namespace MyPlugin\Taxonomies;

use TheFrosty\WpUtilities\Plugin\WpHooksInterface;
use TheFrosty\WpUtilities\Taxonomies\AbstractTaxonomy;

class Genre extends AbstractTaxonomy implements WpHooksInterface
{
    public const string|null TAXONOMY_TYPE = 'genre';
    public const array|string|null POST_TYPE = 'book'; // Or ['book', 'post'].
    public const string|null SLUG = 'book_genre';

    public const string TERM_FICTION = 'fiction';
    public const string TERM_HISTORY = 'history';

    public function __construct()
    {
        $this->setNames(['singular' => 'Genre', 'plural' => 'Genres']);
        $this->setArgs(['hierarchical' => true]);
        $this->setLabels(['menu_name' => 'Genres']);
        parent::__construct();
    }

    public function addHooks(): void
    {
        $this->registerTaxonomy();
    }
}
```

```php
PluginFactory::create('my-plugin')
    ->add(new \MyPlugin\Taxonomies\Genre())
    ->add(new \MyPlugin\PostTypes\Book())
    ->initialize();
```

## Required pieces

| Piece | Purpose |
|---|---|
| `TAXONOMY_TYPE` constant | The taxonomy key passed to `register_taxonomy()` (max. 32 characters). |
| `POST_TYPE` constant | The post type(s) the taxonomy is registered for: a string or an array. |
| `SLUG` constant | Used to derive the default rewrite slug & `rest_base` (see [Slugs](#slugs)). |
| `setNames()` | `singular` / `plural` names used to generate the labels. `name` and `slug` default to the constants. |
| `setArgs()` | Your `register_taxonomy()` args, merged over the [defaults](#default-args). |
| `setLabels()` | Labels that override the generated ones. Must not be empty: set at least one. |

Call the three setters **before** `parent::__construct()`. The constructor throws
`TheFrosty\WpUtilities\Exceptions\Exception` when a constant is missing (`Undefined constants.`) or a setter wasn't
called with a non-empty array (`Required class properties not set.`).

## Registration

`registerTaxonomy()` hooks `register_taxonomy()` to `init` at priority **9**, so the taxonomy exists before post
types register at 10. If `init` is running or has already fired, it's registered immediately instead.

## Default args

`setArgs()` merges your args over these defaults with `wp_parse_args()` (a shallow merge):

| Arg | Default |
|---|---|
| `public` / `publicly_queryable` | `true` |
| `show_ui` / `show_in_menu` | `true` |
| `show_in_nav_menus` | `false` |
| `show_in_rest` | `true` |
| `rest_base` | the [URL slug](#slugs) |
| `show_in_quick_edit` | `false` |
| `meta_box_cb` | `false` (no meta box on the edit screen; pass `null` for the default one) |
| `show_admin_column` | `true` |
| `hierarchical` | `false` |
| `query_var` | `true` |
| `rewrite` | `['slug' => the URL slug]` |

## Labels

Every label you don't set is generated from the names. For `Genre`:

| Label | Value |
|---|---|
| `name`, `menu_name` | Genres |
| `singular_name` | Genre |
| `all_items` | All Genres |
| `edit_item` / `view_item` / `update_item` | Edit Genre / View Genre / Update Genre |
| `add_new_item` / `new_item_name` | Add New Genre / New Genre Name |
| `parent_item` / `parent_item_colon` | Parent Genres / Parent Genres: |
| `search_items` / `popular_items` | Search Genres / Popular Genres |
| `separate_items_with_commas` | Separate Genres with commas |
| `add_or_remove_items` / `choose_from_most_used` | Add or remove Genres / Choose from most used Genres |
| `not_found` | No Genres found |

Without `singular`/`plural`, the names are derived from `TAXONOMY_TYPE` (`book_genre` becomes "Book Genre" and
"Book Genres"; `category` becomes "Categories"). Passing `labels` in `setArgs()` skips the generation entirely.

## Slugs

The rewrite slug and `rest_base` come from `URL_SLUG` when set, otherwise from `SLUG` in camelCase without
separators: `book_genre` becomes `bookGenre`.

## Terms as constants

Constants prefixed with `TERM_` document the taxonomy's known terms, and `AbstractTaxonomy::getTerms()` returns them:

```php
AbstractTaxonomy::getTerms(new Genre()); // ['TERM_FICTION' => 'fiction', 'TERM_HISTORY' => 'history']

if (!has_term(Genre::TERM_FICTION, Genre::TAXONOMY_TYPE, $post)) {
    // …
}
```

## Migrating from v3

v3 built a `PostTypes\Taxonomy` (from `jjgrainger/posttypes` v2) and `registerTaxonomy()` returned it. In v4:

| v3 | v4 |
|---|---|
| `$taxonomy = $this->registerTaxonomy();` | `$this->registerTaxonomy();` (returns `void`) |
| `->posttype('book')` on the returned object | `POST_TYPE` constant (string or array) |
| `jjgrainger/posttypes` installed via wp-utilities | Not installed any more: require it yourself if you use its API directly |

The generated `separate_items_with_commas` label now spells "Separate" correctly (v2 misspelled it).
