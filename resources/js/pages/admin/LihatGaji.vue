<script setup lang="ts">
import { ref, computed, watch } from 'vue';
import { Head } from '@inertiajs/vue3';
import AppLayout from '@/layouts/AppLayout.vue';
import { Button } from '@/components/ui/button';
import { Card, CardContent, CardDescription, CardHeader, CardTitle } from '@/components/ui/card';
import { 
    Select,
    SelectContent,
    SelectItem,
    SelectTrigger,
    SelectValue,
} from '@/components/ui/select';
import { Label } from '@/components/ui/label';
import { BarChart3, Eye, Filter, TrendingUp, Users, Printer, Info, Search, ChevronLeft, ChevronRight, ChevronsLeft, ChevronsRight, Calendar } from 'lucide-vue-next';
import { type BreadcrumbItem } from '@/types';
import PayrollSlipPrinter from '@/components/PayrollSlipPrinter.vue';
import PayrollDetail from '@/components/PayrollDetail.vue';
import PayrollLineChart from '@/components/PayrollLineChart.vue';
import { Input } from '@/components/ui/input';

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

interface User {
    id: number;
    name: string;
    email: string;
    pangkat: string | null;
}

interface ChartDataPoint {
    month: string;
    gaji_bersih: number;
    total_potongan: number;
    gaji_bersih_setelah_potongan: number;
}

interface Props {
    payrolls: {
        data: PayrollRecord[];
        current_page: number;
        last_page: number;
        per_page: number;
        total: number;
        from: number;
        to: number;
    };
    users: User[];
    chartData: ChartDataPoint[];
}

const props = defineProps<Props>();

const breadcrumbs: BreadcrumbItem[] = [
    {
        title: 'Halaman Utama',
        href: '/dashboard',
    },
    {
        title: 'Lihat Gaji',
        href: '/admin/payroll',
    },
];

const selectedFilter = ref('all');
const viewMode = ref<'table' | 'chart'>('table');
const payrollSlipPrinterRef = ref<InstanceType<typeof PayrollSlipPrinter>>();
const showDetailModal = ref(false);
const selectedPayroll = ref<PayrollRecord | null>(null);
const searchQuery = ref('');
const currentPage = ref(1);
const itemsPerPage = ref(10);
const selectedTimeRange = ref('12');
const selectedUserForChart = ref('all');
const customDateRange = ref({
    start: '',
    end: ''
});
const chartUserSearch = ref('');

// Watch for itemsPerPage changes and reset to first page
watch(itemsPerPage, () => {
    currentPage.value = 1;
});

// Watch for time range changes
watch(selectedTimeRange, () => {
    // Reset to first page when time range changes
    currentPage.value = 1;
    
    // Set default custom date range if custom is selected
    if (selectedTimeRange.value === 'custom') {
        const today = new Date();
        const sixMonthsAgo = new Date();
        sixMonthsAgo.setMonth(today.getMonth() - 6);
        
        customDateRange.value.start = sixMonthsAgo.toISOString().split('T')[0];
        customDateRange.value.end = today.toISOString().split('T')[0];
    }
});

// Watch for user filter changes in chart view
watch(selectedUserForChart, () => {
    // Reset to first page when user filter changes
    currentPage.value = 1;
});

const filteredChartUsers = computed(() => {
    if (!chartUserSearch.value.trim()) {
        return props.users;
    }
    
    const query = chartUserSearch.value.toLowerCase();
    return props.users.filter(user => 
        user.name.toLowerCase().includes(query) ||
        user.email.toLowerCase().includes(query) ||
        (user.pangkat && user.pangkat.toLowerCase().includes(query))
    );
});

const filteredPayrolls = computed(() => {
    let filtered = props.payrolls.data;

    // Filter by search query
    if (searchQuery.value.trim()) {
        const query = searchQuery.value.toLowerCase();
        filtered = filtered.filter(payroll => 
            payroll.user.name.toLowerCase().includes(query) ||
            payroll.user.email.toLowerCase().includes(query) ||
            (payroll.user.pangkat && payroll.user.pangkat.toLowerCase().includes(query))
        );
    }

    // Filter by selected user
    if (selectedFilter.value !== 'all') {
        filtered = filtered.filter(payroll => 
            payroll.user_id.toString() === selectedFilter.value
        );
    }

    return filtered;
});

