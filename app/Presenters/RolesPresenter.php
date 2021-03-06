<?php
/**
 * Created by PhpStorm.
 * User: lotiger
 * Date: 17-12-6
 * Time: 下午11:07
 */

namespace App\Presenters;


use Bican\Roles\Models\Permission;
use Bican\Roles\Models\Role;

class RolesPresenter extends Presenter
{
    public function getRolesList()
    {
        $data = '';
        $allRoles = Role::all();

        foreach ($allRoles as $role)
        {
            $data .=
                '<tr onclick="rediredToRole('.$role->id.')" id = "role'.$role->id.'">'
                .'<td>'
                .' <span class="glyphicon glyphicon-user  col-sm-1"></span>'
                .'<div class="col-sm-8">'.$role->name.'</div>'
                .' </td>'
                .'</tr>';
        }

        return $data;
    }

    public function getEditRole($id)
    {
        if ($id)
        {
            $role = Role::query()->find($id);
        }
        else
        {
            $role = Role::query()->first();
        }
        return $role;
    }

    public function getRoleForAddPermission($id = null)
    {
        $data = '';
        if ($id == null)
        {
            foreach (Role::all() as $role)
            {
                $data .= '<label>'
                    .'<input type="checkbox" name="roles[]" class="minimal" value="'
                    .$role->id.'"'
                    .' >'
                    .$role->name
                    .'</label>';
            }
        }
        else
        {
            $permission = Permission::query()->find($id);
            foreach (Role::all() as $role)
            {
                $data .= '<label>'
                    .'<input type="checkbox" name="roles[]" class="minimal" value="'
                    .$role->id.'"';
                if ($this->thePermissionHasOne($role->id, $permission))
                {
                    $data .= 'checked="checked"';
                }
                $data .=' >'
                    .$role->name
                    .'</label>';
            }
        }
        return $data;
    }

    public function thePermissionHasOne($role_id, Permission $permission)
    {
        foreach ($permission->roles as $role)
        {
            if ($role->id == $role_id)
                return true;
        }
        return false;
    }



}