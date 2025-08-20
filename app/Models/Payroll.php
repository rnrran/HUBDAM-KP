<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Payroll extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'gaji_bersih',
        'tanggal_dibayarkan',
        'mdr_bws_bri',
        'btn',
        'twp',
        'persit',
        'ikka_persit',
        'koperasi',
        'barak',
        'kowad',
        'titipan',
        'tenes',
        'remaja',
        'galon',
        'sosial',
        'pns',
        'bel_wajib_kop',
        'custom_1_name',
        'custom_1_value',
        'custom_2_name',
        'custom_2_value',
        'custom_3_name',
        'custom_3_value',
        'custom_4_name',
        'custom_4_value',
        'custom_5_name',
        'custom_5_value',
    ];

    protected $casts = [
        'gaji_bersih' => 'decimal:2',
        'tanggal_dibayarkan' => 'date',
        'mdr_bws_bri' => 'decimal:2',
        'btn' => 'decimal:2',
        'twp' => 'decimal:2',
        'persit' => 'decimal:2',
        'ikka_persit' => 'decimal:2',
        'koperasi' => 'decimal:2',
        'barak' => 'decimal:2',
        'kowad' => 'decimal:2',
        'titipan' => 'decimal:2',
        'tenes' => 'decimal:2',
        'remaja' => 'decimal:2',
        'galon' => 'decimal:2',
        'sosial' => 'decimal:2',
        'pns' => 'decimal:2',
        'bel_wajib_kop' => 'decimal:2',
        'custom_1_value' => 'decimal:2',
        'custom_2_value' => 'decimal:2',
        'custom_3_value' => 'decimal:2',
        'custom_4_value' => 'decimal:2',
        'custom_5_value' => 'decimal:2',
    ];

    /**
     * Get the user that owns the payroll record
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Calculate total deductions
     */
    public function getTotalDeductionsAttribute(): float
    {
        $deductions = [
            $this->mdr_bws_bri,
            $this->btn,
            $this->twp,
            $this->persit,
            $this->ikka_persit,
            $this->koperasi,
            $this->barak,
            $this->kowad,
            $this->titipan,
            $this->tenes,
            $this->remaja,
            $this->galon,
            $this->sosial,
            $this->pns,
            $this->bel_wajib_kop,
            $this->custom_1_value,
            $this->custom_2_value,
            $this->custom_3_value,
            $this->custom_4_value,
            $this->custom_5_value,
        ];

        return array_sum(array_filter($deductions));
    }

    /**
     * Calculate net salary after deductions
     */
    public function getNetSalaryAttribute(): float
    {
        return $this->gaji_bersih - $this->total_deductions;
    }
}