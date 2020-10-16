<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'type', 'name', 'fantasy', 'email', 'phone_1', 'phone_2', 'cpf', 'cnpj', 'rg', 'ie'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [];

    public function getClients()
    {
        return $this->orderBy('id', 'desc')->get();
    }

    public function getCountClients()
    {
        return $this->count();
    }
}
