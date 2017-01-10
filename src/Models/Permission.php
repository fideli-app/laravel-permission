<?php

namespace Spatie\Permission\Models;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Contracts\UsersPermissions;
use Spatie\Permission\Exceptions\PermissionDoesNotExist;
use Spatie\Permission\Traits\RefreshesPermissionCache;

/**
 * Class Permission
 * @package Spatie\Permission\Models
 *
 * @property Model[] $users
 * @property Role[] $roles
 * @property UsersPermissions[] $usersPermissibles
 */
class Permission extends Model implements PermissionContract
{
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

        $this->setTable(config('laravel-permission.table_names.permissions'));
    }

    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(
            config('laravel-permission.models.role'),
            config('laravel-permission.table_names.role_has_permissions')
        );
    }

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function usersPermissibles()
    {
        return $this->hasMany(config('laravel-permission.models.user_has_permissions'));
    }

    /**
     * Find a permission by its name.
     *
     * @param string $name
     * @return PermissionContract
     *
     * @throws PermissionDoesNotExist
     */
    public static function findByName( $name )
    {
        $permission = static::where('name', $name)->first();

        if ( ! $permission )
        {
            throw new PermissionDoesNotExist();
        }

        return $permission;
    }
}
