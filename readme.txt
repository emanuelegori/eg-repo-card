=== EG Ranking Repo ===
Contributors: emanuelegori
Tags: repository, github, forgejo, card, shortcode
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.4.5
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Display a compact card with repository data from GitHub or Forgejo via shortcode.

== Description ==

EG Ranking Repo displays a compact card with data from a GitHub repository or any Forgejo/Gitea instance via the shortcode `[eg-ranking-repo url="..."]`.

Features:

* Responsive card, as wide as the post column
* Unified action row: Website · Source Code · Date · Version · Stars · ?
* Version badge from the latest release; falls back to the latest git tag
* Localised tooltips on every element (based on WordPress language)
* `?` badge linking to the plugin repository
* GitHub REST API v3 and Forgejo/Gitea API v1 support
* Configurable transient cache to reduce API calls (default 6 hours)
* Customisable colours from the admin settings page
* Inline SVG icons — no CDN or external font dependency
* Anti-SSRF protection via `wp_safe_remote_get()`
* Internationalised (it_IT included)

== Installation ==

1. Upload the `eg-ranking-repo` folder to `/wp-content/plugins/`
2. Activate the plugin from the WordPress **Plugins** page
3. Go to **Settings > EG Ranking Repo** to configure API tokens and colours

== Usage ==

Insert the shortcode in any post, page, or widget:

  [eg-ranking-repo url="https://github.com/owner/repo"]
  [eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]

Works with any self-hosted Forgejo or Gitea instance.

== Frequently Asked Questions ==

= Can I use this plugin with a Gitea instance? =

Yes. The Gitea API v1 is compatible with the Forgejo API.

= Is the API token required? =

No. Without a token, GitHub allows 60 requests/hour per IP. A personal access token raises the limit to 5,000/hour and enables access to private repositories.

= How does the cache work? =

Data for each repository is stored as a WordPress transient. The duration is configurable (default 6 hours, maximum 168). The admin page includes a button to flush the cache manually. Errors (connection failures, non-200 HTTP responses) are also cached for 5 minutes to avoid hammering the API on every page load.

== Screenshots ==

![Repository card — name, description, unified action row with tooltips](https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo/raw/branch/main/screenshot-1.png)
Repository card: name, description, unified action row (Website, Source Code, Date, Version, Stars, ?) with localised tooltips.

== Changelog ==

= 1.4.5 =
* Changed: full i18n refactor — all PHP strings now use English msgids (WordPress convention)
* Changed: it_IT.po/mo rebuilt with English→Italian translations
* Fixed: plugin shows correctly in English when WordPress is set to English

= 1.2.7 =
* Changed: card now fills the full post column width — removed fixed `max-width: 420px`, switched to block-level `flex`

= 1.2.6 =
* Added: `.distignore` — `README.it-IT.md` and `.gitignore` excluded from the WordPress installation package

= 1.2.5 =
* Added: `README.it-IT.md` — Italian documentation (served automatically by Forgejo for Italian-language browsers)
* Added: `README.md` translated to English — fallback for all other languages

= 1.2.4 =
* Fixed: `phpcs:ignore` on `echo $notice` — false positive, variable already built with `esc_html__()` and `esc_html()`

= 1.2.3 =
* Fixed: `translators:` comment moved to the line immediately above `esc_html__()` (PHPCS compliance)
* Fixed: translated readme.txt to English (Plugin Check compliance)

= 1.2.2 =
* Fixed: `$val` in cache duration field now uses `esc_attr()` (Plugin Check compliance)
* Fixed: added `translators:` comment to "Token %s removed" string
* Fixed: `phpcs:ignore` on transient DELETE query in `flush_all_cache()` and `uninstall.php` (legitimate bulk delete, not cacheable)
* Fixed: `$style` in card output now uses `esc_attr()` explicitly
* Fixed: `phpcs:ignore` on the five hardcoded inline SVG icon calls
* Fixed: translated readme.txt to English (Plugin Check compliance)
* Removed: `load_plugin_textdomain()` — no longer needed since WP 4.6+ with a compiled `.mo` file
* Added: `languages/eg-ranking-repo-it_IT.mo` compiled from `.po`
* Updated: "Tested up to" to 7.0

= 1.2.1 =
* Fixed: added `rel="noopener noreferrer"` to the Documentation action link
* Fixed: star tooltip now uses `_n()` and is properly translatable

= 1.2.0 =
* Security: enforced HTTPS for Forgejo/Gitea API calls
* Security: `flush_all_cache()` uses `$wpdb->prepare()` with `$wpdb->esc_like()`
* Added: Documentation link in the plugin action links
* Added: Remove API Token section in the admin page
* Added: `uninstall.php` to clean up options and transients on uninstall
* Fixed: API errors are now cached for 5 minutes
* Fixed: `tabindex="-1"` on the disabled Website button

= 1.1.0 =
* Added: `Forgejo Plugin URI` header for auto-updates via EG Forgejo Updater
* Added: Settings link in the plugin action links
* Added: card text colour option
* Added: repository description in the card
* Fixed: hardcoded text colours replaced with CSS custom properties
* Fixed: token field no longer exposes the stored value in the HTML DOM
* Fixed: anti-SSRF protection via `wp_safe_remote_get()`
* Improved: `add_shortcode()` moved to the `init` hook
* Improved: `format_date()` now respects the WordPress timezone

= 1.0.0 =
* Initial release
