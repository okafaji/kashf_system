<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use Illuminate\View\View;
use Spatie\Permission\Models\Role;

class RegisteredUserController extends Controller
{
    /**
     * Display the registration view.
     */
    public function create(): View
    {
        return view('auth.register');
    }

    /**
     * Handle an incoming registration request.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        // إذا لم يوجد مستخدم يحمل دور admin فالمستخدم الأول يصبح admin.
        // هذا المسار آمن حتى لو جدول الأدوار فارغ (مثلاً أثناء الاختبارات).
        $hasAdminUsers = User::query()
            ->whereHas('roles', fn ($query) => $query->where('name', 'admin'))
            ->exists();

        $defaultRole = $hasAdminUsers ? 'data-entry' : 'admin';
        $roleToAssign = Role::query()->where('name', $defaultRole)->first()
            ?? Role::query()->where('name', 'data-entry')->first()
            ?? Role::query()->where('name', 'admin')->first();

        if ($roleToAssign) {
            $user->assignRole($roleToAssign);
        }

        event(new Registered($user));

        Auth::login($user);

        // بدلاً من التوجه لـ dashboard، نتوجه لجدول الإيفادات
        return redirect('/payrolls');
    }
}
