<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Payroll;
use App\Models\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Carbon\Carbon;

class PayrollController extends Controller
{
    /**
     * Display a listing of payroll records
     */
    public function index()
    {
        $payrolls = Payroll::with('user')
            ->select([
                'id', 'user_id', 'gaji_bersih', 'tanggal_dibayarkan',
                'mdr_bws_bri', 'btn', 'twp', 'persit', 'ikka_persit',
                'koperasi', 'barak', 'kowad', 'titipan', 'tenes',
                'remaja', 'galon', 'sosial', 'pns', 'bel_wajib_kop',
                'custom_1_name', 'custom_1_value', 'custom_2_name', 'custom_2_value',
                'custom_3_name', 'custom_3_value', 'custom_4_name', 'custom_4_value',
                'custom_5_name', 'custom_5_value', 'total_deductions', 'net_salary',
                'created_at', 'updated_at'
            ])
            ->orderBy('tanggal_dibayarkan', 'desc')
            ->paginate(10);

        $users = User::select('id', 'name', 'email', 'pangkat')->get();
        
        // Generate chart data based on all payrolls
        $chartData = $this->generateChartDataFromPayrolls();
        
        return Inertia::render('admin/LihatGaji', [
            'payrolls' => $payrolls,
            'users' => $users,
            'chartData' => $chartData
        ]);
    }

