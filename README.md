# Admin Test API

A Laravel-based API application for analytics and reporting.

## System Requirements

Before proceeding with installation, ensure your system meets these minimum requirements:

- **Disk Space:** At least **15GB** of free space (10GB for database, 5GB for Docker images and application)
- **RAM:** Minimum **4GB** (8GB recommended for optimal performance during data import)
- **CPU:** 2+ cores recommended for reasonable import speeds
- **Internet Connection:** Required for initial Docker image download and package installation

## Prerequisites

Before setting up the application, ensure you have the following installed on your system:

### For Windows

- **Windows 10/11** (64-bit)
- **[Docker Desktop for Windows](https://docs.docker.com/desktop/install/windows-install/)** (version 4.0 or higher)
  - Ensure WSL 2 backend is enabled in Docker Desktop settings
- **[WSL 2](https://docs.microsoft.com/en-us/windows/wsl/install)** with Ubuntu distribution
  - Install via PowerShell (as Administrator): `wsl --install`
  - Default Ubuntu distribution will be installed
- **Git** (for cloning the repository)
  - Install from [git-scm.com](https://git-scm.com/download/win) or use Git Bash

### For macOS

- **macOS 11 (Big Sur)** or higher
- **[Docker Desktop for Mac](https://docs.docker.com/desktop/install/mac-install/)**
  - For Apple Silicon (M1/M2): Use the Apple Silicon version
  - For Intel processors: Use the Intel version
- **Git** (usually pre-installed)
  - Verify with: `git --version`
  - If not installed, it will prompt you to install Xcode Command Line Tools

### For Linux

- **Ubuntu 20.04+, Debian 10+, Fedora 33+, or equivalent**
- **[Docker Engine](https://docs.docker.com/engine/install/)**
  - Follow the official installation guide for your distribution
- **[Docker Compose](https://docs.docker.com/compose/install/)** (version 2.0+)
  - Often included with Docker Desktop on newer distributions
- **Git**
  - Install via: `sudo apt install git` (Ubuntu/Debian) or `sudo dnf install git` (Fedora)

## Installation & Setup

### Step 1: Clone the Repository

**Windows (in WSL2 Ubuntu terminal):**
```bash
cd ~
git clone https://github.com/kofer04/admin-test-api.git
cd admin-test-api
```

**macOS / Linux:**
```bash
cd ~
git clone https://github.com/kofer04/admin-test-api.git
cd admin-test-api
```

> **Note for Windows users:** It's recommended to clone the repository inside your WSL2 home directory (`~`) for better performance with Docker Desktop.

### Step 2: Configure Environment Variables

Copy the example environment file to create your `.env` configuration file:

```bash
cp .env.example .env
```

This file contains all the necessary configuration for the application, including database settings, application settings, and API keys.

> **Important:** Review the `.env` file to ensure database credentials and other settings match your environment. For Laravel Sail, the default settings should work out of the box.

### Step 3: Generate Application Key

Generate a unique application encryption key:

```bash
./vendor/bin/sail artisan key:generate
```

This command will set the `APP_KEY` value in your `.env` file, which is used for encrypting sessions, cookies, and other sensitive data.

> **Note:** If you haven't started Sail yet and get an error, you can run this command after starting Sail in Step 4, or use Docker directly:
```bash
docker run --rm -v $(pwd):/app -w /app laravelsail/php83-composer:latest php artisan key:generate
```

### Step 4: Start Docker Containers

Run the following command to start all Docker containers in detached mode:

```bash
./vendor/bin/sail up -d
```

This will:
- Build and start the application containers (PHP, MySQL, Redis, etc.)
- Take several minutes on the first run as Docker images are downloaded and built
- Run in the background once started

**Optional:** Create an alias for convenience (add to your `~/.bashrc` or `~/.zshrc`):
```bash
alias sail='./vendor/bin/sail'
```

Then you can simply use: `sail up -d`

### Step 5: Prepare Data Files

Before seeding the database, you need to place the required CSV data files in the `database/data/` directory.

**Required files:**
- `event_names.csv`
- `log_events.csv`
- `log_service_titan_jobs.csv`
- `markets.csv`

Place these files in:
```
admin-test-api/database/data/
```

### Step 6: Seed the Database

Run the database migrations and seeders:

```bash
./vendor/bin/sail artisan migrate:fresh --seed
```

⚠️ **Important:** This process will take a considerable amount of time, as it imports millions of rows from the CSV files. Please be patient and do not interrupt the process.

You'll see progress indicators in the terminal as data is imported.

### Step 7: (Optional) Install Node Dependencies and Build Assets

If you plan to modify or use frontend assets, install Node dependencies and build the assets:

```bash
./vendor/bin/sail npm install
./vendor/bin/sail npm run build
```

> **Note:** This step is optional for API-only usage. The frontend assets are minimal and primarily used for development tooling.

### Step 8: Verify Installation

Once the seeding process completes successfully, your application is ready to use!

**Quick verification:**
```bash
# Check if containers are running
./vendor/bin/sail ps

# Check application health
curl http://localhost:80
```

## Default User Credentials

Two users are created during the seeding process:

### Super Admin
- **Email:** `admin@example.com`
- **Password:** `password`
- **Role:** Administrator with full access

### Market User (Regular User)
- **Email:** `market@example.com`
- **Password:** `password`
- **Role:** Standard user with limited access

## Accessing the Application

- **API Base URL:** `http://localhost:80`
- **Database:** Accessible via port `3306` (MySQL)
- **Redis:** Accessible via port `6379`

## Useful Commands

### Laravel Sail Commands

```bash
# Start containers
./vendor/bin/sail up -d

# Stop containers
./vendor/bin/sail down

# View logs
./vendor/bin/sail logs

# Access application container shell
./vendor/bin/sail shell

# Run artisan commands
./vendor/bin/sail artisan [command]

# Run composer commands
./vendor/bin/sail composer [command]

# Run tests
./vendor/bin/sail test
```

## Troubleshooting

### Vendor Directory Not Found
If you get an error about `./vendor/bin/sail` not existing, the Composer dependencies need to be installed first:

**Option 1 - Using Docker (recommended):**
```bash
docker run --rm \
    -u "$(id -u):$(id -g)" \
    -v $(pwd):/var/www/html \
    -w /var/www/html \
    laravelsail/php83-composer:latest \
    composer install --ignore-platform-reqs
```

**Option 2 - Using local Composer (if installed):**
```bash
composer install
```

### Port Conflicts
If you encounter port conflicts (80, 3306, 6379 already in use):
1. Stop any services using those ports:
   - **Windows:** Check Task Manager for services using these ports
   - **macOS/Linux:** Use `sudo lsof -i :80` to find the process, then stop it
2. Or modify the ports in your `compose.yaml` file

### Docker Desktop Not Starting (Windows)
1. Ensure Virtualization is enabled in BIOS
2. Ensure WSL 2 is properly installed: `wsl --status`
3. Update WSL to the latest version: `wsl --update`
4. Restart Docker Desktop
5. Check Docker Desktop settings: Ensure "Use WSL 2 based engine" is enabled

### Permission Issues (Linux)
If you encounter permission issues with Docker:
```bash
sudo usermod -aG docker $USER
newgrp docker
```

Then log out and log back in for the changes to take effect.

### Slow Performance (Windows)
Ensure your project is inside the WSL2 filesystem (not in `/mnt/c/`):
```bash
# Good: /home/username/admin-test-api
# Avoid: /mnt/c/Users/username/admin-test-api
```

Performance is significantly better when working within the WSL2 filesystem.

### Database Connection Errors
If you encounter database connection errors:
1. Ensure Docker containers are running: `./vendor/bin/sail ps`
2. Check database logs: `./vendor/bin/sail logs mysql`
3. Verify `.env` database settings match the `compose.yaml` configuration
4. Restart containers: `./vendor/bin/sail restart`

### CSV Import Taking Too Long
The database seeding process with millions of rows can take 30+ minutes depending on your system:
- **SSD:** 15-25 minutes
- **HDD:** 30-60+ minutes
- Ensure you have sufficient disk space (at least 10GB free)
- Don't interrupt the process; let it complete

## License

This application is proprietary software developed for evaluation purposes.
