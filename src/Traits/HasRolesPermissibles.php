<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Traits;


use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permissible;
use Spatie\Permission\Contracts\Role;
use Spatie\Permission\Contracts\UsersRoles;

/**
 * Class HasRolesPermissibles
 * @package Spatie\Permission\Traits
 *
 * @property UsersRoles[] $rolesPermissibles
 */
trait HasRolesPermissibles
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rolesPermissibles()
    {
        return $this->hasMany(config('laravel-permission.models.user_has_roles'));
    }

    /**
     * @param Permissible|Model $permissible
     * @return Role[]
     */
    public function rolesForPermissible( Model $permissible = NULL )
    {
        return app(Role::class)->whereHas('usersPermissibles', function ( Builder $query ) use ( $permissible )
        {
            $query->where('user', $this)
                  ->where('permissible', $permissible);
        });
    }
}
