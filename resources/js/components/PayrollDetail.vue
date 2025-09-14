<script setup lang="ts">
import { computed } from 'vue';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { Button } from '@/components/ui/button';
import { X, DollarSign, Calendar, User, Receipt } from 'lucide-vue-next';

interface User {
    id: number;
    name: string;
    email: string;
    pangkat?: string;
}

interface PayrollDetail {
    id: number;
    user_id: number;
    gaji_bersih: number;
    tanggal_dibayarkan: string;
    mdr_bws_bri: number | null;
    btn: number | null;
    twp: number | null;
    persit: number | null;
    ikka_persit: number | null;
    koperasi: number | null;
    barak: number | null;
    kowad: number | null;
    titipan: number | null;
    tenes: number | null;
    remaja: number | null;
    galon: number | null;
    sosial: number | null;
    pns: number | null;
    bel_wajib_kop: number | null;
    custom_1_name: string | null;
    custom_1_value: number | null;
    custom_2_name: string | null;
    custom_2_value: number | null;
    custom_3_name: string | null;
    custom_3_value: number | null;
    custom_4_name: string | null;
    custom_4_value: number | null;
    custom_5_name: string | null;
    custom_5_value: number | null;
    total_deductions: number;
    net_salary: number;
    created_at: string;
    updated_at: string;
    user: {
        id: number;
        name: string;
        email: string;
        pangkat: string | null;
    };
}

interface Props {
    payroll: PayrollDetail;
    isOpen: boolean;
}

const props = defineProps<Props>();
const emit = defineEmits<{
    close: [];
}>();

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        weekday: 'long',
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const closeModal = () => {
    emit('close');
};

// Group deductions for better display
const fixedDeductions = computed(() => [
    { name: 'MDR/BWS/BRI', value: props.payroll.mdr_bws_bri },
    { name: 'BTN', value: props.payroll.btn },
    { name: 'TWP', value: props.payroll.twp },
    { name: 'PERSIT', value: props.payroll.persit },
    { name: 'IKKA PERSIT', value: props.payroll.ikka_persit },
    { name: 'KOPERASI', value: props.payroll.koperasi },
    { name: 'BARAK', value: props.payroll.barak },
    { name: 'KOWAD', value: props.payroll.kowad },
    { name: 'TITIPAN', value: props.payroll.titipan },
    { name: 'TENES', value: props.payroll.tenes },
    { name: 'REMAJA', value: props.payroll.remaja },
    { name: 'GALON', value: props.payroll.galon },
    { name: 'SOSIAL', value: props.payroll.sosial },
    { name: 'PNS', value: props.payroll.pns },
    { name: 'BEL. WAJIB KOP', value: props.payroll.bel_wajib_kop },
]);

const customDeductions = computed(() => {
    const deductions = [
        { name: props.payroll.custom_1_name, value: props.payroll.custom_1_value },
        { name: props.payroll.custom_2_name, value: props.payroll.custom_2_value },
        { name: props.payroll.custom_3_name, value: props.payroll.custom_3_value },
        { name: props.payroll.custom_4_name, value: props.payroll.custom_4_value },
        { name: props.payroll.custom_5_name, value: props.payroll.custom_5_value },
    ];
    
    // Only return deductions that have both a name and a value > 0
    return deductions.filter(deduction => 
        deduction.name && deduction.name.trim() !== '' && 
        deduction.value && deduction.value > 0
    );
});
</script>

<template>
    <div v-if="isOpen" class="fixed inset-0 z-50 flex items-center justify-center bg-black/50">
        <div class="bg-white rounded-lg shadow-xl max-w-4xl w-full mx-4 max-h-[90vh] overflow-y-auto">
            <!-- Header -->
            <div class="flex items-center justify-between p-6 border-b border-gray-200">
                <div class="flex items-center space-x-3">
                    <Receipt class="h-6 w-6 text-blue-600" />
                    <h2 class="text-xl font-semibold">Detail Gaji Personil</h2>
                </div>
                <Button variant="ghost" size="sm" @click="closeModal">
                    <X class="h-5 w-5" />
                </Button>
            </div>

            <!-- Content -->
            <div class="p-6 space-y-6">
                <!-- User Information -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <User class="h-5 w-5" />
                            <span>Informasi Personil</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="grid md:grid-cols-2 gap-4">
                            <div>
                                <label class="text-sm font-medium text-gray-600">Nama</label>
                                <p class="text-lg font-semibold">{{ payroll.user.name }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Email</label>
                                <p class="text-base">{{ payroll.user.email }}</p>
                            </div>
                            <div v-if="payroll.user.pangkat">
                                <label class="text-sm font-medium text-gray-600">Pangkat</label>
                                <p class="text-base">{{ payroll.user.pangkat }}</p>
                            </div>
                            <div>
                                <label class="text-sm font-medium text-gray-600">Tanggal Dibayarkan</label>
                                <p class="text-base">{{ formatDate(payroll.tanggal_dibayarkan) }}</p>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Salary Summary -->
                <Card>
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <DollarSign class="h-5 w-5" />
                            <span>Ringkasan Gaji</span>
                        </CardTitle>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
                            <div class="flex justify-between items-center py-3 border-b border-gray-200">
                                <span class="font-medium text-base">Gaji Bersih:</span>
                                <span class="text-lg font-semibold text-green-600">
                                    {{ formatCurrency(payroll.gaji_bersih) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-3 border-b border-gray-200">
                                <span class="font-medium text-base">Total Potongan:</span>
                                <span class="text-lg font-semibold text-red-600">
                                    {{ formatCurrency(payroll.total_deductions) }}
                                </span>
                            </div>
                            <div class="flex justify-between items-center py-4 bg-gradient-to-r from-blue-50 to-blue-100 rounded-lg px-6 border border-blue-200">
                                <span class="font-bold text-lg text-blue-900">Gaji Bersih Setelah Potongan:</span>
                                <span class="text-2xl font-bold text-blue-700">
                                    {{ formatCurrency(payroll.net_salary) }}
                                </span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Fixed Deductions -->
                <Card>
                    <CardHeader>
                        <CardTitle>Potongan Tetap</CardTitle>
                        <CardDescription>Detail potongan gaji yang telah ditetapkan</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="(deduction, key) in fixedDeductions" :key="key" class="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                <span class="font-medium text-sm">{{ deduction.name }}</span>
                                <span class="text-sm font-mono">{{ formatCurrency(deduction.value || 0) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Custom Deductions -->
                <Card v-if="customDeductions.length > 0">
                    <CardHeader>
                        <CardTitle>Potongan Kustom</CardTitle>
                        <CardDescription>Potongan gaji tambahan yang telah ditentukan</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                            <div v-for="deduction in customDeductions" :key="deduction.name" class="flex justify-between items-center p-3 bg-muted/50 rounded-lg">
                                <span class="font-medium text-sm">{{ deduction.name }}</span>
                                <span class="text-sm font-mono">{{ formatCurrency(deduction.value) }}</span>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Footer -->
                <div class="flex justify-end space-x-3 pt-4 border-t border-gray-200">
                    <Button variant="outline" @click="closeModal">
                        Tutup
                    </Button>
                </div>
            </div>
        </div>
    </div>
</template>
