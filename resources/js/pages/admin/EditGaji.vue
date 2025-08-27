<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import InputError from '@/components/InputError.vue';
import { LoaderCircle, DollarSign, Calendar, User, Printer } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import PayrollSlipPrinter from '@/components/PayrollSlipPrinter.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';

interface UserType {
    id: number;
    name: string;
    email: string;
    pangkat?: string;
}

interface PayrollRecord {
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
}

interface Props {
    users: UserType[];
    payroll: PayrollRecord;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    { title: 'Halaman Utama', href: '/dashboard' },
    { title: 'Lihat Gaji', href: '/admin/payroll' },
    { title: 'Edit Gaji', href: `/admin/payroll/${props.payroll.id}/edit` },
];

const payrollSlipPrinterRef = ref<InstanceType<typeof PayrollSlipPrinter>>();

const form = useForm({
    user_id: String(props.payroll.user_id ?? ''),
    gaji_bersih: String(props.payroll.gaji_bersih ?? ''),
    tanggal_dibayarkan: props.payroll.tanggal_dibayarkan ?? new Date().toISOString().split('T')[0],
    mdr_bws_bri: String(props.payroll.mdr_bws_bri ?? ''),
    btn: String(props.payroll.btn ?? ''),
    twp: String(props.payroll.twp ?? ''),
    persit: String(props.payroll.persit ?? ''),
    ikka_persit: String(props.payroll.ikka_persit ?? ''),
    koperasi: String(props.payroll.koperasi ?? ''),
    barak: String(props.payroll.barak ?? ''),
    kowad: String(props.payroll.kowad ?? ''),
    titipan: String(props.payroll.titipan ?? ''),
    tenes: String(props.payroll.tenes ?? ''),
    remaja: String(props.payroll.remaja ?? ''),
    galon: String(props.payroll.galon ?? ''),
    sosial: String(props.payroll.sosial ?? ''),
    pns: String(props.payroll.pns ?? ''),
    bel_wajib_kop: String(props.payroll.bel_wajib_kop ?? ''),
    custom_1_name: props.payroll.custom_1_name ?? '',
    custom_1_value: String(props.payroll.custom_1_value ?? ''),
    custom_2_name: props.payroll.custom_2_name ?? '',
    custom_2_value: String(props.payroll.custom_2_value ?? ''),
    custom_3_name: props.payroll.custom_3_name ?? '',
    custom_3_value: String(props.payroll.custom_3_value ?? ''),
    custom_4_name: props.payroll.custom_4_name ?? '',
    custom_4_value: String(props.payroll.custom_4_value ?? ''),
    custom_5_name: props.payroll.custom_5_name ?? '',
    custom_5_value: String(props.payroll.custom_5_value ?? ''),
});

const selectedUser = computed(() => {
    return props.users.find(user => user.id.toString() === form.user_id);
});

const totalDeductions = computed(() => {
    const fields = [
        'mdr_bws_bri', 'btn', 'twp', 'persit', 'ikka_persit', 'koperasi',
        'barak', 'kowad', 'titipan', 'tenes', 'remaja', 'galon', 'sosial',
        'pns', 'bel_wajib_kop', 'custom_1_value', 'custom_2_value',
        'custom_3_value', 'custom_4_value', 'custom_5_value'
    ];
    return fields.reduce((total, field) => {
        const value = parseFloat(form[field as keyof typeof form.data] as string) || 0;
        return total + value;
    }, 0);
});

