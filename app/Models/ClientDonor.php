<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ClientDonor extends Model
{
    use HasFactory;

    protected $table = 'client_donor';

    protected $fillable = [
        'client_id',
        'donor_id',
        'label_id',
        'added_at',
    ];

    protected $casts = [
        'added_at' => 'datetime',
    ];

    public function client()
    {
        return $this->belongsTo(Client::class);
    }

    public function donor()
    {
        return $this->belongsTo(Donor::class);
    }

    public function label()
    {
        return $this->belongsTo(Label::class);
    }
} 