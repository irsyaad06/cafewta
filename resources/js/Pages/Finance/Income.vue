<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { Head, router } from '@inertiajs/vue3';
import { ref, watch } from 'vue';

const props = defineProps({
    transactions: Array,
    filters: Object,
});

const month = ref(props.filters.month || (new Date().getMonth() + 1).toString().padStart(2, '0'));
const year = ref(props.filters.year || new Date().getFullYear().toString());

const months = [
    { value: '01', label: 'Januari' },
    { value: '02', label: 'Februari' },
    { value: '03', label: 'Maret' },
    { value: '04', label: 'April' },
    { value: '05', label: 'Mei' },
    { value: '06', label: 'Juni' },
    { value: '07', label: 'Juli' },
    { value: '08', label: 'Agustus' },
    { value: '09', label: 'September' },
    { value: '10', label: 'Oktober' },
    { value: '11', label: 'November' },
    { value: '12', label: 'Desember' },
];

const currentYear = new Date().getFullYear();
const years = Array.from({ length: 5 }, (_, i) => currentYear - i);

const applyFilter = () => {
    router.get(route('finance.income'), {
        month: month.value,
        year: year.value,
    }, { preserveState: true, replace: true });
};

watch([month, year], () => {
    applyFilter();
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', { style: 'currency', currency: 'IDR' }).format(value);
};

const formatDate = (dateString) => {
    return new Date(dateString).toLocaleString('id-ID', {
        day: '2-digit', month: '2-digit', year: 'numeric',
        hour: '2-digit', minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Keuangan - Pemasukan" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                <h2 class="text-xl font-semibold leading-tight text-gray-800">
                    Pemasukan (Income)
                </h2>
                <div class="flex items-center gap-2 sm:gap-4 w-full sm:w-auto">
                    <select v-model="month" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">Semua Bulan</option>
                        <option v-for="m in months" :key="m.value" :value="m.value">{{ m.label }}</option>
                    </select>
                    
                    <select v-model="year" class="w-full sm:w-auto rounded-md border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 sm:text-sm">
                        <option value="">Semua Tahun</option>
                        <option v-for="y in years" :key="y" :value="y">{{ y }}</option>
                    </select>

                    <a :href="route('finance.income.export', { month, year })" class="inline-flex items-center justify-center rounded-md bg-green-600 px-4 py-2 text-sm font-semibold text-white shadow-sm hover:bg-green-500 focus-visible:outline focus-visible:outline-2 focus-visible:outline-offset-2 focus-visible:outline-green-600 transition-all">
                        <svg class="-ml-0.5 mr-1.5 h-5 w-5 hidden sm:block" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" d="M3 16.5v2.25A2.25 2.25 0 005.25 21h13.5A2.25 2.25 0 0021 18.75V16.5M16.5 12L12 16.5m0 0L7.5 12m4.5 4.5V3" />
                        </svg>
                        Export
                    </a>
                </div>
            </div>
        </template>

        <div class="py-12">
            <div class="mx-auto max-w-7xl sm:px-6 lg:px-8">
                
                <div class="grid grid-cols-1 md:grid-cols-3 gap-6 mb-8 px-4 sm:px-0">
                    <!-- Summary Cards -->
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl p-6 border border-gray-100">
                        <div class="text-sm font-medium text-gray-500 mb-1">Total Tagihan</div>
                        <div class="text-2xl font-extrabold text-gray-900">
                            {{ formatCurrency(transactions.reduce((sum, t) => sum + parseFloat(t.total_amount), 0)) }}
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl p-6 border border-gray-100">
                        <div class="text-sm font-medium text-gray-500 mb-1">Total HPP</div>
                        <div class="text-2xl font-extrabold text-orange-600">
                            {{ formatCurrency(transactions.reduce((sum, t) => sum + parseFloat(t.total_hpp), 0)) }}
                        </div>
                    </div>
                    <div class="bg-white overflow-hidden shadow-sm rounded-2xl p-6 border border-gray-100">
                        <div class="text-sm font-medium text-gray-500 mb-1">Total Keuntungan Bersih</div>
                        <div class="text-2xl font-extrabold text-green-600">
                            {{ formatCurrency(transactions.reduce((sum, t) => sum + parseFloat(t.total_profit), 0)) }}
                        </div>
                    </div>
                </div>

                <div class="bg-white overflow-hidden shadow-sm sm:rounded-2xl border border-gray-100 mx-4 sm:mx-0">
                    <div class="p-0 overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="py-3.5 pl-4 pr-3 text-left text-sm font-semibold text-gray-900 sm:pl-6">Tanggal</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">No Invoice</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Kasir</th>
                                    <th scope="col" class="px-3 py-3.5 text-left text-sm font-semibold text-gray-900">Metode</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Tagihan</th>
                                    <th scope="col" class="px-3 py-3.5 text-right text-sm font-semibold text-gray-900">Keuntungan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-gray-200 bg-white">
                                <tr v-for="transaction in transactions" :key="transaction.id" class="hover:bg-gray-50 transition-colors">
                                    <td class="whitespace-nowrap py-4 pl-4 pr-3 text-sm text-gray-500 sm:pl-6">
                                        {{ formatDate(transaction.created_at) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-medium text-gray-900">
                                        {{ transaction.invoice_number }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        {{ transaction.user ? transaction.user.name : '-' }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm text-gray-500">
                                        <span class="inline-flex items-center rounded-md bg-gray-100 px-2.5 py-1 text-xs font-medium text-gray-800">
                                            {{ transaction.payment_method ? transaction.payment_method.name : '-' }}
                                        </span>
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-gray-900 text-right">
                                        {{ formatCurrency(transaction.total_amount) }}
                                    </td>
                                    <td class="whitespace-nowrap px-3 py-4 text-sm font-bold text-green-600 text-right">
                                        {{ formatCurrency(transaction.total_profit) }}
                                    </td>
                                </tr>
                                <tr v-if="transactions.length === 0">
                                    <td colspan="6" class="py-12 text-center text-gray-500 text-sm">
                                        Tidak ada data pemasukan pada periode ini.
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
