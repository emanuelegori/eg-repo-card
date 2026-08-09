# EG Ranking Repo

[![Version](https://img.shields.io/badge/Version-1.5.1-green)](https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo)
[![License](https://img.shields.io/badge/License-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.0+-orange.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)

WordPress plugin that displays a compact card with repository data from GitHub, Codeberg or Forgejo via shortcode.

![Card example](https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo/raw/branch/main/screenshot-1.png)

---

## Features

- Responsive card, as wide as the post column
- Unified action row: **Website · Source Code · Date · Version · Stars · ?**
- Version badge from the latest release; falls back to the latest git tag
- Localised tooltips on every element (based on WordPress language)
- `?` badge linking to the plugin repository
- Transient cache with configurable duration (default 6 hours)
- Anti-SSRF protection via `wp_safe_remote_get()`
- Customisable colours from the admin settings page
- Internationalised (it_IT included)

---

## Installation

1. Upload the `eg-ranking-repo` folder to `wp-content/plugins/`
2. Activate the plugin from the WordPress Plugins page
3. Configure the options under **Settings > EG Ranking Repo**

### Automatic updates

Install [EG Forgejo Updater](https://git.emanuelegori.uno/emanuelegori/eg-forgejo-updater) to receive automatic updates directly inside WordPress.

---

## Usage

Insert the shortcode in any page, post, or widget:

```
[eg-ranking-repo url="https://github.com/owner/repo"]
[eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]
```

Works with GitHub and any self-hosted Forgejo or Gitea instance.

---

## Card Data

| Field              | Source                                        |
|--------------------|-----------------------------------------------|
| Repository name    | `full_name` API field                         |
| Description        | `description` API field                       |
| Website            | `homepage` / `website` of the repo            |
| Source Code        | URL passed in the shortcode                   |
| Last updated       | `updated_at`                                  |
| Version            | Latest release tag; falls back to git tag     |
| Stars              | `stargazers_count` / `stars_count`            |

If no website is set in the repository, the button is shown as disabled.

---

## Configuration

Go to **Settings > EG Ranking Repo**:

- **GitHub Personal Access Token**: without a token the limit is 60 requests/hour. With a personal token it rises to 5,000/hour and enables access to private repositories.
- **Forgejo API Token**: required only for private repositories or instances with mandatory authentication.
- **Cache duration**: data is cached via WordPress transients. Default: 6 hours.
- **Colours**: customise card background, card text, button background and button text.
- **Remove token**: delete a compromised token without accessing the database.

---

## File Structure

```
eg-ranking-repo/
├── eg-ranking-repo.php          WP header, constants, bootstrap
├── uninstall.php                Options and transient cleanup on uninstall
├── screenshot-1.png             Card screenshot
├── includes/
│   ├── class-egr-main.php       Init, EG Forgejo Updater hook
│   ├── class-egr-api.php        GitHub/Forgejo API calls, cache, formatting
│   ├── class-egr-shortcode.php  Shortcode and card HTML rendering
│   └── class-egr-settings.php  wp-admin settings page
├── assets/
│   └── css/
│       └── eg-ranking-repo.css  Frontend card styles
└── languages/
    ├── eg-ranking-repo.pot      Translation template
    └── eg-ranking-repo-it_IT.po Italian translation
```

---

## Changelog

### [1.5.1] - 2026-06-20
- Author name typo fixed in plugin header and translation template

### [1.5.0] - 2026-06-20
- Dedicated label and badge colour for Codeberg repositories
- New `egr_platform_label` filter to register custom labels for additional hosts

### [1.4.5] - 2026-06-02
- Cache invalidation via generation counter — reliable with Redis selective flush

### [1.4.x] - 2026-06-02
- i18n refactor: English msgids, `it_IT.mo` Italian translations
- Version badges in README, unified EN/IT structure
- Plugin header description in English
- Screenshot with absolute URL in readme.txt

### [1.3.x] - 2026-06-02
- Version badge from API releases/tags
- Unified action row: Website · Source Code · Date · Version · Stars · ?
- `?` badge linking to plugin repository
- Card full-width responsive

### [1.2.x] - 2026-06-02
- Plugin Check compliance fixes
- HTTPS forced for Forgejo API, error caching, uninstall.php

---

## Licence

GPL-2.0-or-later — https://www.gnu.org/licenses/gpl-2.0.html

---

## Author

**Emanuele Gori** — [emanuelegori.uno](https://emanuelegori.uno)
