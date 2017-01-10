<?php

namespace Spatie\Permission\Contracts;

use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\RoleOrPermissionDescriptor;

/**
 * Interface User
 * @package Spatie\Permission\Contracts
 *
 * @property string $name
 * @property UsersRoles[] $rolesPermissibles
 * @property UsersPermissions[] $permissionsPermissibles
 */
interface User
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function rolesPermissibles();

    /**
     * @param Permissible|Model $permissible
     * @return Role[]
     */
    public function rolesForPermissible( Model $permissible = NULL );

    /**
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function permissionsPermissibles();

    /**
     * @param Permissible|Model $permissible
     * @return Permission[]
     */
    public function permissionsForPermissible( Model $permissible = NULL );

    /**
     * @param string|RoleOrPermissionDescriptor $nameOrAcl
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasPermissionTo( $nameOrAcl, Model $permissible = NULL ): bool;

    /**
     * @param string|RoleOrPermissionDescriptor $nameOrAcl
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasRole( $nameOrAcl, Model $permissible = NULL ): bool;

    /**
     * @param string[]|RoleOrPermissionDescriptor[] ...$nameOrAcl
     * @return bool
     */
    public function hasAnyRole( ...$nameOrAcl ): bool;

    /**
     * @param string[]|RoleOrPermissionDescriptor[] ...$nameOrAcl
     * @return bool
     */
    public function hasAllRoles( ...$nameOrAcl ): bool;

    /**
     * @return string|int
     */
    public function getKey();
}
