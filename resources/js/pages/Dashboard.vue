<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import AppLayout from '@/layouts/AppLayout.vue';
import { type BreadcrumbItem } from '@/types';
import { Head } from '@inertiajs/vue3';
import { Card, CardHeader, CardDescription, CardContent } from '@/components/ui/card';
import PayrollLineChart from '@/components/PayrollLineChart.vue';
import { Eye, BarChart3, Users, TrendingUp, Printer, Calendar } from 'lucide-vue-next';
import { Button } from '@/components/ui/button';
import PayrollSlipPrinter from '@/components/PayrollSlipPrinter.vue';
import { 
  Select,
  SelectContent,
  SelectItem,
  SelectTrigger,
  SelectValue,
} from '@/components/ui/select';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';

interface ChartPoint { month: string; gaji_bersih: number; total_potongan: number; gaji_bersih_setelah_potongan: number; date?: string }
interface UserLite { id: number; name: string; email: string; pangkat?: string | null; nomor_registrasi?: string | null }
interface PayrollRecord {
  id: number; user_id: number; gaji_bersih: number; total_deductions: number; net_salary: number; tanggal_dibayarkan: string;
  user: UserLite;
}
interface Props {
  payrolls: { data: PayrollRecord[]; current_page: number; last_page: number; per_page: number; total: number; from: number; to: number };
  chartData: ChartPoint[];
  me: UserLite;
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
  { title: 'Halaman Utama', href: '/dashboard' },
];

const payrollSlipPrinterRef = ref<InstanceType<typeof PayrollSlipPrinter>>();

const selectedTimeRange = ref<'4'|'8'|'12'|'6'|'12m'|'24'|'all'|'custom'>('all');
const customDateRange = ref<{ start: string; end: string }>({ start: '', end: '' });

const getTimeRangeLabel = (value: string) => {
  switch (value) {
    case '4': return '4 Minggu';
    case '8': return '8 Minggu';
    case '12': return '12 Minggu';
    case '6': return '6 Bulan';
    case '12m': return '12 Bulan';
    case '24': return '24 Bulan';
    case 'custom': return 'Custom Range';
    case 'all':
    default: return 'Semua';
  }
};

watch(selectedTimeRange, () => {
  if (selectedTimeRange.value === 'custom') {
    const today = new Date();
    const sixMonthsAgo = new Date();
    sixMonthsAgo.setMonth(today.getMonth() - 6);
    customDateRange.value.start = sixMonthsAgo.toISOString().split('T')[0];
    customDateRange.value.end = today.toISOString().split('T')[0];
  }
});

// Build chart data directly from filtered table rows for consistency
const filteredChartData = computed(() => {
  const rows = [...filteredTableRows.value].sort((a, b) => new Date(a.tanggal_dibayarkan).getTime() - new Date(b.tanggal_dibayarkan).getTime());
  return rows.map(r => ({
    month: new Date(r.tanggal_dibayarkan).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }),
    gaji_bersih: Number(r.gaji_bersih),
    total_potongan: Number(r.total_deductions),
    gaji_bersih_setelah_potongan: Number(r.net_salary)
  }));
});

// Filter table rows by selected time range (client-side)
const filteredTableRows = computed(() => {
  let rows = props.payrolls.data;
  if (selectedTimeRange.value === 'custom' && customDateRange.value.start && customDateRange.value.end) {
    const startDate = new Date(customDateRange.value.start);
    const endDate = new Date(customDateRange.value.end);
    rows = rows.filter(r => {
      const d = new Date(r.tanggal_dibayarkan);
      return d >= startDate && d <= endDate;
    });
  } else if (selectedTimeRange.value !== 'all') {
    const weekToMonthApprox: Record<string, number> = { '4': 1, '8': 2, '12': 3 };
    const monthsBack = weekToMonthApprox[selectedTimeRange.value] ?? (selectedTimeRange.value === '6' ? 6 : selectedTimeRange.value === '12m' ? 12 : selectedTimeRange.value === '24' ? 24 : 0);
    if (monthsBack > 0) {
      // Use latest available tanggal_dibayarkan as reference
      const latest = rows.reduce((acc, r) => {
        const d = new Date(r.tanggal_dibayarkan);
        return d > acc ? d : acc;
      }, new Date(0));
      const cutoff = new Date(latest);
      cutoff.setMonth(cutoff.getMonth() - monthsBack);
      rows = rows.filter(r => new Date(r.tanggal_dibayarkan) >= cutoff);
    }
  }
  return rows;
});

