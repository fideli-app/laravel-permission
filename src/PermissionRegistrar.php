<?php

namespace Spatie\Permission;

use Exception;
use Illuminate\Contracts\Auth\Access\Gate;
use Illuminate\Contracts\Cache\Repository;
use Log;
use Spatie\Permission\Contracts\User;
use Spatie\Permission\Models\AclMap;

class PermissionRegistrar
{
    /**
     * @var Gate
     */
    protected $gate;

    /**
     * @var Repository
     */
    protected $cache;

    /**
     * @param Gate $gate
     * @param Repository $cache
     */
    public function __construct( Gate $gate, Repository $cache )
    {
        $this->gate  = $gate;
        $this->cache = $cache;
    }

    /**
     *  Register the permissions.
     *
     * @return bool
     */
    public function registerPermissions()
    {
        try
        {
            AclMap::load($this->cache, $this->gate);

            return TRUE;
        }
        catch ( Exception $e )
        {
            Log::alert('Could not register permissions');

            return FALSE;
        }
    }

    /**
     * Forget the cached permissions.
     * @param User $user
     */
    public function forgetCachedPermissions( User $user = NULL )
    {
        AclMap::reset($user);
    }
}
