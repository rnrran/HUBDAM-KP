<script setup lang="ts">
import { ref, computed } from 'vue';
import { Head, useForm } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import ConfirmationModal from '@/components/ConfirmationModal.vue';
import NotificationContainer from '@/components/NotificationContainer.vue';
import { useNotifications } from '@/composables/useNotifications';
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

interface User {
    id: number;
    name: string;
    email: string;
    pangkat?: string;
}

interface Props {
    users: User[];
}

const props = defineProps<Props>();

const { success, error } = useNotifications();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Halaman Utama',
        href: '/dashboard',
    },
    {
        title: 'Input Gaji',
        href: '/admin/payroll/create',
    },
];

const showConfirmModal = ref(false);
const payrollSlipPrinterRef = ref<InstanceType<typeof PayrollSlipPrinter>>();

const form = useForm({
    user_id: '',
    gaji_bersih: '',
    tanggal_dibayarkan: new Date().toISOString().split('T')[0], // Today's date
    // Potongan fields
    mdr_bws_bri: '',
    btn: '',
    twp: '',
    persit: '',
    ikka_persit: '',
    koperasi: '',
    barak: '',
    kowad: '',
    titipan: '',
    tenes: '',
    remaja: '',
    galon: '',
    sosial: '',
    pns: '',
    bel_wajib_kop: '',
    // Custom fields
    custom_1_name: '',
    custom_1_value: '',
    custom_2_name: '',
    custom_2_value: '',
    custom_3_name: '',
    custom_3_value: '',
    custom_4_name: '',
    custom_4_value: '',
    custom_5_name: '',
    custom_5_value: '',
});

const selectedUser = computed(() => {
    return props.users.find(user => user.id.toString() === form.user_id);
});

const canSubmit = computed(() => {
    return form.user_id && form.gaji_bersih && !form.processing;
});

const isUserSelected = computed(() => {
    return !!form.user_id;
});

// Calculate total deductions
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

// Calculate net salary
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

const showConfirmation = () => {
    showConfirmModal.value = true;
};

const handleConfirm = () => {
    showConfirmModal.value = false;
    form.post(route('admin.payroll.store'), {
        onSuccess: () => {
            success('Berhasil!', `Data gaji untuk ${selectedUser.value?.name} berhasil disimpan.`);
            // Reset form completely
            form.reset();
            // Reset date to today
            form.tanggal_dibayarkan = new Date().toISOString().split('T')[0];
            // Reset user selection
            form.user_id = '';
        },
        onError: () => {
            error('Gagal!', 'Terjadi kesalahan saat menyimpan data gaji. Silakan coba lagi.');
        },
    });
};

const handleCancel = () => {
    showConfirmModal.value = false;
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
    
    // Call the print function from the component with the actual data
    payrollSlipPrinterRef.value?.printPayrollSlip(payrollData);
};
</script>

