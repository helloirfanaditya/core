# Laravel REST API

This is a Laravel project configured specifically for use as a REST API. It does not use Blade or other server-side rendering features, but focuses on providing API endpoints for frontend clients or mobile applications.

## Features

- **Parse all return to json**: Uses JSON Web Tokens for authentication.

## Prerequisites

Make sure you have the following software installed on your system:

- PHP >= 8.0
- Composer

## Installation

1. Clone this repository

2. Install dependencies using Composer:

    ```bash
    composer install
    ```

3. Copy the `.env.example` file to `.env` and configure it as needed:

    ```bash
    cp .env.example .env
    ```

4. Generate the application key:

    ```bash
    php artisan key:generate
    ```

5. Start the development server:

    ```bash
    php artisan serve
    ```

## Project Structure

- **Routes**: All API routes are defined in `routes/api.php`.
- **Middleware**: Middleware for authentication and authorization is in `app/Http/Middleware`.

## Testing

You can run unit and feature tests using the following command:

```bash
php artisan test
