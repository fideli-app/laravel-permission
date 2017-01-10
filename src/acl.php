<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-10
 */

namespace Spatie\Permission;


use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Models\RoleOrPermissionDescriptor;

/**
 * Class acl
 * @package Spatie\Permission
 */
abstract class acl
{
    /**
     * @var RoleOrPermissionDescriptor[]
     */
    protected static $cache = [];

    /**
     * @param string $name
     * @param Model $permissible
     * @return RoleOrPermissionDescriptor
     */
    public static function build( string $name, Model $permissible ): RoleOrPermissionDescriptor
    {
        $desc = new RoleOrPermissionDescriptor($name, $permissible);
        if ( ! isset(self::$cache[$code = $desc->getCode()]) ) self::$cache[$code] = $desc;

        return $desc;
    }

    /**
     * @param string $name
     * @param Model $permissible
     * @return string
     */
    public function stringify( string $name, Model $permissible ): string
    {
        return self::build($name, $permissible)->__toString();
    }

    /**
     * @param string $code
     * @return RoleOrPermissionDescriptor
     */
    public function parse( string $code ): RoleOrPermissionDescriptor
    {
        if ( isset(self::$cache[$code]) ) return self::$cache[$code];

        return self::$cache[$code] = new RoleOrPermissionDescriptor($code);
    }
}