const paginatedPayrolls = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage.value;
    const endIndex = startIndex + itemsPerPage.value;
    return filteredPayrolls.value.slice(startIndex, endIndex);
});

const totalPages = computed(() => {
    return Math.ceil(filteredPayrolls.value.length / itemsPerPage.value);
});

const paginationInfo = computed(() => {
    const startIndex = (currentPage.value - 1) * itemsPerPage.value;
    const endIndex = Math.min(currentPage.value * itemsPerPage.value, filteredPayrolls.value.length);
    return {
        start: startIndex,
        end: endIndex,
        total: filteredPayrolls.value.length
    };
});

const chartTitle = computed(() => {
    if (selectedUserForChart.value === 'all') {
        return 'Visualisasi Data Gaji: Semua User';
    } else {
        const user = props.users.find(u => u.id.toString() === selectedUserForChart.value);
        return `Visualisasi Data Gaji: ${user?.name || 'User'}`;
    }
});

const getTimeRangeLabel = (value: string) => {
    switch (value) {
        case '4':
            return '4 Minggu';
        case '8':
            return '8 Minggu';
        case '12':
            return '12 Minggu';
        case '6':
            return '6 Bulan';
        case '12':
            return '12 Bulan';
        case '24':
            return '24 Bulan';
        case 'all':
            return 'Semua';
        case 'custom':
            return 'Custom Range';
        default:
            return 'Semua';
    }
};

const getUserName = (userId: string) => {
    if (userId === 'all') return 'Semua User';
    const user = props.users.find(u => u.id.toString() === userId);
    return user ? `${user.name} - ${user.pangkat || 'Tidak ada pangkat'}` : 'Tidak ada user';
};

const filteredChartData = computed(() => {
    let data = props.chartData;
    
    // Filter by user if specific user is selected
    if (selectedUserForChart.value !== 'all') {
        const userId = parseInt(selectedUserForChart.value);
        // Filter chart data based on actual payroll records for the selected user
        const userPayrolls = props.payrolls.data.filter(payroll => payroll.user_id === userId);
        
        if (userPayrolls.length > 0) {
            // Generate chart data from user's payroll records
            data = userPayrolls.map(payroll => ({
                month: new Date(payroll.tanggal_dibayarkan).toLocaleDateString('id-ID', { month: 'short', year: 'numeric' }),
                gaji_bersih: payroll.gaji_bersih,
                total_potongan: payroll.total_deductions,
                gaji_bersih_setelah_potongan: payroll.net_salary,
            }));
        }
    }
    
    // Filter by time range
    if (selectedTimeRange.value === 'custom' && customDateRange.value.start && customDateRange.value.end) {
        const startDate = new Date(customDateRange.value.start);
        const endDate = new Date(customDateRange.value.end);
        
        data = data.filter(item => {
            // Parse the month string (e.g., "Jan 2025") to a date
            const monthNames = ['Jan', 'Feb', 'Mar', 'Apr', 'May', 'Jun', 'Jul', 'Aug', 'Sep', 'Oct', 'Nov', 'Dec'];
            const parts = item.month.split(' ');
            if (parts.length === 2) {
                const monthIndex = monthNames.indexOf(parts[0]);
                const year = parseInt(parts[1]);
                if (monthIndex !== -1 && !isNaN(year)) {
                    const itemDate = new Date(year, monthIndex, 1);
                    return itemDate >= startDate && itemDate <= endDate;
                }
            }
            return true;
        });
    } else if (selectedTimeRange.value !== 'all') {
        const months = parseInt(selectedTimeRange.value);
        if (!isNaN(months)) {
            data = data.slice(-months);
        }
    }
    
    return data;
});

const formatCurrency = (value: number): string => {
    return new Intl.NumberFormat('id-ID', {
        // style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0,
    }).format(value);
};

