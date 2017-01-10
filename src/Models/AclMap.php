<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-10
 */

namespace Spatie\Permission\Models;


use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\acl;
use Spatie\Permission\Contracts\User;
use Spatie\Permission\Helpers;

/**
 * Class AclMap
 * @package Spatie\Permission\Models
 */
class AclMap implements \Serializable
{
    /** @var bool */
    protected static $loaded = FALSE;
    /** @var Repository */
    protected static $cache;
    /** @var string */
    protected static $cachePrefix = '/spatie/permission/cache';
    /** @var array[] */
    protected static $permissionRoles;
    /** @var bool[] */
    protected static $localCache = [];
    /** @var AclMap[] */
    protected static $instances = [];
    /** @var string */
    protected $userId;
    /** @var string[] */
    protected $permissions;
    /** @var string[] */
    protected $roles;
    /** @var bool[] */
    protected $hasPermissionCache = [];

    /**
     * AclMap constructor.
     * @param string $id
     */
    protected function __construct( $id )
    {
        self::$instances[$this->userId = '' . $id] = $this;
    }

    /**
     * @return AclMap
     */
    protected function build(): AclMap
    {
        if ( $this->roles === NULL )
        {
            $T = (object)config('laravel-permission.table_names');
            // roles
            $all         = DB::table($T->user_has_roles)
                             ->join($T->roles, "{$T->user_has_roles}.role_id", '=', "{$T->roles}.id")
                             ->where("{$T->user_has_roles}.user_id", $this->userId)
                             ->select(
                                 "{$T->roles}.name AS name",
                                 "{$T->user_has_roles}.permissible_type AS type",
                                 "{$T->user_has_roles}.permissible_id AS id"
                             )
                             ->get();
            $this->roles = [];
            foreach ( $all as $row )
            {
                $this->roles[Helpers::compose($row->name, $row->type, $row->id)] = $row->name;
            }
            // permissions
            $all               = DB::table($T->user_has_permissions)
                                   ->join($T->permissions, "{$T->user_has_permissions}.permission_id", '=',
                                       "{$T->permissions}.id")
                                   ->where("{$T->user_has_permissions}.user_id", $this->userId)
                                   ->select(
                                       "{$T->permissions}.name AS name",
                                       "{$T->user_has_permissions}.permissible_type AS type",
                                       "{$T->user_has_permissions}.permissible_id AS id"
                                   )
                                   ->get();
            $this->permissions = [];
            foreach ( $all as $row )
            {
                $this->permissions[Helpers::compose($row->name, $row->type, $row->id)] = $row->name;
            }
        }

        return $this;
    }

    /**
     * @param string $code
     * @return bool
     */
    public function hasRole( string $code ): bool
    {
        $acl = acl::cast($code);

        return $this->build()->_hasRole($code)
            || ($acl->hasPermissible() && $this->_hasRole($acl->getName()));
    }

    /**
     * @param string $code
     * @return bool
     */
    protected function _hasRole( string $code ): bool
    {
        return isset($this->roles[$code]);
    }

