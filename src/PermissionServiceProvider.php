<?php

namespace Spatie\Permission;

use Illuminate\Support\ServiceProvider;
use Illuminate\View\Compilers\BladeCompiler;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Contracts\UsersPermissions as UsersPermissionsContract;
use Spatie\Permission\Contracts\UsersRoles as UsersRolesContract;

class PermissionServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap the application services.
     *
     * @param PermissionRegistrar $permissionLoader
     */
    public function boot( PermissionRegistrar $permissionLoader )
    {
        $this->publishes([
            __DIR__ . '/../resources/config/laravel-permission.php' => $this->app->configPath() . '/' . 'laravel-permission.php',
        ], 'config');

        if ( ! class_exists('CreatePermissionTables') )
        {
            // Publish the migration
            $timestamp = date('Y_m_d_His', time());
            $this->publishes([
                __DIR__ . '/../resources/migrations/create_permission_tables.php.stub' => $this->app->databasePath() . '/migrations/' . $timestamp . '_create_permission_tables.php',
            ], 'migrations');
        }

        $this->mergeConfigFrom(
            __DIR__ . '/../resources/config/laravel-permission.php',
            'laravel-permission'
        );
        $this->registerModelBindings();

        $permissionLoader->registerPermissions();
    }

    /**
     * Register the application services.
     */
    public function register()
    {
        $this->registerBladeExtensions();
    }

    /**
     * Bind the Permission and Role model into the IoC.
     */
    protected function registerModelBindings()
    {
        $config = $this->app->config['laravel-permission.models'];

        $this->app->bind(PermissionContract::class, $config['permission']);
        $this->app->bind(RoleContract::class, $config['role']);
        $this->app->bind(UsersPermissionsContract::class, $config['user_has_permissions']);
        $this->app->bind(UsersRolesContract::class, $config['user_has_roles']);
    }

    /**
     * Register the blade extensions.
     */
    protected function registerBladeExtensions()
    {
        $this->app->afterResolving('blade.compiler', function ( BladeCompiler $bladeCompiler )
        {
            $bladeCompiler->directive('role', function ( $expression )
            {
                return "<?php if(auth()->check() && auth()->user()->hasRole{$expression}: ?>";
            });
            $bladeCompiler->directive('endrole', function ()
            {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('can', function ( $expression )
            {
                return "<?php if(auth()->check() && auth()->user()->hasPermission{$expression}: ?>";
            });
            $bladeCompiler->directive('endcan', function ()
            {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('cannot', function ( $expression )
            {
                return "<?php if(!auth()->check() || !auth()->user()->hasPermission{$expression}: ?>";
            });
            $bladeCompiler->directive('endcan', function ()
            {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasrole', function ( $expression )
            {
                return "<?php if(auth()->check() && auth()->user()->hasRole{$expression}): ?>";
            });
            $bladeCompiler->directive('endhasrole', function ()
            {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasanyrole', function ( $expression )
            {
                return "<?php if(auth()->check() && auth()->user()->hasAnyRole{$expression}): ?>";
            });
            $bladeCompiler->directive('endhasanyrole', function ()
            {
                return '<?php endif; ?>';
            });

            $bladeCompiler->directive('hasallroles', function ( $expression )
            {
                return "<?php if(auth()->check() && auth()->user()->hasAllRoles{$expression}): ?>";
            });
            $bladeCompiler->directive('endhasallroles', function ()
            {
                return '<?php endif; ?>';
            });
        });
    }
}
