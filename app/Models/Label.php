<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Label extends Model
{
    use HasFactory;

    protected $fillable = [
        'label_name',
        'label_code',
    ];

    public function donors()
    {
        return $this->belongsToMany(Donor::class, 'client_donor')
                    ->withPivot('client_id', 'added_at')
                    ->withTimestamps();
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_donor')
                    ->withPivot('donor_id', 'added_at')
                    ->withTimestamps();
    }

    public function clientDonors()
    {
        return $this->hasMany(ClientDonor::class);
    }
} 