    /**
     * Show the form for creating a new payroll record
     */
    public function create()
    {
        $users = User::select('id', 'name', 'email', 'pangkat')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/InputGaji', [
            'users' => $users,
        ]);
    }

    /**
     * Store a newly created payroll record
     */
    public function store(Request $request)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'gaji_bersih' => 'required|numeric|min:0',
            'tanggal_dibayarkan' => 'required|date',
            'mdr_bws_bri' => 'nullable|numeric|min:0',
            'btn' => 'nullable|numeric|min:0',
            'twp' => 'nullable|numeric|min:0',
            'persit' => 'nullable|numeric|min:0',
            'ikka_persit' => 'nullable|numeric|min:0',
            'koperasi' => 'nullable|numeric|min:0',
            'barak' => 'nullable|numeric|min:0',
            'kowad' => 'nullable|numeric|min:0',
            'titipan' => 'nullable|numeric|min:0',
            'tenes' => 'nullable|numeric|min:0',
            'remaja' => 'nullable|numeric|min:0',
            'galon' => 'nullable|numeric|min:0',
            'sosial' => 'nullable|numeric|min:0',
            'pns' => 'nullable|numeric|min:0',
            'bel_wajib_kop' => 'nullable|numeric|min:0',
            'custom_1_name' => 'nullable|string|max:255',
            'custom_1_value' => 'nullable|numeric|min:0',
            'custom_2_name' => 'nullable|string|max:255',
            'custom_2_value' => 'nullable|numeric|min:0',
            'custom_3_name' => 'nullable|string|max:255',
            'custom_3_value' => 'nullable|numeric|min:0',
            'custom_4_name' => 'nullable|string|max:255',
            'custom_4_value' => 'nullable|numeric|min:0',
            'custom_5_name' => 'nullable|string|max:255',
            'custom_5_value' => 'nullable|numeric|min:0',
        ]);

        // Calculate total deductions
        $totalDeductions = 0;
        $deductionFields = [
            'mdr_bws_bri', 'btn', 'twp', 'persit', 'ikka_persit', 'koperasi',
            'barak', 'kowad', 'titipan', 'tenes', 'remaja', 'galon', 'sosial',
            'pns', 'bel_wajib_kop', 'custom_1_value', 'custom_2_value',
            'custom_3_value', 'custom_4_value', 'custom_5_value'
        ];

        foreach ($deductionFields as $field) {
            $totalDeductions += $request->input($field, 0) ?? 0;
        }

        // Calculate net salary
        $netSalary = $request->gaji_bersih - $totalDeductions;

        // Prepare data with calculated fields
        $payrollData = $request->all();
        $payrollData['total_deductions'] = $totalDeductions;
        $payrollData['net_salary'] = $netSalary;

        $payroll = Payroll::create($payrollData);

        return redirect()->route('admin.payroll.create');
    }

    /**
     * Display the specified payroll record
     */
    public function show(Payroll $payroll)
    {
        $payroll->load('user:id,name,email,pangkat');
        
        // Ensure all fields are loaded
        $payroll = Payroll::select([
            'id', 'user_id', 'gaji_bersih', 'total_deductions', 'net_salary',
            'tanggal_dibayarkan', 'mdr_bws_bri', 'btn', 'twp', 'persit',
            'ikka_persit', 'koperasi', 'barak', 'kowad', 'titipan', 'tenes',
            'remaja', 'galon', 'sosial', 'pns', 'bel_wajib_kop',
            'custom_1_name', 'custom_1_value', 'custom_2_name', 'custom_2_value',
            'custom_3_name', 'custom_3_value', 'custom_4_name', 'custom_4_value',
            'custom_5_name', 'custom_5_value', 'created_at'
        ])->with('user:id,name,email,pangkat')->find($payroll->id);
        
        return response()->json($payroll);
    }

    /**
     * Show the form for editing the specified payroll record
     */
    public function edit(Payroll $payroll)
    {
        $payroll->load('user:id,name,email,pangkat');

        $users = User::select('id', 'name', 'email', 'pangkat')
            ->orderBy('name')
            ->get();

        return Inertia::render('admin/EditGaji', [
            'users' => $users,
            'payroll' => $payroll,
        ]);
    }

    /**
     * Update the specified payroll record in storage
     */
    public function update(Request $request, Payroll $payroll)
    {
        $request->validate([
            'user_id' => 'required|exists:users,id',
            'gaji_bersih' => 'required|numeric|min:0',
            'tanggal_dibayarkan' => 'required|date',
            'mdr_bws_bri' => 'nullable|numeric|min:0',
            'btn' => 'nullable|numeric|min:0',
            'twp' => 'nullable|numeric|min:0',
            'persit' => 'nullable|numeric|min:0',
            'ikka_persit' => 'nullable|numeric|min:0',
            'koperasi' => 'nullable|numeric|min:0',
            'barak' => 'nullable|numeric|min:0',
            'kowad' => 'nullable|numeric|min:0',
            'titipan' => 'nullable|numeric|min:0',
            'tenes' => 'nullable|numeric|min:0',
            'remaja' => 'nullable|numeric|min:0',
            'galon' => 'nullable|numeric|min:0',
            'sosial' => 'nullable|numeric|min:0',
            'pns' => 'nullable|numeric|min:0',
            'bel_wajib_kop' => 'nullable|numeric|min:0',
            'custom_1_name' => 'nullable|string|max:255',
            'custom_1_value' => 'nullable|numeric|min:0',
            'custom_2_name' => 'nullable|string|max:255',
            'custom_2_value' => 'nullable|numeric|min:0',
            'custom_3_name' => 'nullable|string|max:255',
            'custom_3_value' => 'nullable|numeric|min:0',
            'custom_4_name' => 'nullable|string|max:255',
            'custom_4_value' => 'nullable|numeric|min:0',
            'custom_5_name' => 'nullable|string|max:255',
            'custom_5_value' => 'nullable|numeric|min:0',
        ]);

        // Recalculate totals
        $totalDeductions = 0;
        $deductionFields = [
            'mdr_bws_bri', 'btn', 'twp', 'persit', 'ikka_persit', 'koperasi',
            'barak', 'kowad', 'titipan', 'tenes', 'remaja', 'galon', 'sosial',
            'pns', 'bel_wajib_kop', 'custom_1_value', 'custom_2_value',
            'custom_3_value', 'custom_4_value', 'custom_5_value'
        ];

        foreach ($deductionFields as $field) {
            $totalDeductions += $request->input($field, 0) ?? 0;
        }

        $netSalary = $request->gaji_bersih - $totalDeductions;

        $data = $request->all();
        $data['total_deductions'] = $totalDeductions;
        $data['net_salary'] = $netSalary;

        $payroll->update($data);

        return redirect()->route('admin.payroll.index');
    }

    /**
     * Remove the specified payroll record from storage
     */
    public function destroy(Payroll $payroll)
    {
        $payroll->delete();
        return redirect()->route('admin.payroll.index');
    }

    /**
     * Show payroll history for a specific user
     */
    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);
        
        $payrolls = Payroll::where('user_id', $userId)
            ->select([
                'id', 'user_id', 'gaji_bersih', 'total_deductions', 'net_salary',
                'tanggal_dibayarkan', 'mdr_bws_bri', 'btn', 'twp', 'persit',
                'ikka_persit', 'koperasi', 'barak', 'kowad', 'titipan', 'tenes',
                'remaja', 'galon', 'sosial', 'pns', 'bel_wajib_kop',
                'custom_1_name', 'custom_1_value', 'custom_2_name', 'custom_2_value',
                'custom_3_name', 'custom_3_value', 'custom_4_name', 'custom_4_value',
                'custom_5_name', 'custom_5_value', 'created_at'
            ])
            ->orderBy('tanggal_dibayarkan', 'desc')
            ->get();

        // Generate chart data for this specific user
        $chartData = $this->generateUserChartData($payrolls);

        return Inertia::render('admin/LihatGaji', [
            'payrolls' => $payrolls,
            'chartData' => $chartData,
            'selectedUser' => $user,
        ]);
    }

    /**
     * Generate dummy chart data for demonstration
     */
    private function generateDummyChartData()
    {
        // Try to get actual payroll data first
        $recentPayrolls = Payroll::select([
            'gaji_bersih', 'total_deductions', 'net_salary', 'tanggal_dibayarkan'
        ])
        ->orderBy('tanggal_dibayarkan', 'desc')
        ->take(12)
        ->get();

        if ($recentPayrolls->count() > 0) {
            $data = [];
            foreach ($recentPayrolls as $payroll) {
                $data[] = [
                    'month' => $payroll->tanggal_dibayarkan->format('M Y'),
                    'gaji_bersih' => $payroll->gaji_bersih,
                    'total_potongan' => $payroll->total_deductions,
                    'gaji_bersih_setelah_potongan' => $payroll->net_salary,
                ];
            }
            return array_reverse($data);
        }

        // Fallback to dummy data if no real data exists
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
        $data = [];
        $currentYear = date('Y');

        foreach ($months as $index => $month) {
            $monthNumber = $index + 1;
            $gajiBersih = rand(5000000, 8000000);
            $totalPotongan = rand(500000, 1500000);
            $netSalary = $gajiBersih - $totalPotongan;
            
            $data[] = [
                'month' => $month . ' ' . $currentYear,
                'gaji_bersih' => $gajiBersih,
                'total_potongan' => $totalPotongan,
                'gaji_bersih_setelah_potongan' => $netSalary,
            ];
        }

        return $data;
    }

    /**
     * Generate chart data for a specific user
     */
    private function generateUserChartData($payrolls)
    {
        $data = [];

        foreach ($payrolls->take(24) as $payroll) {
            $data[] = [
                'month' => $payroll->tanggal_dibayarkan->format('M Y'),
                'gaji_bersih' => $payroll->gaji_bersih,
                'total_potongan' => $payroll->total_deductions,
                'gaji_bersih_setelah_potongan' => $payroll->net_salary,
            ];
        }

        return array_reverse($data); // Show oldest to newest
    }

    /**
     * Generate chart data for specific user and time range
     */
    public function getUserChartData($userId, $timeRange = '12')
    {
        $query = Payroll::where('user_id', $userId);
        
        // Apply time range filter
        switch ($timeRange) {
            case '4': // 4 weeks
                $query->where('tanggal_dibayarkan', '>=', now()->subWeeks(4));
                break;
            case '8': // 8 weeks
                $query->where('tanggal_dibayarkan', '>=', now()->subWeeks(8));
                break;
            case '12': // 12 weeks
                $query->where('tanggal_dibayarkan', '>=', now()->subWeeks(12));
                break;
            case '6': // 6 months
                $query->where('tanggal_dibayarkan', '>=', now()->subMonths(6));
                break;
            case '12': // 12 months
                $query->where('tanggal_dibayarkan', '>=', now()->subMonths(12));
                break;
            case '24': // 24 months
                $query->where('tanggal_dibayarkan', '>=', now()->subMonths(24));
                break;
            case 'all':
            default:
                // No time filter
                break;
        }
        
        $payrolls = $query->select([
            'gaji_bersih', 'total_deductions', 'net_salary', 'tanggal_dibayarkan'
        ])
        ->orderBy('tanggal_dibayarkan', 'asc')
        ->get();

        $data = [];
        foreach ($payrolls as $payroll) {
            $data[] = [
                'month' => $payroll->tanggal_dibayarkan->format('M Y'),
                'gaji_bersih' => $payroll->gaji_bersih,
                'total_potongan' => $payroll->total_deductions,
                'gaji_bersih_setelah_potongan' => $payroll->net_salary,
            ];
        }

        return response()->json($data);
    }

    /**
     * Get users for dropdown
     */
    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'pangkat')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }

    /**
     * Generate chart data from actual payroll records
     */
    private function generateChartDataFromPayrolls()
    {
        $payrolls = Payroll::select([
            'gaji_bersih', 'total_deductions', 'net_salary', 'tanggal_dibayarkan'
        ])
        ->orderBy('tanggal_dibayarkan', 'asc')
        ->get();

        if ($payrolls->count() === 0) {
            return $this->generateDummyChartData();
        }

        $data = [];
        foreach ($payrolls->take(24) as $payroll) {
            $data[] = [
                'month' => $payroll->tanggal_dibayarkan->format('M Y'),
                'gaji_bersih' => $payroll->gaji_bersih,
                'total_potongan' => $payroll->total_deductions,
                'gaji_bersih_setelah_potongan' => $payroll->net_salary,
            ];
        }

        return $data;
    }
}