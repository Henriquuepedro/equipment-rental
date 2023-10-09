<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class PlanPayment extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'id_transaction',
        'link_billet',
        'barcode_billet',
        'date_of_expiration',
        'key_pix',
        'base64_key_pix',
        'payment_method_id',
        'payment_type_id',
        'plan',
        'type_payment',
        'plan_id',
        'status_detail',
        'installments',
        'status',
        'gross_amount',
        'net_amount',
        'client_amount',
        'company_id',
        'user_created',
        'user_updated'
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

    public function insert($data)
    {
        return $this->create($data);
    }
}
