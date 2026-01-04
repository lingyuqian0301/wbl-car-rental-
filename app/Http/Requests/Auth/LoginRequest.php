<?php

namespace App\Http\Requests\Auth;

use App\Models\User;
use Illuminate\Auth\Events\Lockout;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
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

        $user = User::where('email', $this->email)->first();

        if (!$user || !$this->validatePassword($user, $this->password)) {
            RateLimiter::hit($this->throttleKey());

            throw ValidationException::withMessages([
                'email' => trans('auth.failed'),
            ]);
        }

        // Rehash plain text passwords to bcrypt for security
        if (!$this->isBcryptHash($user->password)) {
            $user->password = Hash::make($this->password);
            $user->save();
        }

        Auth::login($user, $this->boolean('remember'));

        RateLimiter::clear($this->throttleKey());
    }

    /**
     * Validate password against user's stored password (handles both bcrypt and plain text)
     */
    protected function validatePassword(User $user, string $password): bool
    {
        $storedPassword = $user->password;

        // Try bcrypt first
        try {
            if (Hash::check($password, $storedPassword)) {
                return true;
            }
        } catch (\RuntimeException $e) {
            // Password is not bcrypt format, continue to plain text check
        }

        // Check plain text (for legacy passwords)
        if ($storedPassword === $password) {
            return true;
        }

        // Check MD5 hash (for legacy passwords)
        if ($storedPassword === md5($password)) {
            return true;
        }

        return false;
    }

    /**
     * Check if password is bcrypt hash
     */
    protected function isBcryptHash(string $hash): bool
    {
        return strlen($hash) === 60 && str_starts_with($hash, '$2y$');
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
