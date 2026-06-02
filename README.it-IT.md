# EG Ranking Repo

[![Versione](https://img.shields.io/badge/Versione-1.4.3-green)](https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo)
[![Licenza](https://img.shields.io/badge/Licenza-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.0+-orange.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)

Plugin WordPress che mostra una card con i dati di un repository GitHub o Forgejo tramite shortcode.

![Card di esempio](https://git.emanuelegori.uno/emanuelegori/eg-ranking-repo/raw/branch/main/screenshot-1.png)

---

## Caratteristiche

- Card responsive, larga quanto la colonna del post
- Riga azioni unificata: **Sito Web · Source Code · Data · Versione · Stelle · ?**
- Badge versione letto dalla release più recente; fallback all'ultimo tag git
- Tooltip localizzati su ogni elemento (in base alla lingua di WordPress)
- Badge `?` con link al repository del plugin
- Cache via WordPress transients, durata configurabile (default 6 ore)
- Protezione anti-SSRF con `wp_safe_remote_get()`
- Colori personalizzabili dalla pagina admin
- Internazionalizzato (it_IT incluso)

---

## Installazione

1. Caricare la cartella `eg-ranking-repo` in `wp-content/plugins/`
2. Attivare il plugin dal pannello Plugin di WordPress
3. Configurare le opzioni in **Impostazioni > EG Ranking Repo**

### Aggiornamenti automatici

Installare [EG Forgejo Updater](https://git.emanuelegori.uno/emanuelegori/eg-forgejo-updater) per ricevere aggiornamenti automatici direttamente da WordPress.

---

## Utilizzo

Inserire lo shortcode in qualsiasi pagina, post o widget:

```
[eg-ranking-repo url="https://github.com/owner/repo"]
[eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]
```

Funziona con GitHub e con qualsiasi istanza Forgejo o Gitea self-hosted.

---

## Dati mostrati nella card

| Elemento             | Fonte                                          |
|----------------------|------------------------------------------------|
| Nome repository      | Campo `full_name` dell'API                     |
| Descrizione          | Campo `description` dell'API                   |
| Sito Web             | `homepage` / `website` del repo                |
| Source Code          | URL passato nello shortcode                    |
| Data aggiornamento   | `updated_at`                                   |
| Versione             | Tag dell'ultima release; fallback al tag git   |
| Stelle               | `stargazers_count` / `stars_count`             |

Se il repository non ha un sito web impostato, il bottone viene mostrato come disabilitato.

---

## Configurazione

Andare in **Impostazioni > EG Ranking Repo**:

- **GitHub Personal Access Token**: senza token il limite è 60 richieste/ora. Con token personale il limite sale a 5.000/ora e consente l'accesso a repository privati.
- **Forgejo API Token**: necessario solo per repository privati o istanze con autenticazione obbligatoria.
- **Durata cache**: i dati vengono messi in cache tramite WordPress transients. Default: 6 ore.
- **Colori**: personalizza sfondo card, testo card, sfondo bottoni e testo bottoni.
- **Rimuovi token**: elimina un token compromesso senza accedere al database.

---

## Struttura file

```
eg-ranking-repo/
├── eg-ranking-repo.php          Header WP, costanti, bootstrap
├── uninstall.php                Cleanup opzioni e transient alla disinstallazione
├── screenshot-1.png             Screenshot card
├── includes/
│   ├── class-egr-main.php       Init, hook EG Forgejo Updater
│   ├── class-egr-api.php        Chiamate API GitHub/Forgejo, cache, formattazione
│   ├── class-egr-shortcode.php  Shortcode e rendering HTML card
│   └── class-egr-settings.php  Pagina impostazioni wp-admin
├── assets/
│   └── css/
│       └── eg-ranking-repo.css  Stili frontend card
└── languages/
    ├── eg-ranking-repo.pot      Template traduzioni
    └── eg-ranking-repo-it_IT.po Traduzione italiana
```

---

## Changelog

### [1.4.0] - 2026-06-02
- Refactoring i18n completo: msgid in inglese (convenzione WordPress), `it_IT.mo` con traduzioni italiane

### [1.3.x] - 2026-06-02
- Badge versione da API releases/tag git
- Riga azioni unificata: Sito Web · Source Code · Data · Versione · Stelle · ?
- Badge `?` con link al repository del plugin
- Card full-width responsive

### [1.2.x] - 2026-06-02
- Compliance Plugin Check, HTTPS forzato API Forgejo, cache errori, uninstall.php

---

## Licenza

GPL-2.0-or-later — https://www.gnu.org/licenses/gpl-2.0.html

---

## Autore

**Emanuele Gori** — [emanuelegori.uno](https://emanuelegori.uno)