const netSalary = computed(() => {
    const gajiBersih = parseFloat(form.gaji_bersih) || 0;
    return gajiBersih - totalDeductions.value;
});

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const printPayrollSlip = () => {
    if (!selectedUser.value || !form.gaji_bersih) return;
    const payrollData = {
        user: selectedUser.value,
        gaji_bersih: parseFloat(form.gaji_bersih) || 0,
        tanggal_dibayarkan: form.tanggal_dibayarkan,
        total_deductions: totalDeductions.value,
        net_salary: netSalary.value,
        deductions: {
            mdr_bws_bri: parseFloat(form.mdr_bws_bri) || 0,
            btn: parseFloat(form.btn) || 0,
            twp: parseFloat(form.twp) || 0,
            persit: parseFloat(form.persit) || 0,
            ikka_persit: parseFloat(form.ikka_persit) || 0,
            koperasi: parseFloat(form.koperasi) || 0,
            barak: parseFloat(form.barak) || 0,
            kowad: parseFloat(form.kowad) || 0,
            titipan: parseFloat(form.titipan) || 0,
            tenes: parseFloat(form.tenes) || 0,
            remaja: parseFloat(form.remaja) || 0,
            galon: parseFloat(form.galon) || 0,
            sosial: parseFloat(form.sosial) || 0,
            pns: parseFloat(form.pns) || 0,
            bel_wajib_kop: parseFloat(form.bel_wajib_kop) || 0,
            custom_1: { name: form.custom_1_name, value: parseFloat(form.custom_1_value) || 0 },
            custom_2: { name: form.custom_2_name, value: parseFloat(form.custom_2_value) || 0 },
            custom_3: { name: form.custom_3_name, value: parseFloat(form.custom_3_value) || 0 },
            custom_4: { name: form.custom_4_name, value: parseFloat(form.custom_4_value) || 0 },
            custom_5: { name: form.custom_5_name, value: parseFloat(form.custom_5_value) || 0 },
        }
    };
    payrollSlipPrinterRef.value?.printPayrollSlip(payrollData);
};

const handleUpdate = () => {
    showConfirmModal.value = true;
};

const showConfirmModal = ref(false);
const confirmUpdate = () => {
    showConfirmModal.value = false;
    // @ts-ignore - route helper available via Ziggy
    form.put(route('admin.payroll.update', props.payroll.id));
};
const cancelUpdate = () => {
    showConfirmModal.value = false;
};

</script>