const formatCurrency = (value: number): string => new Intl.NumberFormat('id-ID', { minimumFractionDigits: 0 }).format(value);
const formatDate = (dateString: string): string => new Date(dateString).toLocaleDateString('id-ID', { year: 'numeric', month: 'long', day: 'numeric' });

const printRow = (row: PayrollRecord) => {
  payrollSlipPrinterRef.value?.printPayrollSlip({
    user: { id: props.me.id, name: props.me.name, email: props.me.email, pangkat: props.me.pangkat ?? null, nomor_registrasi: props.me.nomor_registrasi ?? null },
    gaji_bersih: row.gaji_bersih,
    tanggal_dibayarkan: row.tanggal_dibayarkan,
    total_deductions: row.total_deductions,
    net_salary: row.net_salary,
    deductions: {
      mdr_bws_bri: 0, btn: 0, twp: 0, persit: 0, ikka_persit: 0, koperasi: 0, barak: 0, kowad: 0, titipan: 0, tenes: 0, remaja: 0, galon: 0, sosial: 0, pns: 0, bel_wajib_kop: 0,
      custom_1: { name: '', value: 0 }, custom_2: { name: '', value: 0 }, custom_3: { name: '', value: 0 }, custom_4: { name: '', value: 0 }, custom_5: { name: '', value: 0 }
    }
  });
};

</script>

