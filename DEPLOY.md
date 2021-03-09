# Ein Perikles-Installationsarchiv erstellen
Diese Datei beschreibt, wie ein Archiv mit allen Dateien hergestellt werden kann, die zur Installation von Perikles notwendig sind.

## Schritt 1: Repository klonen
Erstelle eine frische, lokale Kopie des Git-Repositories:

```
$ git clone https://github.com/pille1842/perikles
$ cd perikles/
```

## Schritt 2: App-Environment auf "Production" setzen
Bearbeite die Datei `.env` und ersetze `APP_ENV=dev` durch `APP_ENV=prod`:

```
$ sed -i 's/APP_ENV=dev/APP_ENV=prod/' .env
```

## Schritt 3: Composer-Pakete installieren
Installiere alle Abhängigkeiten, die für den Produktionsbetrieb notwendig sind:

```
$ composer install --no-dev --optimize-autoloader
```

## (optional) Für Betrieb mit Apache-Webserver vorbereiten
Installiere das Paket `symfony/apache-pack`, wenn das Installationsarchiv für den Betrieb auf einem Apache-Webserver ausgelegt ist. Dieses Paket erzeugt automatisch eine `.htaccess`-Datei im Verzeichnis `public/`.

```
$ composer require symfony/apache-pack
```

## Schritt 4: Autoloader erneut erzeugen
Bereite den Composer-Autoloader auf den Produktionsbetrieb vor:

```
$ composer dump-autoload --optimize --no-dev --classmap-authoritative
```

## Schritt 5: Installiere Javascript-Abhängigkeiten
Im nächsten Schritt werden alle Javascript-Abhängigkeiten mit `yarn` installiert und die Builds im Verzeichnis `public/` erzeugt:

```
$ yarn install --force
$ yarn run encore production
```

## Schritt 6: Erzeuge Datenbankmuster
Für eine einfache Installation auf jedem Server muss nun eine SQL-Datei mit dem Datenbankschema erzeugt werden. Dazu muss in der Datei .env temporär eine gültige Datenbankverbindung eingestellt werden, die anschließend wieder gelöscht werden sollte.

Nach diesem Schritt sollten im Projektverzeichnis zwei neue Dateien `database.sql` und eine gezippte Version `database.sql.zip` liegen.

```
$ bin/console d:s:u --force
$ mysqldump -u root -p DATABASENAME >database.sql
$ zip database.sql.zip database.sql
```

## Schritt 7: Benötigte Verzeichnisse und Dateien kopieren
Erzeuge ein neues Verzeichnis `Perikles_vX.Y.Z/` und kopiere alle benötigten Dateien dorthin:

```
$ mkdir ../Perikles_vX.Y.Z
$ cp -r assets/ bin/ config/ public/ src/ templates/ translations/ vendor/ DEPLOY.md .env LICENSE README.md composer.json database.sql database.sql.zip ../Perikles_vX.Y.Z/
```

## Schritt 8: Archive erstellen
Aus dem Verzeichnis `Perikles_vX.Y.Z/` können nun gewünschte Archive hergestellt werden, z.B.:

```
$ cd ../
$ zip Perikles_vX.Y.Z.zip Perikles_vX.Y.Z/
$ tar czf Perikles_vX.Y.Z.tar.gz Perikles_vX.Y.Z/
$ tar cJf Perikles_vX.Y.Z.tar.xz Perikles_vX.Y.Z/
```
