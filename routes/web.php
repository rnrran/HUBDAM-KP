<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use Illuminate\Support\Facades\Auth;
use App\Models\Payroll;

Route::get('/', function () {
    return redirect()->route('login');
})->name('home');

Route::get('dashboard', function () {
    $user = Auth::user();
    $payrolls = Payroll::with('user')
        ->where('user_id', $user->id)
        ->select([
            'id','user_id','gaji_bersih','total_deductions','net_salary','tanggal_dibayarkan',
            'mdr_bws_bri','btn','twp','persit','ikka_persit','koperasi','barak','kowad','titipan','tenes','remaja','galon','sosial','pns','bel_wajib_kop',
            'custom_1_name','custom_1_value','custom_2_name','custom_2_value','custom_3_name','custom_3_value','custom_4_name','custom_4_value','custom_5_name','custom_5_value',
        ])
        ->orderBy('tanggal_dibayarkan','desc')
        ->paginate(10);

    // Build chart data (last 24 by date asc)
    $allForChart = Payroll::where('user_id', $user->id)
        ->orderBy('tanggal_dibayarkan','asc')
        ->take(24)
        ->get(['gaji_bersih','total_deductions','net_salary','tanggal_dibayarkan']);

    $chartData = $allForChart->map(function ($p) {
        return [
            'month' => $p->tanggal_dibayarkan->format('M Y'),
            'gaji_bersih' => (float)$p->gaji_bersih,
            'total_potongan' => (float)$p->total_deductions,
            'gaji_bersih_setelah_potongan' => (float)$p->net_salary,
            'date' => $p->tanggal_dibayarkan->toDateString(),
        ];
    });

    return Inertia::render('Dashboard', [
        'payrolls' => $payrolls,
        'chartData' => $chartData,
        'me' => [
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'pangkat' => $user->pangkat ?? null,
            'nomor_registrasi' => $user->nomor_registrasi ?? null,
        ],
    ]);
})->middleware(['auth', 'verified'])->name('dashboard');

require __DIR__.'/settings.php';
require __DIR__.'/auth.php';
require __DIR__.'/admin.php';
