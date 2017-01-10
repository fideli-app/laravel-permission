<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permissible;
use Spatie\Permission\Contracts\Permission;
use Spatie\Permission\Contracts\UsersPermissions;

/**
 * Class HasPermissionsPermissibles
 * @package Spatie\Permission\Traits
 *
 * @property UsersPermissions[] $permissionsPermissibles
 */
trait HasPermissionsPermissibles
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissionsPermissibles()
    {
        return $this->hasMany(config('laravel-permission.models.user_has_permissions'));
    }

    /**
     * @param Permissible|Model $permissible
     * @return Permission[]
     */
    public function permissionsForPermissible( Model $permissible = NULL )
    {

        return app(Permission::class)->whereHas('usersPermissibles', function ( Builder $query ) use ( $permissible )
        {
            $query->where('user', $this)
                  ->where('permissible', $permissible);
        });
    }
}
