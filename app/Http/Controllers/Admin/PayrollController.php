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
        $payrolls = Payroll::with('user:id,name,email,pangkat')
            ->orderBy('tanggal_dibayarkan', 'desc')
            ->orderBy('created_at', 'desc')
            ->paginate(20);

        // Generate dummy chart data for now
        $chartData = $this->generateDummyChartData();

        return Inertia::render('admin/LihatGaji', [
            'payrolls' => $payrolls,
            'chartData' => $chartData,
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

        $payroll = Payroll::create($request->all());

        return redirect()->route('admin.payroll.create');
    }

    /**
     * Display the specified payroll record
     */
    public function show(Payroll $payroll)
    {
        $payroll->load('user:id,name,email,pangkat');
        
        return response()->json($payroll);
    }

    /**
     * Show payroll history for a specific user
     */
    public function userHistory($userId)
    {
        $user = User::findOrFail($userId);
        
        $payrolls = Payroll::where('user_id', $userId)
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
        $months = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun'];
        $data = [];

        foreach ($months as $month) {
            $data[] = [
                'month' => $month,
                'gaji_bersih' => rand(5000000, 8000000),
                'total_potongan' => rand(500000, 1500000),
                'gaji_bersih_setelah_potongan' => rand(4000000, 7000000),
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

        foreach ($payrolls->take(12) as $payroll) {
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
     * Get users for dropdown
     */
    public function getUsers()
    {
        $users = User::select('id', 'name', 'email', 'pangkat')
            ->orderBy('name')
            ->get();

        return response()->json($users);
    }
}