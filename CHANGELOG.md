# Changelog

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
