<?php

namespace App\Http\Requests\Auth;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Support\Facades\Cache;

class VerifyEmailRequest extends FormRequest
{
    /**
     * Determine if the user is authorized to make this request.
     */
    public function authorize(): bool
    {
        return true;
    }

    private function getCachedToken(): ?string
    {
        return Cache::get("EMAIL_VERIFICATION_$this->email");
    }

    /**
     * Get the validation rules that apply to the request.
     *
     * @return array<string, \Illuminate\Contracts\Validation\ValidationRule|array<mixed>|string>
     */
    public function rules(): array
    {
        return [
            'email' => 'required|email|exists:users,email',
            'code' => ['required', 'string', 'size:6', function ($attr, $val, $fail) {
                if (!$this->email || $val !== $this->getCachedToken()) {
                    return $fail('Invalid verification code');
                }
            }],
        ];
    }
}
