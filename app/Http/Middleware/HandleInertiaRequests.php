<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Inertia\Middleware;
use App\Models\Employee;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        $user = $request->user();

        $auth = ['user' => null];

        if ($user) {
            $roles = $user->roles()->pluck('name')->toArray();
            $permissions = $user->roles()->with('permissions')->get()
                ->flatMap(function ($role) { return $role->permissions->pluck('name'); })
                ->unique()
                ->values()
                ->toArray();

            // Get employee id if this user is linked to an employee record
            $employeeId = Employee::where('user_id', $user->id)->value('id');

            $auth = [
                'user' => array_merge($user->toArray(), [
                    'roles' => $roles,
                    'permissions' => $permissions,
                    'employee_id' => $employeeId,
                ])
            ];
        }

        return [
            ...parent::share($request),
            'auth' => $auth,
        ];
    }
}
