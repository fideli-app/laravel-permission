<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Models;


use Closure;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\Permission\Exceptions\PermissionMustNotBeEmpty;
use Spatie\Permission\Helpers;

class RoleOrPermissionDescriptor
{
    protected $meta;
    protected $code;

    /**
     * RoleOrPermissionDescriptor constructor.
     * @param string|array $metaOrCode
     * @param Model $permissible
     */
    public function __construct( $metaOrCode, Model $permissible = NULL )
    {
        if ( ! $metaOrCode ) throw new PermissionMustNotBeEmpty();

        if ( is_string($metaOrCode) && ! $permissible )
        {
            $this->code = $metaOrCode;
        }
        else
        {
            $this->meta =
                (is_array($metaOrCode) && ! $permissible) ? $metaOrCode : Helpers::buildMeta($metaOrCode, $permissible);
        }
    }

    /**
     * @param Model $pivotOwner
     * @return RoleOrPermissionDescriptor
     */
    public static function fromPivot( Model $pivotOwner ): RoleOrPermissionDescriptor
    {
        $hasPermissible = ($type = $pivotOwner->permissible_type) && ($id = $pivotOwner->permissible_id);
        $meta           = [
            'name'        => $pivotOwner->name,
            'permissible' => $hasPermissible ? compact('type', 'id') : NULL
        ];

        return new self($meta);
    }

    /**
     * @return string
     */
    public function getCode(): string
    {
        if ( ! $this->code ) $this->code = Helpers::stringify($this->meta);

        return $this->code;
    }

    /**
     * @param bool $resolvePermissible
     * @return array
     */
    public function getMeta( $resolvePermissible = FALSE ): array
    {
        if ( ! $this->meta )
        {
            $this->meta = Helpers::parse($this->code, $resolvePermissible);
        }
        else if ( $resolvePermissible )
        {
            Helpers::resolvePermissible($this->meta['permissible']);
        }

        return $this->meta;
    }

    /**
     * @param bool $resolve
     * @return array|null
     */
    public function getPermissible( $resolve = FALSE )
    {
        return $this->getMeta($resolve)['permissible'];
    }

    /**
     * @return null|Model
     */
    public function getPermissibleObject()
    {
        $permissible = $this->getPermissible(TRUE);

        return $permissible ? $permissible['object'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getPermissibleType(): string
    {
        return ($permissible = $this->getPermissible()) ? $permissible['type'] : NULL;
    }

    /**
     * @return string|int|null
     */
    public function getPermissibleId()
    {
        return ($permissible = $this->getPermissible()) ? $permissible['id'] : NULL;
    }

    /**
     * @return bool
     */
    public function hasPermissible(): bool
    {
        return $this->getPermissible() !== NULL;
    }

    /**
     * @return string
     */
    public function getName(): string
    {
        return $this->getMeta()['name'];
    }

    /**
     * @param Builder $query
     * @param bool $asPivot
     * @return mixed
     */
    public function applyTo( Builder $query, $asPivot = FALSE )
    {
        $where = $asPivot ? 'wherePivot' : 'where';

        return $query->$where('name', $this->getName())
                     ->$where('permissible_type', $this->getPermissibleType())
                     ->$where('permissible_id', $this->getPermissibleId());
    }

    /**
     * @param bool $asPivot
     * @return Closure
     */
    public function getWhere( $asPivot = FALSE ): Closure
    {
        $where = $asPivot ? 'wherePivot' : 'where';

        return function ( $query ) use ( $where )
        {
            return $query->$where('name', $this->getName())
                         ->$where('permissible_type', $this->getPermissibleType())
                         ->$where('permissible_id', $this->getPermissibleId());
        };
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return [
            'name'             => $this->getName(),
            'permissible_type' => $this->getPermissibleType(),
            'permissible_id'   => $this->getPermissibleId(),
        ];
    }

    /**
     * @param Model $record
     * @return Model
     */
    public function fillIn( Model $record )
    {
        return $record->fill($this->getAttributes());
    }
}
