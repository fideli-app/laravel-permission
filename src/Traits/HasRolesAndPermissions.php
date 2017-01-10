<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-10
 */

namespace Spatie\Permission\Traits;


use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\acl;
use Spatie\Permission\Models\AclMap;

trait HasRolesAndPermissions
{
    use HasRolesPermissibles, HasPermissionsPermissibles, RefreshesPermissionCache;

    /**
     * @inheritdoc
     */
    public function hasRole( $nameOrAcl, Model $permissible = NULL ): bool
    {
        return AclMap::forUser($this)->hasRoleFor($nameOrAcl, $permissible);
    }

    /**
     * @inheritdoc
     */
    public function hasPermission( $nameOrAcl, Model $permissible = NULL ): bool
    {
        return AclMap::forUser($this)->hasPermissionFor($nameOrAcl, $permissible);
    }

    /**
     * @inheritdoc
     */
    public function hasAnyRole( ...$codesOrAclList ): bool
    {
        $map = AclMap::forUser($this);
        // group per permissible
        $groups = acl::groupByPermissible($codesOrAclList);

        // proceed
        foreach ( $groups as $data )
        {
            if ( $map->hasAnyRoleFor($data['names'], $data['desc']->getPermissibleObject()) )
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @inheritdoc
     */
    public function hasAllRoles( ...$codesOrAclList ): bool
    {
        $map = AclMap::forUser($this);
        // group per permissible
        $groups = acl::groupByPermissible($codesOrAclList);

        // proceed
        foreach ( $groups as $data )
        {
            if ( ! $map->hasAllRolesFor($data['names'], $data['desc']->getPermissibleObject()) )
            {
                return FALSE;
            }
        }

        return TRUE;
    }

    /**
     * @inheritdoc
     */
    public function hasAnyPermission( ...$codesOrAclList ): bool
    {
        $map = AclMap::forUser($this);
        // group per permissible
        $groups = acl::groupByPermissible($codesOrAclList);

        // proceed
        foreach ( $groups as $data )
        {
            if ( $map->hasAnyPermissionFor($data['names'], $data['desc']->getPermissibleObject()) )
            {
                return TRUE;
            }
        }

        return FALSE;
    }

    /**
     * @inheritdoc
     */
    public function hasAllPermissions( ...$codesOrAclList ): bool
    {
        $map = AclMap::forUser($this);
        // group per permissible
        $groups = acl::groupByPermissible($codesOrAclList);

        // proceed
        foreach ( $groups as $data )
        {
            if ( ! $map->hasAllPermissionsFor($data['names'], $data['desc']->getPermissibleObject()) )
            {
                return FALSE;
            }
        }

        return TRUE;
    }
}
