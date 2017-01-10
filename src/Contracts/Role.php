<?php

namespace Spatie\Permission\Contracts;

/**
 * Interface Role
 * @package Spatie\Permission\Contracts
 *
 * @property string $name
 * @property Permission[] $permissions
 * @property UsersRoles[] $usersPermissibles
 */
interface Role
{
    /**
     * A role may be given various permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function permissions();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function usersPermissibles();

    /**
     * Find a role by its name.
     *
     * @param string $name
     * @return Role
     *
     * @throws \Spatie\Permission\Exceptions\RoleDoesNotExist
     */
    public static function findByName($name);

    /**
     * Determine if the role may perform the given permission.
     *
     * @param string|Permission $permission
     *
     * @return bool
     */
    public function hasPermissionTo($permission);
}
