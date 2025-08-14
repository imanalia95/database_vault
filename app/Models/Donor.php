<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Donor extends Model
{
    use HasFactory;

    protected $fillable = [
        'donor_phonenum',
        'phone_validation_status',
        'donor_name',
        'donor_email',
        'donor_status',
    ];

    /**
     * Validate Malaysian phone number format
     * Requirements: +60 prefix, 11-13 characters total, numeric after removing +
     * Malaysian mobile format: +60 + 1 + 8 digits = 12 characters total
     */
    public static function validatePhoneNumber($phoneNumber)
    {
        // Remove any whitespace
        $phone = trim($phoneNumber);
        
        // Check if starts with +60
        if (substr($phone, 0, 3) !== '+60') {
            return false;
        }
        
        // Check if length is between 11 and 13 characters (allowing some flexibility)
        $length = strlen($phone);
        if ($length < 11 || $length > 13) {
            return false;
        }
        
        // Check if the rest is numeric (after removing +)
        $numericPart = substr($phone, 1); // Remove the + sign
        if (!is_numeric($numericPart)) {
            return false;
        }
        
        // For Malaysian numbers, the most common format is +601XXXXXXXXX (12 characters)
        // But we'll be lenient and accept any +60 number with 11-13 digits
        
        return true;
    }

    /**
     * Set phone number and automatically validate it
     */
    public function setPhoneNumberAttribute($value)
    {
        $this->attributes['donor_phonenum'] = $value;
        $this->attributes['phone_validation_status'] = self::validatePhoneNumber($value) ? 'valid' : 'invalid';
    }

    /**
     * Get phone validation status
     */
    public function getPhoneValidationStatusAttribute($value)
    {
        // If status is not set, validate current phone number
        if (empty($value) && !empty($this->donor_phonenum)) {
            $isValid = self::validatePhoneNumber($this->donor_phonenum);
            $this->update(['phone_validation_status' => $isValid ? 'valid' : 'invalid']);
            return $isValid ? 'valid' : 'invalid';
        }
        return $value;
    }

    public function clients()
    {
        return $this->belongsToMany(Client::class, 'client_donor')
                    ->withPivot('label_id', 'added_at')
                    ->withTimestamps();
    }

    public function labels()
    {
        return $this->belongsToMany(Label::class, 'client_donor')
                    ->withPivot('client_id', 'added_at')
                    ->withTimestamps();
    }

    public function clientDonors()
    {
        return $this->hasMany(ClientDonor::class);
    }
} 