    /**
     * @param array $roles
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasAnyRoleFor( array $roles, Model $permissible = NULL ): bool
    {
        $roles = array_unique($roles);

        return $this->build()->_hasAnyRole($roles, $permissible)
            || ($permissible && $this->_hasAnyRole($roles));
    }

    /**
     * @param array $roles
     * @param Model|NULL $permissible
     * @return bool
     */
    protected function _hasAnyRole( array $roles, Model $permissible = NULL ): bool
    {
        $suffix = $permissible ? '(' . Helpers::stringifyPermissible($permissible) . ')' : '';
        foreach ( $roles as $role )
        {
            if ( $this->_hasRole($role . $suffix) ) return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array $roles
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasAllRolesFor( array $roles, Model $permissible = NULL ): bool
    {
        $roles = array_unique($roles);
        $which = $this->_whichRoles($roles, $permissible);
        if ( $permissible ) $which = array_merge($which, $this->_whichRoles($roles));

        return count(array_diff($roles, $which)) === 0;
    }

    /**
     * @param array $roles
     * @param Model|NULL $permissible
     * @return string[]
     */
    protected function _whichRoles( array $roles, Model $permissible = NULL ): array
    {
        $suffix = $permissible ? '(' . Helpers::stringifyPermissible($permissible) . ')' : '';
        $res    = [];
        foreach ( $roles as $role )
        {
            if ( isset($res[$role]) ) continue;
            if ( $this->_hasRole($role . $suffix) ) $res[$role] = $role;
        }

        return $res;
    }

    /**
     * @param string $role
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasRoleFor( string $role, Model $permissible = NULL ): bool
    {
        return $this->build()->_hasRole($role)
            || ($permissible && $this->_hasRole(Helpers::stringify($role, $permissible)));
    }

    /**
     * @param string $code
     * @return bool
     */
    public function hasPermission( string $code ): bool
    {
        $acl = acl::cast($code);

        return $this->build()->_hasPermission($code, ! $acl->hasPermissible())
            || ($acl->hasPermissible() && $this->_hasPermission($acl->getName(), TRUE));
    }

    /**
     * @param string $code
     * @param bool $deep
     * @return bool
     */
    protected function _hasPermission( string $code, bool $deep = FALSE ): bool
    {
        return isset($this->permissions[$code])
            || ($deep && $this->_hasAnyRole(self::rolesWithPermission($code)));
    }

    /**
     * @param string[] $permissions
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasAnyPermissionFor( array $permissions, Model $permissible = NULL ): bool
    {
        $permissions = array_unique($permissions);

        return $this->build()->_hasAnyPermission($permissions, $permissible, ! $permissible)
            || ($permissible && $this->_hasAnyPermission($permissions, NULL, TRUE));
    }

    /**
     * @param string[] $permissions
     * @param Model|NULL $permissible
     * @return bool
     */
    protected function _hasAnyPermission( array $permissions, Model $permissible = NULL, bool $deep = FALSE )
    {
        $suffix = $permissible ? '(' . Helpers::stringifyPermissible($permissible) . ')' : '';
        foreach ( $permissions as $permission )
        {
            if ( $this->_hasPermission($permission . $suffix, $deep) ) return TRUE;
        }

        return FALSE;
    }

    /**
     * @param array $permissions
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasAllPermissionsFor( array $permissions, Model $permissible = NULL ): bool
    {
        $permissions = array_unique($permissions);
        $which       = $this->_whichPermissions($permissions, $permissible, ! $permissible);
        if ( $permissible ) $which = array_merge($which, $this->_whichPermissions($permissions, NULL, TRUE));

        return count(array_diff($permissions, $which)) === 0;
    }

    /**
     * @param array $permissions
     * @param Model|NULL $permissible
     * @param bool $deep
     * @return string[]
     */
    protected function _whichPermissions( array $permissions, Model $permissible = NULL, bool $deep = FALSE ): array
    {
        $suffix = $permissible ? '(' . Helpers::stringifyPermissible($permissible) . ')' : '';
        $res    = [];
        foreach ( $permissions as $permission )
        {
            if ( isset($res[$permission]) ) continue;
            if ( $this->_hasPermission($permission . $suffix, $deep) ) $res[$permission] = $permission;
        }

        return $res;
    }

    /**
     * @param string $permission
     * @param Model|NULL $permissible
     * @return bool
     */
    public function hasPermissionFor( string $permission, Model $permissible = NULL ): bool
    {
        $key = Helpers::stringify($permission, $permissible);
        if ( isset($this->hasPermissionCache[$key]) ) return $this->hasPermissionCache[$key];

        return $this->hasPermissionCache[$key]
            = $this->build()->_hasPermission($permission, TRUE)
            || $this->_hasPermission($key)
            || ($permissible && $this->_hasAnyRole(self::rolesWithPermission($permission), $permissible));
    }

    /**
     * @param User $user
     * @return AclMap
     */
    public static function forUser( User $user ): AclMap
    {
        if ( ! ($key = '' . $user->getKey()) )
        {
            throw new \InvalidArgumentException('Cannot get an ACL map for a not saved user (no PK)');
        }
        if ( isset(self::$instances[$key]) ) return self::$instances[$key];

        return self::$instances[$key] = self::$cache->rememberForever(
            self::cacheKeyFor($user), function () use ( $user ) { new self($user); }
        );
    }

    /**
     * @param User $user
     * @return string
     */
    protected static function cacheKeyFor( User $user = NULL ): string
    {
        return self::$cachePrefix . ($user ? '/user/' . $user->getKey() : '');
    }


    /**
     * @param User|NULL $user
     */
    public static function reset( User $user = NULL )
    {
        if ( $user )
        {
            if ( ($acl = self::instanceForUser($user)) )
            {
                $acl->hasPermissionCache = [];
                $acl->roles              = NULL;
                $acl->permissions        = NULL;
            }
        }
        else
        {
            foreach ( self::$instances as $acl )
            {
                $acl->hasPermissionCache = [];
            }
            self::$permissionRoles = NULL;
            self::$localCache      = [];
        }
    }

    /**
     * @param User|NULL $user
     */
    public static function forget( User $user = NULL )
    {
        self::$cache->forget(self::cacheKeyFor($user));
    }


    /**
     * @param User $user
     * @return AclMap|NULL
     */
    protected static function instanceForUser( User $user )
    {
        $key = '' . $user->getKey();

        return isset(self::$instances[$key]) ? self::$instances[$key] : NULL;
    }

    /**
     * Builds the global permissions map
     * @return array[]
     */
    protected static function buildPermissionRolesMap()
    {
        if ( self::$permissionRoles == NULL )
        {
            $T   = (object)config('laravel-permission.table_names');
            $all = DB::table($T->role_has_permissions)
                     ->join($T->roles, "{$T->role_has_permissions}.role_id", '=', "{$T->roles}.id")
                     ->join($T->permissions, "{$T->role_has_permissions}.permission_id", '=', "{$T->permissions}.id")
                     ->select("{$T->roles}.name AS role", "{$T->permissions}.name AS permission")
                     ->get();
            foreach ( $all as $row )
            {
                if ( ! isset(self::$permissionRoles[$row->permission]) ) self::$permissionRoles[$row->permission] = [];
                self::$permissionRoles[$row->permission][$row->role] = TRUE;
            }
        }

        return self::$permissionRoles;
    }

    /**
     * @param string $role
     * @param string $permission
     * @return bool
     */
    public static function roleHasPermission( string $role, string $permission ): bool
    {
        $key = $role . ':' . $permission;
        if ( isset(self::$localCache[$key]) ) return self::$localCache[$key];

        return self::$localCache[$key] = isset(self::$permissionRoles[$permission])
            && isset(self::$permissionRoles[$permission][$role]);
    }

    /**
     * @param string $permission
     * @return string[]
     */
    public static function rolesWithPermission( string $permission ): array
    {
        return isset(self::buildPermissionRolesMap()[$permission]) ? self::buildPermissionRolesMap()[$permission] : [];
    }

    /**
     * @return string
     */
    static public function serializeStatic(): string
    {
        return json_encode(self::$permissionRoles);
    }

    /**
     * @param string $serialized
     */
    static public function unserializeStatic( string $serialized )
    {
        self::$localCache      = [];
        self::$permissionRoles = json_decode($serialized);
    }

    /**
     * @param Repository $cache
     * @param string $cachePrefix
     * @param Gate $gate
     */
    static public function load( Repository $cache, Gate $gate )
    {
        if ( ! self::$loaded )
        {
            self::$loaded = TRUE;
            self::$cache  = $cache;

            // cache and/or restore the global permission map
            self::unserializeStatic($cache->rememberForever(self::cacheKeyFor(), function ()
            {
                return self::buildPermissionRolesMap();
            }));

            // define rules within the gate
            array_walk(self::$permissionRoles, function ( $dummy, $permission ) use ( $gate )
            {
                $gate->define($permission, function ( User $user, Model $permissible = NULL ) use ( $permission )
                {
                    return self::forUser($user)->hasPermissionFor($permission, $permissible);
                });
            });
        }
    }


    /**
     * @inheritdoc
     */
    public function serialize()
    {
        return serialize(['u' => $this->userId, 'r' => $this->roles, 'p' => $this->permissions]);
    }

    /**
     * @inheritdoc
     */
    public function unserialize( $serialized )
    {
        $data                           = unserialize($serialized);
        $this->userId                   = $data['u'];
        $this->roles                    = $data['r'];
        $this->permissions              = $data['p'];
        self::$instances[$this->userId] = $this;
    }
}
