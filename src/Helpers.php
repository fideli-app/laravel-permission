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
     * @param bool $resolveTarget
     * @param string $roleOrPermissionName
     * @param array $target
     * @return array
     */
    public static function parse( string $code,
                                  $resolveTarget = FALSE,
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
            $target               = ['type' => $matches[2], 'id' => $matches[3]];
            if ( $resolveTarget ) self::resolveTarget($target);
        }

        return ['name' => $roleOrPermissionName, 'target' => $target];
    }

    /**
     * @param array|null $target
     * @return null|Model
     */
    public static function resolveTarget( &$target )
    {
        if ( $target === NULL ) return NULL;
        if ( ! isset($target['object']) )
        {
            $class            = self::classForKey($target['type']);
            $target['object'] = $class::findOrFail($target['id']);
        }

        return $target['object'];
    }

    /**
     * @param string $roleOrPermissionName
     * @param Model|NULL $target
     * @return array
     */
    public static function buildMeta( string $roleOrPermissionName, Model $target = NULL ): array
    {
        return ['name' => $roleOrPermissionName, 'target' => $target ? self::stringifyTarget($target, TRUE) : NULL];
    }

    /**
     * @param Model $target
     * @return string|array
     */
    private function stringifyTarget( Model $target, $asArray = FALSE )
    {
        if ( ! ($id = $target->getKey()) )
        {
            throw new \InvalidArgumentException("Target must be an existing record");
        }
        if ( $asArray ) return ['type' => self::keyForClass($target), 'id' => $id, 'object' => $target];

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
