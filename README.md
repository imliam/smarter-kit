# Smarter Kit

<p>
<a href="https://github.com/imliam/smarter-kit/actions"><img src="https://github.com/imliam/smarter-kit/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://github.com/imliam/smarter-kit/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License"></a>
</p>

Smarter Kit is a Laravel starter kit with a handful of pre-configured features and tools to help build applications.

## Installation

As pre-requisites, you are expected to have [Git](https://git-scm.com/), [Composer](https://getcomposer.org/), [Node.js](https://nodejs.org/) and [pnpm](https://pnpm.io/) installed.

Run the following commands:

```sh
# Clone the repository and navigate into its directory
git clone https://github.com/imliam/smarter-kit.git
cd smarter-kit

# Install Dependencies
composer install
pnpm install

# Build assets
pnpm run build

# Create an environment file and generate an application key
cp .env.example .env
php artisan key:generate

# Run the database migrations
php artisan migrate

# Create the public storage symlink and ensure it is writable
php artisan storage:link
chmod -R 775 storage

# Ensure the bootstrap cache is writeable
chmod -R 775 bootstrap/cache

# (Optional) Seed the database with sample data
php artisan db:seed

# (Optional) Install Laravel Octane for improved performance, select FrankenPHP as the server:
php artisan octane:install

# (Optional) Run Boost to generate AI guidelines for your preferred tooling:
php artisan boost:install
```

## Running the Application

To start the application locally, run:

```sh
php artisan solo
```

Then, open your browser and navigate to `http://localhost:8000`

## Testing

Run the application's PHP test suite (testing, static analysis, etc.) with:

```sh
composer test
```

Run the application's JS test suite with:

```sh
pnpm test
```

Run the GitHub Action workflows locally with the [Act](https://github.com/nektos/act) CLI (install separately):

```sh
act
```

## Code Style

Automatically fix style issues with:

```sh
composer fix
pnpm format
```

## Deployments

### First-Time Setup

When deploying the application to a new server for the first time, follow the installation instructions above. You can also refer to the [Laravel Deployment Documentation](https://laravel.com/docs/deployment) for additional guidance.

You should ensure your environment variables are configured appropriately for your environment. The default `.env.example` provided is for development purposes, so in production you may want to set the following:

```sh
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-production-url.com
```

Add a cron job to run the Laravel scheduler every minute. You can do this by adding the following line to your server's crontab (edit it by running `crontab -e`):

```
* * * * * cd /path-to-your-project && php artisan schedule:run >> /dev/null 2>&1
```

### Updating an Existing Deployment

When updating an existing deployment, you can follow these steps:

```sh
# Put the application into maintenance mode
php artisan down --refresh=30 || true

# Pull the latest changes from your version control system
git pull origin main

# Install PHP dependencies
composer install --no-dev --optimize-autoloader --no-interaction --no-scripts --prefer-dist

# Install Node.js dependencies and build assets
pnpm install --prod
pnpm run build

# Run database migrations
php artisan migrate --force

# Clear any cached configuration, cache entries, classes, events, routes, and views
php artisan optimize:clear

# Cache configuration, events, routes, and views
php artisan optimize

# Restart the queue workers (if applicable)
php artisan queue:restart

# Bring the application out of maintenance mode
php artisan up
```
