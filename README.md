Project Documentation System
A Laravel application for organizing software project documentation by application and module, with supporting manuals, PDF exports, and testing records.
Why I built this
The goal of this project is to keep the information needed to understand a software application in one structured place. Instead of maintaining disconnected documents, each project is registered as an application, divided into functional modules, and documented at module level.

For example, an e-commerce application could contain Products, Orders, and Payments modules. Each module can have its own documentation and associated testing records, while application-level manuals provide a broader guide to the system.

This repository is intended to demonstrate how I approach organizing application data, implementing related CRUD workflows, and connecting documentation with the software testing process.
Core features
Area	Purpose
Applications	Register projects, organize them by category, and track documentation status.
Modules	Divide each application into functional units. Modules are represented by the Unit model.
Documentation	Create, view, edit, and delete module documentation, with content, status, and author information.
Manuals	Maintain application-level user guides and download them as PDFs.
PDF exports	Export individual documentation entries or application documentation.
Testing	Associate test cases, results, categories, and comments with application modules.
Issue follow-up	Record submitted fixes, verification, reopening, and related activity.
User administration	Support user access levels and application assignments through the existing admin system.


The main documentation relationship is:

Application → Modules → Documentation entries

Manuals belong directly to an application. Testing records reference applications and modules, keeping test results connected to the functionality they describe.
Technology
- PHP 8.1 or later, subject to the requirements in composer.lock.
- Laravel 10 and Eloquent ORM.
- Blade templates, Bootstrap 4, and jQuery.
- MySQL or a compatible MariaDB database.
- Laravel Mix 6 for compiling frontend assets.
- DOMPDF for PDF generation.
- Cartalyst Sentinel for authentication.
The application builds on the Josh Laravel admin template and includes InfyOm generator packages. The project-specific documentation and testing workflows sit within that broader admin foundation; the repository also contains additional administrative and template modules.
Repository structure
Path	Contents
app/Models/	Eloquent models and relationships.
app/Http/Controllers/	Request handling for documentation, applications, modules, and other workflows.
resources/views/	Blade interfaces.
routes/web.php	Web routes, including documentation and testing endpoints.
config/	Application and service configuration.
public/	Web entry point and public assets.
storage/	Runtime files and application storage.
tests/	Existing test directory.
infyomlabs/	Local packages required by Composer.


Installation
1. Prerequisites
Install PHP, Composer, MySQL/MariaDB, Node.js, npm, and Git. Enable the PHP extensions required by Composer, including GD and the PDO driver for MySQL.

On Windows, Laragon can provide the local PHP web server and database. Start the database service before configuring the application.
2. Download the project
Clone this repository using its GitHub URL, or download and extract it. Open a terminal in the project root, where artisan and composer.json are located.
3. Prepare the environment
If .env.example is available, copy it to .env:
Copy-Item .env.example .env
Otherwise, create .env manually. A starting configuration for local development is:
APP_NAME="Project Documentation System"
APP_ENV=local
APP_KEY=
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

LOG_CHANNEL=stack

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=project_documentation
DB_USERNAME=root
DB_PASSWORD=

CACHE_DRIVER=file
SESSION_DRIVER=file
QUEUE_CONNECTION=sync
MAIL_MAILER=log
Replace the database credentials with your local values. These settings are for local development; a deployed environment needs its own configuration.
4. Install PHP dependencies
composer install
php artisan key:generate
Keep the infyomlabs/ directory in place: composer.json references packages inside it through local path repositories.
5. Set up the database
Create an empty database named project_documentation using your database client, or execute:
CREATE DATABASE project_documentation
CHARACTER SET utf8mb4
COLLATE utf8mb4_unicode_ci;
Current setup requirement: the project copy inspected for this README does not include a database/ directory or .env.example. Creating an empty database alone is therefore insufficient. To run this version, obtain a compatible database schema or a sanitized SQL export from the project maintainer and import it into the database configured in .env.

If a complete migration set is added later, initialize a fresh database with:
php artisan migrate
Run seeders only if they are supplied and documented. This README does not assume a default administrator account or password; a compatible user account must be provisioned through the application's authentication setup or supplied demo database.
6. Build frontend assets
npm install
npm run dev
For an optimized asset build:
npm run production
This project uses Laravel Mix rather than Vite. Some frontend dependencies are older packages or Git repositories, so installation depends on their continued availability and a compatible Node.js toolchain.
7. Prepare storage and start the application
Ensure storage/ and bootstrap/cache/ exist and are writable by PHP. Then run:
php artisan storage:link
php artisan config:clear
php artisan serve
Open http://127.0.0.1:8000/admin/login and sign in with an account from your configured database.

When using a Laragon virtual host, point its document root to the project's public/ directory and update APP_URL to match the local domain.
Suggested walkthrough
1. Sign in to the administration area.
2. Create an application and assign its category.
3. Add modules representing the application's main functional areas.
4. Write documentation for each module and update its status as work progresses.
5. Add an application manual and inspect the PDF exports.
6. Record test cases and results, then follow an issue through fix submission and verification.
What this project demonstrates
- Translating a documentation workflow into related database entities.
- Organizing Laravel routes, controllers, Eloquent relationships, and Blade views.
- Building CRUD interfaces around application and module context.
- Tracking documentation authorship, completion, and testing activity.
- Generating downloadable documentation through PDF exports.
- Extending an existing admin foundation with project-specific workflows.
Current scope
This is a portfolio project with an existing admin-template foundation. A fresh installation currently requires the database setup described above. Installation commands and runtime behavior were not executed as part of preparing this README.

Before sharing a runnable demo, provide a sanitized schema or complete migrations, a safe environment example, and documented demo-account provisioning. Keep .env, private credentials, real user uploads, and unsanitized database exports out of Git.

If .github/workflows/main.yml remains in the repository, it defines an FTP deployment workflow triggered by pushes to main. Configure its secrets before enabling deployment, or remove the workflow when only source hosting is required.
Third-party licensing
The project includes third-party packages and an admin template. composer.json identifies the template license as Envato License. Their respective license terms continue to apply; this README does not grant redistribution rights for those components.
