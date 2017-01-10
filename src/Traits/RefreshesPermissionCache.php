<?php

namespace Spatie\Permission\Traits;

use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Spatie\Permission\PermissionRegistrar;

trait RefreshesPermissionCache
{
    public static function bootRefreshesPermissionCache()
    {
        static::created(function ( $model )
        {
            $model->forgetCachedPermissions();
        });

        static::updated(function ( $model )
        {
            $model->forgetCachedPermissions();
        });

        static::deleted(function ( $model )
        {
            $model->forgetCachedPermissions();
        });
    }

    /**
     *  Forget the cached permissions.
     */
    public function forgetCachedPermissions()
    {
        $user_id = method_exists($this, 'user') && $this->user() instanceof BelongsTo
            ? $this->user_id
            : NULL;
        app(PermissionRegistrar::class)->forgetCachedPermissions($user_id);
    }
}
