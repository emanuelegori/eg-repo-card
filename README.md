# EG Repo Card

[![Version](https://img.shields.io/badge/Version-2.2.0-green)](https://git.emanuelegori.uno/emanuelegori/eg-repo-card)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.0+-orange.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)

WordPress plugin that shows a card with the data of a GitHub, Codeberg, Forgejo or Gitea repository, or of a plugin in the WordPress.org directory: latest version, download button, stars or rating.

*Italiano: [README.it-IT.md](README.it-IT.md)*

![Repository cards from Forgejo, Codeberg, GitHub and Gitea](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-1.png)

---

## Features

- Responsive card, as wide as the content column
- Platform logo for GitHub, Codeberg, Forgejo and Gitea (Forgejo and Gitea are told apart automatically)
- Buttons: **Website · Source Code · Download**
- Badges: **last update · version · language · license · stars**, each with a tooltip
- Download button: the `.zip` attached to the latest release, or the release page
- Version from the latest release; falls back to the latest git tag
- "Archived" label for read-only repositories
- Cards for WordPress.org plugins: icon, plugin page, download, tested WordPress version, active installations, rating, "Closed" label
- Appearance: neutral preset, transparent, custom color or follow the visitor browser (light/dark), for the card and for the buttons; text adapts to the background
- Optional border, shadow and owner avatar
- Transient cache (default 6 hours); when an API does not answer, the last data received stays on the card
- Errors visible only to editors, never to visitors
- Inline SVG icons, no CDN or external fonts
- Internationalised (Italian included)

---

## Usage

```
[eg-repo-card url="https://github.com/WordPress/wordpress-develop"]
[eg-repo-card url="https://codeberg.org/forgejo/forgejo"]
[eg-repo-card url="https://git.emanuelegori.uno/emanuelegori/eg-repo-card"]
[eg-repo-card url="https://wordpress.org/plugins/akismet/"]
```

---

## Card data

| Element      | Source                                                          |
|--------------|-----------------------------------------------------------------|
| Name         | `full_name`                                                     |
| Description  | `description`                                                   |
| Website      | `homepage` (GitHub) / `website` (Forgejo, Gitea)                |
| Source Code  | URL written in the shortcode                                    |
| Download     | single `.zip` asset of the latest release, else the release page |
| Last update  | `updated_at`                                                    |
| Version      | latest release tag; falls back to the latest git tag            |
| Language     | `language`                                                      |
| License      | `license.spdx_id` (GitHub) / `licenses` (Forgejo, Gitea)        |
| Stars        | `stargazers_count` / `stars_count`                              |
| Archived     | `archived`                                                      |

Without a website the button stays visible, greyed out. Language, license, version and Download appear only when the platform provides them.

### WordPress.org plugins

| Element         | Source (`plugins/info/1.2` API)                        |
|-----------------|--------------------------------------------------------|
| Name, icon      | `name`, `icons`                                        |
| Description     | `short_description`                                    |
| Website         | `homepage` (hidden when it is the WordPress.org page)  |
| Plugin page     | URL written in the shortcode                           |
| Download        | `download_link`                                        |
| Last update     | `last_updated`                                         |
| Version         | `version`                                              |
| Tested up to    | `tested`                                               |
| Installations   | `active_installs`                                      |
| Rating          | `rating` (out of 5) and `num_ratings` in the tooltip  |
| Closed          | `closed`                                               |

### Screenshots

| | |
|---|---|
| ![WordPress.org plugin cards](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-2.png) | ![Dark mode](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-3.png) |
| WordPress.org plugin cards | "Follow the visitor browser" in dark mode |

---

## Settings

![Settings page](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-4.png)

**Settings > EG Repo Card**

- **GitHub personal access token**: raises the limit from 60 to 5,000 requests per hour.
- **Forgejo or Gitea token**: only for private repositories or instances that require login.
- **Cache duration**: 1 to 168 hours, default 6. The page also has a button to flush the cache.
- **Card background / Button background**: neutral preset, transparent, follow the visitor browser, custom color.
- **Border, Shadow, Avatar**: on/off. Avatar shows the owner avatar or the plugin icon, loaded from the platform that hosts it.

### Filters

| Filter                          | Use                                         |
|---------------------------------|---------------------------------------------|
| `eg_repo_card_platform_label`   | change the platform label (label, host, platform) |
| `eg_repo_card_language_colors`  | add or change language colors (lowercase name → hex) |

---

## External services

The plugin contacts only the hosts written in your shortcodes (GitHub API, Codeberg, Forgejo or Gitea instances, WordPress.org API) to read public data. Details are in the *External services* section of `readme.txt`.

---

## File structure

```
eg-repo-card/
├── eg-repo-card.php                       Header, constants, bootstrap
├── uninstall.php                          Options and transient cleanup
├── includes/
│   ├── class-eg-repo-card-main.php        Hooks
│   ├── class-eg-repo-card-settings.php    Options and defaults
│   ├── class-eg-repo-card-api.php         API calls and cache
│   ├── class-eg-repo-card-style.php       CSS of the appearance settings
│   ├── class-eg-repo-card-shortcode.php   Shortcode and card markup
│   └── class-eg-repo-card-admin.php       Settings page
├── assets/css/eg-repo-card.css            Card layout
└── languages/eg-repo-card.pot             Translation template
```

---

## Changelog

### [2.2.0] - 2026-10-03
- Removed the `[eg-ranking-repo]` shortcode, the `egr_platform_label` filter and the import of EG Ranking Repo settings
- Real, working addresses in the usage examples

### [2.1.3] - 2026-10-01
- The card follows the width of the post content
- Plugins without ratings show 0

### [2.1.2] - 2026-10-01
- Footer on the settings page: documentation, repository, donations, version and license

### [2.1.1] - 2026-10-01
- The update date follows the WordPress date format
- Language dots easier to see on dark backgrounds
- Screenshots

### [2.1.0] - 2026-10-01
- Cards for WordPress.org plugins: plugin page, download, tested WordPress version, active installations, rating, "Closed" label
- The Avatar option also shows the plugin icon

### [2.0.1] - 2026-10-01
- Fixed automatic updates

### [2.0.0] - 2026-10-01
- Renamed from EG Ranking Repo; new `[eg-repo-card]` shortcode, `[eg-ranking-repo]` deprecated alias
- Settings imported from EG Ranking Repo
- Appearance: neutral, transparent, custom or browser-driven backgrounds; border, shadow, avatar
- Platform logos, Gitea detection, Download button, language, license, "Archived" label
- Relative time in the date tooltip
- Last good data shown when an API fails; errors only for editors
- Removed the "?" badge and the text color settings

The 1.x history is in [changelog.txt](changelog.txt).

---

## License

GPL-2.0-or-later — https://www.gnu.org/licenses/gpl-2.0.html

---

## Author

**Emanuele Gori** — [emanuelegori.uno](https://emanuelegori.uno)
