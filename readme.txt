=== EG Repo Card ===
Contributors: emanuelegori
Tags: repository, github, forgejo, codeberg, gitea
Requires at least: 6.0
Tested up to: 7.1
Requires PHP: 8.0
Stable tag: 2.1.1
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

A card for a GitHub, Codeberg, Forgejo or Gitea repository, or a WordPress.org plugin: latest version, download button, stars or rating.

== Description ==

EG Repo Card shows a compact card for a code repository hosted on GitHub, Codeberg or any Forgejo or Gitea instance. Add the shortcode with the repository address and the card shows:

* repository name, platform logo and description
* buttons for the project website, the source code and the latest release download
* last update date (the time since the update appears on hover)
* latest version, from the latest release or, when there are none, the latest git tag
* main language, license and star count
* an "Archived" label for read-only repositories

The same shortcode works with plugins from the WordPress.org directory. Their card shows the plugin name, icon and description, the plugin page, the download of the latest version, the WordPress version it is tested up to, the active installations and the average rating. Closed plugins get a "Closed" label.

The card adapts to your theme: card and buttons can use a neutral preset, a transparent background, a custom color, or follow the light or dark mode of the visitor browser. Text and icons always stay readable on the chosen background.

Repository data is cached. If the platform does not answer, the card keeps showing the last data it received; visitors never see an error message.

== Installation ==

1. Upload the `eg-repo-card` folder to `/wp-content/plugins/`, or install the plugin from the **Plugins** screen.
2. Activate the plugin.
3. Optionally, go to **Settings > EG Repo Card** to add API tokens and choose the appearance.

== Usage ==

Insert the shortcode in any post, page, or widget:

  [eg-repo-card url="https://github.com/owner/repo"]
  [eg-repo-card url="https://codeberg.org/owner/repo"]
  [eg-repo-card url="https://git.example.com/owner/repo"]
  [eg-repo-card url="https://wordpress.org/plugins/plugin-slug/"]

== Frequently Asked Questions ==

= Which platforms are supported? =

GitHub, Codeberg, any self-hosted Forgejo or Gitea instance, and the WordPress.org plugin directory. The plugin tells Forgejo and Gitea apart and shows the right logo.

= Is an API token required? =

No. Without a token, GitHub allows 60 requests per hour from your server. A personal access token raises the limit to 5,000. A Forgejo or Gitea token is only needed for private repositories or instances that require login.

= Where does the Download button point? =

For a repository: to the `.zip` file attached to the latest release, when the release has exactly one. Otherwise it opens the release page. Without releases the button is not shown. For a WordPress.org plugin: to the official `.zip` of the latest version.

= How does the cache work? =

Data for each repository is stored as a WordPress transient for the duration set in the settings (default 6 hours, maximum 168). The settings page has a button to flush the cache.

= I used EG Ranking Repo. What do I need to do? =

EG Repo Card is the new name of EG Ranking Repo. Deactivate EG Ranking Repo, then activate EG Repo Card: it imports the cache duration, the colors and the tokens. Existing `[eg-ranking-repo]` shortcodes keep working, but they are deprecated: replace them with `[eg-repo-card]`. Once your cards look right, delete EG Ranking Repo.

== External services ==

To build a card, the plugin asks the platform that hosts the repository or the plugin for its public data. It contacts only the hosts written in the shortcodes on your site.

= GitHub =

* **What**: the GitHub REST API at `https://api.github.com`.
* **When**: when a page with a GitHub card is displayed and the cached data has expired.
* **Data sent**: the owner and name of the repository, and the personal access token if you saved one.
* **Data received**: public repository data (description, website, stars, language, license, latest release and tag).
* Terms of service: https://docs.github.com/en/site-policy/github-terms/github-terms-of-service
* Privacy statement: https://docs.github.com/en/site-policy/privacy-policies/github-general-privacy-statement

= Codeberg =

* **What**: the Codeberg API at `https://codeberg.org/api/v1`.
* **When**: when a page with a Codeberg card is displayed and the cached data has expired.
* **Data sent**: the owner and name of the repository, and the Forgejo token if you saved one.
* **Data received**: public repository data (description, website, stars, language, license, latest release and tag).
* Terms of use: https://codeberg.org/codeberg/org/src/branch/main/TermsOfUse.md
* Privacy policy: https://codeberg.org/codeberg/org/src/branch/main/PrivacyPolicy.md

= Forgejo and Gitea instances =

* **What**: the instance written in the shortcode, for example `https://git.example.com`.
* **When**: when a page with a card from that instance is displayed and the cached data has expired. The plugin also requests `/api/forgejo/v1/version` once a week to tell Forgejo from Gitea.
* **Data sent**: the owner and name of the repository, and the Forgejo token if you saved one.
* **Data received**: public repository data, as above.
* Terms and privacy policy depend on the instance and are published by whoever runs it.

= WordPress.org =

* **What**: the WordPress.org plugin directory API at `https://api.wordpress.org/plugins/info/1.2/`.
* **When**: when a page with a WordPress.org plugin card is displayed and the cached data has expired.
* **Data sent**: the slug of the plugin. No token is used.
* **Data received**: public plugin data (name, description, icon, version, last update, tested WordPress version, active installations, rating, download link).
* Privacy policy: https://wordpress.org/about/privacy/

= Owner avatar and plugin icon =

The image is off by default. When you enable it, visitors' browsers load the owner's avatar or the plugin icon directly from the platform that hosts it (GitHub, Codeberg, the Forgejo or Gitea instance, or WordPress.org), which receives the visitor's IP address like for any image request.

== Screenshots ==

1. Repository cards from Forgejo, Codeberg, GitHub and Gitea.
2. WordPress.org plugin cards: rating, active installations, tested WordPress version, and a closed plugin.
3. "Follow the visitor browser": the same cards in dark mode, with the optional shadow.
4. The settings page: API tokens, cache and appearance.

== Changelog ==

= 2.1.1 =
* Changed: the update date follows the date format set in WordPress.
* Fixed: language dots are easier to see on dark backgrounds.

= 2.1.0 =
* New: cards for plugins in the WordPress.org directory, with plugin page, download, tested WordPress version, active installations and rating.
* New: the Avatar option also shows the plugin icon.

= 2.0.1 =
* Fixed: automatic updates.

= 2.0.0 =
* New name: EG Ranking Repo is now EG Repo Card, with the new `[eg-repo-card]` shortcode. `[eg-ranking-repo]` still works but is deprecated.
* New: settings, colors and tokens of EG Ranking Repo are imported on first activation.
* New: card and button backgrounds offer a neutral preset, transparent, custom color, or follow the visitor browser; text adapts to the background.
* New: optional border, shadow and owner avatar.
* New: platform logos for GitHub, Codeberg, Forgejo and Gitea; Gitea instances are now recognized.
* New: Download button for the latest release.
* New: main language, license and "Archived" label.
* New: the date tooltip shows the time since the last update.
* Changed: when the platform does not answer, the card shows the last data received; error messages are visible only to editors.
* Removed: the "?" badge on the card.
* Removed: the text color settings, now chosen automatically.
* Deprecated: the `egr_platform_label` filter, replaced by `eg_repo_card_platform_label`.

The changelog of EG Ranking Repo 1.x is in `changelog.txt`.

== Upgrade Notice ==

= 2.1.0 =
The shortcode now also accepts WordPress.org plugin addresses.

= 2.0.0 =
EG Ranking Repo is now EG Repo Card. Deactivate EG Ranking Repo before activating this plugin; your settings are imported. Replace `[eg-ranking-repo]` with `[eg-repo-card]` in your content.
