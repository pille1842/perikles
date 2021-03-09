#!/bin/bash

while [[ $# -gt 0 ]]; do
    key="$1"
    case $key in
        -v|--version)
            VERSION="$2"
            shift
            shift
            ;;
        -d|--database)
            DATABASE="$2"
            shift
            shift
            ;;
        -u|--user)
            USER="$2"
            shift
            shift
            ;;
        -p|--password)
            PASSWORD="$2"
            shift
            shift
            ;;
        -h|--help)
            echo "Synopsis: archive.sh --version X.Y.Z --database DATABASE --user USER --password PASSWORD" >&2
            echo "" >&2
            echo "Create archives (.zip, .tar.gz and .tar.xz) of Perikles that can be installed on a server." >&2
            echo "DATABASE, USER and PASSWORD should refer to a valid database connection from which to generate a schema." >&2
            echo "Use --help to show this help and exit." >&2
            exit 0
            ;;
        *)
            echo "Unexpected parameter: $1" >&2
            exit 1
            ;;
    esac
done

TMPDIR=$(mktemp -d)
pushd $TMPDIR >/dev/null 2>&1
git clone https://github.com/pille1842/perikles perikles
pushd perikles >/dev/null 2>&1
sed -i 's/APP_ENV=dev/APP_ENV=prod/' .env
composer install --no-dev --optimize-autoloader
composer require symfony/apache-pack
composer dump-autoload --optimize --no-dev --classmap-authoritative
yarn install --force
yarn run encore production
echo "DATABASE_URL=\"mysql://$USER:$PASSWORD@127.0.0.1:3306/$DATABASE?serverVersion=5.7\"" >.env.local
bin/console d:s:u --force
rm .env.local
mysqldump -u $USER --password="$PASSWORD" $DATABASE >database.sql
zip database.sql.zip database.sql
mkdir ../Perikles_v$VERSION
cp -r assets/ bin/ config/ public/ src/ templates/ translations/ vendor/ DEPLOY.md .env LICENSE README.md composer.json database.sql database.sql.zip ../Perikles_v$VERSION/
popd >/dev/null 2>&1
zip Perikles_v$VERSION.zip Perikles_v$VERSION/
tar czf Perikles_v$VERSION.tar.gz Perikles_v$VERSION/
tar cJf Perikles_v$VERSION.tar.xz Perikles_v$VERSION/
popd >/dev/null 2>&1
mv $TMPDIR/Perikles_v$VERSION.zip $TMPDIR/Perikles_v$VERSION.tar.gz $TMPDIR/Perikles_v$VERSION.tar.xz .
