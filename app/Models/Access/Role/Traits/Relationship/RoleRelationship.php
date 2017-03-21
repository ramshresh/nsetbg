<?php

namespace App\Models\Access\Role\Traits\Relationship;

/**
 * Class RoleRelationship.
 */
trait RoleRelationship
{
    /**
     * @return mixed
     */
    public function users()
    {
        return $this->belongsToMany(config('auth.providers.users.model'), config('access.role_user_table'), 'role_id', 'user_id');
    }

    /**
     * @return mixed
     */
    public function permissions()
    {
        return $this->belongsToMany(config('access.permission'), config('access.permission_role_table'), 'role_id', 'permission_id')
            ->orderBy('display_name', 'asc');
    }

    /**
     * Role belongs to parent role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */

    public function parent()
    {
        return $this->belongsTo(config('access.role'),'parent_id');
    }

    /**
     * Role has many children roles
     *
     * @return \Illuminate\Database\Eloquent\Relations\HasMany
     */
    public function children()
    {
        return $this->hasMany(config('access.role'),'parent_id');
    }

    public function ancestors()
    {
        $ancestors = $this->where('id', '=', $this->parent_id)->get();
        while ($ancestors->last() && $ancestors->last()->parent_id !== null)
        {
            $parent = $this->where('id', '=', $ancestors->last()->parent_id)->get();
            $ancestors = $ancestors->merge($parent);
        }
        return $ancestors;
    }

    public function descendants()
    {
        $descendants = $this->where('parent_id', '=', $this->id)->get();

        foreach($descendants as $descendant)
            $descendants = $descendants->merge($descendant->descendants());

        return $descendants;
    }
}
