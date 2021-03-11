# Installationsanleitung
Dieses Dokument beschreibt, wie Perikles mithilfe eines Installationsarchivs auf einem Webserver installiert werden kann.

## Voraussetzungen
Perikles benötigt wenigstens:

- PHP >= 7.2.5
- PHP-Extensions: Ctype, iconv, JSON, PCRE, Session, SimpleXML, Tokenizer
- Datenbankserver: MySQL, MariaDB, PostgreSQL

Installationsarchive werden derzeit auf einem Linux-Server mit PHP 7.4.3 erzeugt und wurden außerdem mit PHP 7.2.30 getestet.

## Archiv entpacken
Entpacke das Installationsarchiv auf dem Webserver **außerhalb der Document Root**. Alle Dateien, die im Verzeichnis `public/` liegen, müssen anschließend in dein Document-Root-Verzeichnis kopiert werden. Wenn die Site im Verzeichnis `/home/site/` liegt und die Document Root darin `/home/site/public_html/` ist, sollte sich folgender Verzeichnisbaum ergeben:

```
/home/site
├── .env
├── assets
├── bin
├── composer.json
├── config
├── database.sql
├── database.sql.zip
├── LICENSE
├── public_html
│   ├── .htaccess
│   ├── build
│   └── index.php
├── README.md
├── src
├── templates
├── translations
└── vendor
```

## Datenbank einrichten
Perikles ist darauf ausgelegt, eine eigene Datenbank zur Verfügung zu haben. Wenn du diese angelegt hast, führe darin die SQL-Statements in `database.sql` aus. (Für den Upload in phpMyAdmin steht auch eine komprimierte Version in `database.sql.zip` zur Verfügung.) Diese Datei legt die erforderlichen Tabellen in der Datenbank an.

Trage anschließend die Verbindungsinformationen zur Datenbank in der Datei `.env` ein. Du findest dort mehrere Zeilen, die mit `DATABASE_URL=` beginnen.

## Mailserver einrichten
Perikles kann Mails über verschiedene Transportwege versenden. Getestet wurde der Versand per SMTP-Server. Trage die Zugangsdaten zu einem SMTP-Server folgendermaßen in `.env` ein (und ersetze die Zeile, die mit `MAILER_DSN=` beginnt):

```
MAILER_DSN=smtp://SMTPUSER:SMTPPASSWORD@SMTPSERVER:25
```

(Die Portnummer 25 muss hier natürlich gegebenenfalls angepasst werden.)