<template>
    <Head title="Edit Gaji" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">Edit Gaji Personil</h1>
                <p class="text-muted-foreground">
                    Ubah data gaji dan potongan untuk personil yang dipilih.
                </p>
            </div>

            <!-- User Selection -->
            <Card class="w-full max-w">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <User class="h-5 w-5" />
                            <span>Pilih Personil</span>
                        </div>
                        <Button 
                            v-if="selectedUser && form.gaji_bersih"
                            @click="printPayrollSlip"
                            variant="default"
                            size="sm"
                            class="ml-4 bg-green-600 hover:bg-green-700 text-white"
                        >
                            <Printer class="h-4 w-4 mr-2" />
                            Cetak Struk
                        </Button>
                    </div>
                    <CardDescription>
                        Pilih personil yang akan diubah data gajinya.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="grid gap-2">
                        <Label for="user_id">Personil *</Label>
                        <Select v-model="form.user_id" required>
                            <SelectTrigger>
                                <SelectValue :placeholder="selectedUser ? selectedUser.name : 'Pilih personil...'" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem 
                                    v-for="user in props.users" 
                                    :key="user.id" 
                                    :value="user.id.toString()"
                                >
                                    <div class="flex flex-col">
                                        <span class="font-medium">{{ user.name }}</span>
                                        <span class="text-sm text-muted-foreground">{{ user.email }}</span>
                                        <span v-if="user.pangkat" class="text-xs text-muted-foreground">{{ user.pangkat }}</span>
                                    </div>
                                </SelectItem>
                            </SelectContent>
                        </Select>
                        <InputError :message="form.errors.user_id" />
                    </div>
                </CardContent>
            </Card>

            <!-- Salary Input Form -->
            <div class="space-y-6">
                <!-- Basic Salary Info -->
                <Card class="w-full max-w">
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <DollarSign class="h-5 w-5" />
                            <span>Informasi Gaji</span>
                        </CardTitle>
                        <CardDescription>
                            Ubah gaji bersih dan tanggal pembayaran untuk {{ selectedUser?.name }}.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid md:grid-cols-2 gap-6">
                            <div class="grid gap-2">
                                <Label for="gaji_bersih">Gaji Bersih *</Label>
                                <Input 
                                    id="gaji_bersih" 
                                    type="number"
                                    v-model="form.gaji_bersih" 
                                    placeholder="Masukkan gaji bersih"
                                    required
                                />
                                <InputError :message="form.errors.gaji_bersih" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="tanggal_dibayarkan" class="flex items-center space-x-2">
                                    <Calendar class="h-4 w-4" />
                                    <span>Tanggal Dibayarkan *</span>
                                </Label>
                                <Input 
                                    id="tanggal_dibayarkan" 
                                    type="date"
                                    v-model="form.tanggal_dibayarkan" 
                                    required
                                />
                                <InputError :message="form.errors.tanggal_dibayarkan" />
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Deductions -->
                <Card class="w-full max-w">
                    <CardHeader>
                        <CardTitle>Potongan Gaji</CardTitle>
                        <CardDescription>
                            Ubah berbagai potongan gaji (opsional).
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <div class="grid gap-2" v-for="field in [
                                { key: 'mdr_bws_bri', label: 'MDR/BWS/BRI' },
                                { key: 'btn', label: 'BTN' },
                                { key: 'twp', label: 'TWP' },
                                { key: 'persit', label: 'PERSIT' },
                                { key: 'ikka_persit', label: 'IKKA PERSIT' },
                                { key: 'koperasi', label: 'KOPERASI' },
                                { key: 'barak', label: 'BARAK' },
                                { key: 'kowad', label: 'KOWAD' },
                                { key: 'titipan', label: 'TITIPAN' },
                                { key: 'tenes', label: 'TENES' },
                                { key: 'remaja', label: 'REMAJA' },
                                { key: 'galon', label: 'GALON' },
                                { key: 'sosial', label: 'SOSIAL' },
                                { key: 'pns', label: 'PNS' },
                                { key: 'bel_wajib_kop', label: 'BEL. WAJIB KOP.' }
                            ]" :key="field.key">
                                <Label :for="field.key">{{ field.label }}</Label>
                                <Input 
                                    :id="field.key" 
                                    type="number"
                                    v-model="form[field.key as keyof typeof form.data] as any" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors[field.key as keyof typeof form.errors] as any" />
                            </div>
                        </div>

                        <!-- Custom fields -->
                        <div class="mt-8">
                            <h4 class="text-lg font-medium mb-4">Potongan Tambahan (Opsional)</h4>
                            <div class="space-y-4">
                                <div v-for="i in 5" :key="i" class="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div class="grid gap-2">
                                        <Label :for="`custom_${i}_name`">Nama Potongan {{ i }}</Label>
                                        <Input 
                                            :id="`custom_${i}_name`" 
                                            v-model="form[`custom_${i}_name` as keyof typeof form.data]" 
                                            :placeholder="`Nama potongan tambahan ${i}`"
                                        />
                                        <InputError :message="form.errors[`custom_${i}_name` as keyof typeof form.errors]" />
                                    </div>
                                    <div class="grid gap-2">
                                        <Label :for="`custom_${i}_value`">Nilai Potongan {{ i }}</Label>
                                        <Input 
                                            :id="`custom_${i}_value`" 
                                            type="number"
                                            v-model="form[`custom_${i}_value` as keyof typeof form.data]" 
                                            placeholder="0"
                                        />
                                        <InputError :message="form.errors[`custom_${i}_value` as keyof typeof form.errors]" />
                                    </div>
                                </div>
                            </div>
                        </div>
                    </CardContent>
                </Card>

                <!-- Summary and Actions -->
                <Card class="w-full max-w">
                    <CardHeader>
                        <CardTitle>Ringkasan</CardTitle>
                        <CardDescription>Periksa kembali sebelum menyimpan.</CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="flex justify-between items-center py-3 border-b border-border">
                            <span class="font-medium text-base">Gaji Bersih:</span>
                            <span class="text-lg font-semibold text-green-600">
                                {{ formatCurrency(parseFloat(form.gaji_bersih) || 0) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-3 border-b border-border">
                            <span class="font-medium text-base">Total Potongan:</span>
                            <span class="text-lg font-semibold text-red-600">
                                {{ formatCurrency(totalDeductions) }}
                            </span>
                        </div>
                        <div class="flex justify-between items-center py-4 bg-gradient-to-r from-blue-50 to-blue-100 dark:from-blue-950/20 dark:to-blue-900/20 rounded-lg px-6 border border-blue-200 dark:border-blue-800">
                            <span class="font-bold text-lg text-blue-900 dark:text-blue-100">Gaji Bersih Setelah Potongan:</span>
                            <span class="text-2xl font-bold text-blue-700 dark:text-blue-300">
                                {{ formatCurrency(netSalary) }}
                            </span>
                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <Button 
                                @click="handleUpdate"
                                :disabled="form.processing"
                                size="lg"
                                class="px-8 py-3"
                            >
                                <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                                <DollarSign v-else class="h-5 w-5 mr-2" />
                                Simpan Perubahan
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>
        </div>

        <!-- Payroll Slip Printer -->
        <PayrollSlipPrinter 
            ref="payrollSlipPrinterRef"
            :payroll-data="{
                user: selectedUser || { id: 0, name: '', email: '' },
                gaji_bersih: parseFloat(form.gaji_bersih) || 0,
                tanggal_dibayarkan: form.tanggal_dibayarkan,
                total_deductions: totalDeductions,
                net_salary: netSalary,
                deductions: {
                    mdr_bws_bri: parseFloat(form.mdr_bws_bri) || 0,
                    btn: parseFloat(form.btn) || 0,
                    twp: parseFloat(form.twp) || 0,
                    persit: parseFloat(form.persit) || 0,
                    ikka_persit: parseFloat(form.ikka_persit) || 0,
                    koperasi: parseFloat(form.koperasi) || 0,
                    barak: parseFloat(form.barak) || 0,
                    kowad: parseFloat(form.kowad) || 0,
                    titipan: parseFloat(form.titipan) || 0,
                    tenes: parseFloat(form.tenes) || 0,
                    remaja: parseFloat(form.remaja) || 0,
                    galon: parseFloat(form.galon) || 0,
                    sosial: parseFloat(form.sosial) || 0,
                    pns: parseFloat(form.pns) || 0,
                    bel_wajib_kop: parseFloat(form.bel_wajib_kop) || 0,
                    custom_1: { name: form.custom_1_name, value: parseFloat(form.custom_1_value) || 0 },
                    custom_2: { name: form.custom_2_name, value: parseFloat(form.custom_2_value) || 0 },
                    custom_3: { name: form.custom_3_name, value: parseFloat(form.custom_3_value) || 0 },
                    custom_4: { name: form.custom_4_name, value: parseFloat(form.custom_4_value) || 0 },
                    custom_5: { name: form.custom_5_name, value: parseFloat(form.custom_5_value) || 0 },
                }
            }"
        />
    </AppLayout>

    <!-- Confirmation Modal -->
    <ConfirmationModal
        v-model:open="showConfirmModal"
        title="Konfirmasi Simpan Perubahan"
        :description="`Apakah Anda yakin ingin menyimpan perubahan data gaji untuk ${selectedUser?.name}?`"
        confirm-text="Simpan"
        cancel-text="Batal"
        :loading="form.processing"
        @confirm="confirmUpdate"
        @cancel="cancelUpdate"
    />
</template>


