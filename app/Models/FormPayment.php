<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class FormPayment extends Model
{
    use HasFactory;

    public function getById(int $id)
    {
        return $this->find($id);
    }
}