const formatDate = (dateString: string): string => {
    return new Date(dateString).toLocaleDateString('id-ID', {
        year: 'numeric',
        month: 'long',
        day: 'numeric',
    });
};

const printPayrollSlip = (payroll: PayrollRecord) => {
    // Create payroll data structure for the printer component
    const payrollData = {
        user: payroll.user,
        gaji_bersih: payroll.gaji_bersih,
        tanggal_dibayarkan: payroll.tanggal_dibayarkan,
        total_deductions: payroll.total_deductions,
        net_salary: payroll.net_salary,
        deductions: {
            mdr_bws_bri: payroll.mdr_bws_bri || 0,
            btn: payroll.btn || 0,
            twp: payroll.twp || 0,
            persit: payroll.persit || 0,
            ikka_persit: payroll.ikka_persit || 0,
            koperasi: payroll.koperasi || 0,
            barak: payroll.barak || 0,
            kowad: payroll.kowad || 0,
            titipan: payroll.titipan || 0,
            tenes: payroll.tenes || 0,
            remaja: payroll.remaja || 0,
            galon: payroll.galon || 0,
            sosial: payroll.sosial || 0,
            pns: payroll.pns || 0,
            bel_wajib_kop: payroll.bel_wajib_kop || 0,
            custom_1: { name: payroll.custom_1_name || '', value: payroll.custom_1_value || 0 },
            custom_2: { name: payroll.custom_2_name || '', value: payroll.custom_2_value || 0 },
            custom_3: { name: payroll.custom_3_name || '', value: payroll.custom_3_value || 0 },
            custom_4: { name: payroll.custom_4_name || '', value: payroll.custom_4_value || 0 },
            custom_5: { name: payroll.custom_5_name || '', value: payroll.custom_5_value || 0 },
        }
    };
    
    // Call the print function from the component with the actual data
    payrollSlipPrinterRef.value?.printPayrollSlip(payrollData);
};

const printChartData = () => {
    if (filteredChartData.value.length === 0) return;
    
    // Try to get actual payroll data first
    if (props.payrolls.data.length > 0) {
        const actualPayroll = props.payrolls.data[0];
        printPayrollSlip(actualPayroll);
        return;
    }
    
    // Fallback to chart data if no payroll records exist
    const sampleData = filteredChartData.value[0];
    const payrollData = {
        user: { 
            id: selectedUserForChart.value === 'all' ? 0 : parseInt(selectedUserForChart.value), 
            name: selectedUserForChart.value === 'all' ? 'Sample Data' : props.users.find(u => u.id.toString() === selectedUserForChart.value)?.name || 'Sample Data', 
            email: 'sample@example.com' 
        },
        gaji_bersih: sampleData.gaji_bersih,
        tanggal_dibayarkan: new Date().toISOString().split('T')[0],
        total_deductions: sampleData.total_potongan,
        net_salary: sampleData.gaji_bersih_setelah_potongan,
        deductions: {
            mdr_bws_bri: 0,
            btn: 0,
            twp: 0,
            persit: 0,
            ikka_persit: 0,
            koperasi: 0,
            barak: 0,
            kowad: 0,
            titipan: 0,
            tenes: 0,
            remaja: 0,
            galon: 0,
            sosial: 0,
            pns: 0,
            bel_wajib_kop: 0,
            custom_1: { name: '', value: 0 },
            custom_2: { name: '', value: 0 },
            custom_3: { name: '', value: 0 },
            custom_4: { name: '', value: 0 },
            custom_5: { name: '', value: 0 },
        }
    };
    
    // Call the print function from the component with the actual data
    payrollSlipPrinterRef.value?.printPayrollSlip(payrollData);
};

const showDetail = (payroll: PayrollRecord) => {
    selectedPayroll.value = payroll;
    showDetailModal.value = true;
};

const closeDetail = () => {
    showDetailModal.value = false;
    selectedPayroll.value = null;
};

