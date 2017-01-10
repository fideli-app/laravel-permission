<?php
/**
 * @author Huafu Gandon <huafu.gandon@gmail.com>
 * @since 2017-01-09
 */

namespace Spatie\Permission\Contracts;

/**
 * Interface UsersRoles
 * @package Spatie\Permission\Contracts
 *
 * @property int $role_id
 * @property int $user_id
 * @property string $target_type
 * @property int $target_id
 * @property \Carbon\Carbon $created_at
 * @property \Carbon\Carbon $updated_at
 *
 * @property Role $role
 * @property User $user
 * @property \Illuminate\Database\Eloquent\Model|Permissible $permissible
 */
interface UsersRoles
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
