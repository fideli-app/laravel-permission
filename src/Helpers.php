<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission;

use Illuminate\Database\Eloquent\Model;


abstract class Helpers
{
    /**
     * @param string|string[] $roleOrPermissionName
     * @param Model|NULL $permissible
     * @return string|string[]
     */
    public static function stringify( $roleOrPermissionName, Model $permissible = NULL )
    {
        if ( ! $permissible ) return $roleOrPermissionName;

        $permissible = '(' . self::stringifyPermissible($permissible) . ')';
        if ( is_array($roleOrPermissionName) )
        {
            return array_map(function ( $name ) use ( $permissible ) { return $name . $permissible; },
                $roleOrPermissionName);
        }

        return $roleOrPermissionName . $permissible;
    }

    /**
     * @param string $code
     * @param bool $resolvePermissible
     * @param string $roleOrPermissionName
     * @param array $permissible
     * @return array
     */
    public static function parse( string $code,
                                  $resolvePermissible = FALSE,
                                  &$roleOrPermissionName = NULL,
                                  &$permissible = NULL ): array
    {
        $roleOrPermissionName = $permissible = NULL;
        if ( strpos($code, '(') !== FALSE )
        {
            if ( ! preg_match('/^([a-z_]+)\(([^:]+):([^\)]+)\)$/', $code, $matches) )
            {
                throw new \InvalidArgumentException("Invalid role/permission code: {$code}");
            }
            $roleOrPermissionName = $matches[1];
            $permissible          = ['type' => $matches[2], 'id' => $matches[3]];
            if ( $resolvePermissible ) self::resolvePermissible($permissible);
        }

        return ['name' => $roleOrPermissionName, 'permissible' => $permissible];
    }

    /**
     * @param array|null $permissible
     * @return null|Model
     */
    public static function resolvePermissible( &$permissible )
    {
        if ( $permissible === NULL ) return NULL;
        if ( ! isset($permissible['object']) )
        {
            $class                 = self::classForKey($permissible['type']);
            $permissible['object'] = $class::findOrFail($permissible['id']);
        }

        return $permissible['object'];
    }

    /**
     * @param string $roleOrPermissionName
     * @param Model|NULL $permissible
     * @return array
     */
    public static function buildMeta( string $roleOrPermissionName, Model $permissible = NULL ): array
    {
        return ['name'        => $roleOrPermissionName,
                'permissible' => $permissible ? self::stringifyPermissible($permissible, TRUE) : NULL];
    }

    /**
     * @param Model $permissible
     * @return string|array
     */
    private function stringifyPermissible( Model $permissible, $asArray = FALSE )
    {
        if ( ! ($id = $permissible->getKey()) )
        {
            throw new \InvalidArgumentException("Permissible must be an existing record");
        }
        if ( $asArray ) return ['type' => self::keyForClass($permissible), 'id' => $id, 'object' => $permissible];

        return self::keyForClass($permissible) . ':' . $id;
    }

    /**
     * @param string|Model $class
     * @return string
     */
    private static function keyForClass( $class ): string
    {
        if ( is_object($class) )
        {
            $class = get_class($class);
        }
        else if ( ! is_string($class) || ! class_exists($class) )
        {
            throw new \InvalidArgumentException("Given class must be an object or a class, {$class} given");
        }

        return $class;
    }

    /**
     * @param string $key
     * @return string
     */
    private static function classForKey( string $key ): string
    {
        if ( ! class_exists($key) )
        {
            throw new \InvalidArgumentException("Given key is not referencing a known class: {$key}");
        }

        return $key;
    }
}