const fetchChartData = async () => {
    if (selectedUserForChart.value === 'all') {
        // Use the default chart data for all users
        return;
    }
    
    try {
        const timeRange = selectedTimeRange.value === 'custom' ? 'all' : selectedTimeRange.value;
        const response = await fetch(`/admin/payroll/user/${selectedUserForChart.value}/chart/${timeRange}`);
        if (response.ok) {
            const data = await response.json();
            // Update the chart data
            // Note: This would require updating the parent component's chart data
            // For now, we'll use the existing chart data structure
        }
    } catch (error) {
        console.error('Error fetching chart data:', error);
    }
};

// Watch for filter changes and fetch new data
watch([selectedUserForChart, selectedTimeRange, customDateRange], () => {
    if (viewMode.value === 'chart') {
        fetchChartData();
    }
}, { deep: true });

const goToPage = (page: number) => {
    if (page >= 1 && page <= totalPages.value) {
        currentPage.value = page;
    }
};

const goToFirstPage = () => {
    currentPage.value = 1;
};

const goToLastPage = () => {
    currentPage.value = totalPages.value;
};

const goToPreviousPage = () => {
    if (currentPage.value > 1) {
        currentPage.value--;
    }
};

const goToNextPage = () => {
    if (currentPage.value < totalPages.value) {
        currentPage.value++;
    }
};

// Reset to first page when filters change
watch([selectedFilter, searchQuery], () => {
    currentPage.value = 1;
});

// Helper to get visible pages for pagination
const getVisiblePages = () => {
    const pages: (number | string)[] = [];
    const startPage = Math.max(1, currentPage.value - 2);
    const endPage = Math.min(totalPages.value, currentPage.value + 2);

    if (startPage > 1) {
        pages.push(1);
        if (startPage > 2) {
            pages.push('...');
        }
    }
    for (let i = startPage; i <= endPage; i++) {
        pages.push(i);
    }
    if (endPage < totalPages.value) {
        if (endPage < totalPages.value - 1) {
            pages.push('...');
        }
        pages.push(totalPages.value);
    }
    return pages;
};
</script>

