<?php

namespace App\Http\Controllers\Concerns;

trait HasSalarieScope
{
    protected function salarie(): ?\App\Models\User
    {
        $user = auth()->user();
        return $user->isSalarie() ? $user : null;
    }

    protected function checkOwnership(mixed $model): void
    {
        if ($sal = $this->salarie()) {
            if ($model->employe_id !== $sal->employe_id) abort(403);
        }
    }
}
