<?php

declare(strict_types=1);

namespace App\Rules;

use Closure;
use Illuminate\Contracts\Validation\DataAwareRule;
use Illuminate\Contracts\Validation\ValidationRule;
use Illuminate\Support\Facades\Auth;

class ValidLoginCredentials implements DataAwareRule, ValidationRule
{
    protected $data = [];

    public function setData(array $data): static
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Run the validation rule.
     *
     * @param  \Closure(string, ?string = null): \Illuminate\Translation\PotentiallyTranslatedString  $fail
     */
    public function validate(string $attribute, mixed $value, Closure $fail): void
    {
        if (!isset($this->data['email']) || !isset($this->data['password'])) {
            $fail('Invalid login credentials');
            return;
        }

        $credentials = [
            'email' => $this->data['email'],
            'password' => $this->data['password'],
        ];

        if (! Auth::attempt($credentials)) {
            $fail('Invalid login credentials');
        }
    }
}
