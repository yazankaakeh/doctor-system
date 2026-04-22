# Doctor System

A comprehensive, multi-tenant medical practice management platform built with **Laravel 12** and **PHP 8.4**. Doctors run their clinics, patients book and attend appointments (in-person or via self-hosted Jitsi video consultation), and admins oversee the whole operation from a single dashboard.

The platform covers the full patient journey — online booking, medical examination with vital signs, prescription generation (PDF via Browsershot), test results, follow-up messaging, and billing through Turkish bank gateways (Ziraat, Vakıf, Garanti, Yapı Kredi).

**Repository:** <https://github.com/yazankaakeh/doctor-system.git>

---

## Table of Contents

- [Features](#features)
- [System Requirements](#system-requirements)
- [Installation Guide](#installation-guide)
  - [Step 1: Install Laragon](#step-1-install-laragon)
  - [Step 2: Configure PHP](#step-2-configure-php)
  - [Step 3: Install Node.js](#step-3-install-nodejs)
  - [Step 4: Install Yarn](#step-4-install-yarn)
  - [Step 5: Install Composer](#step-5-install-composer)
  - [Step 6: Clone the Project](#step-6-clone-the-project)
  - [Step 7: Install Dependencies](#step-7-install-dependencies)
  - [Step 8: Environment Configuration](#step-8-environment-configuration)
  - [Step 9: Database Setup](#step-9-database-setup)
  - [Step 10: Run the Application](#step-10-run-the-application)
- [Project Structure](#project-structure)
- [Available Commands](#available-commands)
- [Password Policy & Default Credentials](#password-policy--default-credentials)
- [Real-Time / WebSockets (Laravel Reverb)](#real-time--websockets-laravel-reverb)
- [Troubleshooting](#troubleshooting)
- [Contributing](#contributing)
- [License](#license)

---

## Features

### Medical Workflow

- **Multi-guard Authentication** for three distinct roles — Doctor, Patient, Admin — each with its own login, registration, dashboard, and permission set
- **Doctor Profiles** with medical specialties, bio, clinic assignments, and availability
- **Patient Records** with demographics, blood type, allergies, medical/surgical/accident history, disabilities, nationality, and secure attachment storage
- **Online Booking** for appointments — patients pick specialty, doctor, clinic, and time slot
- **Medical Examinations** — vital signs (with min/max thresholds), medical tests, prescribed medicines with dosage forms, final diagnoses, and custom doctor notes
- **Prescription PDFs** generated on demand via Spatie Browsershot (headless Chrome)
- **Video Consultations** powered by self-hosted Jitsi (`meet.yupcrm.com`) with optional lobby, password protection, and recording
- **Test Result Upload** — patients can upload imaging/lab reports to their secure file store
- **Real-time Messaging** between patient and doctor over Laravel Reverb WebSockets, with typing indicators, read receipts, and an agent inbox for support staff

### Platform Features

- **Role-Based Access Control** via Spatie Permission (admin, messaging_agent, support, and custom roles)
- **Multi-language Support** — English, Arabic (RTL), Turkish — with Spatie Translatable
- **Notifications** — email (SMTP), SMS (1telekom.com.tr), push, and in-app
- **Turkish Payment Gateways** — Ziraat, Vakıf, Garanti, Yapı Kredi provision + 3DS init
- **Social Login** — Google, Facebook, X (Twitter) via Laravel Socialite
- **Blog / CMS / SEO** modules for the public marketing site
- **Modular Architecture** (nwidart/laravel-modules) — each feature is a self-contained module under `Modules/`
- **AI Chatbot** — MCP integration for intelligent patient triage and FAQ answers
- **Secure Media Library** — patient uploads land on a private `secure` disk, served through authenticated download routes
- **Password Policy** enforced in every write path (registration, admin forms, reset) with breach-corpus check in production
- **Login Rate Limiting** — 5 failed attempts per email + IP + guard, then 60-second lockout
- **KVKK Consent Tracking** (Turkish data-protection compliance)

---

## System Requirements

Before starting, make sure your computer meets these requirements:

| Requirement     | Minimum Version | Recommended      |
|-----------------|-----------------|------------------|
| Operating System| Windows 10/11   | Windows 11       |
| PHP             | 8.4             | 8.4+             |
| MySQL           | 8.0             | 8.0+             |
| Node.js         | 18.x            | 20.x LTS         |
| NPM             | 9.x             | 10.x             |
| Yarn            | 4.x             | 4.x (Berry)      |
| Composer        | 2.x             | 2.7+             |
| RAM             | 4 GB            | 8 GB+            |
| Disk Space      | 2 GB            | 5 GB+            |

> **Important**: This project uses **two package managers**:
> - **NPM** for the root Laravel project
> - **Yarn** for the Theme module (Vuexy admin template)

---

## Installation Guide

Follow these steps carefully. If you are new to web development, don't worry - we'll guide you through everything.

### Step 1: Install Laragon

Laragon is a portable, isolated, fast & powerful universal development environment for PHP, Node.js, Python, Java, Go, Ruby. It is very easy to use and includes everything you need.

#### Download Laragon

1. Go to the official Laragon website: **https://laragon.org/download/**
2. Click on **"Download Laragon - Full"** (approximately 180MB)
3. Run the downloaded installer (`laragon-wamp.exe`)

#### Install Laragon

1. **Welcome Screen**: Click **Next**
2. **Choose Install Location**:
   - Default is `C:\laragon` (recommended)
   - Click **Next**
3. **Select Components**: Make sure these are checked:
   - [x] Create Laragon auto virtual host
   - [x] Add Notepad++ & Terminal
   - Click **Next**
4. **Ready to Install**: Click **Install**
5. Wait for the installation to complete
6. Click **Finish**

#### Start Laragon

1. Open Laragon from your desktop or Start menu
2. Click the big **"Start All"** button
3. You should see Apache and MySQL turn green (running)

> **Note**: Laragon comes with PHP 8.1 by default. We need PHP 8.4, which we'll configure in the next step.

### Step 2: Configure PHP

The project requires **PHP 8.4**. Laragon makes it easy to add new PHP versions.

#### Download PHP 8.4

1. Go to: **https://windows.php.net/download/**
2. Find **PHP 8.4** section
3. Download **VS16 x64 Thread Safe** (the ZIP file)
4. Extract the ZIP file

#### Add PHP 8.4 to Laragon

1. Open the extracted PHP folder
2. Copy the entire folder (e.g., `php-8.4.x-Win32-vs16-x64`)
3. Go to `C:\laragon\bin\php\`
4. Paste the PHP 8.4 folder here
5. Rename it to `php-8.4.x` (keep it simple)

#### Switch to PHP 8.4

1. Open Laragon
2. Right-click anywhere in the Laragon window
3. Go to **PHP** > select **php-8.4.x**
4. Laragon will restart Apache automatically

#### Enable Required PHP Extensions

1. Right-click in Laragon > **PHP** > **php.ini**
2. Find and uncomment (remove the `;` at the beginning) these lines:
   ```ini
   extension=curl
   extension=fileinfo
   extension=gd
   extension=intl
   extension=mbstring
   extension=exif
   extension=openssl
   extension=pdo_mysql
   extension=sodium
   extension=zip
   ```
3. Save the file
4. Restart Laragon (click **Stop All**, then **Start All**)

#### Verify PHP Installation

1. Open Laragon Terminal (right-click > Terminal)
2. Type:
   ```bash
   php -v
   ```
3. You should see something like:
   ```
   PHP 8.4.x (cli) (built: ...)
   ```

### Step 3: Install Node.js

Node.js is required to compile frontend assets (CSS, JavaScript).

#### Download Node.js

1. Go to: **https://nodejs.org/**
2. Download the **LTS version** (e.g., 20.x LTS)
3. Run the installer

#### Install Node.js

1. **Welcome**: Click **Next**
2. **License Agreement**: Accept and click **Next**
3. **Destination Folder**: Keep default, click **Next**
4. **Custom Setup**: Keep defaults, click **Next**
5. **Tools for Native Modules**: You can skip this, click **Next**
6. **Ready to Install**: Click **Install**
7. Wait for installation to complete
8. Click **Finish**

#### Verify Node.js Installation

1. Open a new Command Prompt or Laragon Terminal
2. Type:
   ```bash
   node -v
   ```
   Should show: `v20.x.x` (or your version)

3. Type:
   ```bash
   npm -v
   ```
   Should show: `10.x.x` (or your version)

### Step 4: Install Yarn

Yarn is a fast, reliable package manager. The **Theme module** uses Yarn to manage its dependencies (Bootstrap 5, DataTables, Charts, etc.).

#### Install Yarn Globally

1. Open Command Prompt or Laragon Terminal
2. Run:
   ```bash
   npm install -g yarn
   ```
3. Wait for the installation to complete

#### Enable Corepack (Recommended)

Corepack is a tool that manages package manager versions. It comes with Node.js:

1. Run:
   ```bash
   corepack enable
   ```
2. This ensures you're using the correct Yarn version

#### Verify Yarn Installation

1. Open a new terminal
2. Type:
   ```bash
   yarn -v
   ```
   Should show: `4.x.x` or `1.x.x` (both work)

> **Note**: The Theme module uses Yarn Berry (v4). When you run `yarn install` inside the Theme module, it will automatically use the correct version defined in `.yarnrc.yml`.

### Step 5: Install Composer

Composer is a dependency manager for PHP. It's used to install PHP packages.

#### Download Composer

1. Go to: **https://getcomposer.org/download/**
2. Click **"Composer-Setup.exe"** under Windows Installer
3. Run the downloaded installer

#### Install Composer

1. **Installation Options**:
   - Select **"Install for all users"**
   - Click **Next**
2. **Settings Check**:
   - The installer should auto-detect PHP from Laragon
   - If not, browse to: `C:\laragon\bin\php\php-8.4.x\php.exe`
   - Click **Next**
3. **Proxy Settings**:
   - Usually leave blank
   - Click **Next**
4. **Ready to Install**: Click **Install**
5. Click **Finish**

#### Verify Composer Installation

1. Open a new Command Prompt or Laragon Terminal
2. Type:
   ```bash
   composer -V
   ```
   Should show: `Composer version 2.x.x ...`

### Step 6: Clone the Project

Now let's get the project code onto your computer.

#### Using Git (Recommended)

If you don't have Git installed:
1. Download from: **https://git-scm.com/download/win**
2. Install with default options

Clone the repository:

1. Open Laragon Terminal
2. Navigate to Laragon's www folder:
   ```bash
   cd C:\laragon\www
   ```
3. Clone the project:
   ```bash
   git clone https://github.com/yazankaakeh/doctor-system.git
   ```

4. Navigate into the project:
   ```bash
   cd doctor-system
   ```

#### Without Git (Manual Download)

1. Download the project ZIP from <https://github.com/yazankaakeh/doctor-system>
2. Extract it to `C:\laragon\www\doctor-system`

### Step 7: Install Dependencies

Now we need to install all the PHP and JavaScript packages the project needs.

#### Install PHP Dependencies

1. Open Laragon Terminal
2. Navigate to the project folder:
   ```bash
   cd C:\laragon\www\doctor-system
   ```
3. Install Composer dependencies:
   ```bash
   composer install
   ```
4. Wait for the installation to complete (this may take a few minutes)

> **Note**: If you see any errors about missing extensions, go back to Step 2 and make sure all required PHP extensions are enabled.

#### Install Root Project JavaScript Dependencies

1. Still in the project root folder, run:
   ```bash
   npm install
   ```
2. Wait for the installation to complete

#### Install Theme Module Dependencies (IMPORTANT)

The Theme module has its own `package.json` with many UI libraries (Bootstrap 5, DataTables, Charts, etc.). You **must** install these separately using Yarn.

1. Navigate to the Theme module:
   ```bash
   cd Modules/Theme
   ```
2. Install dependencies with Yarn:
   ```bash
   yarn install
   ```
3. Wait for the installation to complete (this may take several minutes due to many packages)
4. Return to the project root:
   ```bash
   cd ../..
   ```

> **What does the Theme module include?**
> - Bootstrap 5.3 (CSS framework)
> - DataTables (Advanced tables)
> - FullCalendar (Calendar component)
> - ApexCharts & Chart.js (Charts)
> - Select2, Flatpickr (Form components)
> - SweetAlert2, Notyf (Notifications)
> - And 50+ more UI libraries

### Step 8: Environment Configuration

The `.env` file contains all the configuration for your application (database, mail, etc.).

#### Create the .env File

1. In the project folder, copy the example environment file:
   ```bash
   copy .env.example .env
   ```
   Or on Git Bash/WSL:
   ```bash
   cp .env.example .env
   ```

#### Generate Application Key

1. Run:
   ```bash
   php artisan key:generate
   ```
2. This generates a unique encryption key for your application

#### Configure the .env File

1. Open `.env` file in a text editor (Notepad++, VS Code, etc.)
2. Update these important settings:

```env
# Application Settings
APP_NAME="Doctor System"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://doctor-systems.dev

# Database Settings (Laragon uses MySQL with root user and no password by default)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=doctor-system
DB_USERNAME=root
DB_PASSWORD=

# Locale Settings
APP_LOCALE=en
APP_FALLBACK_LOCALE=en
```

3. Save the file

#### Create Storage Link

Create a symbolic link from `public/storage` to `storage/app/public`:

```bash
php artisan storage:link
```

This allows uploaded files to be accessible from the web.

### Step 9: Database Setup

#### Create the Database

**Option A: Using Laragon (Easiest)**

1. Open Laragon
2. Right-click > **MySQL** > **Create database**
3. Enter: `doctor-system`
4. Click **OK**

**Option B: Using HeidiSQL (Comes with Laragon)**

1. Open Laragon
2. Right-click > **MySQL** > **HeidiSQL**
3. Click **Open** (no password needed)
4. Right-click on the left panel > **Create new** > **Database**
5. Name it: `doctor-system`
6. Click **OK**

**Option C: Using Command Line**

1. Open Laragon Terminal
2. Connect to MySQL:
   ```bash
   mysql -u root
   ```
3. Create the database:
   ```sql
   CREATE DATABASE doctor-system CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
   ```
4. Exit:
   ```sql
   exit;
   ```

#### Run Migrations

Migrations create all the necessary database tables.

1. In Laragon Terminal, navigate to the project:
   ```bash
   cd C:\laragon\www\doctor-system
   ```
2. Run migrations:
   ```bash
   php artisan migrate
   ```
3. Type `yes` if prompted to confirm

#### Seed the Database (Optional)

Seeders add sample data to your database for testing.

```bash
php artisan db:seed
```

Or run everything at once (fresh database with seeders):
```bash
php artisan migrate:fresh --seed
```

> **Warning**: `migrate:fresh` will delete all existing data!

### Step 10: Run the Application

You have two options to run the application.

#### Option A: Using Composer Dev Script (Recommended for Backend)

This starts the main Laravel services (server, queue, logs, and root Vite):

```bash
composer dev
```

This will:
- Start the Laravel development server at `http://127.0.0.1:8000`
- Start the queue worker for background jobs
- Start the log viewer (Laravel Pail)
- Start Vite for root frontend assets

**IMPORTANT**: You also need to run the Theme module's Vite in a separate terminal.

#### Running Theme Module Assets

Open a **new terminal window** and run:

```bash
cd Modules/Theme
yarn dev
```

This compiles the Theme module assets (Bootstrap, DataTables, Charts, etc.).

#### Option B: Manual Startup (Run Each Service Separately)

Open **multiple** terminal windows:

**Terminal 1 - Laravel Server:**
```bash
php artisan serve
```

**Terminal 2 - Queue Worker:**
```bash
php artisan queue:listen --tries=1
```

**Terminal 3 - Root Vite (Frontend Assets):**
```bash
npm run dev
```

**Terminal 4 - Theme Module Vite:**
```bash
cd Modules/Theme && yarn dev
```

#### Summary: What You Need Running

For full functionality, you need these services running:

| Terminal | Command | Purpose |
|----------|---------|---------|
| 1 | `php artisan serve` | Laravel web server |
| 2 | `php artisan queue:listen` | Background jobs |
| 3 | `npm run dev` | Root Vite (Tailwind, etc.) |
| 4 | `cd Modules/Theme && yarn dev` | Theme assets (Bootstrap, etc.) |

Or use `composer dev` in Terminal 1 and Theme Vite in Terminal 2.

#### Access the Application

1. Open your web browser
2. Go to: **http://127.0.0.1:8000** or **http://localhost:8000**

If using Laragon's Pretty URLs:
- Go to: **http://doctor-systems.dev**

> **Note**: To enable Pretty URLs in Laragon, right-click > **Preferences** > check **"Enable pretty URLs"**, then restart Laragon.

---

## Project Structure

The application uses a modular architecture with the following structure:

```
doctor-system/
|-- app/                    # Core Laravel application code
|-- bootstrap/              # Framework bootstrap files
|-- config/                 # Configuration files
|-- database/               # Migrations, seeders, factories
|-- lang/                   # Language files (en, ar, tr)
|-- Modules/                # Application modules
|   |-- AdminManagement/    # Admin users, roles & permissions
|   |-- Auth/               # Multi-guard auth (Doctor/Patient/Admin)
|   |-- Blog/               # Blog posts, categories, tags
|   |-- Booking/            # Appointment booking system
|   |-- CMS/                # Content management / page builder
|   |-- Core/               # Shared components, enums, helpers
|   |-- Doctor/             # Doctors, patients, clinics, medical exams, prescriptions
|   |-- MCP/                # AI chatbot for triage / FAQ
|   |-- Messaging/          # Real-time chat (doctor <-> patient, agent inbox)
|   |-- Notification/       # Email, SMS, push notifications
|   |-- Patient/            # Patient dashboard, profile, medical history
|   |-- Payment/            # Payment processing
|   |-- Seo/                # SEO management
|   |-- Theme/              # UI themes (Vuexy Bootstrap 5 Admin)
|   +-- Website/            # Public website
|-- public/                 # Publicly accessible files
|-- resources/              # Views, CSS, JS
|-- routes/                 # Application routes
|-- storage/                # File storage, logs, cache
|-- tests/                  # Automated tests
+-- vendor/                 # Composer dependencies
```

---

## Available Commands

### Development

```bash
# Start main Laravel services (recommended)
composer dev

# Start individual services
php artisan serve              # Web server
php artisan queue:listen       # Queue worker
npm run dev                    # Root Vite dev server
```

### Theme Module Commands

The Theme module has its own set of commands. Run these from `Modules/Theme/`:

```bash
cd Modules/Theme

yarn install                   # Install Theme dependencies
yarn dev                       # Start Theme Vite dev server
yarn build                     # Build Theme assets for production
```

Or from the project root:

```bash
# Install Theme dependencies
cd Modules/Theme && yarn install && cd ../..

# Run Theme Vite (keep this running during development)
cd Modules/Theme && yarn dev
```

### Database

```bash
php artisan migrate                    # Run migrations
php artisan migrate:fresh --seed       # Fresh database with seeders
php artisan migrate:rollback           # Rollback last migration
php artisan db:seed                    # Run seeders
```

### Testing

```bash
composer test                          # Run all tests
php artisan test                       # Alternative
php artisan test --filter=TestName     # Run specific test
composer test:unit                     # Unit tests only
composer test:feature                  # Feature tests only
```

### Code Quality

```bash
composer lint                          # Fix code style with Pint
composer lint:check                    # Check code style
composer analyse                       # Static analysis with PHPStan
```

### Module Management

```bash
php artisan module:list                # List all modules
php artisan module:enable ModuleName   # Enable a module
php artisan module:disable ModuleName  # Disable a module
```

### Cache & Optimization

```bash
php artisan cache:clear                # Clear cache
php artisan config:clear               # Clear config cache
php artisan view:clear                 # Clear view cache
php artisan optimize:clear             # Clear all caches
```

### Build for Production

```bash
# Build root frontend assets
npm run build

# Build Theme module assets
cd Modules/Theme && yarn build && cd ../..

# Publish Theme assets to public folder
php artisan vendor:publish --tag=theme-assets

# Cache Laravel config, routes, views
php artisan optimize
```

---

## Demo Mode

The application includes a **Demo Mode** feature that displays demo credentials on login forms with an auto-fill functionality. This is useful for:

- Showcasing the application to potential clients
- Testing and development purposes
- Onboarding new users

### Enabling Demo Mode

1. Open your `.env` file
2. Set the following variables:

```env
# Enable Demo Mode
DEMO_MODE=true

# Demo Credentials for Doctor Login (must satisfy the password policy)
DEMO_DOCTOR_EMAIL=doctor@demo.com
DEMO_DOCTOR_PASSWORD=Doctor@2026!
DEMO_DOCTOR2_PASSWORD=Doctor2@2026!

# Demo Credentials for Patient Login
DEMO_PATIENT_EMAIL=patient@demo.com
DEMO_PATIENT_PASSWORD=Patient@2026!

# Demo Credentials for Admin Login
DEMO_ADMIN_EMAIL=admin@demo.com
DEMO_ADMIN_PASSWORD=Admin@2026!
```

> **Heads up**: The seeders (`Modules/AdminManagement/database/seeders/DoctorSeeder.php`) now hash these strong defaults instead of the old literal `"password"`. If you had already run the seeder with the old weak password, re-seed the database after pulling these changes (`php artisan migrate:fresh --seed`) so the hashes match the documented values.

3. Make sure the demo accounts exist in your database (run seeders if needed)
4. Clear config cache:
   ```bash
   php artisan config:clear
   ```

### Demo Mode Features

When demo mode is enabled, login forms will display:

- A highlighted card showing demo credentials
- **Copy buttons** to copy email/password to clipboard
- **Auto-fill button** that types credentials into the form with a smooth animation
- Visual feedback on successful copy/fill operations
- Full support for dark mode and RTL languages

### Disabling Demo Mode

For production environments, ensure demo mode is disabled:

```env
DEMO_MODE=false
```

> **Security Note**: Never enable demo mode in production unless you specifically want to showcase the application. Always use secure, unique passwords for demo accounts.

---

## Password Policy & Default Credentials

The project enforces a strong-password policy in every flow that accepts a password - patient/doctor registration, the admin "create doctor" form, the admin "update doctor" form, the patient profile update, the doctor profile update, password reset, and the public booking wizard.

### Policy Rules

All newly created / changed passwords must contain:

- **Minimum 8 characters**
- **At least one lower-case letter** and **at least one upper-case letter**
- **At least one number**
- **At least one symbol** (e.g. `!@#$%^&*`)
- In **production** only: the password is also checked against the [haveibeenpwned](https://haveibeenpwned.com/Passwords) breach corpus (`uncompromised()`).

The rule is defined once in `app/Providers/AppServiceProvider.php` via `Illuminate\Validation\Rules\Password::defaults(...)` and every FormRequest that writes `Password::defaults()` automatically inherits it.

### Registration Flow

Both register requests already reference `Password::defaults()`, so they immediately pick up the rule above:

```
Modules/Auth/app/Http/Requests/Patient/RegisterRequest.php
Modules/Auth/app/Http/Requests/Doctor/RegisterRequest.php
```

Invalid examples (rejected by the validator): `password`, `12345678`, `abcdefgh`.
Valid example: `Patient@2026!`.

### Seeded Demo Credentials

The demo accounts created by `php artisan db:seed` now ship with strong defaults (they can be overridden by `.env`):

| Role     | Email                | Default Password  | Env Override            |
|----------|----------------------|-------------------|-------------------------|
| Doctor 1 | `doctor@demo.com`    | `Doctor@2026!`    | `DEMO_DOCTOR_PASSWORD`  |
| Doctor 2 | `doctor2@demo.com`   | `Doctor2@2026!`   | `DEMO_DOCTOR2_PASSWORD` |
| Patient  | `patient@demo.com`   | `Patient@2026!`   | `DEMO_PATIENT_PASSWORD` |
| Admin    | `admin@demo.com`     | `Admin@2026!`     | `DEMO_ADMIN_PASSWORD`   |

> **Never** use the defaults above in a public-facing environment. Override every `DEMO_*_PASSWORD` entry in your `.env` before deploying, and keep `DEMO_MODE=false` in production.

### Testing Environment

The `testing` environment reverts to a permissive default (`Password::min(6)`) so the existing PHPUnit suite - which uses short fixtures such as `"password"` or `"password123"` - keeps passing. Every other environment (`local`, `staging`, `production`) applies the full policy.

### Changing the Policy

Edit `configurePasswordPolicy()` inside `app/Providers/AppServiceProvider.php`. Because the rule is centralized, a single edit propagates to every registration, reset, profile-update, and admin-panel form across the project.

---

## Real-Time / WebSockets (Laravel Reverb)

The Messaging module (doctor ↔ patient chat, agent inbox, typing indicators, unread counters) broadcasts over **Laravel Reverb**, Laravel's first-party WebSocket server. Every `ShouldBroadcast` event in `Modules/Messaging/app/Events/` (`NewMessageEvent`, `ConversationUpdatedEvent`, `AgentTyping`, `MessageStatusUpdated`, `ConversationAssigned`) is pushed through Reverb to private channels defined in `Modules/Messaging/routes/channels.php`.

### 1. `.env` Variables

The following block must be present in `.env` (already wired by default):

```env
# Tell Laravel to broadcast through Reverb (NOT "log" / "null")
BROADCAST_CONNECTION=reverb

# Queue is used to dispatch ShouldBroadcast events - keep it on database or redis
QUEUE_CONNECTION=database

# Client-side credentials (shared with the browser)
REVERB_APP_ID=761660
REVERB_APP_KEY=sigqltj4txwb7ywvgf1t
REVERB_APP_SECRET=yvsqwat6vicczgrfvufu
REVERB_HOST=localhost
REVERB_PORT=8080
REVERB_SCHEME=http

# Server bind settings (what `reverb:start` actually listens on)
REVERB_SERVER_HOST=0.0.0.0
REVERB_SERVER_PORT=8080
REVERB_HOSTNAME=localhost

# Exposed to the Vite bundle so laravel-echo can connect from the browser
VITE_REVERB_APP_KEY="${REVERB_APP_KEY}"
VITE_REVERB_HOST="${REVERB_HOST}"
VITE_REVERB_PORT="${REVERB_PORT}"
VITE_REVERB_SCHEME="${REVERB_SCHEME}"
```

> **Important**: Every time you change any `VITE_REVERB_*` value you MUST rebuild the front-end bundle (`npm run dev` or `npm run build`), otherwise the browser will keep using the old values baked in at compile time.

### 2. Running Reverb

For day-to-day development just run `composer dev` - it already boots Reverb alongside the web server, queue listener, logs and Vite:

```bash
composer dev
# → server, queue, logs, vite, reverb
```

To run Reverb by itself:

```bash
# Defaults to 0.0.0.0:8080
php artisan reverb:start

# With verbose output (shows every WebSocket connection, event and channel)
php artisan reverb:start --debug

# Custom host / port (remember to keep REVERB_PORT and VITE_REVERB_PORT in sync)
php artisan reverb:start --host=0.0.0.0 --port=8081
```

You typically need **four processes running in parallel** for broadcasting to work end-to-end:

| Process                          | Purpose                                                      |
|----------------------------------|--------------------------------------------------------------|
| `php artisan serve`              | Serves the Laravel app (HTTP)                                |
| `php artisan reverb:start`       | WebSocket server that fans out broadcast events              |
| `php artisan queue:listen`       | Processes `ShouldBroadcast` jobs (they are queued by default)|
| `npm run dev` (or `yarn dev`)    | Compiles `laravel-echo` + `pusher-js` into the front-end     |

### 3. How the Messaging module uses Reverb

`Modules/Theme/resources/js/bootstrap.js` boots `laravel-echo` against Reverb using the `VITE_REVERB_*` env vars. The Messaging Livewire components then subscribe to private channels such as:

- `conversation.{id}` - real-time messages inside one conversation
- `doctor.{doctorId}` and `patient.{patientId}` - per-user inbox updates
- `agent.{userId}` and `messaging.unassigned` / `messaging.admins` - agent inbox / admin dashboards

Channel authorization lives in `Modules/Messaging/routes/channels.php`. A user can only subscribe if they match the doctor/patient/admin/assigned-agent relationship that guard defines.

### 4. Production Notes

- Put Reverb behind Nginx with a `wss://` virtual host and a valid TLS cert - set `REVERB_SCHEME=https`, `REVERB_PORT=443` on the client side, and keep `REVERB_SERVER_PORT=8080` internal.
- Run `php artisan reverb:start` under Supervisor / systemd so it auto-restarts on crash.
- Keep `QUEUE_CONNECTION=redis` in production for throughput; `database` is fine for local dev.
- Use `php artisan config:cache` after editing `.env` on production - the broadcasting config is cached, so uncached env vars will silently fail.

### 5. Troubleshooting

| Symptom                                                                 | Likely cause / Fix                                                                                  |
|-------------------------------------------------------------------------|-----------------------------------------------------------------------------------------------------|
| Messages appear only after refresh, never in real time                  | `BROADCAST_CONNECTION=log` or `null` - change it to `reverb` and run `php artisan config:clear`.    |
| Browser console: "Reverb is not configured. Real-time features disabled"| `VITE_REVERB_APP_KEY` is empty or Vite wasn't rebuilt after editing `.env`. Rebuild with `npm run dev`. |
| WebSocket connects but events never arrive                              | `php artisan queue:listen` is not running. `ShouldBroadcast` events are queued before fan-out.      |
| "Unable to connect to ws://localhost:8080"                              | Reverb isn't running. Start it with `composer dev` or `php artisan reverb:start`.                   |
| 403 on subscribing to a private channel                                 | Channel auth in `Modules/Messaging/routes/channels.php` rejected the user - confirm the guard matches (Doctor/Patient/Admin). |

---

## Troubleshooting

### Common Issues and Solutions

#### "PHP is not recognized as a command"

**Solution**: Add PHP to your system PATH:
1. Open **Environment Variables** (search in Windows)
2. Under **System Variables**, find **Path**
3. Click **Edit** > **New**
4. Add: `C:\laragon\bin\php\php-8.4.x`
5. Click **OK** on all windows
6. Restart your terminal

#### "Composer is not recognized"

**Solution**: Reinstall Composer using the Windows installer, or add it to PATH:
1. Add `C:\ProgramData\ComposerSetup\bin` to your system PATH
2. Restart your terminal

#### "SQLSTATE[HY000] [1049] Unknown database"

**Solution**: The database doesn't exist. Create it:
```bash
mysql -u root -e "CREATE DATABASE doctor-system"
```

#### "npm: command not found"

**Solution**:
1. Restart your terminal after installing Node.js
2. If still not working, reinstall Node.js
3. Verify with: `node -v` and `npm -v`

#### "yarn: command not found"

**Solution**:
1. Install Yarn globally:
   ```bash
   npm install -g yarn
   ```
2. Or enable Corepack:
   ```bash
   corepack enable
   ```
3. Restart your terminal
4. Verify with: `yarn -v`

#### Theme module assets not loading (styles broken)

**Solution**:
1. Make sure you installed Theme dependencies:
   ```bash
   cd Modules/Theme
   yarn install
   ```
2. Make sure Theme Vite is running:
   ```bash
   cd Modules/Theme
   yarn dev
   ```
3. Check if `public/build/modules/theme/` folder exists
4. If not, build the assets:
   ```bash
   cd Modules/Theme && yarn build
   ```

#### Yarn install fails with "Invalid checksum"

**Solution**:
1. Clear Yarn cache:
   ```bash
   yarn cache clean
   ```
2. Delete `node_modules` and try again:
   ```bash
   cd Modules/Theme
   rm -rf node_modules
   yarn install
   ```

#### "VITE_* variables not working"

**Solution**:
1. Stop Vite (`Ctrl+C`)
2. Run `npm run dev` again
3. Hard refresh browser (`Ctrl+Shift+R`)

#### "Class not found" errors

**Solution**: Regenerate autoload files:
```bash
composer dump-autoload
```

#### "Permission denied" on storage folder

**Solution** (Windows/Laragon):
1. Right-click `storage` folder
2. **Properties** > **Security** > **Edit**
3. Give **Full control** to your user

#### Migration errors about existing tables

**Solution**:
```bash
php artisan migrate:fresh --seed
```
> **Warning**: This deletes all data!

#### Slow performance

**Solution**:
1. Clear all caches: `php artisan optimize:clear`
2. Disable debug mode in production: Set `APP_DEBUG=false` in `.env`
3. Cache config: `php artisan config:cache`

#### "Your requirements could not be resolved" (Composer)

**Solution**:
1. Make sure you have PHP 8.4:
   ```bash
   php -v
   ```
2. If not, switch to PHP 8.4 in Laragon
3. Try updating Composer:
   ```bash
   composer self-update
   ```

#### Storage link errors

**Solution**: Create the storage symlink:
```bash
php artisan storage:link
```

---

## Environment Variables Reference

Key environment variables you may need to configure:

| Variable | Description | Example |
|----------|-------------|---------|
| `APP_NAME` | Application name | `"Doctor System"` |
| `APP_ENV` | Environment | `local`, `production` |
| `APP_DEBUG` | Debug mode | `true`, `false` |
| `APP_URL` | Application URL | `http://localhost:8000` |
| `DB_DATABASE` | Database name | `doctor-system` |
| `DB_USERNAME` | Database user | `root` |
| `DB_PASSWORD` | Database password | `` (empty for Laragon) |
| `MAIL_MAILER` | Mail driver | `smtp`, `log` |
| `QUEUE_CONNECTION` | Queue driver | `database`, `sync` |
| `DEMO_MODE` | Enable demo mode | `true`, `false` |
| `DEMO_DOCTOR_EMAIL` | Demo doctor email | `doctor@demo.com` |
| `DEMO_PATIENT_EMAIL` | Demo patient email | `patient@demo.com` |

---

## Quick Start Checklist

Use this checklist to verify your setup:

### Prerequisites
- [ ] Laragon installed and running (Apache & MySQL green)
- [ ] PHP 8.4 installed and selected in Laragon
- [ ] Node.js installed (`node -v` works)
- [ ] Yarn installed (`yarn -v` works)
- [ ] Composer installed (`composer -V` works)

### Project Setup
- [ ] Project cloned to `C:\laragon\www\doctor-system`
- [ ] `composer install` completed successfully
- [ ] `npm install` completed successfully (root project)
- [ ] `cd Modules/Theme && yarn install` completed successfully
- [ ] `.env` file created from `.env.example`
- [ ] `php artisan key:generate` executed
- [ ] `php artisan storage:link` executed

### Database
- [ ] Database `doctor-system` created
- [ ] `php artisan migrate` completed

### Running the Application
- [ ] `composer dev` running (or `php artisan serve`)
- [ ] `cd Modules/Theme && yarn dev` running (Theme assets)
- [ ] Application accessible at `http://localhost:8000`
- [ ] Admin dashboard styles loading correctly

---

## Tech Stack

| Category | Technology |
|----------|------------|
| **Backend** | Laravel 12, PHP 8.4 |
| **Frontend** | Blade, Livewire 3.6 |
| **Admin Theme** | Vuexy (Bootstrap 5.3) |
| **CSS Frameworks** | Tailwind CSS 4, Bootstrap 5.3 |
| **Database** | MySQL 8.0 |
| **Build Tools** | Vite 6, NPM, Yarn |
| **Authentication** | Laravel Sanctum, Socialite |
| **Media** | Spatie Media Library |
| **Permissions** | Spatie Permission |
| **Translations** | Spatie Translatable |
| **Payments** | Iyzico |
| **PDF** | Spatie Browsershot |
| **Testing** | PHPUnit 11, Mockery |
| **Code Quality** | Laravel Pint, PHPStan, ESLint, Prettier |

### Theme Module UI Libraries

The Theme module (Vuexy) includes these frontend libraries:

| Library | Purpose |
|---------|---------|
| Bootstrap 5.3 | CSS framework |
| DataTables | Advanced data tables |
| FullCalendar | Calendar component |
| ApexCharts | Modern charts |
| Chart.js | Simple charts |
| Select2 | Enhanced select boxes |
| Flatpickr | Date/time picker |
| SweetAlert2 | Beautiful alerts |
| Dropzone | File uploads |
| TinyMCE | Rich text editor |
| Leaflet/Mapbox | Maps |
| And 40+ more... | Various UI components |

---

## Contributing

1. Fork the repository
2. Create a feature branch: `git checkout -b feature/my-feature`
3. Make your changes
4. Run tests: `composer test`
5. Run code style check: `composer lint`
6. Commit your changes: `git commit -m "Add my feature"`
7. Push to the branch: `git push origin feature/my-feature`
8. Open a Pull Request

---

## License

This project is proprietary software. All rights reserved.

---

## Support

If you encounter any issues or have questions:

1. Check the [Troubleshooting](#troubleshooting) section
2. Review the documentation in `CLAUDE.md`
3. Open an issue on the repository: <https://github.com/yazankaakeh/doctor-system/issues>
4. Contact the development team

---

**Happy Coding!**
