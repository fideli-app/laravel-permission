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
    public static function stringify( string $name, Model $permissible ): string
    {
        return self::build($name, $permissible)->__toString();
    }

    /**
     * @param string $code
     * @return RoleOrPermissionDescriptor
     */
    public static function parse( string $code ): RoleOrPermissionDescriptor
    {
        if ( isset(self::$cache[$code]) ) return self::$cache[$code];

        return self::$cache[$code] = new RoleOrPermissionDescriptor($code);
    }


    /**
     * @param string|array|RoleOrPermissionDescriptor $item
     * @return RoleOrPermissionDescriptor
     */
    public static function cast( $item )
    {
        if ( is_array($item) )
        {
            if ( count($item) === 1 )
            {
                return self::cast($item[0]);
            }
            else if ( count($item) === 2 )
            {
                if ( $item[1] === NULL ) return self::cast($item[0]);
                if ( is_string($item[0]) ) return self::build(...$item);
            }
        }
        else if ( $item instanceof RoleOrPermissionDescriptor )
        {
            return $item;
        }
        else if ( is_string($item) )
        {
            return self::parse($item);
        }
        throw new \InvalidArgumentException("Invalid ACL item given: {$item}");
    }
}
