<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueriesExplainRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'connection' => ['required', 'string'],
            'query' => ['required', 'string'],
            'bindings' => ['nullable', 'json'],
            'hash' => ['required', 'string'],
            'mode' => ['nullable', 'string', 'in:explain,visual,result'],
            'format' => ['nullable', 'string'],
        ];
    }
}
