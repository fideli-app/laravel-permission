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
    public function hasPermission( $nameOrAcl, Model $permissible = NULL ): bool;

    /**
     * @param string|RoleOrPermissionDescriptor $nameOrAcl
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasRole( $nameOrAcl, Model $permissible = NULL ): bool;

    /**
     * @param string[]|RoleOrPermissionDescriptor[] ...$codesOrAclList
     * @return bool
     */
    public function hasAnyRole( ...$codesOrAclList ): bool;

    /**
     * @param string[]|RoleOrPermissionDescriptor[] ...$codesOrAclList
     * @return bool
     */
    public function hasAllRoles( ...$codesOrAclList ): bool;

    /**
     * @param string[]|RoleOrPermissionDescriptor[] ...$codesOrAclList
     * @return bool
     */
    public function hasAnyPermission( ...$codesOrAclList ): bool;

    /**
     * @param string[]|RoleOrPermissionDescriptor[] ...$codesOrAclList
     * @return bool
     */
    public function hasAllPermissions( ...$codesOrAclList ): bool;

    /**
     * @return string|int
     */
    public function getKey();
}
