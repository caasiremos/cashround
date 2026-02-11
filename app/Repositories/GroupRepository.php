<?php

namespace App\Repositories;

use App\Models\Group;

class GroupRepository
{
    public function all()
    {
        return Group::all();
    }
}