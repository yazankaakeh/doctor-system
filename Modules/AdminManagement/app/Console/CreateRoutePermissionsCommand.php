<?php

namespace Modules\AdminManagement\Console;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Route;
use Modules\AdminManagement\Enums\Roles;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class CreateRoutePermissionsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'perms';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Create a permission routes.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     */
    public function handle(): void
    {
        Artisan::call('cache:forget spatie.permission.cache');
        Artisan::call('cache:clear');

        $routes = Route::getRoutes()->getRoutes();

        $role = Role::query()->where(['name' => Roles::SUPER_ADMIN->value])->first();

        foreach ($routes as $route) {
            if (! empty($route->getName())
                && isset($route->getAction()['middleware'])
                && in_array('admin-enabled', $route->getAction()['middleware'])
                && (str_starts_with($route->uri, 'admin/')
                    || str_starts_with($route->uri, 'doctor/'))
            ) {
                $routeName = $route->getName(); // مثلا admin.user_management.index
                $parts = explode('.', $routeName);

                Permission::query()->updateOrCreate([
                    'name' => $routeName,
                    'guard_name' => 'doctor',
                ], [
                    'section' => $parts[1] ?? 'etc', // هنا نأخذ الجزء الثاني (0 => admin, 1 => user_management)
                ]);

                // assign permission to super admin
                try {
                    $role?->givePermissionTo($routeName);
                } catch (Exception $e) {
                    echo "{$e->getMessage()} \r\n";
                }
            }
        }

        $this->info('Permission routes added successfully.');
    }
}
