<?php

namespace App\Http\Controllers;

use App\Models\Blast;
use App\Models\Donor;
use App\Models\Client;
use App\Models\Label;
use App\Models\User;
use Illuminate\Http\Request;

class DashboardController extends Controller
{
    public function index()
    {
        // Get basic statistics
        $totalBlasts = 1247; // Dummy high number
        $totalDonors = Donor::count();
        $totalClients = Client::count();

        // Get or create dummy user "Nur Iman Alia"
        $dummyUser = User::firstOrCreate(
            ['email' => 'nur.iman.alia@example.com'],
            [
                'name' => 'Nur Iman Alia',
                'password' => bcrypt('password'),
            ]
        );

        // Get the three specific clients
        $mtdisClient = Client::where('client_name', 'MTDIS')->first();
        $tdatClient = Client::where('client_name', 'TDAT')->first();
        $ptayasClient = Client::where('client_name', 'PTAYAS')->first();

        // Create dummy recent blasts if they don't exist
        $recentBlasts = $this->createDummyBlasts($dummyUser, $mtdisClient, $tdatClient, $ptayasClient);

        return view('dashboard', compact(
            'totalBlasts',
            'totalDonors', 
            'totalClients',
            'recentBlasts'
        ));
    }

    private function createDummyBlasts($user, $mtdisClient, $tdatClient, $ptayasClient)
    {
        // Check if we already have recent blasts
        $existingBlasts = Blast::with(['client', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();

        // Create dummy blasts if we don't have enough
        $blastData = [
            [
                'blast_name' => 'JUL: [C1]',
                'client' => $mtdisClient,
                'blast_date' => now()->subDays(2),
                'created_at' => now()->subDays(3),
            ],
            [
                'blast_name' => 'JUL: [C2]',
                'client' => $tdatClient,
                'blast_date' => now()->subDays(5),
                'created_at' => now()->subDays(6),
            ],
            [
                'blast_name' => 'JUL: [C3]',
                'client' => $ptayasClient,
                'blast_date' => now()->subDays(7),
                'created_at' => now()->subDays(8),
            ],
            [
                'blast_name' => 'JUN: [C1]',
                'client' => $mtdisClient,
                'blast_date' => now()->subDays(10),
                'created_at' => now()->subDays(11),
            ],
            [
                'blast_name' => 'JUN: [C2]',
                'client' => $tdatClient,
                'blast_date' => now()->subDays(12),
                'created_at' => now()->subDays(13),
            ],
            [
                'blast_name' => 'JUN: [C3]',
                'client' => $ptayasClient,
                'blast_date' => now()->subDays(15),
                'created_at' => now()->subDays(16),
            ],
            [
                'blast_name' => 'MAY: [C1]',
                'client' => $mtdisClient,
                'blast_date' => now()->subDays(18),
                'created_at' => now()->subDays(19),
            ],
            [
                'blast_name' => 'MAY: [C2]',
                'client' => $tdatClient,
                'blast_date' => now()->subDays(20),
                'created_at' => now()->subDays(21),
            ],
        ];

        // Clear existing blasts and create new ones with proper names
        Blast::truncate();

        foreach ($blastData as $data) {
            if ($data['client']) {
                Blast::create([
                    'blast_name' => $data['blast_name'],
                    'client_id' => $data['client']->id,
                    'user_id' => $user->id,
                    'blast_date' => $data['blast_date'],
                    'created_at' => $data['created_at'],
                    'updated_at' => $data['created_at'],
                ]);
            }
        }

        // Return the recent blasts
        return Blast::with(['client', 'user'])
            ->orderBy('created_at', 'desc')
            ->take(10)
            ->get();
    }
} 