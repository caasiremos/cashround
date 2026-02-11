<?php

namespace App\Repositories;

use App\Models\MomoTransaction;

class MomoTransactionRepository
{
    public function all()
    {
        return MomoTransaction::all();
    }
}