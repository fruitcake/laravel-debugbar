<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Requests;

use Illuminate\Foundation\Http\FormRequest;

class OpenHandlerRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'op' => ['nullable', 'string'],
        ];
    }
}