<template>
    <Head title="Input Gaji" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">Input Gaji Personil</h1>
                <p class="text-muted-foreground">
                    Input data gaji dan potongan untuk personil yang dipilih.
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
                            v-if="isUserSelected && form.gaji_bersih"
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
                        Pilih personil yang akan diinput data gajinya.
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
                        <p class="text-xs text-muted-foreground mt-2">
                            Setelah memilih personil dan mengisi data gaji, Anda dapat mencetak struk gaji menggunakan tombol "Cetak Struk".
                        </p>
                    </div>
                </CardContent>
            </Card>

            <!-- Salary Input Form -->
            <div v-if="isUserSelected" class="space-y-6">
                <!-- Basic Salary Info -->
                <Card class="w-full max-w">
                    <CardHeader>
                        <CardTitle class="flex items-center space-x-2">
                            <DollarSign class="h-5 w-5" />
                            <span>Informasi Gaji</span>
                        </CardTitle>
                        <CardDescription>
                            Input gaji bersih dan tanggal pembayaran untuk {{ selectedUser?.name }}.
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
                            Input berbagai potongan gaji (opsional). Kosongkan jika tidak ada potongan.
                        </CardDescription>
                    </CardHeader>
                    <CardContent class="space-y-6">
                        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
                            <!-- Fixed deduction fields -->
                            <div class="grid gap-2">
                                <Label for="mdr_bws_bri">MDR/BWS/BRI</Label>
                                <Input 
                                    id="mdr_bws_bri" 
                                    type="number"
                                    v-model="form.mdr_bws_bri" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.mdr_bws_bri" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="btn">BTN</Label>
                                <Input 
                                    id="btn" 
                                    type="number"
                                    v-model="form.btn" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.btn" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="twp">TWP</Label>
                                <Input 
                                    id="twp" 
                                    type="number"
                                    v-model="form.twp" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.twp" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="persit">PERSIT</Label>
                                <Input 
                                    id="persit" 
                                    type="number"
                                    v-model="form.persit" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.persit" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="ikka_persit">IKKA PERSIT</Label>
                                <Input 
                                    id="ikka_persit" 
                                    type="number"
                                    v-model="form.ikka_persit" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.ikka_persit" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="koperasi">KOPERASI</Label>
                                <Input 
                                    id="koperasi" 
                                    type="number"
                                    v-model="form.koperasi" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.koperasi" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="barak">BARAK</Label>
                                <Input 
                                    id="barak" 
                                    type="number"
                                    v-model="form.barak" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.barak" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="kowad">KOWAD</Label>
                                <Input 
                                    id="kowad" 
                                    type="number"
                                    v-model="form.kowad" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.kowad" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="titipan">TITIPAN</Label>
                                <Input 
                                    id="titipan" 
                                    type="number"
                                    v-model="form.titipan" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.titipan" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="tenes">TENES</Label>
                                <Input 
                                    id="tenes" 
                                    type="number"
                                    v-model="form.tenes" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.tenes" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="remaja">REMAJA</Label>
                                <Input 
                                    id="remaja" 
                                    type="number"
                                    v-model="form.remaja" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.remaja" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="galon">GALON</Label>
                                <Input 
                                    id="galon" 
                                    type="number"
                                    v-model="form.galon" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.galon" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="sosial">SOSIAL</Label>
                                <Input 
                                    id="sosial" 
                                    type="number"
                                    v-model="form.sosial" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.sosial" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="pns">PNS</Label>
                                <Input 
                                    id="pns" 
                                    type="number"
                                    v-model="form.pns" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.pns" />
                            </div>

                            <div class="grid gap-2">
                                <Label for="bel_wajib_kop">BEL. WAJIB KOP.</Label>
                                <Input 
                                    id="bel_wajib_kop" 
                                    type="number"
                                    v-model="form.bel_wajib_kop" 
                                    placeholder="0"
                                />
                                <InputError :message="form.errors.bel_wajib_kop" />
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

                <!-- Summary -->
                <Card class="w-full max-w">
                    <CardHeader>
                        <CardTitle>Ringkasan Gaji</CardTitle>
                        <CardDescription>
                            Ringkasan perhitungan gaji untuk {{ selectedUser?.name }}.
                        </CardDescription>
                    </CardHeader>
                    <CardContent>
                        <div class="space-y-4">
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
                        </div>

                        <div class="flex justify-end gap-3 mt-8">
                            <Button 
                                @click="printPayrollSlip"
                                variant="outline"
                                size="lg"
                                class="px-6 py-3"
                                :disabled="!isUserSelected || !form.gaji_bersih"
                            >
                                <Printer class="h-5 w-5 mr-2" />
                                Cetak Struk
                            </Button>
                            <Button 
                                @click="showConfirmation"
                                :disabled="!canSubmit"
                                size="lg"
                                class="px-8 py-3"
                            >
                                <LoaderCircle v-if="form.processing" class="h-5 w-5 animate-spin mr-2" />
                                <DollarSign v-else class="h-5 w-5 mr-2" />
                                Simpan Data Gaji
                            </Button>
                        </div>
                    </CardContent>
                </Card>
            </div>

            <!-- Empty State -->
            <Card v-if="!isUserSelected" class="w-full max-w">
                <CardContent class="flex flex-col items-center justify-center py-16">
                    <User class="h-16 w-16 text-muted-foreground mb-6" />
                    <h3 class="text-xl font-semibold text-muted-foreground mb-3">
                        Pilih Personil untuk Input Gaji
                    </h3>
                    <p class="text-base text-muted-foreground text-center max-w-md">
                        Silakan pilih personil dari dropdown di atas untuk mulai menginput data gaji dan potongan.
                    </p>
                </CardContent>
            </Card>
        </div>

        <!-- Confirmation Modal -->
        <ConfirmationModal
            v-model:open="showConfirmModal"
            title="Konfirmasi Simpan Data Gaji"
            :description="`Apakah Anda yakin ingin menyimpan data gaji untuk ${selectedUser?.name}?`"
            confirm-text="Simpan Data"
            cancel-text="Batal"
            :loading="form.processing"
            @confirm="handleConfirm"
            @cancel="handleCancel"
        />
        
        <!-- Notifications -->
        <NotificationContainer />
        
        <!-- Payroll Slip Printer Component -->
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
</template>