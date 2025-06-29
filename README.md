# Headless CMS - Palm Code Tech Test

This is a Laravel-based Headless CMS implementation as part of the technical assessment
for [Palm Code](https://palm-co.de). The application is containerized using Docker with a complete development
environment.

## System Architecture

The application consists of the following services:

- **Laravel Application** (PHP-FPM 8.3)
- **Nginx Web Server**
- **MariaDB Database**
- **Redis** for caching
- **MailHog** for email testing

## Prerequisites

- Docker Engine
- Git

## Quick Start

1. **Clone the repository**
   ```bash
   git clone https://github.com/aldesrahim/test-headless-cms.git
   cd test-headless-cms
   ```

2. **Setup environment variables**
   ```bash
   cp .env.example .env
   ```
   Edit the `.env` file with your preferred credentials (particularly database).

3. **Build and start containers**
   ```bash
   docker compose build
   docker compose up -d
   ```

4. **Attach to app container**
    ```bash
    docker compose exec --user=headless-cms -it app bash
    ```

5. **Install dependencies**
   ```bash
   composer install
   ```

6. **Install Node.js dependencies**
   ```bash
   npm i && npm run build
   ```

7. **Generate application key**
   ```bash
   php artisan key:generate
   ```

8. **Run database migrations and seeders**
   ```bash
   php artisan migrate --seed
   ```

9. **Run public storage link**
   ```bash
   php artisan storage:link
   ```

## Access Services

| Service     | URL                   | Credentials                  |
|-------------|-----------------------|------------------------------|
| Application | http://localhost      | N/A                          |
| MailHog UI  | http://localhost:8025 | N/A                          |
| MariaDB     | localhost:3306        | laravel / secret (from .env) |
| Redis       | localhost:6379        | N/A                          |

## Development Workflow

**Viewing Logs:**

```bash
docker compose logs [service_name]  # e.g., app, webserver, mariadb
```

## Configuration Files

Key configuration files:

- `docker compose.yml` - Main service definitions
- `docker/nginx/default.conf` - Nginx server configuration
- `docker/php/Dockerfile` - PHP-FPM image configuration
- `docker/mysql/my.cnf` - MySQL configuration

## Stopping the Environment

To stop all services:

```bash
docker compose down
```

To stop and remove all data volumes (warning: this will delete your database):

```bash
docker compose down -v
```

## Implementation Notes

1. The application follows standard Laravel conventions
2. All services are connected through a dedicated Docker network
3. Persistent storage is configured for:
    - Database data
    - Redis cache

## Troubleshooting

**Port conflicts:** Ensure ports 80, 3306, 6379, 9000-9001, and 8025 are available.

**Permission issues:** If you encounter file permission problems:

```bash
docker compose exec app chown -R www-data:www-data /var/www/storage
```

**If permission issues persist:** This is often caused by user ID mismatches between host and container.

1. Check your current user's ID:
    ```bash
    id -u
    ```

2. Note the numeric user ID displayed (e.g., `1000`)

3. Update the `uid` argument in your `docker compose.yml` (in the `app` service build section):
    ```yaml
    build:
      context: .
      dockerfile: docker/php/Dockerfile
      args:
        user: headless-cms
        uid: 1000  # Update this value with your host user ID
    ```

4. Rebuild and restart the containers:
    ```bash
    # Stop existing containers
    docker compose down
    
    # Rebuild with updated UID
    docker compose build --no-cache app
    
    # Start fresh containers
    docker compose up -d
    ```

5. Verify file permissions:
    ```bash
    docker compose exec app ls -la /var/www/storage
    ```

**Service not responding:** Check container status:

```bash
docker compose ps
```

**Node.js/npm issues:** If frontend dependencies fail:

```bash
# Attach to app container
docker compose exec --user=headless-cms -it app bash

# Clean node_modules and reinstall
rm -rf node_modules
npm i && npm run build
```

## Additional Considerations

1. For production deployment, you would want to:
    - Configure proper SSL certificates
    - Set up database backups
    - Use a more secure Redis configuration

2. The current setup uses development-oriented configurations for easier testing.
