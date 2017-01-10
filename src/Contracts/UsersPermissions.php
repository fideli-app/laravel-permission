<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Contracts;

/**
 * Interface UsersPermissions
 * @package Spatie\Permission\Contracts
 *
 * @property int $permission_id
 * @property int $user_id
 * @property string $permissible_type
 * @property int $permissible_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property Permission $permission
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Model|Permissible $permissible
 */
interface UsersPermissions
{
    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function role();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user();

    /**
     * @return \Illuminate\Database\Eloquent\Relations\MorphTo
     */
    public function permissible();
}
