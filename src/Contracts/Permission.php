<?php

namespace Spatie\Permission\Contracts;


/**
 * Interface Permission
 * @package Spatie\Permission\Contracts
 *
 * @property string $name
 * @property Role[] $roles
 * @property UsersPermissions[] $usersPermissibles
 */
interface Permission
{
    /**
     * A permission can be applied to roles.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usersPermissibles();

    /**
     * Find a permission by its name.
     *
     * @param string $name
     * @return Permission
     *
     * @throws \Spatie\Permission\Exceptions\PermissionDoesNotExist
     */
    public static function findByName($name);
}
