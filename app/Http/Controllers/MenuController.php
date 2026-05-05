<?php

namespace App\Http\Controllers;

use App\Models\Menu;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MenuController extends Controller
{
    public function getAllMenu(Request $request)
    {
        try {
            $menus = Menu::with('childs')->whereNull('parent')->where('is_active', 1)->orderBy('ordering', 'asc')->get();
            $response = [];
            $user = Auth::user();
            if ($user->is_super == 0) {
                $perms = explode(',', $user->permission);
                foreach ($menus as $menu) {
                    if (in_array($menu->id, $perms)) {
                        $response[] = [
                            'id' => $menu->id,
                            'parent' => $menu->parent,
                            'label' => $menu->label,
                            'key' => $menu->key,
                            'icon' => $menu->icon,
                            'url' => $menu->url,
                            'ordering' => $menu->ordering,
                            'childs' => $this->getChilds($menu, $perms)
                        ];
                    }
                }
            } else {
                $response = $menus;
            }

            return successHandler($response);
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    private function getChilds($menu, $perms)
    {
        $response = [];
        foreach ($menu->childs as $chil) {
            if (in_array($chil->id, $perms)) {
                $response[] = $chil;
            }
        }
        return $response;
    }
}
