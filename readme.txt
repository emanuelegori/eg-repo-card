=== EG Ranking Repo ===
Contributors: emanuelegori
Tags: repository, github, forgejo, card, shortcode
Requires at least: 6.0
Tested up to: 7.0
Requires PHP: 8.0
Stable tag: 1.2.2
License: GPL-2.0-or-later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Mostra una card con i dati di un repository GitHub o Forgejo tramite shortcode.

== Description ==

EG Ranking Repo visualizza una card compatta con i dati di un repository GitHub o di qualsiasi istanza Forgejo/Gitea tramite shortcode `[eg-ranking-repo url="..."]`.

Funzionalità:

* Supporto GitHub REST API v3 e Forgejo/Gitea API v1
* Visualizza nome, descrizione, stelle e data ultimo aggiornamento
* Bottone "Source Code" e "Sito Web" (se impostato nel repository)
* Cache via WordPress transients configurabile per ridurre le chiamate API
* Colori personalizzabili dalla pagina admin (sfondo card, testo, bottoni)
* Icone SVG inline: nessuna dipendenza da CDN o font esterni
* Protezione anti-SSRF con `wp_safe_remote_get()`
* Internazionalizzato (it_IT incluso)

== Installation ==

1. Carica la cartella `eg-ranking-repo` in `/wp-content/plugins/`
2. Attiva il plugin dalla pagina **Plugin** di WordPress
3. Vai in **Impostazioni > EG Ranking Repo** per configurare token API e colori

== Utilizzo ==

Inserisci lo shortcode in qualsiasi post, pagina o widget:

  [eg-ranking-repo url="https://github.com/owner/repo"]
  [eg-ranking-repo url="https://git.emanuelegori.uno/owner/repo"]

Funziona con qualsiasi istanza Forgejo o Gitea self-hosted.

== Frequently Asked Questions ==

= Posso usare il plugin con un'istanza Gitea? =

Sì. La Gitea API v1 è compatibile con quella di Forgejo.

= Il token API è obbligatorio? =

No. Senza token GitHub permette 60 richieste/ora per IP. Il token eleva il limite a 5000/ora e consente l'accesso a repository privati.

= Come funziona la cache? =

I dati di ogni repository vengono memorizzati come WordPress transient. La durata è configurabile (default 6 ore, massimo 168). La pagina admin include un pulsante per svuotare manualmente la cache.

== Screenshots ==

1. Card repository con nome, descrizione, stelle, data aggiornamento e bottoni azione.
2. Pagina impostazioni admin: token API, durata cache, colori.

== Changelog ==

= 1.2.2 =
* Corretto: `$val` nel campo cache ora usa `esc_attr()` (Plugin Check compliance)
* Corretto: aggiunto commento `translators:` alla stringa "Token %s rimosso"
* Corretto: `phpcs:ignore` su query DELETE transient (uso legittimo, non cachabile)
* Corretto: `$style` nella card ora usa `esc_attr()` esplicitamente
* Corretto: `phpcs:ignore` sulle icone SVG hardcoded inline
* Rimosso: `load_plugin_textdomain()` — non necessario da WP 4.6+ se esiste il file `.mo`
* Aggiunto: `languages/eg-ranking-repo-it_IT.mo` compilato da `.po`
* Aggiornato: "Tested up to" a 7.0

= 1.2.1 =
* Corretto: aggiunto `rel="noopener noreferrer"` al link "Documentazione" (target="_blank" senza rel)
* Corretto: tooltip stelle ora usa `_n()` ed è traducibile correttamente

= 1.2.0 =
* Sicurezza: HTTPS forzato per le chiamate API Forgejo/Gitea
* Sicurezza: `flush_all_cache()` usa `$wpdb->prepare()` con `$wpdb->esc_like()`
* Aggiunto: link "Documentazione" nella lista plugin
* Aggiunto: sezione "Rimuovi token API" nella pagina admin
* Aggiunto: `uninstall.php` per rimuovere opzioni e transient alla disinstallazione
* Corretto: errori API messi in cache per 5 minuti
* Corretto: `tabindex="-1"` sul bottone "Nessun sito" disabilitato

= 1.1.0 =
* Aggiunto: header `Forgejo Plugin URI` per auto-update tramite EG Forgejo Updater
* Aggiunto: link "Impostazioni" nella lista plugin
* Aggiunto: opzione colore testo card
* Aggiunto: descrizione del repository nella card
* Corretto: colori testo hardcoded sostituiti con CSS custom properties
* Corretto: campo token non espone più il valore nel DOM HTML
* Corretto: protezione anti-SSRF con `wp_safe_remote_get()`
* Migliorato: `add_shortcode()` spostato sull'hook `init`
* Migliorato: `format_date()` rispetta il timezone di WordPress

= 1.0.0 =
* Prima release
