# EG Ranking Repo

Plugin WordPress che mostra una card con i dati di un repository GitHub o Forgejo tramite shortcode.

## Requisiti

- WordPress 6.0 o superiore
- PHP 8.0 o superiore
- EG Forgejo Updater (per gli aggiornamenti automatici)

## Installazione

1. Caricare la cartella `eg-ranking-repo` in `wp-content/plugins/`
2. Attivare il plugin dal pannello Plugin di WordPress
3. Configurare le opzioni in **Impostazioni > EG Ranking Repo**

## Utilizzo

Inserire lo shortcode in qualsiasi pagina, post o widget con supporto agli shortcode:

```
[eg-ranking-repo url="https://github.com/owner/repo"]
[eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]
```

Funziona con GitHub e con qualsiasi istanza Forgejo o Gitea self-hosted.

## Dati mostrati nella card

| Elemento        | Fonte                              |
|-----------------|------------------------------------|
| Nome repository | Campo `full_name` dell'API         |
| Stelle          | `stargazers_count` / `stars_count` |
| Ultimo aggiornamento | `updated_at`                  |
| Source Code     | URL passato nello shortcode        |
| Sito Web        | `homepage` / `website` del repo    |

Se il repository non ha un sito web impostato, il bottone viene mostrato come disabilitato con la scritta "Nessun sito".

## Configurazione

Andare in **Impostazioni > EG Ranking Repo**:

- **GitHub Personal Access Token**: senza token il limite è 60 richieste/ora. Con token personale il limite sale a 5000 richieste/ora.
- **Forgejo API Token**: necessario solo per repository privati.
- **Durata cache**: i dati vengono messi in cache tramite WordPress transients. Default: 6 ore.
- **Colori**: è possibile personalizzare il colore dello sfondo della card, lo sfondo e il testo dei bottoni.
- **Rimuovi token**: sezione dedicata per eliminare un token compromesso senza accedere al database.

## Aggiornamenti

Il plugin si integra con **EG Forgejo Updater** tramite l'hook:

```php
do_action('eg_forgejo_updater_register', __FILE__, 'eg-ranking-repo');
```

Per rilasciare una nuova versione:

1. Aggiornare `EGR_VERSION` in `eg-ranking-repo.php` e il campo `Version:` nell'header
2. Fare commit e push sul repository Forgejo
3. Creare una nuova Release su Forgejo con tag corrispondente alla versione (es. `1.1.0`)
4. Caricare lo ZIP del plugin come asset della release

## Struttura file

```
eg-ranking-repo/
├── eg-ranking-repo.php          Header WP, costanti, bootstrap
├── uninstall.php                Cleanup opzioni e transient alla disinstallazione
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
