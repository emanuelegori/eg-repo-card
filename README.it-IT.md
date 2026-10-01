# EG Repo Card

[![Versione](https://img.shields.io/badge/Versione-2.1.2-green)](https://git.emanuelegori.uno/emanuelegori/eg-repo-card)
[![Licenza](https://img.shields.io/badge/Licenza-GPL--2.0--or--later-blue.svg)](https://www.gnu.org/licenses/gpl-2.0.html)
[![WordPress](https://img.shields.io/badge/WordPress-6.0+-orange.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-8.0+-purple.svg)](https://php.net)

Plugin WordPress che mostra una card con i dati di un repository GitHub, Codeberg, Forgejo o Gitea, oppure di un plugin della directory di WordPress.org: ultima versione, pulsante per scaricarla, stelle o valutazione.

*English: [README.md](README.md)*

![Card di repository Forgejo, Codeberg, GitHub e Gitea](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-1.png)

> **Prima si chiamava EG Ranking Repo.** La versione 2.0.0 rinomina il plugin. Vedi [Passare da EG Ranking Repo](#passare-da-eg-ranking-repo).

---

## Funzionalità

- Card responsive, larga quanto la colonna del contenuto
- Logo della piattaforma per GitHub, Codeberg, Forgejo e Gitea (Forgejo e Gitea vengono distinti in automatico)
- Pulsanti: **Sito web · Codice sorgente · Scarica**
- Badge: **ultimo aggiornamento · versione · linguaggio · licenza · stelle**, ognuno con il suo tooltip
- Pulsante Scarica: lo `.zip` allegato all'ultima release, oppure la pagina della release
- Versione dall'ultima release; in mancanza, dall'ultimo tag git
- Etichetta "Archiviato" per i repository in sola lettura
- Card per i plugin di WordPress.org: icona, pagina del plugin, Scarica, versione di WordPress testata, installazioni attive, valutazione, etichetta "Chiuso"
- Aspetto: preset neutro, trasparente, colore personalizzato o segui il browser del visitatore (chiaro/scuro), per la card e per i pulsanti; il testo si adatta allo sfondo
- Bordo, ombra e avatar del proprietario opzionali
- Cache con transient (predefinito 6 ore); se un'API non risponde, la card mostra gli ultimi dati ricevuti
- Errori visibili solo a chi modifica i contenuti, mai ai visitatori
- Icone SVG inline, nessuna CDN né font esterni
- Internazionalizzato (italiano incluso)

---

## Utilizzo

```
[eg-repo-card url="https://github.com/owner/repo"]
[eg-repo-card url="https://codeberg.org/owner/repo"]
[eg-repo-card url="https://git.example.com/owner/repo"]
[eg-repo-card url="https://wordpress.org/plugins/plugin-slug/"]
```

---

## Dati della card

| Elemento          | Fonte                                                              |
|-------------------|--------------------------------------------------------------------|
| Nome              | `full_name`                                                        |
| Descrizione       | `description`                                                      |
| Sito web          | `homepage` (GitHub) / `website` (Forgejo, Gitea)                   |
| Codice sorgente   | URL scritto nello shortcode                                        |
| Scarica           | unico allegato `.zip` dell'ultima release, altrimenti la pagina della release |
| Ultimo aggiornamento | `updated_at`                                                    |
| Versione          | tag dell'ultima release; in mancanza, l'ultimo tag git             |
| Linguaggio        | `language`                                                         |
| Licenza           | `license.spdx_id` (GitHub) / `licenses` (Forgejo, Gitea)           |
| Stelle            | `stargazers_count` / `stars_count`                                 |
| Archiviato        | `archived`                                                         |

Senza sito web il pulsante resta visibile, in grigio. Linguaggio, licenza, versione e Scarica compaiono solo se la piattaforma li fornisce.

### Plugin di WordPress.org

| Elemento            | Fonte (API `plugins/info/1.2`)                          |
|---------------------|---------------------------------------------------------|
| Nome, icona         | `name`, `icons`                                         |
| Descrizione         | `short_description`                                     |
| Sito web            | `homepage` (nascosto se è la pagina su WordPress.org)   |
| Pagina del plugin   | URL scritto nello shortcode                             |
| Scarica             | `download_link`                                         |
| Ultimo aggiornamento | `last_updated`                                         |
| Versione            | `version`                                               |
| Testato fino a      | `tested`                                                |
| Installazioni       | `active_installs`                                       |
| Valutazione         | `rating` (su 5) e `num_ratings` nel tooltip             |
| Chiuso              | `closed`                                                |

### Screenshot

| | |
|---|---|
| ![Card dei plugin di WordPress.org](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-2.png) | ![Tema scuro](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-3.png) |
| Card dei plugin di WordPress.org | "Segui il browser del visitatore" in tema scuro |

---

## Impostazioni

![Pagina delle impostazioni](https://git.emanuelegori.uno/emanuelegori/eg-repo-card/raw/branch/main/.wordpress-org/screenshot-4.png)

**Impostazioni > EG Repo Card**

- **Token di accesso personale GitHub**: alza il limite da 60 a 5.000 richieste all'ora.
- **Token Forgejo o Gitea**: solo per repository privati o istanze che richiedono l'accesso.
- **Durata cache**: da 1 a 168 ore, predefinito 6. Nella pagina c'è anche il pulsante per svuotarla.
- **Sfondo della card / Sfondo dei pulsanti**: preset neutro, trasparente, segui il browser del visitatore, colore personalizzato.
- **Bordo, Ombra, Avatar**: attivabili singolarmente. Avatar mostra l'avatar del proprietario o l'icona del plugin, caricati dalla piattaforma che li ospita.

### Filtri

| Filtro                          | Uso                                         |
|---------------------------------|---------------------------------------------|
| `eg_repo_card_platform_label`   | cambia l'etichetta della piattaforma (etichetta, host, piattaforma) |
| `eg_repo_card_language_colors`  | aggiunge o cambia i colori dei linguaggi (nome minuscolo → hex) |

`egr_platform_label` (1.x) funziona ancora ma è deprecato.

---

## Passare da EG Ranking Repo

1. Disattiva **EG Ranking Repo**.
2. Installa e attiva **EG Repo Card**: durata della cache, colori e token vengono importati.
3. Controlla le card. Gli shortcode `[eg-ranking-repo]` continuano a funzionare ma sono deprecati: sostituiscili con `[eg-repo-card]`.
4. Elimina EG Ranking Repo.

Le impostazioni del colore del testo non ci sono più: ora il testo si adatta allo sfondo.

---

## Servizi esterni

Il plugin contatta solo gli host scritti nei tuoi shortcode (API di GitHub, Codeberg, istanze Forgejo o Gitea, API di WordPress.org) per leggere i dati pubblici. I dettagli sono nella sezione *External services* di `readme.txt`.

---

## Struttura dei file

```
eg-repo-card/
├── eg-repo-card.php                       Header, costanti, avvio
├── uninstall.php                          Pulizia di option e transient
├── includes/
│   ├── class-eg-repo-card-main.php        Hook
│   ├── class-eg-repo-card-settings.php    Option e valori predefiniti
│   ├── class-eg-repo-card-migration.php   Import da EG Ranking Repo 1.x
│   ├── class-eg-repo-card-api.php         Chiamate API e cache
│   ├── class-eg-repo-card-style.php       CSS delle impostazioni Aspetto
│   ├── class-eg-repo-card-shortcode.php   Shortcode e markup della card
│   └── class-eg-repo-card-admin.php       Pagina impostazioni
├── assets/css/eg-repo-card.css            Layout della card
└── languages/eg-repo-card.pot             Modello di traduzione
```

---

## Changelog

### [2.1.2] - 2026-10-01
- Footer nella pagina impostazioni: documentazione, repository, donazioni, versione e licenza

### [2.1.1] - 2026-10-01
- La data di aggiornamento segue il formato data di WordPress
- Pallini dei linguaggi più visibili sugli sfondi scuri
- Screenshot

### [2.1.0] - 2026-10-01
- Card per i plugin di WordPress.org: pagina del plugin, Scarica, versione di WordPress testata, installazioni attive, valutazione, etichetta "Chiuso"
- L'opzione Avatar mostra anche l'icona del plugin

### [2.0.1] - 2026-10-01
- Corretti gli aggiornamenti automatici

### [2.0.0] - 2026-10-01
- Rinominato da EG Ranking Repo; nuovo shortcode `[eg-repo-card]`, `[eg-ranking-repo]` resta come alias deprecato
- Impostazioni importate da EG Ranking Repo
- Aspetto: sfondi neutro, trasparente, personalizzato o che seguono il browser; bordo, ombra, avatar
- Loghi delle piattaforme, riconoscimento di Gitea, pulsante Scarica, linguaggio, licenza, etichetta "Archiviato"
- Tempo trascorso nel tooltip della data
- Ultimi dati validi mostrati se un'API non risponde; errori solo per chi modifica i contenuti
- Rimossi il badge "?" e le impostazioni del colore del testo

Lo storico 1.x è in [changelog.txt](changelog.txt).

---

## Licenza

GPL-2.0-or-later — https://www.gnu.org/licenses/gpl-2.0.html

---

## Autore

**Emanuele Gori** — [emanuelegori.uno](https://emanuelegori.uno)
