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
     * @param Model|NULL $target
     * @return string|string[]
     */
    public static function stringify( $roleOrPermissionName, Model $target = NULL )
    {
        if ( ! $target ) return $roleOrPermissionName;

        $target = '(' . self::stringifyTarget($target) . ')';
        if ( is_array($roleOrPermissionName) )
        {
            return array_map(function ( $name ) use ( $target ) { return $name . $target; }, $roleOrPermissionName);
        }

        return $roleOrPermissionName . $target;
    }

    /**
     * @param string $code
     * @param bool $loadTarget
     * @param string $roleOrPermissionName
     * @param array|Model $target
     * @return array
     */
    public static function parse( string $code,
                                  $loadTarget = FALSE,
                                  &$roleOrPermissionName = NULL,
                                  &$target = NULL ): array
    {
        $roleOrPermissionName = $target = NULL;
        if ( strpos($code, '(') !== FALSE )
        {
            if ( ! preg_match('/^([a-z_]+)\(([^:]+):([^\)]+)\)$/', $code, $matches) )
            {
                throw new \InvalidArgumentException("Invalid role/permission code: {$code}");
            }
            $roleOrPermissionName = $matches[1];
            $class                = self::classForKey($matches[2]);
            if ( $loadTarget )
            {
                $target = $class::findOrFail($matches[3]);
            }
            else
            {
                $target = ['type' => $class, 'id' => $matches[3]];
            }
        }

        return ['name' => $roleOrPermissionName, 'target' => $target];
    }

    /**
     * @param Model $target
     * @return string
     */
    private function stringifyTarget( Model $target )
    {
        if ( ! ($id = $target->getKey()) )
        {
            throw new \InvalidArgumentException("Target must be an existing record");
        }

        return self::keyForClass($target) . ':' . $id;
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
