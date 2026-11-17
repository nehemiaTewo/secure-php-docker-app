# Secure PHP Web App with Docker, 2FA, and CI/CD

A containerized PHP web application with email verification, authenticator-app two-factor authentication, and role-based admin controls. It runs on Docker Compose (Nginx, PHP-FPM, MySQL 8) and is tested automatically with GitHub Actions on every push.

## Features
- Registration with email verification
- TOTP two-factor authentication (Google Authenticator, Authy)
- Password reset
- Role-based access control: admin requests start as "pending" and must be approved by an existing admin; non-admins get a 403 on the admin panel
- Responsive Bootstrap 5 interface
- Database schema built automatically on first start (`db/init.sql`)
- MySQL healthcheck, restart policies, and memory/CPU limits
- CI pipeline: PHP syntax checks, PHPUnit tests, full Docker stack build, and access-control verification

## Tech Stack
PHP 8, Nginx, MySQL 8, Docker Compose, Bootstrap 5, PHPUnit, GitHub Actions

## Run It Locally
    git clone https://github.com/nehemiaTewo/secure-php-docker-app.git
    cd secure-php-docker-app
    cp .env.example .env    # fill in your own values
    docker compose up -d

Then open http://localhost:8080

The first admin account must be promoted directly in MySQL (set `Role` to `admin` in the `users` table). After that, admins approve new admin requests from the admin panel.

## Run the Tests
    composer install
    vendor/bin/phpunit tests

## Security Notes
- Credentials live in `.env`, which is never committed
- User roles can't be set from form input; new admin accounts always start as pending
- Admin pages check the session role on every request

Built for CIS 3100 (Containerization Technologies) at Temple University.