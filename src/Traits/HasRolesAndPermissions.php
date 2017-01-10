<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-10
 */

namespace Spatie\Permission\Traits;


use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\acl;
use Spatie\Permission\Contracts\User;
use Spatie\Permission\Models\AclMap;

class HasRolesAndPermissions extends Model implements User
{
    use HasRolesPermissibles, HasPermissionsPermissibles, RefreshesPermissionCache;

    /**
     * @inheritdoc
     */
    public function hasRole( $nameOrAcl, Model $permissible = NULL ): bool
    {
        return AclMap::forUser($this)->hasRoleFor($nameOrAcl, $permissible);
    }

    public function hasAnyRole( ...$nameOrAcl ): bool
    {
        return AclMap::forUser($this)->hasAnyRole(...$nameOrAcl);
    }
}