<template>
    <Head title="Lihat Gaji" />

    <AppLayout :breadcrumbs="breadcrumbs">
        <div class="flex h-full flex-1 flex-col gap-6 rounded-xl p-6 overflow-x-auto">
            <!-- Header -->
            <div class="space-y-2">
                <h1 class="text-2xl font-bold tracking-tight">Lihat Gaji Personil</h1>
                <p class="text-muted-foreground">
                    Lihat riwayat gaji dan visualisasi data gaji personil.
                </p>
            </div>

            <!-- Controls -->
            <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center justify-between">
                <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                    <!-- Search -->
                    <div class="flex items-center space-x-2">
                        <Search class="h-4 w-4" />
                        <Input
                            v-model="searchQuery"
                            placeholder="Cari personil..."
                            class="w-64"
                        />
                    </div>
                    
                    <!-- Filter -->
                    <div class="flex items-center space-x-2">
                        <Filter class="h-4 w-4" />
                        <Label for="filter-select">Filter:</Label>
                        <Select v-model="selectedFilter">
                            <SelectTrigger class="w-48">
                                <SelectValue placeholder="Semua Personil" />
                            </SelectTrigger>
                            <SelectContent>
                                <SelectItem value="all">Semua Personil</SelectItem>
                                <SelectItem 
                                    v-for="user in props.users" 
                                    :key="user.id" 
                                    :value="user.id.toString()"
                                >
                                    {{ user.name }} - {{ user.pangkat || 'Tidak ada pangkat' }}
                                </SelectItem>
                            </SelectContent>
                        </Select>
                    </div>

                    <!-- View Mode Toggle -->
                    <div class="flex items-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            :class="{ 'bg-primary text-primary-foreground': viewMode === 'table' }"
                            @click="viewMode = 'table'"
                        >
                            <Eye class="h-4 w-4 mr-2" />
                            Tabel
                        </Button>
                        <Button
                            variant="outline"
                            size="sm"
                            :class="{ 'bg-primary text-primary-foreground': viewMode === 'chart' }"
                            @click="viewMode = 'chart'"
                        >
                            <BarChart3 class="h-4 w-4 mr-2" />
                            Grafik
                        </Button>
                    </div>
                </div>

                <!-- Chart Filters (Outside Card) -->
                <div v-if="viewMode === 'chart'" class="flex flex-col lg:flex-row gap-4 items-start lg:items-center justify-between p-4 bg-muted/50 rounded-lg border">
                    <div class="flex flex-col sm:flex-row gap-4 items-start sm:items-center">
                        <!-- Search Bar for Users -->
                        <div class="flex items-center space-x-2">
                            <Search class="h-4 w-4" />
                            <Input
                                v-model="chartUserSearch"
                                placeholder="Cari user..."
                                class="w-48"
                            />
                        </div>
                        
                        <!-- User Filter for Chart -->
                        <div class="flex items-center space-x-2">
                            <Users class="h-4 w-4" />
                            <Label for="chart-user-filter">User:</Label>
                            <Select v-model="selectedUserForChart">
                                <SelectTrigger class="w-48">
                                    <SelectValue :placeholder="selectedUserForChart === 'all' ? 'Semua User' : getUserName(selectedUserForChart)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="all">Semua User</SelectItem>
                                    <SelectItem 
                                        v-for="user in filteredChartUsers" 
                                        :key="user.id" 
                                        :value="user.id.toString()"
                                    >
                                        {{ user.name }} - {{ user.pangkat || 'Tidak ada pangkat' }}
                                    </SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                        
                        <!-- Time Range Filter -->
                        <div class="flex items-center space-x-2">
                            <Calendar class="h-4 w-4" />
                            <Label for="time-range">Rentang Waktu:</Label>
                            <Select v-model="selectedTimeRange">
                                <SelectTrigger class="w-32">
                                    <SelectValue :placeholder="getTimeRangeLabel(selectedTimeRange)" />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="4">4 Minggu</SelectItem>
                                    <SelectItem value="8">8 Minggu</SelectItem>
                                    <SelectItem value="12">12 Minggu</SelectItem>
                                    <SelectItem value="6">6 Bulan</SelectItem>
                                    <SelectItem value="12">12 Bulan</SelectItem>
                                    <SelectItem value="24">24 Bulan</SelectItem>
                                    <SelectItem value="all">Semua</SelectItem>
                                    <SelectItem value="custom">Custom Range</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                </div>
                
                <!-- Custom Date Range -->
                <div v-if="viewMode === 'chart' && selectedTimeRange === 'custom'" class="flex flex-col sm:flex-row gap-4 items-start sm:items-center p-4 bg-blue-50 dark:bg-blue-950/20 rounded-lg border border-blue-200 dark:border-blue-800">
                    <div class="flex items-center space-x-2">
                        <Calendar class="h-4 w-4 text-blue-600" />
                        <Label for="start-date" class="text-blue-700 dark:text-blue-300">Dari:</Label>
                        <Input
                            id="start-date"
                            v-model="customDateRange.start"
                            type="date"
                            class="w-40"
                        />
                    </div>
                    <div class="flex items-center space-x-2">
                        <Calendar class="h-4 w-4 text-blue-600" />
                        <Label for="end-date" class="text-blue-700 dark:text-blue-300">Sampai:</Label>
                        <Input
                            id="end-date"
                            v-model="customDateRange.end"
                            type="date"
                            class="w-40"
                        />
                    </div>
                </div>

                <!-- Stats -->
                <div class="flex items-center space-x-4 text-sm text-muted-foreground">
                    <div class="flex items-center space-x-2">
                        <Users class="h-4 w-4" />
                        <span>{{ props.users.length }} Personil</span>
                    </div>
                    <div class="flex items-center space-x-2">
                        <TrendingUp class="h-4 w-4" />
                        <span>{{ props.payrolls.total }} Record</span>
                    </div>
                </div>
            </div>

            <!-- Chart View -->
            <Card v-if="viewMode === 'chart'" class="w-full">
                <CardHeader>
                    <div class="flex items-center justify-between">
                        <div class="flex items-center space-x-2">
                            <BarChart3 class="h-5 w-5" />
                            <span>{{ chartTitle }}</span>
                        </div>
                        <Button 
                            v-if="filteredChartData.length > 0"
                            @click="printChartData()"
                            variant="outline"
                            size="sm"
                        >
                            <Printer class="h-4 w-4 mr-2" />
                            Cetak Struk
                        </Button>
                    </div>
                    <CardDescription>
                        Grafik perbandingan gaji bersih, potongan, dan gaji bersih setelah potongan.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="space-y-6">
                        <!-- Legend -->
                        <div class="flex flex-wrap gap-4 text-sm">
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-green-500 rounded"></div>
                                <span>Gaji Bersih</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-red-500 rounded"></div>
                                <span>Total Potongan</span>
                            </div>
                            <div class="flex items-center space-x-2">
                                <div class="w-4 h-4 bg-blue-500 rounded"></div>
                                <span>Gaji Bersih Setelah Potongan</span>
                            </div>
                        </div>

                        <!-- Professional Line Chart -->
                        <PayrollLineChart 
                            :chart-data="filteredChartData" 
                            :height="500"
                        />
                    </div>
                </CardContent>
            </Card>

            <!-- Table View -->
            <Card v-if="viewMode === 'table'" class="w-full">
                <CardHeader>
                    <CardTitle class="flex items-center space-x-2">
                        <Eye class="h-5 w-5" />
                        <span>Riwayat Gaji Personil</span>
                    </CardTitle>
                    <CardDescription>
                        Data riwayat gaji dan potongan personil.
                    </CardDescription>
                </CardHeader>
                <CardContent>
                    <div class="overflow-x-auto">
                        <div v-if="filteredPayrolls.length === 0" class="text-center py-12 text-muted-foreground">
                            <Users class="h-12 w-12 mx-auto mb-4 opacity-50" />
                            <p>Tidak ada data gaji yang ditemukan</p>
                        </div>
                        
                        <table v-else class="w-full table-auto">
                            <thead>
                                <tr class="border-b">
                                    <th class="text-center p-4 font-medium">Personil</th>
                                    <th class="text-center p-4 font-medium">Pangkat</th>
                                    <th class="text-center p-4 font-medium">Gaji Bersih (Rp)</th>
                                    <th class="text-center p-4 font-medium">Total Potongan (Rp)</th>
                                    <th class="text-center p-4 font-medium">Gaji Bersih Setelah Potongan (Rp)</th>
                                    <th class="text-center p-4 font-medium">Tanggal Dibayar</th>
                                    <th class="text-center p-4 font-medium">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr v-for="payroll in paginatedPayrolls" :key="payroll.id" class="border-b hover:bg-muted/50 text-left">
                                    <td class="p-4">
                                        <div>
                                            <div class="font-medium ">{{ payroll.user.name }}</div>
                                            <div class="text-sm text-muted-foreground">{{ payroll.user.email }}</div>
                                        </div>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm">{{ payroll.user.pangkat || 'Tidak ada pangkat' }}</span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-medium text-green-600">
                                            {{ formatCurrency(payroll.gaji_bersih) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-medium  text-red-600">
                                            {{ formatCurrency(payroll.total_deductions) }}
                                        </span>
                                    </td>
                                    <td class="p-4 text-right">
                                        <span class="font-bold text-black">
                                            {{ formatCurrency(payroll.net_salary) }}
                                        </span>
                                    </td>
                                    <td class="p-4">
                                        <span class="text-sm">{{ formatDate(payroll.tanggal_dibayarkan) }}</span>
                                    </td>
                                    <td class="p-4">
                                        <div class="flex items-center justify-center space-x-2">
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="h-8 w-8 p-0"
                                                @click="showDetail(payroll)"
                                                title="Lihat Detail"
                                            >
                                                <Info class="h-4 w-4" />
                                            </Button>
                                            <Button
                                                variant="outline"
                                                size="sm"
                                                class="h-8 w-8 p-0"
                                                @click="printPayrollSlip(payroll)"
                                                title="Cetak Struk"
                                            >
                                                <Printer class="h-4 w-4" />
                                            </Button>
                                        </div>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>

                    <!-- Pagination Info -->
                    <div class="mt-4 flex flex-col sm:flex-row items-center justify-between gap-4">
                        <div class="flex items-center space-x-2 text-sm text-muted-foreground">
                            <span>Menampilkan {{ paginationInfo.start }} - {{ paginationInfo.end }} dari {{ paginationInfo.total }} record</span>
                            <span class="hidden sm:inline">(Halaman {{ currentPage }} dari {{ totalPages }})</span>
                        </div>
                        
                        <!-- Items per page selector -->
                        <div class="flex items-center space-x-2">
                            <Label for="items-per-page" class="text-sm">Per halaman:</Label>
                            <Select v-model="itemsPerPage" @update:model-value="(value) => itemsPerPage = Number(value)">
                                <SelectTrigger class="w-20">
                                    <SelectValue />
                                </SelectTrigger>
                                <SelectContent>
                                    <SelectItem value="5">5</SelectItem>
                                    <SelectItem value="10">10</SelectItem>
                                    <SelectItem value="20">20</SelectItem>
                                    <SelectItem value="50">50</SelectItem>
                                </SelectContent>
                            </Select>
                        </div>
                    </div>
                    
                    <!-- Pagination Controls -->
                    <div v-if="totalPages > 1" class="mt-6 flex items-center justify-center space-x-2">
                        <Button
                            variant="outline"
                            size="sm"
                            @click="goToFirstPage"
                            :disabled="currentPage === 1"
                        >
                            <ChevronsLeft class="h-4 w-4" />
                        </Button>
                        
                        <Button
                            variant="outline"
                            size="sm"
                            @click="goToPreviousPage"
                            :disabled="currentPage === 1"
                        >
                            <ChevronLeft class="h-4 w-4" />
                        </Button>
                        
                        <!-- Page Numbers -->
                        <div class="flex items-center space-x-1">
                            <template v-for="page in getVisiblePages()" :key="page">
                                <Button
                                    v-if="typeof page === 'number'"
                                    variant="outline"
                                    size="sm"
                                    :class="{ 'bg-primary text-primary-foreground': page === currentPage }"
                                    @click="goToPage(page)"
                                >
                                    {{ page }}
                                </Button>
                                <span v-else class="px-2 text-muted-foreground">{{ page }}</span>
                            </template>
                        </div>
                        
                        <Button
                            variant="outline"
                            size="sm"
                            @click="goToNextPage"
                            :disabled="currentPage === totalPages"
                        >
                            <ChevronRight class="h-4 w-4" />
                        </Button>
                        
                        <Button
                            variant="outline"
                            size="sm"
                            @click="goToLastPage"
                            :disabled="currentPage === totalPages"
                        >
                            <ChevronsRight class="h-4 w-4" />
                        </Button>
                    </div>
                </CardContent>
            </Card>
        </div>
        
        <!-- Payroll Slip Printer Component -->
        <PayrollSlipPrinter 
            ref="payrollSlipPrinterRef"
            :payroll-data="{
                user: { id: 0, name: '', email: '' },
                gaji_bersih: 0,
                tanggal_dibayarkan: new Date().toISOString().split('T')[0],
                total_deductions: 0,
                net_salary: 0,
                deductions: {
                    mdr_bws_bri: 0,
                    btn: 0,
                    twp: 0,
                    persit: 0,
                    ikka_persit: 0,
                    koperasi: 0,
                    barak: 0,
                    kowad: 0,
                    titipan: 0,
                    tenes: 0,
                    remaja: 0,
                    galon: 0,
                    sosial: 0,
                    pns: 0,
                    bel_wajib_kop: 0,
                    custom_1: { name: '', value: 0 },
                    custom_2: { name: '', value: 0 },
                    custom_3: { name: '', value: 0 },
                    custom_4: { name: '', value: 0 },
                    custom_5: { name: '', value: 0 },
                }
            }"
        />
        
        <!-- Payroll Detail Modal -->
        <PayrollDetail
            v-if="selectedPayroll"
            :payroll="selectedPayroll"
            :is-open="showDetailModal"
            @close="closeDetail"
        />
    </AppLayout>
</template>