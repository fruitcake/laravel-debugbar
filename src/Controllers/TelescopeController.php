<?php

declare(strict_types=1);

namespace Fruitcake\LaravelDebugbar\Controllers;

use Laravel\Telescope\Contracts\EntriesRepository;
use Laravel\Telescope\Storage\EntryQueryOptions;

class TelescopeController
{
    public function show(EntriesRepository $storage, $uuid)
    {
        $entry = $storage->find($uuid);
        $result = $storage->get('request', (new EntryQueryOptions())->batchId($entry->batchId))->first();

        return redirect(config('telescope.domain') . '/' . config('telescope.path') . '/requests/' . $result->id);
    }
}
