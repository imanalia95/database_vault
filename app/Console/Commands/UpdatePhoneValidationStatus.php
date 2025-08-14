<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Donor;

class UpdatePhoneValidationStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'donors:update-phone-validation';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update phone validation status for all existing donors';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Updating phone validation status for all donors...');
        
        $donors = Donor::all();
        $updated = 0;
        $valid = 0;
        $invalid = 0;
        
        foreach ($donors as $donor) {
            $isValid = Donor::validatePhoneNumber($donor->donor_phonenum);
            $status = $isValid ? 'valid' : 'invalid';
            
            $donor->update(['phone_validation_status' => $status]);
            $updated++;
            
            if ($isValid) {
                $valid++;
            } else {
                $invalid++;
            }
        }
        
        $this->info("Updated {$updated} donors:");
        $this->info("- Valid: {$valid}");
        $this->info("- Invalid: {$invalid}");
        
        return 0;
    }
}
