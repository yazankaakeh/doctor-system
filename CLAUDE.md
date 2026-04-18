# CLAUDE.md

This file provides guidance to Claude Code (claude.ai/code) when working with code in this repository.

## Project Overview

This is a modular Laravel 12 application (PHP 8.4) serving as a base project foundation, built using the
`nwidart/laravel-modules` package. The system provides a robust modular architecture with user management, authentication, blog/CMS functionality, and extensible modules for building custom applications.

## Architecture

### Modular Structure

The application follows a **modular architecture** using Laravel Modules. All feature code is organized into
self-contained modules in the `Modules/` directory:

- **Core**: Base models, enums, helpers, rules, traits, and shared components
- **AdminManagement**: User/role/permission management
- **Auth**: Multi-guard authentication with social login
- **Blog**: Blog posts, categories, tags
- **CMS**: Content management and page builder
- **Seo**: SEO meta management
- **Notification**: Email, SMS, and push notifications
- **Theme**: UI themes and views (Vuexy Bootstrap 5)
- **MCP**: AI chatbot integration
- **Messaging**: Multi-channel messaging system
- **Website**: Public-facing website
- **Doctor/Patient/Booking**: Optional medical modules (can be removed for non-medical projects)

Each module contains its own:

- `app/` - Controllers, Actions, Models, Livewire components, Enums, Traits, Rules
- `routes/` - web.php and api.php
- `resources/views/` - Blade templates
- `database/` - Migrations, seeders, factories
- `config/` - Module-specific config
- `Repository/` - Repository pattern implementation (in some modules)

### Coding Standards (from .cursor/rules/my-rules.mdc)

**CRITICAL: Follow these architectural patterns:**

1. **Controllers MUST be thin** - No business logic in controllers
2. **Validation** - Use Form Requests only (in `app/Http/Requests/`)
3. **Business Logic** - Use Actions/Services (in `app/Actions/` or `app/Services/`)
4. **Data Access** - Use Repositories (in `app/Repository/` or `Repository/`)
5. **Views** - Use Blade components/partials to avoid repetition
6. **Module Organization** - All routes, repositories, and views go inside their related Module

**Example Controller Pattern:**

```php
public function store(\App\Http\Requests\Tag\StoreTagRequest $request,
                      \App\Actions\Tag\CreateTagAction $action)
{
    $tag = $action->handle($request->validated());
    return redirect()->route('tags.show', $tag)->with('status','Created');
}
```

### Key Packages

- **Livewire 3.6** - Interactive components (`mhmiton/laravel-modules-livewire`)
- **Spatie Media Library** - Media/image handling
- **Spatie Permission** - Role/permission management
- **Spatie Translatable** - Multi-language support
- **Laravel Sanctum** - API authentication
- **Laravel Socialite** - Social authentication
- **Iyzico** - Payment integration

## Development Commands

### Running the Application

```bash
# Start all services (server, queue, logs, vite) - RECOMMENDED
composer dev

# Or manually:
php artisan serve          # Development server
php artisan queue:listen --tries=1  # Queue worker
php artisan pail --timeout=0        # Log viewer
npm run dev                         # Vite asset compilation
```

### Asset Compilation

```bash
npm run dev    # Development with hot reload
npm run build  # Production build
```

### Testing

```bash
composer test          # Run all tests
php artisan test       # Alternative test command
php artisan test --filter TestName  # Run specific test
```

### Database

```bash
php artisan migrate              # Run migrations
php artisan migrate:fresh --seed # Fresh database with seeders
php artisan db:seed              # Run seeders
```

### Code Quality

```bash
composer pint  # Laravel Pint (code formatting)
```

### Module Commands

```bash
php artisan module:list                    # List all modules
php artisan module:make ModuleName         # Create new module
php artisan module:make-controller Name ModuleName
php artisan module:make-model Name ModuleName
php artisan module:make-migration name ModuleName
php artisan module:make-request Name ModuleName
php artisan perms                          # Create permission routes
```

## Important Implementation Notes

### When Creating New Features

1. **Determine the appropriate Module** - Place all related code in the same module
2. **Create Form Request** for validation
3. **Create Action/Service class** for business logic
4. **Create Repository** if data access logic is complex
5. **Controller** should only accept FormRequest, call Action, return view/resource
6. **Use Blade components** from Theme module where possible

### Repository Pattern

Repositories implement interfaces and handle all database queries. Example structure:

```php
namespace Modules\Blog\Repository\Post;

class PostRepository implements PostInterface
{
    public function index(): LengthAwarePaginator { }
    public function store(Request $request): BlogPost { }
    public function update(int $id, Request $request): BlogPost { }
    public function find(int $id): BlogPost { }
    public function destroy(int $id): void { }
}
```

### Multi-language Support

Models use Spatie Translatable. Translatable fields are stored as JSON:

```php
$post->title = ['en' => 'Title', 'ar' => 'عنوان'];
$post->getTranslation('title', 'en');
```

### Media Handling

Uses Spatie Media Library:

```php
$model->addMediaFromRequest('image')->toMediaCollection('img');
```

### Permissions

Custom artisan command for generating permissions:

```bash
php artisan perms
```

## File Locations

- **Global app code**: `app/` (Enums, Http, Models, Providers)
- **Module code**: `Modules/{ModuleName}/app/`
- **Views**: `Modules/Theme/resources/views/` and module-specific views
- **Migrations**: `Modules/{ModuleName}/database/migrations/`
- **Routes**: `Modules/{ModuleName}/routes/`
- **Config**: `config/` (global) and `Modules/{ModuleName}/config/`

## Common Pitfalls

1. **Don't put business logic in controllers** - Always use Actions/Services
2. **Don't skip Form Requests** - Never validate directly in controllers
3. **Keep module boundaries** - Don't mix concerns across unrelated modules
4. **Follow naming conventions** - PSR-12 and Laravel standards
5. **Use English for comments** - Maintain consistency

# Project Rules – Laravel 12 + nWidart Modules

Standards

- PSR-4, DRY, KISS, SoC. Comments in English only.
- Controllers thin; use Form Requests for validation.
- Business logic in Services/Actions; data access via Repositories.
- Reuse Blade components/partials; Livewire 3 for interactivity.
- For Modules: keep PHP under `Modules/*/src/` only.
- Don’t put heavy tasks in composer hooks; keep `post-autoload-dump` light.
  Output
- Paste-ready code with file paths + brief rationale.
  Testing
- Add tests for domain logic; include migrations/factories with schema changes.

