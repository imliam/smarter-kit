# Smarter Kit

<p>
<a href="https://github.com/imliam/smarter-kit/actions"><img src="https://github.com/imliam/smarter-kit/workflows/tests/badge.svg" alt="Build Status"></a>
<a href="https://github.com/imliam/smarter-kit/blob/main/LICENSE.md"><img src="https://img.shields.io/badge/License-MIT-blue.svg" alt="License"></a>
</p>

Smarter Kit is a Laravel starter kit with a handful of pre-configured features and tools to help build applications.

## Installation

As pre-requisites, you are expected to have [Git](https://git-scm.com/), [Composer](https://getcomposer.org/) and [Node.js](https://nodejs.org/) installed.

Run the following commands:

```sh
# Clone the repository and navigate into its directory
git clone https://github.com/imliam/smarter-kit.git
cd smarter-kit

# Install Dependencies
composer install
npm install

# Build assets
npm run build

# Create an environment file and generate an application key
cp .env.example .env
php artisan key:generate

# Run the database migrations
php artisan migrate

# (Optional) Seed the database with sample data
php artisan db:seed

# Create the public storage symlink and ensure it is writable
php artisan storage:link
chmod -R 775 storage
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
