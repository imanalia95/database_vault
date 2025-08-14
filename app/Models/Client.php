<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Client extends Model
{
    use HasFactory;

    protected $fillable = [
        'client_name',
        'client_org',
        'client_phonenum',
    ];

    public function donors()
    {
        return $this->belongsToMany(Donor::class, 'client_donor')
                    ->withPivot('label_id', 'added_at')
                    ->withTimestamps();
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'client_donor')
                    ->withPivot('donor_id', 'added_at')
                    ->withTimestamps();
    }

    public function blasts()
    {
        return $this->hasMany(Blast::class);
    }

    public function clientDonors()
    {
        return $this->hasMany(ClientDonor::class);
    }
} 