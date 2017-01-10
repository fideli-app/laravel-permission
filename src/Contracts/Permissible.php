<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Contracts;


/**
 * Interface Permissible
 * @package Spatie\Permission\Contracts
 *
 * @property UsersRoles[] $usersRoles
 * @property UsersPermissions[] $usersPermissions
 */
interface Permissible
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function usersRoles();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphMany
     */
    public function usersPermissions();

    /**
     * @param User $user
     * @return Permission[]
     */
    public function permissionsForUser( User $user );

    /**
     * @param User $user
     * @return Role[]
     */
    public function rolesForUser( User $user );
}