<template>
  <Head title="Halaman Utama" />
  <AppLayout :breadcrumbs="breadcrumbs">
    <div class="mx-6 my-6 space-y-6">
      <!-- Header -->
      <div class="flex items-center justify-between">
        <div>
          <h1 class="text-3xl font-bold tracking-tight">Profil Gaji Saya</h1>
          <p class="text-muted-foreground">Riwayat gaji dan visualisasi untuk akun Anda.</p>
        </div>
      </div>

      <!-- Stats -->
      <div class="flex items-center space-x-4 text-sm text-muted-foreground">
        <div class="flex items-center space-x-2">
          <Users class="h-4 w-4" />
          <span>{{ props.me.name }}</span>
        </div>
        <div class="flex items-center space-x-2">
          <TrendingUp class="h-4 w-4" />
          <span>{{ filteredTableRows.length }} Record</span>
        </div>
      </div>

      <!-- Time Range Filter (for Chart) -->
      <div class="flex items-center space-x-4">
        <div class="flex items-center space-x-2">
          <Calendar class="h-4 w-4" />
          <Label for="time-range">Rentang Waktu:</Label>
          <Select v-model="selectedTimeRange">
            <SelectTrigger class="w-40">
              <SelectValue :placeholder="getTimeRangeLabel(selectedTimeRange)" />
            </SelectTrigger>
            <SelectContent>
              <SelectItem value="all">Semua</SelectItem>
              <SelectItem value="4">4 Minggu</SelectItem>
              <SelectItem value="8">8 Minggu</SelectItem>
              <SelectItem value="12">12 Minggu</SelectItem>
              <SelectItem value="6">6 Bulan</SelectItem>
              <SelectItem value="12m">12 Bulan</SelectItem>
              <SelectItem value="24">24 Bulan</SelectItem>
              <SelectItem value="custom">Custom Range</SelectItem>
            </SelectContent>
          </Select>
        </div>
        <div v-if="selectedTimeRange === 'custom'" class="flex items-center space-x-4 p-3 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-800">
          <div class="flex items-center space-x-2">
            <Calendar class="h-4 w-4 text-blue-600" />
            <Label for="start-date" class="text-blue-700 dark:text-blue-300">Dari:</Label>
            <Input id="start-date" v-model="customDateRange.start" type="date" class="w-40" />
          </div>
          <div class="flex items-center space-x-2">
            <Calendar class="h-4 w-4 text-blue-600" />
            <Label for="end-date" class="text-blue-700 dark:text-blue-300">Sampai:</Label>
            <Input id="end-date" v-model="customDateRange.end" type="date" class="w-40" />
          </div>
        </div>
      </div>

      <!-- Chart -->
      <Card class="w-full">
        <CardHeader>
          <div class="flex items-center space-x-2">
            <BarChart3 class="h-5 w-5" />
            <span>Visualisasi Gaji Saya</span>
          </div>
          <CardDescription>Perbandingan gaji bersih, potongan, dan gaji bersih setelah potongan.</CardDescription>
        </CardHeader>
        <CardContent>
          <PayrollLineChart :chart-data="filteredChartData" :height="400" />
        </CardContent>
      </Card>

      <!-- Table -->
      <Card class="w-full">
        <CardHeader>
          <div class="flex items-center justify-between">
            <div class="flex items-center space-x-2">
              <Eye class="h-5 w-5" />
              <span>Riwayat Gaji</span>
            </div>
            <div class="text-sm text-muted-foreground">Total: {{ filteredTableRows.length }} record</div>
          </div>
          <CardDescription>Daftar riwayat gaji Anda.</CardDescription>
        </CardHeader>
        <CardContent class="px-0">
          <div class="overflow-x-auto">
            <table class="w-full">
              <thead class="bg-muted/50">
                <tr>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gaji Bersih</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Total Potongan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Gaji Setelah Potongan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Dibayarkan</th>
                  <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                </tr>
              </thead>
              <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                <tr v-if="filteredTableRows.length === 0">
                  <td colspan="5" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400">Tidak ada data</td>
                </tr>
                <tr v-for="row in filteredTableRows" :key="row.id" class="hover:bg-muted/50">
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatCurrency(row.gaji_bersih) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatCurrency(row.total_deductions) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatCurrency(row.net_salary) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">{{ formatDate(row.tanggal_dibayarkan) }}</td>
                  <td class="px-6 py-4 whitespace-nowrap text-sm">
                    <Button variant="outline" size="sm" @click="printRow(row)"><Printer class="h-4 w-4 mr-2" />Cetak Struk</Button>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>

          <!-- Pagination footer -->
          <div v-if="filteredTableRows.length > 0" class="px-6 py-4 border-t bg-muted/30">
            <div class="text-sm text-muted-foreground">
              <span class="font-medium">Menampilkan 1-{{ filteredTableRows.length }} dari {{ filteredTableRows.length }} data</span>
              <span class="ml-2 text-xs">(Terfilter oleh rentang waktu)</span>
            </div>
          </div>
        </CardContent>
      </Card>

      <!-- Hidden printer component -->
      <PayrollSlipPrinter ref="payrollSlipPrinterRef" :payroll-data="{
        user: { id: props.me.id, name: props.me.name, email: props.me.email, pangkat: props.me.pangkat ?? null, nomor_registrasi: props.me.nomor_registrasi ?? null },
        gaji_bersih: 0,
        tanggal_dibayarkan: new Date().toISOString().split('T')[0],
        total_deductions: 0,
        net_salary: 0,
        deductions: { mdr_bws_bri: 0, btn: 0, twp: 0, persit: 0, ikka_persit: 0, koperasi: 0, barak: 0, kowad: 0, titipan: 0, tenes: 0, remaja: 0, galon: 0, sosial: 0, pns: 0, bel_wajib_kop: 0, custom_1: { name: '', value: 0 }, custom_2: { name: '', value: 0 }, custom_3: { name: '', value: 0 }, custom_4: { name: '', value: 0 }, custom_5: { name: '', value: 0 } }
      }" />
    </div>
  </AppLayout>
</template>
