# Changelog

## [2.1.3] - 2026-10-01

### Fixed
- La card occupava tutta la larghezza della finestra invece di quella del testo. Causa: `width: 100%` su `.eg-repo-card`, ereditato dalla 1.2.7 di EG Ranking Repo. Blocksy e i temi a blocchi limitano i contenuti con `.is-layout-constrained > :where(...) { width: var(--theme-block-width) }`: specificità (0,1,0), uguale alla nostra regola che, caricata dopo, vinceva. Misurato sullo stage (Blocksy, finestra 1280 px): paragrafo 1152 px a 64 px dal bordo, card 1280 px a 0. Senza la proprietà la card coincide con il paragrafo anche su mobile (343 px). Nei temi classici non cambia nulla: la card è un blocco e occupa comunque la colonna. Il problema c'era anche nel vecchio plugin, quindi anche in produzione.

### Changed
- Plugin di WordPress.org senza valutazioni: "0" invece di "–", con il tooltip "Nessuna valutazione", come le stelle a 0 dei repository (richiesta dell'utente: badge allineati).

## [2.1.2] - 2026-10-01

### Added
- Footer della pagina impostazioni identico a eg-social-timeline: a sinistra "Developed with ❤️ and maintained by Emanuele Gori · Documentation · Repository · Support the project", a destra "EG Repo Card v2.1.2 · License GPL-2.0-or-later". Solo sulla schermata `settings_page_eg-repo-card` (`current_screen`), negli slot `admin_footer_text` e `update_footer` (priorità 20), testo filtrato con `wp_kses` (solo link).
- Indirizzi traducibili: in inglese `/en/`, `/en/plugins/eg-repo-card/`, `/en/donate/`; in italiano home, `/plugin/eg-repo-card/`, `/sostieni/`. Anche il link "Documentation" nella riga del plugin usa ora la pagina del sito invece del repo.
- ⏳ Le due pagine del plugin sul sito non esistono ancora (404 al 2026-10-01): scelta dell'utente, sono il prossimo lavoro.
- `Donate link` nell'header di `readme.txt`.

### Removed
- Riga "Plugin version" in fondo alla pagina: la versione è nel footer a destra.

## [2.1.1] - 2026-10-01

Correzioni emerse guardando i primi screenshot fatti con Chromium headless sullo stage.

### Changed
- Data dell'ultimo aggiornamento: dal formato fisso `d-m-Y` al formato data di WordPress (`date_format`, Impostazioni → Generali). Un sito in inglese mostrava "27-09-2026" invece di "September 27, 2026". Il tooltip con il tempo trascorso resta.

### Fixed
- Pallino del linguaggio: il viola del PHP (`#4f5d95`) quasi spariva sulle card scure. Aggiunto un anello di 1px nel colore del testo al 45% (`color-mix()`), che funziona su qualsiasi sfondo senza dover conoscere il colore della card. Sui browser senza `color-mix()` l'anello semplicemente non compare.

### Added
- Screenshot per WordPress.org in `.wordpress-org/` (fuori dal pacchetto), con le didascalie in `readme.txt` e le immagini nei README.

## [2.1.0] - 2026-10-01

### Added
- **Card per i plugin della directory di WordPress.org**: `[eg-repo-card url="https://wordpress.org/plugins/<slug>/"]`, anche dai sottodomini delle lingue (`it.wordpress.org`), il cui indirizzo resta quello del pulsante. Una sola chiamata a `api.wordpress.org/plugins/info/1.2/` con i soli campi necessari (niente sezioni, recensioni, versioni), nessun token. Stessa cache e stesso fallback all'ultimo dato valido dei repository.
- Mappatura rispetto ai repository: logo WordPress e badge blu `#21759b`; icona del plugin al posto dell'avatar (stessa opzione); "Pagina del plugin" al posto di "Codice sorgente"; Scarica = `download_link`; "Testato fino a" al posto del linguaggio; installazioni attive al posto della licenza; valutazione media su 5 al posto delle stelle, con il numero di recensioni nel tooltip.
- Senza recensioni la valutazione mostra "–" con tooltip "Nessuna valutazione": uno "0" avrebbe fatto pensare a un voto pessimo. Sotto le 10 installazioni "Meno di 10", come su WordPress.org.
- Plugin chiusi: l'API risponde 404 sia per i chiusi sia per gli inesistenti, li distingue `"error":"closed"` nel corpo. Card ridotta con etichetta "Chiuso", pagina del plugin, nessun Scarica e nessun badge. Inesistente = errore (solo per gli editor).
- Se `homepage` punta alla pagina del plugin su WordPress.org (succede spesso, es. WP Fastest Cache) viene trattato come "nessun sito", per non avere due pulsanti verso lo stesso indirizzo.
- Nomi e descrizioni arrivano con entità HTML (`&#8211;`): decodificate prima dell'escape.
- `readme.txt`: nuova voce WordPress.org in External services (link alla privacy policy).

### Changed
- Cache: schema 3, i dati in cache delle versioni precedenti vengono riletti.
- Testi dell'opzione Avatar e dell'aiuto sullo shortcode estesi ai plugin di WordPress.org.

## [2.0.1] - 2026-10-01

### Fixed
- Rimesso nell'header `Gitea Plugin URI: https://git.emanuelegori.uno/emanuelegori/eg-repo-card`. Nella 2.0.0 l'avevo tolto pensando che violasse la linea guida 8 di WordPress.org: non è così, eg-social-timeline è stato approvato con lo stesso header (è una riga letta da un altro plugin, non codice di aggiornamento). Senza l'header EG Forgejo Updater non trova il plugin: il form "Installa plugin da Forgejo" installa ma non registra, quindi la 2.0.0 sarebbe rimasta senza aggiornamenti automatici.
- Il vecchio `do_action( 'eg_forgejo_updater_register' )` resta fuori: con l'header non serve, ed eg-social-timeline non ce l'ha.

## [2.0.0] - 2026-10-01

Il plugin cambia nome: **EG Ranking Repo → EG Repo Card**. Il vecchio nome non diceva cosa fa il plugin (non c'è nessuna classifica: mostra una card per un singolo repository). È anche il primo passo verso WordPress.org.

Repository nuovo, `emanuelegori/eg-repo-card`, con dentro tutta la storia di `eg-ranking-repo`. Il vecchio repository resta così com'è, archiviato, per le installazioni che non sono ancora passate al nuovo plugin.

### Nome, prefissi, shortcode
- Slug e text domain `eg-repo-card`. Prefissi completi al posto delle abbreviazioni `EGR_`/`egr_`, come chiede la revisione di WordPress.org: classi `EG_Repo_Card_*`, costanti `EG_REPO_CARD_*`, option e transient `eg_repo_card_*`, classi CSS `eg-repo-card__*`.
- Nuovo shortcode `[eg-repo-card]`. `[eg-ranking-repo]` resta come alias deprecato, senza avvisi sul frontend, per i contenuti già pubblicati. Se il vecchio plugin è ancora attivo, il tag resta suo.
- Filtro `egr_platform_label` deprecato con `apply_filters_deprecated()`, sostituito da `eg_repo_card_platform_label`. Nuovo filtro `eg_repo_card_language_colors`.

### Migrazione
- Alla prima esecuzione, se `eg_repo_card_settings` non esiste, vengono **copiate** le option `egr_*`: durata cache, token, colori. Un colore di sfondo diverso dal default 1.x diventa "Colore personalizzato", altrimenti resta il preset neutro (stesso aspetto di prima). Le option vecchie non vengono toccate: le cancella la disinstallazione di EG Ranking Repo.
- I colori del testo non vengono importati: ora si ricavano dallo sfondo.

### Aspetto
- Card e pulsanti hanno ognuno un menu come in eg-social-timeline: preset neutro, trasparente, segui il browser del visitatore (`prefers-color-scheme`), colore personalizzato.
- Il colore del testo si calcola dalla luminanza dello sfondo (soglia 0,18, dove il contrasto con testo scuro e chiaro si equivale). Con lo sfondo trasparente il testo eredita dal tema.
- Checkbox per bordo (attivo), ombra (spenta) e avatar del proprietario (spento: l'immagine arriva dal server del repository, quindi il browser del visitatore lo contatta).
- Il CSS delle impostazioni viene generato da `EG_Repo_Card_Style` e aggiunto con `wp_add_inline_style()`, una volta per pagina. Il foglio statico contiene solo il layout.
- Loghi Simple Icons (CC0) per GitHub, Codeberg, Forgejo e Gitea nel badge della piattaforma. Il badge Forgejo passa dal verde Gitea all'arancio.

### Contenuto della card
- Badge `?` rimosso: era un "Powered by" sempre acceso sul frontend.
- Gitea riconosciuto: le istanze che rispondono a `/api/forgejo/v1/version` sono Forgejo, le altre Gitea. Esito in cache per host, una settimana.
- Pulsante **Scarica**: lo `.zip` allegato all'ultima release se è l'unico `.zip`, altrimenti la pagina della release (Forgejo stesso ha 21 allegati). Senza release il pulsante non c'è: lo zip sorgente di un tag avrebbe il nome cartella sbagliato per un plugin.
- Release su Forgejo/Gitea filtrate con `draft=false&pre-release=false`, come fa già `/releases/latest` di GitHub.
- Nuovi badge: linguaggio (pallino colorato, colori di GitHub Linguist), licenza (su Forgejo il campo `licenses` è quasi sempre vuoto, quindi compare soprattutto su GitHub e Gitea), etichetta "Archiviato".
- Data: resta la data completa; nel tooltip il tempo trascorso (`human_time_diff()`).
- Invariati, per scelta: pulsante grigio "Nessun sito" e stelle anche a 0.

### Affidabilità
- Oltre alla cache normale, l'ultimo risultato valido resta 30 giorni in un transient a parte. Se l'API fallisce, la card mostra quello invece dell'errore.
- I messaggi d'errore li vede solo chi ha `edit_posts`; per i visitatori lo shortcode non produce nulla.
- Gli URL vengono accettati solo con schema http/https; gestiti porta e suffisso `.git`.

### WordPress.org
- Tolti l'header `Forgejo Plugin URI` e l'hook `eg_forgejo_updater_register` (linea guida 8). ⚠️ Scelta sbagliata, corretta nella 2.0.1: senza header l'updater non trova il plugin.
- `readme.txt` con la sezione `== External services ==`, `Tested up to: 7.1`, changelog solo della 2.x; lo storico 1.x va in `changelog.txt`.
- `.gitattributes` con elenco esplicito: il vecchio `*.md export-ignore` escludeva anche `README.md` dal pacchetto. Fuori anche `.po`/`.mo` (il `.pot` resta). Eliminato `.distignore`, duplicato.
- Aggiunto `LICENSE.md`.

## [1.5.1] - 2026-06-20

### Fixed
- Author name typo in the plugin header and translation template: "Emanuele Egori" → "Emanuele Gori".

## [1.5.0] - 2026-06-20

### Added
- Dedicated platform label and badge colour for Codeberg repositories. Codeberg runs Forgejo, so it shares the Gitea/Forgejo API, but the card now shows "Codeberg" (blue badge) instead of the generic "Forgejo".
- New `egr_platform_label` filter to register custom labels for additional hosts.

## [1.4.5] - 2026-06-02

### Fixed
- Cache invalidation now uses a generation counter (`egr_cache_gen` option) instead of relying on `wp_cache_flush()`, which may be a no-op on Redis with selective flush enabled. Old cache entries become orphaned automatically on flush.

## [1.4.4] - 2026-06-02

### Fixed
- `flush_all_cache()` now also calls `wp_cache_flush()` to clear Redis/Memcached object cache alongside the DB transient delete.

## [1.4.3] - 2026-06-02

### Added
- Screenshot with absolute Forgejo URL in readme.txt and README files for correct rendering in "View Details" popup.

## [1.4.2] - 2026-06-02

### Fixed
- Plugin header `Description:` translated to English (was hardcoded Italian).

## [1.4.1] - 2026-06-02

### Added
- Version badges (shields.io) in README.md and README.it-IT.md.
- Unified structure between English and Italian README files.

## [1.4.0] - 2026-06-02

### Changed
- Full i18n refactor: all PHP strings now use English msgids (WordPress convention)
- `it_IT.po`/`.mo` rebuilt with proper English→Italian translations
- `en_US.po`/`.mo` not needed — English is the native fallback

## [1.3.0] - 2026-06-02

### Aggiunto
- Badge "ultima versione rilasciata" nella card (da API releases GitHub/Forgejo); non mostrato se il repository non ha release
- Icona SVG tag per il badge versione

### Modificato
- Stelle e data ora stilizzate come badge (stesso ritmo visivo dei pulsanti, non interattivi)
- Tutti gli elementi unificati in un'unica riga: Sito Web | Source Code | Data | Versione | Stelle
- Tooltip i18n aggiunti su tutti gli elementi (Sito Web, Vai al repository, Ultimo aggiornamento, Ultima versione rilasciata, Stelle)
- Rimossa sezione `.egr-card__meta` separata

## [1.2.7] - 2026-06-02

### Modificato
- Card: rimosso `max-width: 420px` e cambiato `inline-flex` → `flex` — la card si allarga fino alla colonna del post e si adatta al tema responsive

## [1.2.6] - 2026-06-02

### Aggiunto
- `.distignore`: `README.it-IT.md` e `.gitignore` esclusi dall'installazione WordPress tramite il nuovo supporto `.distignore` di EG Forgejo Updater

## [1.2.5] - 2026-06-02

### Aggiunto
- `README.it-IT.md` — documentazione in italiano (Forgejo serve questo file automaticamente ai browser con lingua italiana)
- `README.md` tradotto in inglese — fallback per tutte le altre lingue

## [1.2.4] - 2026-06-02

### Corretto
- `phpcs:ignore` su `echo $notice` — falso positivo: la variabile è già costruita con `esc_html__()` e `esc_html()`, PHPCS non traccia l'escaping su righe precedenti

## [1.2.3] - 2026-06-02

### Corretto
- Commento `translators:` spostato sulla riga immediatamente sopra `esc_html__()` per compliance PHPCS
- `readme.txt` tradotto in inglese (Plugin Check compliance)

## [1.2.2] - 2026-06-02

### Rimosso
- `load_plugin_textdomain()` — non necessario da WP 4.6+; WordPress carica automaticamente le traduzioni dal file `.mo` nella cartella `languages/`

### Aggiunto
- `languages/eg-ranking-repo-it_IT.mo` compilato dal file `.po` (necessario per il caricamento automatico delle traduzioni)

### Corretto (Plugin Check compliance)
- `$val` nel campo durata cache usa ora `esc_attr()` invece del formato `%d` non escaped
- Commento `translators:` aggiunto alla stringa `esc_html__( 'Token %s rimosso.' )`
- `phpcs:ignore` documentato sulla query DELETE di transient in `flush_all_cache()` e `uninstall.php` (uso legittimo, bulk per pattern, non cachabile)
- `$style` nella card ora usa `esc_attr()` esplicitamente
- `phpcs:ignore` documentato sulle 5 chiamate SVG inline hardcoded
- "Tested up to" aggiornato a 7.0

## [1.2.1] - 2026-06-02

### Corretto
- Aggiunto `rel="noopener noreferrer"` al link "Documentazione" nei plugin action links (`target="_blank"` senza rel è una falla di sicurezza)
- Stringa "stars" nel tooltip stelle era hardcoded in inglese: ora usa `_n()` e viene tradotta correttamente con il testo domain (`%s star` / `%s stars`)

## [1.2.0] - 2026-06-02

### Sicurezza
- HTTPS forzato per le chiamate API Forgejo/Gitea: il token non viene più trasmesso in chiaro se l'URL del repository usa `http://`
- `flush_all_cache()` usa `$wpdb->prepare()` con `$wpdb->esc_like()` per i pattern LIKE

### Aggiunto
- Link "Documentazione" nella lista plugin (`plugin_action_links_`)
- Sezione "Rimuovi token API" nella pagina admin per eliminare token GitHub o Forgejo compromessi senza accedere al database
- `uninstall.php`: rimuove tutte le opzioni e i transient del plugin alla disinstallazione

### Corretto
- Errori API (connessione fallita, HTTP non 200, JSON non valido) vengono messi in cache per 5 minuti, evitando chiamate ripetute all'API su ogni page load in caso di errore
- Attributo `tabindex="-1"` aggiunto al bottone "Nessun sito" disabilitato per escluderlo dalla navigazione da tastiera

## [1.1.0] - 2026-06-02

### Aggiunto
- Header `Forgejo Plugin URI` per auto-update tramite EG Forgejo Updater
- Link "Impostazioni" nella lista plugin (`plugin_action_links_`)
- Opzione admin `egr_card_txt_color` per il colore del testo della card
- Campo `description` del repository visualizzato nella card

### Corretto
- Colori testo della card (`#111`, `#555`) ora usano CSS custom properties legate all'opzione admin, evitando testo illeggibile su sfondi scuri
- Campo token nella pagina admin non espone più il valore nel DOM; se già salvato mostra il placeholder `••••••••` e mantiene il valore esistente se il campo viene lasciato vuoto
- Protezione anti-SSRF: `wp_remote_get()` sostituito con `wp_safe_remote_get()` per bloccare automaticamente richieste verso IP privati e loopback

### Migliorato
- `add_shortcode()` spostato sull'hook `init` (pattern idiomatico WordPress)
- `format_date()` ora usa `wp_timezone()` per rispettare il fuso orario configurato nel sito

## [1.0.0] - 2026-06-02

### Aggiunto
- Prima release
- Shortcode `[eg-ranking-repo url="..."]` per visualizzare card repository GitHub e Forgejo/Gitea
- Supporto GitHub REST API v3 e Forgejo/Gitea API v1
- Pagina admin (Impostazioni > EG Ranking Repo): token API, durata cache, colori sfondo card e bottoni
- Cache via WordPress transients configurabile (default 6 ore)
- Svuota cache manuale dalla pagina admin
- Bottone "Sito Web" condizionale (visibile solo se il repository ha homepage impostata)
- Icone SVG inline (nessuna dipendenza esterna)
- Internazionalizzazione it_IT
