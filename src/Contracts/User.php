<?php

namespace Spatie\Permission\Contracts;
use Illuminate\Database\Eloquent\Model;

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
}
