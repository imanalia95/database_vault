<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Client;
use App\Models\Donor;
use App\Models\Label;
use App\Models\ClientDonor;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // Create a user
        $user = User::create([
            'name' => 'Admin User',
            'email' => 'admin@example.com',
            'password' => bcrypt('password'),
        ]);

        // Create clients
        $clients = [
            ['client_name' => 'SRITITG', 'client_org' => 'SRITITG', 'client_phonenum' => '+1234567890'],
            ['client_name' => 'SMUDA', 'client_org' => 'SMUDA', 'client_phonenum' => '+1234567891'],
            ['client_name' => 'MANHAL', 'client_org' => 'MANHAL', 'client_phonenum' => '+1234567892'],
            ['client_name' => 'MPI', 'client_org' => 'MPI', 'client_phonenum' => '+1234567893'],
            ['client_name' => 'PDM', 'client_org' => 'PDM', 'client_phonenum' => '+1234567894'],
            ['client_name' => 'MASK', 'client_org' => 'MASK', 'client_phonenum' => '+1234567895'],
            ['client_name' => 'PTQGB', 'client_org' => 'PTQGB', 'client_phonenum' => '+1234567896'],
            ['client_name' => 'MAJAMI', 'client_org' => 'MAJAMI', 'client_phonenum' => '+1234567897'],
            ['client_name' => 'SMIDIYK', 'client_org' => 'SMIDIYK', 'client_phonenum' => '+1234567898'],
            ['client_name' => 'PTAYAS', 'client_org' => 'PTAYAS', 'client_phonenum' => '+1234567899'],
            ['client_name' => 'MTDIS', 'client_org' => 'MTDIS', 'client_phonenum' => '+1234567800'],
            ['client_name' => 'TDAT', 'client_org' => 'TDAT', 'client_phonenum' => '+1234567801'],
        ];

        foreach ($clients as $clientData) {
            Client::create([
                'client_name' => $clientData['client_name'],
                'client_org' => $clientData['client_org'],
                'client_phonenum' => $clientData['client_phonenum'],
            ]);
        }

        // Create labels
        $labels = [
            ['label_name' => 'VIP', 'label_code' => 'VIP001'],
            ['label_name' => 'Regular', 'label_code' => 'REG001'],
            ['label_name' => 'Premium', 'label_code' => 'PRE001'],
            ['label_name' => 'Standard', 'label_code' => 'STD001'],
            ['label_name' => 'Gold', 'label_code' => 'GLD001'],
            ['label_name' => 'Silver', 'label_code' => 'SLV001'],
        ];

        foreach ($labels as $labelData) {
            Label::create($labelData);
        }

        // Create some sample donors
        $donors = [
            ['donor_name' => 'John Doe', 'donor_phonenum' => '+1111111111', 'donor_email' => 'john@example.com'],
            ['donor_name' => 'Jane Smith', 'donor_phonenum' => '+2222222222', 'donor_email' => 'jane@example.com'],
            ['donor_name' => 'Bob Johnson', 'donor_phonenum' => '+3333333333', 'donor_email' => 'bob@example.com'],
        ];

        foreach ($donors as $donorData) {
            Donor::create([
                'donor_name' => $donorData['donor_name'],
                'donor_phonenum' => $donorData['donor_phonenum'],
                'donor_email' => $donorData['donor_email'],
                'donor_status' => 'active',
            ]);
        }

        // Create some sample client-donor-label relationships
        $clientDonorAssignments = [
            ['client_name' => 'SRITITG', 'donor_phone' => '+1111111111', 'label_name' => 'VIP'],
            ['client_name' => 'SMUDA', 'donor_phone' => '+1111111111', 'label_name' => 'Regular'],
            ['client_name' => 'MANHAL', 'donor_phone' => '+2222222222', 'label_name' => 'Premium'],
            ['client_name' => 'MPI', 'donor_phone' => '+3333333333', 'label_name' => 'Gold'],
        ];

        foreach ($clientDonorAssignments as $assignment) {
            $client = Client::where('client_name', $assignment['client_name'])->first();
            $donor = Donor::where('donor_phonenum', $assignment['donor_phone'])->first();
            $label = Label::where('label_name', $assignment['label_name'])->first();

            if ($client && $donor && $label) {
                ClientDonor::create([
                    'client_id' => $client->id,
                    'donor_id' => $donor->id,
                    'label_id' => $label->id,
                    'added_at' => now(),
                ]);
            }
        }
    }
}
