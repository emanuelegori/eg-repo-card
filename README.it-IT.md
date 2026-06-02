# EG Ranking Repo

Plugin WordPress che mostra una card con i dati di un repository GitHub o Forgejo tramite shortcode.

![Card di esempio](screenshot-1.png)

## Caratteristiche

- Card responsive, larga quanto la colonna del post
- Riga azioni unificata: **Sito Web · Source Code · Data · Versione · Stelle · ?**
- Badge versione letto dalla release più recente; se non esistono release, usa l'ultimo tag git
- Tooltip localizzati su ogni elemento (italiano / inglese in base alla lingua di WordPress)
- Badge `?` con link al repository del plugin
- Cache via WordPress transients, durata configurabile (default 6 ore)
- Protezione anti-SSRF con `wp_safe_remote_get()`
- Colori personalizzabili dalla pagina admin
- Internazionalizzato (it_IT incluso)

## Requisiti

- WordPress 6.0 o superiore
- PHP 8.0 o superiore
- EG Forgejo Updater (per gli aggiornamenti automatici)

## Installazione

1. Caricare la cartella `eg-ranking-repo` in `wp-content/plugins/`
2. Attivare il plugin dal pannello Plugin di WordPress
3. Configurare le opzioni in **Impostazioni > EG Ranking Repo**

## Utilizzo

Inserire lo shortcode in qualsiasi pagina, post o widget:

```
[eg-ranking-repo url="https://github.com/owner/repo"]
[eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]
```

Funziona con GitHub e con qualsiasi istanza Forgejo o Gitea self-hosted.

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

## Configurazione

Andare in **Impostazioni > EG Ranking Repo**:

- **GitHub Personal Access Token**: senza token il limite è 60 richieste/ora. Con token personale il limite sale a 5.000/ora e consente l'accesso a repository privati.
- **Forgejo API Token**: necessario solo per repository privati o istanze con autenticazione obbligatoria.
- **Durata cache**: i dati vengono messi in cache tramite WordPress transients. Default: 6 ore.
- **Colori**: personalizza sfondo card, testo card, sfondo bottoni e testo bottoni.
- **Rimuovi token**: elimina un token compromesso senza accedere al database.

## Aggiornamenti

Il plugin si integra con **EG Forgejo Updater** tramite l'hook:

```php
do_action('eg_forgejo_updater_register', __FILE__, 'eg-ranking-repo');
```

Per rilasciare una nuova versione:

1. Aggiornare `EGR_VERSION` in `eg-ranking-repo.php` e il campo `Version:` nell'header
2. Fare commit e push sul repository Forgejo

EG Forgejo Updater rileva la nuova versione leggendo direttamente il campo `Version:` dal file sorgente sul branch `main` — non sono necessari release o tag.

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

## Licenza

GPL-2.0-or-later — https://www.gnu.org/licenses/gpl-2.0.html
