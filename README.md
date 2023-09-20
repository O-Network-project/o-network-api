# O'Network (API)

## Installation

### Dependencies

To run this project on your machine, you will need:

- Git
- PHP >= 7.0
- Composer >= 2
- MySql or MariaDB
- the SQLite driver enabled in your php.ini (for tests only)

### Procedure

Clone this repo first:

```bash
git clone git@github.com:O-clock-Tesla/projet-02-O-Network-back.git
```

And in the newly created folder, install the Composer dependencies:

```bash
composer install
```

Now create a new database (`o_network` for example) with the following command (don't forget to set the desired user instead of `root` if needed):

```bash
mysql -u root -p -e 'CREATE DATABASE o_network'
```

In the project folder root, make a copy of the `.env.example` file and rename it `.env`:

```bash
cp .env.example .env
```

Create also the app key with the following command:

```bash
php artisan key:generate
```

Open the `.env` one, and set all the values prefixed with `DB_` to match your own database configuration. For example, with a database named `o_network`:

```dotenv
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=o_network
DB_USERNAME=root
DB_PASSWORD=root
```

Finally, populate the database with the migrations and seed data:

```bash
php artisan migrate --seed
```

Now and every time you need to run the project, launch the following command to start the server:

```bash
php artisan serve
```
