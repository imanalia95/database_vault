<?php

namespace App\Http\Controllers;

use App\Models\Donor;
use App\Models\Label;
use App\Models\Client;
use App\Models\ClientDonor;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DonorController extends Controller
{
    public function index(Request $request)
    {
        $query = Donor::with(['clientDonors.client', 'clientDonors.label']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_phonenum', 'like', "%{$search}%");
                // Also search with '+' prefix if search doesn't have it and starts with '60'
                if (!str_starts_with($search, '+') && str_starts_with($search, '60')) {
                    $q->orWhere('donor_phonenum', 'like', "%+{$search}%");
                }
                // Also search without '+' prefix if search has it
                if (str_starts_with($search, '+')) {
                    $q->orWhere('donor_phonenum', 'like', "%" . substr($search, 1) . "%");
                }
            });
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        // Filter by label
        if ($request->filled('label_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('label_id', $request->label_id);
            });
        }

        $donors = $query->paginate(15);
        $labels = Label::all();
        $clients = Client::all();

        return view('donors.index', compact('donors', 'labels', 'clients'));
    }

    public function create()
    {
        $labels = Label::all();
        $clients = Client::all();
        return view('donors.create', compact('labels', 'clients'));
    }

    public function show(Donor $donor)
    {
        $donor->load('clientDonors.client', 'clientDonors.label');
        $clients = Client::all();
        $labels = Label::all();
        return view('donors.show', compact('donor', 'clients', 'labels'));
    }

    public function store(Request $request)
    {
        $request->validate([
            'donor_name' => 'required|string|max:255',
            'donor_phonenum' => 'required|string|max:20|unique:donors',
            'donor_email' => 'nullable|email|max:255',
            'donor_status' => 'required|in:active,inactive',
            'client_assignments' => 'array',
            'client_assignments.*.client_id' => 'required|exists:clients,id',
            'client_assignments.*.label_ids' => 'array',
            'client_assignments.*.label_ids.*' => 'exists:labels,id',
        ]);

        $donor = Donor::create([
            'donor_name' => $request->donor_name,
            'donor_phonenum' => $request->donor_phonenum,
            'donor_email' => $request->donor_email,
            'donor_status' => $request->donor_status,
            'phone_validation_status' => Donor::validatePhoneNumber($request->donor_phonenum) ? 'valid' : 'invalid',
        ]);

        // Handle client-label assignments
        if ($request->has('client_assignments')) {
            foreach ($request->client_assignments as $assignment) {
                if (!empty($assignment['label_ids'])) {
                    foreach ($assignment['label_ids'] as $labelId) {
                        ClientDonor::create([
                            'client_id' => $assignment['client_id'],
                            'donor_id' => $donor->id,
                            'label_id' => $labelId,
                            'added_at' => now(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('donors.index')->with('success', 'Donor created successfully.');
    }

    public function edit(Donor $donor)
    {
        $labels = Label::all();
        $clients = Client::all();
        $donor->load('clientDonors.client', 'clientDonors.label');
        
        // Group client assignments for easier handling in the form
        $clientAssignments = $donor->clientDonors->groupBy('client_id')->map(function ($assignments) {
            return [
                'client_id' => $assignments->first()->client_id,
                'label_ids' => $assignments->pluck('label_id')->toArray()
            ];
        })->values();

        return view('donors.edit', compact('donor', 'labels', 'clients', 'clientAssignments'));
    }

    public function update(Request $request, Donor $donor)
    {
        $request->validate([
            'donor_name' => 'required|string|max:255',
            'donor_phonenum' => 'required|string|max:20|unique:donors,donor_phonenum,' . $donor->id,
            'donor_email' => 'nullable|email|max:255',
            'donor_status' => 'required|in:active,inactive',
            'client_assignments' => 'array',
            'client_assignments.*.client_id' => 'required|exists:clients,id',
            'client_assignments.*.label_ids' => 'array',
            'client_assignments.*.label_ids.*' => 'exists:labels,id',
        ]);

        $donor->update([
            'donor_name' => $request->donor_name,
            'donor_phonenum' => $request->donor_phonenum,
            'donor_email' => $request->donor_email,
            'donor_status' => $request->donor_status,
            'phone_validation_status' => Donor::validatePhoneNumber($request->donor_phonenum) ? 'valid' : 'invalid',
        ]);

        // Clear existing assignments and create new ones
        $donor->clientDonors()->delete();

        if ($request->has('client_assignments')) {
            foreach ($request->client_assignments as $assignment) {
                if (!empty($assignment['label_ids'])) {
                    foreach ($assignment['label_ids'] as $labelId) {
                        ClientDonor::create([
                            'client_id' => $assignment['client_id'],
                            'donor_id' => $donor->id,
                            'label_id' => $labelId,
                            'added_at' => now(),
                        ]);
                    }
                }
            }
        }

        return redirect()->route('donors.index')->with('success', 'Donor updated successfully.');
    }

    public function destroy(Donor $donor)
    {
        try {
            $donor->delete();
            
            if (request()->ajax()) {
                return response()->json(['success' => true, 'message' => 'Donor deleted successfully']);
            }
            
            return redirect()->route('donors.index')->with('success', 'Donor deleted successfully.');
        } catch (\Exception $e) {
            if (request()->ajax()) {
                return response()->json(['success' => false, 'message' => 'Error deleting donor: ' . $e->getMessage()]);
            }
            
            return redirect()->route('donors.index')->with('error', 'Error deleting donor: ' . $e->getMessage());
        }
    }

    public function bulkDelete(Request $request)
    {
        // Always redirect back to donor list, even if nothing is selected
        if (!$request->has('donor_ids')) {
            return redirect()->route('donors.index')->with('error', 'No donors selected for deletion.');
        }

        $request->validate([
            'donor_ids' => 'required|array',
            'donor_ids.*' => 'exists:donors,id',
        ]);

        Donor::whereIn('id', $request->donor_ids)->delete();

        return redirect()->route('donors.index')->with('success', 'Selected donors deleted successfully.');
    }

    public function bulkDeleteAjax(Request $request)
    {
        $ids = $request->input('donor_ids', []);
        if (empty($ids)) {
            return response()->json(['success' => false, 'message' => 'No donors selected.']);
        }
        Donor::whereIn('id', $ids)->delete();
        return response()->json(['success' => true, 'message' => 'Selected donors deleted.']);
    }

    public function importForm()
    {
        $clients = Client::all();
        return view('donors.import', compact('clients'));
    }

    public function import(Request $request)
    {
        $request->validate([
            'file' => 'required|file|mimes:xlsx,xls,csv|mimetypes:text/plain,text/csv,application/csv,text/comma-separated-values,application/vnd.ms-excel,application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
            'client_id' => 'required|exists:clients,id',
        ]);

        try {
            $file = $request->file('file');
            $clientId = $request->client_id;
            $importedCount = 0;
            $skippedCount = 0;
            $errors = [];

            // Read the file based on its type
            if ($file->getClientOriginalExtension() === 'csv') {
                $data = $this->parseCSV($file);
            } else {
                $data = $this->parseExcel($file);
            }

            // Full import logic (existing functionality)
            $missingLabels = [];

            // First pass: collect all unique labels from Excel and validate they exist
            $excelLabels = [];
            foreach ($data as $index => $row) {
                if ($index === 0) continue; // Skip header
                $labelName = trim($row[0] ?? ''); // 1st column - Label
                if (!empty($labelName) && !in_array($labelName, $excelLabels)) {
                    $excelLabels[] = $labelName;
                }
            }

            // Check if all labels exist in database
            foreach ($excelLabels as $labelName) {
                $label = Label::where('label_name', $labelName)->first();
                if (!$label) {
                    $missingLabels[] = $labelName;
                }
            }

            // If any labels are missing, return error
            if (!empty($missingLabels)) {
                $missingLabelsList = implode(', ', array_unique($missingLabels));
                return back()->with('error', "Import failed: The following labels do not exist in the database: {$missingLabelsList}. Please create them first.");
            }

            // Second pass: import donors
            foreach ($data as $index => $row) {
                try {
                    if ($index === 0) continue; // Skip header
                    $labelName = trim($row[0] ?? ''); // 1st column - Label
                    $phoneNumber = trim($row[1] ?? ''); // 2nd column - Phone Number
                    $contactName = trim($row[2] ?? ''); // 3rd column - Contact Name (ignored)
                    $whatsappName = trim($row[3] ?? ''); // 4th column - WhatsApp Name (used as donor name)
                    $country = trim($row[4] ?? ''); // 5th column - Country (ignored)

                    if (empty($phoneNumber)) {
                        $skippedCount++;
                        continue;
                    }

                    $donorName = !empty($whatsappName) ? $whatsappName : (!empty($contactName) ? $contactName : 'Unknown');
                    $currentLabelId = null;
                    if (!empty($labelName)) {
                        $label = Label::where('label_name', $labelName)->first();
                        $currentLabelId = $label ? $label->id : null;
                    }

                    // Create new donor
                    $donor = Donor::create([
                        'donor_name' => $donorName,
                        'donor_phonenum' => $phoneNumber,
                        'donor_email' => null, // No email in this format
                        'donor_status' => 'active',
                        'phone_validation_status' => Donor::validatePhoneNumber($phoneNumber) ? 'valid' : 'invalid',
                    ]);
                    // Create client assignment with label if provided
                    if ($currentLabelId) {
                        ClientDonor::create([
                            'client_id' => $clientId,
                            'donor_id' => $donor->id,
                            'label_id' => $currentLabelId,
                            'added_at' => now(),
                        ]);
                    }
                    $importedCount++;
                } catch (\Exception $e) {
                    $errors[] = "Row " . ($index + 1) . ": " . $e->getMessage();
                }
            }

            $message = "Import completed. Imported: {$importedCount}, Skipped: {$skippedCount}";
            if (!empty($errors)) {
                $message .= ". Errors: " . implode(', ', $errors);
            }

            return redirect()->route('donors.index')->with('success', $message);
        } catch (\Exception $e) {
            \Log::error('Import error: ' . $e->getMessage());
            return back()->with('error', 'Error importing file: ' . $e->getMessage());
        }
    }

    private function parseCSV($file)
    {
        $data = [];
        $handle = fopen($file->getPathname(), 'r');
        
        while (($row = fgetcsv($handle)) !== false) {
            // Clean and encode each cell properly
            $cleanRow = [];
            foreach ($row as $cell) {
                // Clean the data without relying on auto-detection
                $cleanCell = trim($cell);
                $cleanCell = preg_replace('/[^\x20-\x7E]/', '', $cleanCell); // Remove non-printable characters
                $cleanRow[] = $cleanCell;
            }
            $data[] = $cleanRow;
        }
        
        fclose($handle);
        return $data;
    }

    private function parseExcel($file)
    {
        // Simple Excel parsing using PHPSpreadsheet if available, otherwise fallback
        try {
            // Try to use PHPSpreadsheet if it's available
            if (class_exists('\PhpOffice\PhpSpreadsheet\IOFactory')) {
                $spreadsheet = \PhpOffice\PhpSpreadsheet\IOFactory::load($file->getPathname());
                $worksheet = $spreadsheet->getActiveSheet();
                $data = [];
                
                foreach ($worksheet->getRowIterator() as $row) {
                    $rowData = [];
                    foreach ($row->getCellIterator() as $cell) {
                        $value = $cell->getValue();
                        
                        // Force phone numbers to be treated as text to avoid scientific notation
                        if ($cell->getColumn() === 'B') { // Assuming phone number is in column B
                            $value = (string)$value; // Convert to string to preserve all digits
                        }
                        
                        // Clean the cell value without relying on auto-detection
                        $cleanValue = trim($value);
                        $cleanValue = preg_replace('/[^\x20-\x7E]/', '', $cleanValue); // Remove non-printable characters
                        $rowData[] = $cleanValue;
                    }
                    $data[] = $rowData;
                }
                
                return $data;
            }
        } catch (\Exception $e) {
            \Log::warning('PHPSpreadsheet not available, using fallback method');
        }

        // Fallback: Try to read as CSV even if it's Excel
        return $this->parseCSV($file);
    }

    public function assignLabels(Request $request, Donor $donor)
    {
        if ($request->isMethod('GET')) {
            $clientId = $request->get('client_id');
            $labels = $donor->clientDonors()
                           ->where('client_id', $clientId)
                           ->pluck('label_id')
                           ->toArray();
            
            return response()->json(['labels' => $labels]);
        }

        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'label_ids' => 'array',
        ]);

        // Remove existing assignments for this client-donor pair
        $donor->clientDonors()
              ->where('client_id', $request->client_id)
              ->delete();

        // Create new assignments
        if ($request->has('label_ids')) {
            foreach ($request->label_ids as $labelId) {
                ClientDonor::create([
                    'client_id' => $request->client_id,
                    'donor_id' => $donor->id,
                    'label_id' => $labelId,
                    'added_at' => now(),
                ]);
            }
        }

        return response()->json(['success' => true]);
    }

    /**
     * Remove donor from a specific client/label combination.
     * Only deletes the donor entirely if no relationships remain.
     */
    public function removeFromClient(Request $request, Donor $donor)
    {
        $request->validate([
            'client_id' => 'required|exists:clients,id',
            'label_id' => 'required|exists:labels,id',
        ]);

        try {
            // Remove the specific client/label relationship
            $removed = $donor->clientDonors()
                ->where('client_id', $request->client_id)
                ->where('label_id', $request->label_id)
                ->delete();

            if ($removed > 0) {
                // Check if donor has any remaining relationships
                $remainingRelationships = $donor->clientDonors()->count();
                
                if ($remainingRelationships === 0) {
                    // No more relationships, delete the donor entirely
                    $donor->delete();
                    $message = 'Donor removed from client/label and deleted entirely (no more relationships).';
                } else {
                    $message = 'Donor removed from client/label successfully.';
                }

                return response()->json([
                    'success' => true,
                    'message' => $message,
                    'remaining_relationships' => $remainingRelationships
                ]);
            } else {
                return response()->json([
                    'success' => false,
                    'message' => 'Relationship not found.'
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error removing donor from client: ' . $e->getMessage()
            ]);
        }
    }

    public function blastingDownloadForm()
    {
        $labels = Label::all();
        return view('donors.blasting_download', compact('labels'));
    }

    public function blastingDownloadExport(Request $request)
    {
        $request->validate([
            'label_id' => 'required|exists:labels,id',
        ]);

        $label = Label::findOrFail($request->label_id);
        $donors = $label->clientDonors()->with('donor')->get()->pluck('donor')->unique('donor_phonenum');

        // Prepare data for Excel
        $data = [
            ['Donor Name', 'Phone Number', 'Email'],
        ];
        foreach ($donors as $donor) {
            $data[] = [
                $donor->donor_name,
                $donor->donor_phonenum,
                $donor->donor_email,
            ];
        }

        // Generate Excel file
        $filename = 'donors_label_' . $label->label_name . '_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://output', 'w');
        ob_start();
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show download page with filtering and selection options.
     */
    public function download(Request $request)
    {
        $query = Donor::with(['clientDonors.client', 'clientDonors.label']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_phonenum', 'like', "%{$search}%");
            });
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        // Filter by label
        if ($request->filled('label_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('label_id', $request->label_id);
            });
        }

        // Filter by phone status
        if ($request->filled('phone_status')) {
            $query->where('phone_validation_status', $request->phone_status);
        }

        // Apply limit
        $limit = $request->get('limit', '10');
        if ($limit === 'all') {
            $donors = $query->get();
        } else {
            $donors = $query->limit((int)$limit)->get();
        }

        $labels = Label::all();
        $clients = Client::all();

        return view('donors.download', compact('donors', 'labels', 'clients'));
    }

    /**
     * Export selected donors with custom options.
     */
    public function downloadExport(Request $request)
    {
        $request->validate([
            'export_type' => 'required|in:all,selected',
            'format' => 'required|in:csv,xlsx',
            'columns' => 'required|array|min:1',
            'columns.*' => 'in:label,phone,email,client,phone_status',
        ]);

        $query = Donor::with(['clientDonors.client', 'clientDonors.label']);

        // Apply filters
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('donor_phonenum', 'like', "%{$search}%");
        }

        if ($request->filled('client_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        if ($request->filled('label_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('label_id', $request->label_id);
            });
        }

        // Filter by phone status
        if ($request->filled('phone_status')) {
            $query->where('phone_validation_status', $request->phone_status);
        }

        // Apply limit
        $limit = $request->get('limit', '10');
        if ($limit !== 'all') {
            $query->limit((int)$limit);
        }

        // Get donors
        $donors = $query->get();

        // Filter by selected donors if export_type is 'selected'
        if ($request->export_type === 'selected' && $request->has('selected_donors')) {
            $selectedIds = $request->selected_donors;
            $donors = $donors->whereIn('id', $selectedIds);
        }

        // Prepare headers based on selected columns
        $headers = [];
        foreach ($request->columns as $column) {
            switch ($column) {
                case 'label':
                    $headers[] = 'Label';
                    break;
                case 'phone':
                    $headers[] = 'Phone Number';
                    break;
                case 'phone_status':
                    $headers[] = 'Phone Status';
                    break;
                case 'email':
                    $headers[] = 'Email';
                    break;
                case 'client':
                    $headers[] = 'Client';
                    break;
            }
        }

        // Prepare data
        $data = [$headers];
        foreach ($donors as $donor) {
            $row = [];
            foreach ($request->columns as $column) {
                switch ($column) {
                    case 'label':
                        $labels = $donor->clientDonors->pluck('label.label_name')->unique()->implode(', ');
                        $row[] = $labels ?: 'No labels';
                        break;
                    case 'phone':
                        $row[] = $donor->donor_phonenum;
                        break;
                    case 'phone_status':
                        $row[] = $donor->phone_validation_status;
                        break;
                    case 'email':
                        $row[] = $donor->donor_email ?: '';
                        break;
                    case 'client':
                        $clients = $donor->clientDonors->pluck('client.client_name')->unique()->implode(', ');
                        $row[] = $clients ?: 'No clients';
                        break;
                }
            }
            $data[] = $row;
        }

        // Generate filename
        $timestamp = now()->format('Ymd_His');
        $filename = "donors_export_{$timestamp}";

        // Export based on format
        if ($request->format === 'xlsx') {
            return $this->exportToXlsx($data, $filename);
        } else {
            return $this->exportToCsv($data, $filename);
        }
    }

    /**
     * Export data to XLSX format.
     */
    private function exportToXlsx($data, $filename)
    {
        // Check if PhpSpreadsheet is available
        if (class_exists('\PhpOffice\PhpSpreadsheet\Spreadsheet')) {
            $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            
            foreach ($data as $rowIndex => $row) {
                foreach ($row as $colIndex => $value) {
                    $sheet->setCellValueByColumnAndRow($colIndex + 1, $rowIndex + 1, $value);
                }
            }
            
            $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
            
            header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
            header('Content-Disposition: attachment;filename="' . $filename . '.xlsx"');
            header('Cache-Control: max-age=0');
            
            $writer->save('php://output');
            exit;
        } else {
            // Fallback to CSV if XLSX is not available
            return $this->exportToCsv($data, $filename);
        }
    }

    /**
     * Export data to CSV format.
     */
    private function exportToCsv($data, $filename)
    {
        $handle = fopen('php://output', 'w');
        ob_start();
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '.csv"');
    }

    /**
     * Export filtered donors as CSV.
     */
    public function export(Request $request)
    {
        $query = Donor::with(['clientDonors.client', 'clientDonors.label']);

        // Search functionality
        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('donor_name', 'like', "%{$search}%")
                  ->orWhere('donor_phonenum', 'like', "%{$search}%");
            });
        }

        // Filter by client
        if ($request->filled('client_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('client_id', $request->client_id);
            });
        }

        // Filter by label
        if ($request->filled('label_id')) {
            $query->whereHas('clientDonors', function($q) use ($request) {
                $q->where('label_id', $request->label_id);
            });
        }

        $donors = $query->get();

        // For the export() method (CSV export)
        $data = [
            ['Donor Name', 'Phone Number', 'Phone Status', 'Email', 'Clients', 'Labels'],
        ];
        foreach ($donors as $donor) {
            $clients = $donor->clientDonors->pluck('client.client_name')->unique()->implode(', ');
            $labels = $donor->clientDonors->pluck('label.label_name')->unique()->implode(', ');
            $data[] = [
                $donor->donor_name,
                $donor->donor_phonenum,
                $donor->phone_validation_status,
                $donor->donor_email,
                $clients,
                $labels,
            ];
        }

        // Generate CSV file
        $filename = 'donors_export_' . now()->format('Ymd_His') . '.csv';
        $handle = fopen('php://output', 'w');
        ob_start();
        foreach ($data as $row) {
            fputcsv($handle, $row);
        }
        fclose($handle);
        $csv = ob_get_clean();

        return response($csv)
            ->header('Content-Type', 'text/csv')
            ->header('Content-Disposition', 'attachment; filename="' . $filename . '"');
    }

    /**
     * Show the bulk delete by phone numbers form.
     */
    public function bulkDeleteForm(Request $request)
    {
        $labels = Label::all();
        $clients = Client::all();
        return view('donors.bulk_delete', compact('labels', 'clients'));
    }

    /**
     * Handle bulk delete by phone numbers (full delete or remove from client/label).
     */
    public function bulkDeletePhones(Request $request)
    {
        $request->validate([
            'phone_numbers' => 'required|string',
            'delete_type' => 'required|in:full,relationship',
            'client_id' => 'nullable|exists:clients,id',
            'label_id' => 'nullable|exists:labels,id',
        ]);

        // Parse phone numbers (split by line, comma, or space)
        $phones = preg_split('/[\s,]+/', trim($request->phone_numbers));
        $phones = array_filter($phones, fn($p) => !empty($p));
        $phones = array_unique($phones);

        if (empty($phones)) {
            return response()->json([
                'success' => false,
                'message' => 'No valid phone numbers provided.'
            ]);
        }

        // Find matching donors
        $donors = Donor::whereIn('donor_phonenum', $phones)->get();
        
        // Also search for phone numbers with '+' prefix if they don't have it
        $phonesWithPlus = [];
        foreach ($phones as $phone) {
            if (!str_starts_with($phone, '+') && str_starts_with($phone, '60')) {
                $phonesWithPlus[] = '+' . $phone;
            }
        }
        
        if (!empty($phonesWithPlus)) {
            $donorsWithPlus = Donor::whereIn('donor_phonenum', $phonesWithPlus)->get();
            $donors = $donors->merge($donorsWithPlus);
        }
        
        // Create a mapping of original input to found phone numbers for proper not_found calculation
        $inputToFoundMapping = [];
        foreach ($phones as $inputPhone) {
            $foundPhone = null;
            // Check exact match first
            if ($donors->where('donor_phonenum', $inputPhone)->count() > 0) {
                $foundPhone = $inputPhone;
            }
            // Check with '+' prefix
            elseif (!str_starts_with($inputPhone, '+') && str_starts_with($inputPhone, '60')) {
                $plusPhone = '+' . $inputPhone;
                if ($donors->where('donor_phonenum', $plusPhone)->count() > 0) {
                    $foundPhone = $plusPhone;
                }
            }
            // Check without '+' prefix
            elseif (str_starts_with($inputPhone, '+')) {
                $noPlusPhone = substr($inputPhone, 1);
                if ($donors->where('donor_phonenum', $noPlusPhone)->count() > 0) {
                    $foundPhone = $noPlusPhone;
                }
            }
            
            if ($foundPhone) {
                $inputToFoundMapping[$inputPhone] = $foundPhone;
            }
        }
        
        $foundPhones = array_values($inputToFoundMapping);
        $notFound = array_diff($phones, array_keys($inputToFoundMapping));

        // Debug: Let's also check what's actually in the database
        $allDonorPhones = Donor::pluck('donor_phonenum')->all();
        
        if ($donors->isEmpty()) {
            return response()->json([
                'success' => false,
                'message' => 'No donors found with the provided phone numbers.',
                'not_found' => array_values($phones),
                'debug' => [
                    'searched_phones' => $phones,
                    'total_donors_in_db' => count($allDonorPhones),
                    'sample_db_phones' => array_slice($allDonorPhones, 0, 5), // Show first 5 for comparison
                ]
            ]);
        }

        $deleted = [];
        $removed = [];
        $errors = [];

        foreach ($donors as $donor) {
            try {
                if ($request->delete_type === 'full') {
                    $donor->delete();
                    $deleted[] = $donor->donor_phonenum;
                } else {
                    // Remove from client/label
                    $query = $donor->clientDonors();
                    if ($request->filled('client_id')) {
                        $query->where('client_id', $request->client_id);
                    }
                    if ($request->filled('label_id')) {
                        $query->where('label_id', $request->label_id);
                    }
                    $removedCount = $query->delete();
                    if ($removedCount > 0) {
                        $removed[] = $donor->donor_phonenum;
                        // If no more relationships, delete donor
                        if ($donor->clientDonors()->count() === 0) {
                            $donor->delete();
                        }
                    }
                }
            } catch (\Exception $e) {
                $errors[] = $donor->donor_phonenum . ': ' . $e->getMessage();
            }
        }

        // Check if any operations were actually performed
        $totalOperations = count($deleted) + count($removed);
        
        if ($totalOperations === 0) {
            return response()->json([
                'success' => false,
                'message' => 'No donors were processed. All provided phone numbers may not exist or may not be associated with the selected client.',
                'not_found' => array_values($notFound),
                'errors' => $errors,
            ]);
        }

        return response()->json([
            'success' => true,
            'deleted' => array_unique($deleted),
            'removed' => array_unique($removed),
            'not_found' => array_values($notFound),
            'errors' => $errors,
        ]);
    }
} 