<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Requests;

use Illuminate\Foundation\Http\FormRequest;

class QueriesExplainRequest extends FormRequest
{
    public function rules(): array
    {
        return [
            'id' => ['required', 'string'],
            'hash' => ['required', 'string'],
            'mode' => ['nullable', 'string', 'in:explain,visual,result'],
            'format' => ['nullable', 'string'],
        ];
    }
}
