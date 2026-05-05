<?php

namespace App\Http\Controllers\Setting;

use App\Http\Controllers\Controller;
use App\Models\Menu;
use App\Models\Roles;
use App\Models\User;
use Exception;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

class UserController extends Controller
{
    public function index(Request $request): Response
    {
        $menus = Menu::with('childs')->whereNull('parent')->where('is_active', 1)->orderBy('ordering', 'asc')->get();
        $roles = Roles::get(['id', 'name'])->map(function ($roles) {
            return [
                'value' => $roles->id,
                'label' => $roles->name
            ];
        });
        $response = [
            'open_key' => 'setting',
            'selected_key' => 'users',
            'menus' => $menus,
            'roles' => $roles,
        ];

        return Inertia::render('Users/Index', $response);
    }

    public function getUsers(Request $request)
    {
        try {
            $page = $request->page;
            $per_page = $request->per_page;
            $query = User::with(['dtrole'])
                ->where('role', '!=', 0);

            if ($request->filled('search')) {
                $search = strtolower($request->search);

                $query->where(function ($q) use ($search) {
                    $q->whereRaw('LOWER(email) LIKE ?', ["%{$search}%"])
                        ->orWhereRaw('LOWER(name) LIKE ?', ["%{$search}%"]);
                });
            }

            $users = $query->orderBy('id', 'desc')->paginate($per_page, ['*'], 'page', $page);

            $response = [
                'users' => $users,
                'meta' => [
                    'current_page' => $users->currentPage(),
                    'last_page' => $users->lastPage(),
                    'per_page' => $users->perPage(),
                    'total' => $users->total(),
                ]
            ];

            return successHandler($response);
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }

    public function changePermission(Request $request)
    {
        try {
            $request->validate([
                'permission' => 'required|boolean',
                'user' => 'required|exists:users,id',
                'menu' => 'required|exists:menus,id',
            ]);

            $user = User::where('id', $request->user)->first();
            if ($user->is_super) {
                throw new Exception("Dilarang menambahkan permission ke super admin", 422);
            }
            $current_perm = [];
            if ($user->permission != null || $user->permission != '') {
                $current_perm = explode(',', $user->permission);
            }

            if ($request->permission == 1) {
                $current_perm[] = $request->menu;
            } else {
                $current_perm = array_diff($current_perm, [$request->menu]);
            }

            $user->permission = implode(',', $current_perm);
            $user->saveOrFail();

            $response = [
                'current_perms' => explode(',', $user->permission)
            ];
            return successHandler($response);
        } catch (Exception $e) {
            return errorHandler($e);
        }
    }
}
