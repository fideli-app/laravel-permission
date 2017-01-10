<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Role as RoleContract;
use Spatie\Permission\Contracts\UsersRoles;
use Spatie\Permission\Exceptions\RoleDoesNotExist;
use Spatie\Permission\Traits\RefreshesPermissionCache;
use Spatie\Permission\Traits\RoleHasPermissions;

/**
 * Class Role
 * @package Spatie\Permission\Models
 *
 * @property Model[] $users
 * @property Permission[] $permissions
 * @property UsersRoles[] $usersPermissibles
 */
class Role extends Model implements RoleContract
{
    use RoleHasPermissions;
    use RefreshesPermissionCache;

    /**
     * The attributes that aren't mass assignable.
     *
     * @var array
     */
    public $guarded = ['id'];

    /**
     * Create a new Eloquent model instance.
     *
     * @param array $attributes
     */
    public function __construct( array $attributes = [] )
    {
        parent::__construct($attributes);

        $this->setTable(config('laravel-permission.table_names.roles'));
    }

    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions()
    {
        return $this->belongsToMany(
            config('laravel-permission.models.permission'),
            config('laravel-permission.table_names.role_has_permissions')
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersPermissibles()
    {
        return $this->hasMany(config('laravel-permission.models.user_has_roles'));
    }

    /**
     * Find a role by its name.
     *
     * @param string $name
     *
     * @throws RoleDoesNotExist
     *
     * @return Role
     */
    public static function findByName( $name )
    {
        $role = static::where('name', $name)->first();

        if ( ! $role )
        {
            throw new RoleDoesNotExist();
        }

        return $role;
    }

    /**
     * Determine if the role may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo( $permission )
    {
        if ( is_string($permission) )
        {
            $permission = app(Permission::class)->findByName($permission);
        }

        return $this->permissions->contains('id', $permission->id);
    }
}
