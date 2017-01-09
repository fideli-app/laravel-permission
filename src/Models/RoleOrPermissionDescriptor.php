<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Models;


use Illuminate\Database\Eloquent\Builder;
use Spatie\Permission\Exceptions\PermissionMustNotBeEmpty;
use Spatie\Permission\Helpers;

class RoleOrPermissionDescriptor
{
    protected $meta;
    protected $code;

    /**
     * RoleOrPermissionDescriptor constructor.
     * @param string|array $metaOrCode
     * @param Model $target
     */
    public function __construct( $metaOrCode, Model $target = NULL )
    {
        if ( ! $metaOrCode ) throw new PermissionMustNotBeEmpty();

        if ( is_string($metaOrCode) && ! $target )
        {
            $this->code = $metaOrCode;
        }
        else
        {
            $this->meta = (is_array($metaOrCode) && ! $target) ? $metaOrCode : Helpers::buildMeta($metaOrCode, $target);
        }
    }

    /**
     * @param Model $pivotOwner
     * @return RoleOrPermissionDescriptor
     */
    public static function fromPivot( Model $pivotOwner ): RoleOrPermissionDescriptor
    {
        $hasTarget = ($type = $pivotOwner->target_type) && ($id = $pivotOwner->target_id);
        $meta      = [
            'name'   => $pivotOwner->name,
            'target' => $hasTarget ? compact('type', 'id') : NULL
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
     * @param bool $resolveTarget
     * @return array
     */
    public function getMeta( $resolveTarget = FALSE ): array
    {
        if ( ! $this->meta )
        {
            $this->meta = Helpers::parse($this->code, $resolveTarget);
        }
        else if ( $resolveTarget )
        {
            Helpers::resolveTarget($this->meta['target']);
        }

        return $this->meta;
    }

    /**
     * @param bool $resolve
     * @return array|null
     */
    public function getTarget( $resolve = FALSE )
    {
        return $this->getMeta($resolve)['target'];
    }

    /**
     * @return null|Model
     */
    public function getTargetObject()
    {
        $target = $this->getTarget(TRUE);

        return $target ? $target['object'] : NULL;
    }

    /**
     * @return string|null
     */
    public function getTargetType(): string
    {
        return ($target = $this->getTarget()) ? $target['type'] : NULL;
    }

    /**
     * @return string|int|null
     */
    public function getTargetId()
    {
        return ($target = $this->getTarget()) ? $target['id'] : NULL;
    }

    /**
     * @return bool
     */
    public function hasTarget(): bool
    {
        return $this->getTarget() !== NULL;
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
                     ->$where('target_type', $this->getTargetType())
                     ->$where('target_id', $this->getTargetId());
    }

    /**
     * @return array
     */
    public function getAttributes()
    {
        return [
            'name'        => $this->getName(),
            'target_type' => $this->getTargetType(),
            'target_id'   => $this->getTargetId(),
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
