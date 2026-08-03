<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\StoreTeamMemberRequest;
use App\Models\User;
use App\Notifications\TeamMemberInvited;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Spatie\Permission\Models\Permission;

class TeamController extends Controller
{
    public function index(Request $request): View
    {
        $company = $request->user()->company;

        $members = $company->users()->with('roles')->orderBy('created_at')->get();

        return view('tenant.team.index', compact('members'));
    }

    public function store(StoreTeamMemberRequest $request): RedirectResponse
    {
        $company = $request->user()->company;
        $temporaryPassword = Str::password(12);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($temporaryPassword),
            'company_id' => $company->id,
            'status' => 'active',
            'email_verified_at' => now(),
        ]);

        $user->assignRole('company-staff');

        try {
            $user->notify(new TeamMemberInvited($company->name, $temporaryPassword));
            $sentMessage = __('An invitation email with login details has been sent to :email.', ['email' => $user->email]);
        } catch (\Throwable $e) {
            $sentMessage = __('User created, but the invitation email could not be sent. Temporary password: :password', ['password' => $temporaryPassword]);
        }

        return back()->with('success', $sentMessage);
    }

    public function toggleStatus(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTeamMember($request, $user);

        $user->update([
            'status' => $user->status === 'active' ? 'inactive' : 'active',
        ]);

        return back()->with('success', __('Team member status updated.'));
    }

    public function destroy(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTeamMember($request, $user);

        if ($user->id === $request->user()->id) {
            return back()->with('error', __('You cannot remove yourself from the team.'));
        }

        if ($user->hasRole('company-owner')) {
            return back()->with('error', __('The company owner cannot be removed.'));
        }

        $user->delete();

        return back()->with('success', __('Team member removed.'));
    }

    /**
     * Ensure the target user belongs to the current admin's company and the action is permitted.
     */
    protected function authorizeTeamMember(Request $request, User $user): void
    {
        abort_unless($request->user()->can('manage company users'), 403);
        abort_unless($user->company_id === $request->user()->company_id, 403);
    }

    public function resetPassword(Request $request, User $user): RedirectResponse
    {
        $this->authorizeTeamMember($request, $user);

        $temporaryPassword = Str::password(12);
         
        $user->update(['password' => Hash::make($temporaryPassword)]);

        try {
            $user->notify(new TeamMemberInvited($user->company->name, $temporaryPassword));
            return back()->with('success', __('A new password ('.$temporaryPassword.') has been generated and emailed to :email.', ['email' => $user->email]));
        } catch (\Throwable $e) {
            return back()->with([
                'success' => __('New password  generated for :name.', ['name' => $user->name]),
                'revealed_password' => $temporaryPassword,
                'revealed_for' => $user->email,
            ]);
        }
    }

    public function editPermissions(Request $request, User $user): \Illuminate\Http\JsonResponse
    {
        $this->authorizeTeamMember($request, $user);
 
        // Group all available permissions by their module prefix (e.g., "easykhata.manage-customers" -> "easykhata")
        $allPermissions = Permission::where('guard_name', 'web')
            ->where('name', 'like', '%.%') // only module-style permissions, not core Abaan ones like "manage companies"
            ->get()
            ->groupBy(fn ($perm) => explode('.', $perm->name)[0]);

        $userPermissions = $user->getDirectPermissions()->pluck('name')->toArray();

        return response()->json([
            'groups' => $allPermissions->map(fn ($perms, $module) => [
                'module' => $module,
                'label' => $this->moduleLabel($module),
                'permissions' => $perms->map(fn ($p) => [
                    'name' => $p->name,
                    'label' => $this->permissionLabel($p->name),
                    'granted' => in_array($p->name, $userPermissions),
                ]),
            ])->values(),
        ]);
    }

    public function updatePermissions(Request $request, User $user): \Illuminate\Http\RedirectResponse
    {
        $this->authorizeTeamMember($request, $user);
        
        if ($user->hasRole('company-owner')) {
            return back()->with('error', __('The company owner already has full access and cannot be restricted.'));
        }

        $request->validate([
            'permissions' => 'nullable|array',
            'permissions.*' => 'string|exists:permissions,name',
        ]);

        // Only sync permissions that belong to module-style names (e.g. "easykhata.*"),
        // leaving any core Abaan-level permissions this user might have untouched.
        $modulePermissionNames = Permission::where('name', 'like', '%.%')->pluck('name')->toArray();
        $currentNonModule = $user->getDirectPermissions()->pluck('name')
            ->reject(fn ($name) => in_array($name, $modulePermissionNames))
            ->toArray();

        $newModulePermissions = $request->input('permissions', []);

        $user->syncPermissions(array_merge($currentNonModule, $newModulePermissions));

        return back()->with('success', __('Permissions updated for :name.', ['name' => $user->name]));
    }

    protected function moduleLabel(string $module): string
    {
        return match ($module) {
            'easykhata' => __('EasyKhata (Ledger)'),
            default => ucfirst($module),
        };
    }

    protected function permissionLabel(string $permission): string
    {
        $labels = [
            'easykhata.view-dashboard' => __('View Dashboard'),
            'easykhata.manage-customers' => __('Manage Customers'),
            'easykhata.manage-suppliers' => __('Manage Suppliers'),
            'easykhata.manage-transactions' => __('Manage Transactions'),
            'easykhata.manage-categories' => __('Manage Categories & Payment Methods'),
            'easykhata.view-reports' => __('View Reports'),
            'easykhata.manage-reminders' => __('Manage Reminders'),
        ];

        return $labels[$permission] ?? ucfirst(str_replace(['.', '-', '_'], ' ', $permission));
    }
}