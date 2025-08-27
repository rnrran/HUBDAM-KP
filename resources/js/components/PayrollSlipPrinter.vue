<script setup lang="ts">
import { computed } from 'vue';
import { useNotifications } from '@/composables/useNotifications';

interface User {
    id: number;
    name: string;
    email: string;
    pangkat?: string | null;
    nomor_registrasi?: string | null;
}

interface PayrollData {
    user: {
        id: number;
        name: string;
        email: string;
        pangkat?: string | null;
        nomor_registrasi?: string | null;
    };
    gaji_bersih: number;
    tanggal_dibayarkan: string;
    total_deductions: number;
    net_salary: number;
    deductions: {
        mdr_bws_bri: number;
        btn: number;
        twp: number;
        persit: number;
        ikka_persit: number;
        koperasi: number;
        barak: number;
        kowad: number;
        titipan: number;
        tenes: number;
        remaja: number;
        galon: number;
        sosial: number;
        pns: number;
        bel_wajib_kop: number;
        custom_1: { name: string; value: number };
        custom_2: { name: string; value: number };
        custom_3: { name: string; value: number };
        custom_4: { name: string; value: number };
        custom_5: { name: string; value: number };
    };
}

interface Props {
    payrollData: PayrollData;
}

const props = defineProps<Props>();
const { success } = useNotifications();

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const printPayrollSlip = (payrollData?: PayrollData) => {
    const data = payrollData || props.payrollData;
    
    if (!data.user || !data.gaji_bersih) return;
    
    const printWindow = window.open('', '_blank');
    if (!printWindow) return;
    
    // Show success message
    success('Berhasil!', 'Struk gaji telah dibuka di tab baru. Silakan cetak atau simpan sebagai PDF.');
    
    const printContent = `
        <!DOCTYPE html>
        <html>
        <head>
            <title>Struk Gaji - ${data.user.name}</title>
            <style>
                body { 
                    font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; 
                    margin: 20px; 
                    font-size: 12px;
                    line-height: 1.4;
                    color: #333;
                }
                .header { 
                    text-align: center; 
                    border-bottom: 3px solid #2563eb; 
                    padding-bottom: 15px; 
                    margin-bottom: 25px;
                }
                .title { 
                    font-size: 20px; 
                    font-weight: bold; 
                    margin-bottom: 5px;
                    color: #1e40af;
                }
                .subtitle { 
                    font-size: 14px; 
                    color: #6b7280;
                }
                .info-section { 
                    margin-bottom: 25px; 
                    background: #f8fafc;
                    padding: 15px;
                    border-radius: 8px;
                }
                .info-row { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 10px;
                    border-bottom: 1px solid #e2e8f0;
                    padding-bottom: 8px;
                }
                .info-row:last-child {
                    border-bottom: none;
                    margin-bottom: 0;
                }
                .deductions-section { 
                    margin: 25px 0; 
                }
                .deduction-item { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 8px;
                    padding-left: 20px;
                    color: #dc2626;
                }
                .deduction-item-zero { 
                    display: flex; 
                    justify-content: space-between; 
                    margin-bottom: 8px;
                    padding-left: 20px;
                    color: #6b7280;
                    font-style: italic;
                }
                .total-section { 
                    border-top: 3px solid #2563eb; 
                    padding-top: 20px; 
                    margin-top: 25px;
                    background: #f0f9ff;
                    padding: 20px;
                    border-radius: 8px;
                }
                .total-row { 
                    display: flex; 
                    justify-content: space-between; 
                    font-weight: bold; 
                    font-size: 14px;
                    margin-bottom: 12px;
                }
                .total-row:last-child {
                    margin-bottom: 0;
                }
                .footer { 
                    text-align: center; 
                    margin-top: 40px; 
                    font-size: 10px; 
                    color: #6b7280;
                    border-top: 1px solid #e5e7eb;
                    padding-top: 15px;
                }
                @media print {
                    body { margin: 0; }
                    .no-print { display: none; }
                    .header { border-bottom-color: #000; }
                    .total-section { border-top-color: #000; background: #f0f0f0; }
                }
            </style>
        </head>
        <body>
            <div class="header">
                <div class="title">STRUK GAJI PERSONIL</div>
                <div class="subtitle">KOMLEKDAM XIV/Hasanuddin</div>
                <div class="subtitle">${new Date(data.tanggal_dibayarkan).toLocaleDateString('id-ID', { month: 'long', year: 'numeric' })}</div>
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <strong>Nama:</strong>
                    <span>${data.user.name}</span>
                </div>
                <div class="info-row">
                    <strong>Pangkat:</strong>
                    <span>${data.user.pangkat ?? 'Belum ada ...'}</span>
                </div>
                <div class="info-row">
                    <strong>Nomor Registrasi:</strong>
                    <span>${data.user.nomor_registrasi ?? 'Belum ada ...'}</span>
                </div>
                <div class="info-row">
                    <strong>Email:</strong>
                    <span>${data.user.email}</span>
                </div>
                <div class="info-row">
                    <strong>Tanggal Dibayarkan:</strong>
                    <span>${new Date(data.tanggal_dibayarkan).toLocaleDateString('id-ID', { 
                        weekday: 'long', 
                        year: 'numeric', 
                        month: 'long', 
                        day: 'numeric' 
                    })}</span>
                </div>
            </div>
            
            <div class="info-section">
                <div class="info-row">
                    <strong>Gaji Bersih:</strong>
                    <span style="color: #059669; font-weight: bold;">${formatCurrency(data.gaji_bersih)}</span>
                </div>
            </div>
            
            <div class="deductions-section">
                <div class="info-row">
                    <strong>DETAIL POTONGAN:</strong>
                    <span></span>
                </div>
                
                <!-- Fixed Deductions - Always Show -->
                <div class="deduction-item${data.deductions.mdr_bws_bri === 0 ? '-zero' : ''}">
                    <span>MDR/BWS/BRI:</span>
                    <span>${formatCurrency(data.deductions.mdr_bws_bri)}</span>
                </div>
                <div class="deduction-item${data.deductions.btn === 0 ? '-zero' : ''}">
                    <span>BTN:</span>
                    <span>${formatCurrency(data.deductions.btn)}</span>
                </div>
                <div class="deduction-item${data.deductions.twp === 0 ? '-zero' : ''}">
                    <span>TWP:</span>
                    <span>${formatCurrency(data.deductions.twp)}</span>
                </div>
                <div class="deduction-item${data.deductions.persit === 0 ? '-zero' : ''}">
                    <span>PERSIT:</span>
                    <span>${formatCurrency(data.deductions.persit)}</span>
                </div>
                <div class="deduction-item${data.deductions.ikka_persit === 0 ? '-zero' : ''}">
                    <span>IKKA PERSIT:</span>
                    <span>${formatCurrency(data.deductions.ikka_persit)}</span>
                </div>
                <div class="deduction-item${data.deductions.koperasi === 0 ? '-zero' : ''}">
                    <span>KOPERASI:</span>
                    <span>${formatCurrency(data.deductions.koperasi)}</span>
                </div>
                <div class="deduction-item${data.deductions.barak === 0 ? '-zero' : ''}">
                    <span>BARAK:</span>
                    <span>${formatCurrency(data.deductions.barak)}</span>
                </div>
                <div class="deduction-item${data.deductions.kowad === 0 ? '-zero' : ''}">
                    <span>KOWAD:</span>
                    <span>${formatCurrency(data.deductions.kowad)}</span>
                </div>
                <div class="deduction-item${data.deductions.titipan === 0 ? '-zero' : ''}">
                    <span>TITIPAN:</span>
                    <span>${formatCurrency(data.deductions.titipan)}</span>
                </div>
                <div class="deduction-item${data.deductions.tenes === 0 ? '-zero' : ''}">
                    <span>TENES:</span>
                    <span>${formatCurrency(data.deductions.tenes)}</span>
                </div>
                <div class="deduction-item${data.deductions.remaja === 0 ? '-zero' : ''}">
                    <span>REMAJA:</span>
                    <span>${formatCurrency(data.deductions.remaja)}</span>
                </div>
                <div class="deduction-item${data.deductions.galon === 0 ? '-zero' : ''}">
                    <span>GALON:</span>
                    <span>${formatCurrency(data.deductions.galon)}</span>
                </div>
                <div class="deduction-item${data.deductions.sosial === 0 ? '-zero' : ''}">
                    <span>SOSIAL:</span>
                    <span>${formatCurrency(data.deductions.sosial)}</span>
                </div>
                <div class="deduction-item${data.deductions.pns === 0 ? '-zero' : ''}">
                    <span>PNS:</span>
                    <span>${formatCurrency(data.deductions.pns)}</span>
                </div>
                <div class="deduction-item${data.deductions.bel_wajib_kop === 0 ? '-zero' : ''}">
                    <span>BEL. WAJIB KOP:</span>
                    <span>${formatCurrency(data.deductions.bel_wajib_kop)}</span>
                </div>
                
                <!-- Custom Deductions - Always Show -->
                ${data.deductions.custom_1.name && data.deductions.custom_1.value !== 0 ? `
                <div class="deduction-item">
                    <span>${data.deductions.custom_1.name}:</span>
                    <span>${formatCurrency(data.deductions.custom_1.value)}</span>
                </div>` : ''}
                ${data.deductions.custom_2.name && data.deductions.custom_2.value !== 0 ? `
                <div class="deduction-item">
                    <span>${data.deductions.custom_2.name}:</span>
                    <span>${formatCurrency(data.deductions.custom_2.value)}</span>
                </div>` : ''}
                ${data.deductions.custom_3.name && data.deductions.custom_3.value !== 0 ? `
                <div class="deduction-item">
                    <span>${data.deductions.custom_3.name}:</span>
                    <span>${formatCurrency(data.deductions.custom_3.value)}</span>
                </div>` : ''}
                ${data.deductions.custom_4.name && data.deductions.custom_4.value !== 0 ? `
                <div class="deduction-item">
                    <span>${data.deductions.custom_4.name}:</span>
                    <span>${formatCurrency(data.deductions.custom_4.value)}</span>
                </div>` : ''}
                ${data.deductions.custom_5.name && data.deductions.custom_5.value !== 0 ? `
                <div class="deduction-item">
                    <span>${data.deductions.custom_5.name}:</span>
                    <span>${formatCurrency(data.deductions.custom_5.value)}</span>
                </div>` : ''}
            </div>
            
            <div class="total-section">
                <div class="total-row">
                    <span>Total Potongan:</span>
                    <span style="color: #dc2626;">${formatCurrency(data.total_deductions)}</span>
                </div>
                <div class="total-row">
                    <span>Gaji Bersih Setelah Potongan:</span>
                    <span style="color: #2563eb; font-size: 16px;">${formatCurrency(data.net_salary)}</span>
                </div>
            </div>
            
            <div class="footer">
                <p>Dicetak pada: ${new Date().toLocaleString('id-ID')}</p>
                <p>Struk ini adalah bukti pembayaran gaji yang sah</p>
            </div>
            
            <div class="no-print" style="text-align: center; margin-top: 20px;">
                <button onclick="window.print()" style="padding: 12px 24px; background: #2563eb; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold;">
                    🖨️ Cetak Struk
                </button>
                <button onclick="window.close()" style="padding: 12px 24px; background: #6b7280; color: white; border: none; border-radius: 6px; cursor: pointer; font-weight: bold; margin-left: 10px;">
                    ❌ Tutup
                </button>
            </div>
        </body>
        </html>
    `;
    
    printWindow.document.write(printContent);
    printWindow.document.close();
    printWindow.focus();
};

// Expose the function
defineExpose({
    printPayrollSlip
});
</script>

<template>
    <!-- This component doesn't render anything in the template -->
    <!-- It's purely functional for printing -->
</template>
