<?php

namespace App\Models\Access\User\Traits\Relationship;

use App\Models\Access\User\SocialLogin;
use Illuminate\Support\Collection;

/**
 * Class UserRelationship.
 */
trait UserRelationship
{
    /**
     * Many-to-Many relations with Role.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function roles()
    {
        return $this->belongsToMany(config('access.role'), config('access.role_user_table'), 'user_id', 'role_id')
            ->withTimestamps()
            ->withPivot('granted');;
    }


    /**
     * Get only Granted Roles
     */
    public function grantedRoles() {
        return $this->roles()->wherePivot('granted', true);
    }

    /**
     * Get only Denied Roles
     */
    public function deniedRoles() {
        return $this->roles()->wherePivot('granted', false);
    }

    /**
     * Get only Granted Permissions
     */
    public function grantedPermissions() {
        return $this->userPermissions()->wherePivot('granted', true);
    }

    /**
     * Get only Denied Permissions
     */
    public function deniedPermissions() {
        return $this->userPermissions()->wherePivot('granted', false);
    }

    /**
     * Get all roles as collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getRoles()
    {
        if(!$this->roles){

            $this->roles = $this->grantedRoles()->get();

            $deniedRoles = $this->deniedRoles()->get();
            foreach($deniedRoles as $role)
                $deniedRoles = $deniedRoles->merge($role->descendants());

            foreach($this->roles as $role)
                if(!$deniedRoles->contains($role))
                    $this->roles = $this->roles->merge($role->descendants());

            $this->roles = $this->roles->filter(function($role) use ($deniedRoles){
                return !$deniedRoles->contains($role);
            });
        }
        return  $this->roles;
    }
    /**
     * Get all permissions from roles.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function rolePermissions()
    {

        $permissions = new Collection();
        foreach ($this->getRoles() as $role)
            $permissions = $permissions->merge($role->permissions);
        return $permissions;
    }


    /**
     * Get all permissions as collection.
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getPermissions()
    {
        if(!$this->permissions){
            $rolePermissions = $this->rolePermissions();
            $userPermissions = $this->grantedPermissions()->get();

            $permissions = $rolePermissions->merge($userPermissions);
            $deniedPermissions =$this->deniedPermissions()->get();

            $this->permissions = $permissions->filter(function($permission) use ($deniedPermissions)
            {
                return !$deniedPermissions->contains($permission);
            });
        }
        return $this->permissions;
    }


    /**
     * User belongs to many permissions.
     *
     * @return \Illuminate\Database\Eloquent\Relations\BelongsToMany
     */
    public function userPermissions()
    {
        return $this->belongsToMany(config('access.permission'))->withTimestamps()->withPivot('granted');
    }


    /**
     * @return mixed
     */
    public function providers()
    {
        return $this->hasMany(SocialLogin::class);
    }
}
