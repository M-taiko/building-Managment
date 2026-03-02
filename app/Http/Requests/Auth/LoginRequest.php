<?php

namespace App\Http\Requests\Auth;

use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class LoginRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'tenant_code' => ['nullable', 'string'],
            'email' => ['required', 'string', 'email'],
            'password' => ['required', 'string'],
        ];
    }

    /**
     * Attempt to authenticate the request's credentials.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function authenticate(): void
    {
        $this->ensureIsNotRateLimited();

        if (! Auth::attempt($this->only('email', 'password'), $this->boolean('remember'))) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => 'البريد الإلكتروني أو كلمة المرور غير صحيحة.',
            ]);
        }

        // Check tenant code for non-super admins
        $user = Auth::user();
        $tenantCode = $this->input('tenant_code');

        // If tenant_code provided, validate it
        if ($tenantCode) {
            $tenant = \App\Models\Tenant::where('tenant_code', $tenantCode)->first();

            if (!$tenant) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'tenant_code' => 'رقم العمارة غير صحيح.',
                ]);
            }

            if ($user->role !== 'super_admin' && $user->tenant_id !== $tenant->id) {
                Auth::logout();
                throw ValidationException::withMessages([
                    'tenant_code' => 'أنت لا تملك صلاحية الدخول لهذه العمارة.',
                ]);
            }
        } else {
            // No tenant code provided - must be super admin
            if ($user->role !== 'super_admin') {
                Auth::logout();
                throw ValidationException::withMessages([
                    'tenant_code' => 'يجب إدخال رقم العمارة.',
                ]);
            }
        }

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Ensure the login request is not rate limited.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function ensureIsNotRateLimited(): void
    {
        if (! RateLimiter::tooManyAttempts($this->throttleKey(), 5)) {
            return;
        }

        event(new Lockout($this));

        $seconds = RateLimiter::availableIn($this->throttleKey());

        throw ValidationException::withMessages([
            'email' => trans('auth.throttle', [
                'seconds' => $seconds,
                'minutes' => ceil($seconds / 60),
            ]),
        ]);
    }

    /**
     * Get the rate limiting throttle key for the request.
     */
    public function throttleKey(): string
    {
        return Str::transliterate(Str::lower($this->string('email')).'|'.$this->ip());
    }
}
