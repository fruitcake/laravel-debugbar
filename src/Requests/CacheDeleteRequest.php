<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Requests;

use Illuminate\Foundation\Http\FormRequest;

class CacheDeleteRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->hasValidSignature() && debugbar()->isStorageOpen($this);
    }

    public function rules(): array
    {
        return [
            'tags' => ['sometimes', 'array'],
            'tags.*' => ['string'],
        ];
    }
}
