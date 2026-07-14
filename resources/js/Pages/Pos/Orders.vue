<script setup>
import { ref, computed, watch } from 'vue';
import { Head, Link, useForm, router, usePage } from '@inertiajs/vue3';
import html2canvas from 'html2canvas';

const page = usePage();
const userRole = computed(() => page.props.auth?.user?.role);
const isDapurOrPelayan = computed(() => userRole.value === 'kitchen' || userRole.value === 'waiter');

const props = defineProps({
    transactions: Array,
    filters: {
        type: Object,
        default: () => ({ search: '', period: 'today' })
    }
});

const searchQuery = ref(props.filters.search || '');
const periodQuery = ref(props.filters.period || 'today');

let debounceTimeout = null;
watch([searchQuery, periodQuery], ([search, period]) => {
    clearTimeout(debounceTimeout);
    debounceTimeout = setTimeout(() => {
        router.get(route('pos.orders'), { search, period }, {
            preserveState: true,
            preserveScroll: true,
            replace: true,
        });
    }, 300);
});

const activeTab = ref(userRole.value === 'kitchen' ? 'completed' : (userRole.value === 'waiter' ? 'cooking' : 'pending'));
const selectedTransaction = ref(null);
const isPaymentModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const successTransaction = ref(null);
const invoiceElement = ref(null);
const isDownloading = ref(false);
const isPrinting = ref(false);

const form = useForm({
    status: 'completed',
    amount_paid: 0,
});

const filteredTransactions = computed(() => {
    return props.transactions.filter(t => t.status === activeTab.value);
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const formatDate = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleTimeString('id-ID', { hour: '2-digit', minute: '2-digit' });
};

const changeAmount = computed(() => {
    if (!selectedTransaction.value || !form.amount_paid) return 0;
    return Math.max(0, form.amount_paid - selectedTransaction.value.total_amount);
});

const openPaymentModal = (transaction) => {
    selectedTransaction.value = transaction;
    form.amount_paid = transaction.amount_paid > 0 ? transaction.amount_paid : transaction.total_amount;
    form.status = 'completed';
    isPaymentModalOpen.value = true;
};

const closePaymentModal = () => {
    isPaymentModalOpen.value = false;
    selectedTransaction.value = null;
    form.reset();
};

const submitPayment = () => {
    if (!selectedTransaction.value) return;

    form.patch(route('pos.updateStatus', selectedTransaction.value.id), {
        preserveScroll: true,
        onSuccess: () => {
            successTransaction.value = {
                ...selectedTransaction.value,
                status: 'completed',
                amount_paid: form.amount_paid
            };
            closePaymentModal();
            isSuccessModalOpen.value = true;
        }
    });
};

const formatDateTime = (dateString) => {
    if (!dateString) return '';
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const downloadInvoice = async () => {
    if (!invoiceElement.value || !successTransaction.value) return;
    try {
        isDownloading.value = true;
        const canvas = await html2canvas(invoiceElement.value, {
            scale: 2,
            backgroundColor: '#ffffff',
            logging: false,
            useCORS: true
        });
        const link = document.createElement('a');
        link.download = `Invoice-${successTransaction.value.invoice_number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (error) {
        console.error('Download failed', error);
        alert('Gagal mengunduh invoice.');
    } finally {
        isDownloading.value = false;
    }
};

const printInvoice = () => {
    if (!invoiceElement.value) return;
    
    isPrinting.value = true;
    const printContent = invoiceElement.value.innerHTML;
    
    // Get all stylesheets from current document
    let stylesHtml = '';
    for (const node of [...document.querySelectorAll('link[rel="stylesheet"], style')]) {
        stylesHtml += node.outerHTML;
    }

    const iframe = document.createElement('iframe');
    iframe.style.display = 'none';
    document.body.appendChild(iframe);
    
    iframe.contentWindow.document.open();
    iframe.contentWindow.document.write(`
        <!DOCTYPE html>
        <html>
        <head>
            <title>Print Invoice</title>
            ${stylesHtml}
            <style>
                body { background: white !important; margin: 0; padding: 20px; font-family: sans-serif; display: flex; justify-content: center; }
                .invoice-container { width: 100%; max-width: 400px; }
            </style>
        </head>
        <body onload="setTimeout(() => { window.print(); }, 500)">
            <div class="invoice-container">
                ${printContent}
            </div>
        </body>
        </html>
    `);
    iframe.contentWindow.document.close();
    
    // Cleanup
    setTimeout(() => {
        document.body.removeChild(iframe);
        isPrinting.value = false;
    }, 2000);
};

const updateStatus = (transaction, newStatus) => {
    form.status = newStatus;
    form.patch(route('pos.updateStatus', transaction.id), {
        preserveScroll: true,
    });
};
</script>

<template>
    <Head title="Daftar Pesanan" />

    <div class="min-h-screen bg-gray-50 flex flex-col font-sans">
        
        <!-- Header -->
        <header class="bg-white border-b border-gray-200 sticky top-0 z-30 shadow-sm">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between py-4 sm:py-0 sm:h-20 gap-4">
                    <div class="flex items-center gap-4">
                        <Link v-if="!isDapurOrPelayan" :href="route('pos.index')" class="p-2.5 bg-gray-50 rounded-xl hover:bg-gray-100 text-gray-600 hover:text-primary-600 transition-all border border-gray-200">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                        </Link>
                        <div>
                            <h1 class="text-xl sm:text-2xl font-extrabold text-gray-800 tracking-tight">Daftar Pesanan</h1>
                            <p class="text-xs sm:text-sm text-gray-500 font-medium">Kelola pesanan pelanggan dari satu tempat</p>
                        </div>
                    </div>
                    
                    <!-- Filters or Role Info -->
                    <div class="flex w-full sm:w-auto items-center justify-between sm:justify-end gap-3">
                        <template v-if="!isDapurOrPelayan">
                            <div class="relative flex-1 sm:flex-none">
                                <select 
                                    v-model="periodQuery"
                                    class="w-full sm:w-auto appearance-none bg-gray-50 border border-gray-200 text-gray-700 text-sm font-bold rounded-xl focus:ring-primary-500 focus:border-primary-500 block px-4 py-2.5 pr-10 shadow-sm"
                                >
                                    <option value="today">Hari Ini</option>
                                    <option value="yesterday">Kemarin</option>
                                    <option value="this_week">Minggu Ini</option>
                                    <option value="this_month">Bulan Ini</option>
                                    <option value="all_time">Semua Waktu</option>
                                </select>
                                <div class="pointer-events-none absolute inset-y-0 right-0 flex items-center px-3 text-gray-500">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                                </div>
                            </div>
                            
                            <div class="relative flex-1 sm:flex-none">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <svg class="w-4 h-4 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </div>
                                <input 
                                    type="text" 
                                    v-model="searchQuery" 
                                    placeholder="Cari invoice..."
                                    class="w-full sm:w-64 pl-10 pr-4 py-2.5 bg-gray-50 border border-gray-200 text-gray-900 text-sm font-medium rounded-xl focus:ring-primary-500 focus:border-primary-500 shadow-sm transition-colors"
                                >
                            </div>
                        </template>

                        <div v-else class="flex items-center gap-3">
                            <span class="px-3 py-1.5 bg-gray-100 text-gray-600 rounded-lg text-sm font-bold border border-gray-200">
                                Login sebagai: {{ userRole === 'kitchen' ? 'Dapur' : 'Pelayan' }}
                            </span>
                        </div>

                        <!-- Logout Button -->
                        <Link :href="route('logout')" method="post" as="button" class="inline-flex items-center justify-center space-x-1 md:space-x-2 bg-red-50 hover:bg-red-100 text-red-600 border border-red-200 px-3 md:px-4 py-2 rounded-xl transition-colors shadow-sm w-auto">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"></path></svg>
                            <span class="font-bold text-sm md:text-base hidden sm:inline">Logout</span>
                        </Link>
                    </div>
                </div>
            </div>
        </header>

        <!-- Main Content -->
        <main class="flex-1 max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col">
            
            <!-- Segmented Control Tabs -->
            <div class="flex mb-8 overflow-x-auto pb-2 md:justify-center hide-scrollbar">
                <div class="inline-flex bg-gray-200/80 p-1.5 rounded-2xl shadow-inner border border-gray-200/50 min-w-max">
                    <button 
                        @click="activeTab = 'pending'"
                        :class="[
                            'flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300',
                            activeTab === 'pending' 
                                ? 'bg-white text-primary-600 shadow-md ring-1 ring-gray-900/5 transform scale-100' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200 scale-95'
                        ]"
                    >
                        <span>Pesanan Baru</span>
                        <span v-if="props.transactions.filter(t => t.status === 'pending').length > 0" 
                              class="ml-2.5 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-extrabold text-white bg-red-500 rounded-full shadow-sm">
                            {{ props.transactions.filter(t => t.status === 'pending').length }}
                        </span>
                    </button>
                    <button 
                        @click="activeTab = 'completed'"
                        :class="[
                            'flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300',
                            activeTab === 'completed' 
                                ? 'bg-white text-primary-600 shadow-md ring-1 ring-gray-900/5 transform scale-100' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200 scale-95'
                        ]"
                    >
                        Pesanan Terbayar
                        <span v-if="props.transactions.filter(t => t.status === 'completed').length > 0" 
                              class="ml-2.5 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-extrabold text-white bg-yellow-500 rounded-full shadow-sm">
                            {{ props.transactions.filter(t => t.status === 'completed').length }}
                        </span>
                    </button>
                    <button 
                        @click="activeTab = 'cooking'"
                        :class="[
                            'flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300',
                            activeTab === 'cooking' 
                                ? 'bg-white text-primary-600 shadow-md ring-1 ring-gray-900/5 transform scale-100' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200 scale-95'
                        ]"
                    >
                        Sedang Dimasak
                        <span v-if="props.transactions.filter(t => t.status === 'cooking').length > 0" 
                              class="ml-2.5 inline-flex items-center justify-center min-w-[20px] h-5 px-1.5 text-[11px] font-extrabold text-white bg-blue-500 rounded-full shadow-sm">
                            {{ props.transactions.filter(t => t.status === 'cooking').length }}
                        </span>
                    </button>
                    <button 
                        @click="activeTab = 'delivered'"
                        :class="[
                            'flex items-center justify-center px-4 sm:px-6 py-2.5 rounded-xl text-sm font-bold transition-all duration-300',
                            activeTab === 'delivered' 
                                ? 'bg-white text-primary-600 shadow-md ring-1 ring-gray-900/5 transform scale-100' 
                                : 'text-gray-500 hover:text-gray-700 hover:bg-gray-200 scale-95'
                        ]"
                    >
                        Sudah Diantarkan
                    </button>
                </div>
            </div>

            <!-- Empty State -->
            <div v-if="filteredTransactions.length === 0" class="flex flex-col items-center justify-center flex-1 py-16 bg-white rounded-3xl border border-gray-100 shadow-sm">
                <div class="w-24 h-24 bg-gray-50 rounded-full flex items-center justify-center mb-6">
                    <svg class="w-10 h-10 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                </div>
                <h3 class="text-xl font-bold text-gray-800">Belum ada pesanan</h3>
                <p class="text-gray-500 font-medium mt-2 text-center max-w-sm">
                    Tidak ada pesanan di kategori ini.
                </p>
            </div>

            <!-- Transaction Grid -->
            <div v-else class="grid gap-6 sm:grid-cols-2 lg:grid-cols-3">
                <div v-for="transaction in filteredTransactions" :key="transaction.id" class="bg-white rounded-3xl border border-gray-100 shadow-sm hover:shadow-xl transition-all duration-300 overflow-hidden flex flex-col group relative">
                    
                    <!-- Card Accent Line -->
                    <div :class="['absolute top-0 left-0 right-0 h-1.5', {
                        'bg-red-500': transaction.status === 'pending',
                        'bg-yellow-400': transaction.status === 'completed',
                        'bg-blue-500': transaction.status === 'cooking',
                        'bg-green-500': transaction.status === 'delivered',
                    }]"></div>

                    <!-- Card Header -->
                    <div class="p-6 pb-4 border-b border-gray-50">
                        <div class="flex justify-between items-start mb-4">
                            <div>
                                <span class="block text-xs font-bold text-gray-400 uppercase tracking-wider mb-1">{{ formatDate(transaction.created_at) }}</span>
                                <span class="font-extrabold text-lg text-gray-800">{{ transaction.invoice_number }}</span>
                            </div>
                            <span :class="['px-3 py-1 rounded-full text-xs font-black uppercase tracking-wider flex items-center gap-1.5 shadow-sm', {
                                'bg-red-50 text-red-600 ring-1 ring-red-200': transaction.status === 'pending',
                                'bg-yellow-50 text-yellow-600 ring-1 ring-yellow-200': transaction.status === 'completed',
                                'bg-blue-50 text-blue-600 ring-1 ring-blue-200': transaction.status === 'cooking',
                                'bg-green-50 text-green-600 ring-1 ring-green-200': transaction.status === 'delivered',
                            }]">
                                <span :class="['w-1.5 h-1.5 rounded-full', {
                                    'bg-red-500': transaction.status === 'pending',
                                    'bg-yellow-500': transaction.status === 'completed',
                                    'bg-blue-500': transaction.status === 'cooking',
                                    'bg-green-500': transaction.status === 'delivered',
                                }]"></span>
                                {{ 
                                    transaction.status === 'pending' ? 'BARU' : 
                                    transaction.status === 'completed' ? 'TERBAYAR' : 
                                    transaction.status === 'cooking' ? 'DIMASAK' : 'DIANTAR' 
                                }}
                            </span>
                        </div>
                        
                        <div class="inline-flex items-center gap-2 bg-gray-50/80 px-3 py-1.5 rounded-lg border border-gray-100">
                            <svg class="w-4 h-4 text-primary-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                            <span class="font-bold text-sm text-gray-700">{{ transaction.cafe_table ? `Meja ${transaction.cafe_table.table_number}` : 'Takeaway' }}</span>
                        </div>
                    </div>
                    
                    <!-- Order Items -->
                    <div class="p-6 py-4 flex-1 bg-gray-50/30">
                        <h4 class="text-xs font-bold text-gray-400 uppercase tracking-wider mb-3">Detail Pesanan</h4>
                        <ul class="space-y-3">
                            <li v-for="detail in transaction.transaction_details" :key="detail.id" class="flex justify-between items-start text-sm group/item">
                                <div class="flex items-start gap-2.5">
                                    <span class="font-black text-gray-800 bg-gray-100 px-1.5 py-0.5 rounded text-xs">{{ detail.quantity }}x</span>
                                    <span class="text-gray-600 font-medium group-hover/item:text-primary-600 transition-colors">{{ detail.menu_name }}</span>
                                </div>
                                <span class="text-gray-800 font-semibold shrink-0">{{ formatCurrency(detail.subtotal) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Card Footer & Action -->
                    <div class="p-6 pt-5 border-t border-gray-100 bg-white">
                        <div class="flex justify-between items-center mb-5">
                            <span class="text-sm text-gray-500 font-bold uppercase tracking-wider">Total Tagihan</span>
                            <span class="font-black text-xl text-primary-600">{{ formatCurrency(transaction.total_amount) }}</span>
                        </div>
                        
                        <button 
                            v-if="transaction.status === 'pending'"
                            @click="openPaymentModal(transaction)"
                            class="w-full py-3.5 bg-gray-900 text-white font-bold rounded-xl hover:bg-primary-600 transition-all shadow-md hover:shadow-primary-500/30 flex justify-center items-center gap-2 transform active:scale-95"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                            Bayar Pesanan
                        </button>
                        
                        <button 
                            v-if="transaction.status === 'completed'"
                            @click="updateStatus(transaction, 'cooking')"
                            :disabled="form.processing"
                            class="w-full py-3.5 bg-yellow-500 text-white font-bold rounded-xl hover:bg-yellow-600 transition-all shadow-md flex justify-center items-center gap-2 transform active:scale-95"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z"></path></svg>
                            Mulai Masak
                        </button>
                        
                        <button 
                            v-if="transaction.status === 'cooking'"
                            @click="updateStatus(transaction, 'delivered')"
                            :disabled="form.processing"
                            class="w-full py-3.5 bg-blue-600 text-white font-bold rounded-xl hover:bg-blue-700 transition-all shadow-md flex justify-center items-center gap-2 transform active:scale-95"
                        >
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg>
                            Tandai Diantarkan
                        </button>

                    </div>
                </div>
            </div>

        </main>

        <!-- Payment Modal -->
        <div v-if="isPaymentModalOpen" class="fixed inset-0 z-50 flex items-center justify-center p-4">
            <div class="absolute inset-0 bg-gray-900/60 backdrop-blur-sm transition-opacity" @click="closePaymentModal"></div>
            
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg overflow-hidden transform transition-all ring-1 ring-black/5">
                <!-- Modal Header -->
                <div class="bg-gradient-to-r from-gray-900 to-gray-800 p-6 sm:p-8 text-center relative overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                    <h3 class="text-2xl font-extrabold text-white relative z-10 tracking-tight">Penyelesaian Pesanan</h3>
                    <p class="text-gray-300 text-sm mt-2 font-medium relative z-10 font-mono bg-white/10 inline-block px-3 py-1 rounded-full">{{ selectedTransaction?.invoice_number }}</p>
                </div>
                
                <form @submit.prevent="submitPayment" class="p-6 sm:p-8 bg-gray-50/50">
                    
                    <!-- Amount Required -->
                    <div class="mb-8">
                        <div class="flex justify-between items-center p-5 bg-white rounded-2xl border border-gray-200 shadow-sm">
                            <span class="text-gray-500 font-bold uppercase tracking-wider text-sm">Total Tagihan</span>
                            <span class="text-3xl font-black text-gray-900">{{ formatCurrency(selectedTransaction?.total_amount) }}</span>
                        </div>
                    </div>

                    <div class="space-y-6">
                        <div>
                            <label class="block text-sm font-bold text-gray-700 mb-3">Jumlah Uang Diterima</label>
                            <div class="relative group">
                                <div class="absolute inset-y-0 left-0 pl-5 flex items-center pointer-events-none">
                                    <span class="text-gray-400 font-bold text-lg">Rp</span>
                                </div>
                                <input 
                                    type="number" 
                                    v-model="form.amount_paid" 
                                    class="w-full pl-14 pr-5 py-4 bg-white rounded-2xl border border-gray-300 shadow-sm focus:border-primary-500 focus:ring-2 focus:ring-primary-500/20 text-xl font-bold text-gray-900 transition-all"
                                    placeholder="0"
                                    min="0"
                                >
                            </div>
                            <div class="flex flex-wrap gap-2 mt-3">
                                <button type="button" @click="form.amount_paid = selectedTransaction.total_amount" class="text-xs font-bold bg-white border border-gray-200 hover:border-primary-500 hover:text-primary-600 text-gray-600 px-4 py-2 rounded-full transition-colors shadow-sm">Uang Pas</button>
                                <button type="button" @click="form.amount_paid = Math.ceil(selectedTransaction.total_amount / 50000) * 50000" class="text-xs font-bold bg-white border border-gray-200 hover:border-primary-500 hover:text-primary-600 text-gray-600 px-4 py-2 rounded-full transition-colors shadow-sm">Bulatkan 50k</button>
                                <button type="button" @click="form.amount_paid = Math.ceil(selectedTransaction.total_amount / 100000) * 100000" class="text-xs font-bold bg-white border border-gray-200 hover:border-primary-500 hover:text-primary-600 text-gray-600 px-4 py-2 rounded-full transition-colors shadow-sm">Bulatkan 100k</button>
                            </div>
                            <p v-if="form.errors.amount_paid" class="text-red-500 text-xs font-medium mt-2">{{ form.errors.amount_paid }}</p>
                        </div>

                        <!-- Change Display -->
                        <div class="pt-2">
                            <div class="flex justify-between items-center p-5 rounded-2xl border transition-colors" :class="changeAmount >= 0 ? 'bg-green-50/50 border-green-200' : 'bg-red-50/50 border-red-200'">
                                <span class="font-bold text-sm uppercase tracking-wider" :class="changeAmount >= 0 ? 'text-green-700' : 'text-red-700'">Kembalian</span>
                                <span class="text-2xl font-black" :class="changeAmount >= 0 ? 'text-green-600' : 'text-red-600'">
                                    {{ formatCurrency(changeAmount) }}
                                </span>
                            </div>
                            <p v-if="form.amount_paid < selectedTransaction?.total_amount" class="text-red-500 text-sm mt-3 text-center font-bold bg-red-50 py-2 rounded-lg">Peringatan: Pembayaran kurang dari total tagihan!</p>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-4">
                        <button 
                            type="button" 
                            @click="closePaymentModal"
                            class="w-1/3 py-4 rounded-2xl font-bold text-gray-600 bg-white border border-gray-200 hover:bg-gray-50 transition-colors shadow-sm"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing || form.amount_paid < selectedTransaction?.total_amount"
                            class="w-2/3 py-4 rounded-2xl font-bold text-white shadow-lg transition-all flex justify-center items-center gap-2 transform active:scale-95"
                            :class="(form.processing || form.amount_paid < selectedTransaction?.total_amount) ? 'bg-gray-300 cursor-not-allowed shadow-none' : 'bg-primary-600 hover:bg-primary-700 hover:shadow-primary-500/30'"
                        >
                            <svg v-if="!form.processing" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 13l4 4L19 7"></path></svg>
                            <svg v-else class="animate-spin -ml-1 mr-3 h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                            </svg>
                            <span>{{ form.processing ? 'Memproses...' : 'Konfirmasi Selesai' }}</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success Bottom Sheet -->
        <Transition 
            enter-active-class="transition-opacity ease-out duration-300" 
            enter-from-class="opacity-0" 
            enter-to-class="opacity-100" 
            leave-active-class="transition-opacity ease-in duration-200" 
            leave-from-class="opacity-100" 
            leave-to-class="opacity-0"
        >
            <div v-if="isSuccessModalOpen" class="fixed inset-0 z-50 bg-gray-900/60 backdrop-blur-sm" @click="isSuccessModalOpen = false"></div>
        </Transition>
        
        <Transition 
            enter-active-class="transition ease-out duration-300 transform" 
            enter-from-class="translate-y-full" 
            enter-to-class="translate-y-0" 
            leave-active-class="transition ease-in duration-200 transform" 
            leave-from-class="translate-y-0" 
            leave-to-class="translate-y-full"
        >
            <div v-if="isSuccessModalOpen" class="fixed inset-x-0 bottom-0 z-50 flex flex-col justify-end pointer-events-none w-full max-w-lg mx-auto">
                <div class="relative bg-white w-full rounded-t-3xl shadow-2xl flex flex-col pointer-events-auto overflow-hidden">
                    <!-- Handle -->
                    <div class="flex justify-center p-3 shrink-0 cursor-pointer bg-white" @click="isSuccessModalOpen = false">
                        <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                    </div>
                    
                    <div class="px-8 pb-8 pt-2">
                        <div class="w-20 h-20 bg-green-100 rounded-full flex items-center justify-center mx-auto mb-5 shadow-inner">
                            <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                        </div>
                        <h3 class="text-2xl font-black text-gray-900 text-center tracking-tight mb-2">Pembayaran Berhasil!</h3>
                        <p class="text-gray-500 text-center mb-8 font-medium">Status pesanan <span class="font-bold text-gray-800">#{{ successTransaction?.invoice_number }}</span> telah diperbarui menjadi lunas.</p>
                        
                        <div class="space-y-3">
                            <div class="flex gap-3">
                                <button 
                                    @click="printInvoice"
                                    :disabled="isPrinting"
                                    class="flex-1 py-4 bg-primary-600 hover:bg-primary-700 text-white font-bold rounded-2xl flex items-center justify-center gap-2 transition-all shadow-md active:scale-95"
                                >
                                    <svg v-if="!isPrinting" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 17h2a2 2 0 002-2v-4a2 2 0 00-2-2H5a2 2 0 00-2 2v4a2 2 0 002 2h2m2 4h6a2 2 0 002-2v-4a2 2 0 00-2-2H9a2 2 0 00-2 2v4a2 2 0 002 2zm8-12V5a2 2 0 00-2-2H9a2 2 0 00-2 2v4h10z"></path></svg>
                                    <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Print
                                </button>
                                <button 
                                    @click="downloadInvoice"
                                    :disabled="isDownloading"
                                    class="flex-1 py-4 bg-blue-600 hover:bg-blue-700 text-white font-bold rounded-2xl flex items-center justify-center gap-2 transition-all shadow-md active:scale-95"
                                >
                                    <svg v-if="!isDownloading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                    <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                                    Download
                                </button>
                            </div>
                            
                            <button 
                                @click="isSuccessModalOpen = false" 
                                class="w-full py-4 bg-gray-100 hover:bg-gray-200 text-gray-700 font-bold rounded-2xl transition-colors active:scale-95"
                            >
                                Tutup
                            </button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

        <!-- Hidden Invoice Template for Print/Download -->
        <div class="fixed top-0 left-[9999px] z-[-1] bg-white w-[400px] p-8" ref="invoiceElement">
            <div v-if="successTransaction" class="bg-white">
                <div class="text-center mb-6 pt-2 flex flex-col items-center">
                    <div class="w-16 h-16 mb-2">
                        <img src="/logo.png" alt="Cafe Logo" class="w-full h-full object-contain" />
                    </div>
                    <h2 class="text-2xl font-black text-gray-900 tracking-tight">INVOICE</h2>
                    <p class="text-gray-500 font-medium">{{ successTransaction.invoice_number }}</p>
                </div>

                <div class="flex justify-between items-end mb-6 pb-6 border-b border-gray-100">
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Tanggal</p>
                        <p class="font-bold text-gray-800">{{ formatDateTime(successTransaction.created_at) }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Tipe Pesanan</p>
                        <p class="font-bold text-gray-800">{{ successTransaction.cafe_table ? `Meja ${successTransaction.cafe_table.table_number}` : 'Takeaway' }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <ul class="space-y-4">
                        <li v-for="item in successTransaction.transaction_details" :key="item.id" class="flex justify-between items-start text-sm">
                            <div class="flex items-start gap-3">
                                <span class="font-black text-gray-800">{{ item.quantity }}x</span>
                                <div>
                                    <p class="text-gray-800 font-bold">{{ item.menu_name }}</p>
                                    <p class="text-xs text-gray-500">{{ formatCurrency(item.price || (item.subtotal / item.quantity)) }}</p>
                                </div>
                            </div>
                            <span class="text-gray-900 font-bold">{{ formatCurrency(item.subtotal) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-gray-50 rounded-2xl p-5 mb-6 border border-gray-100">
                    <div class="space-y-2 mb-3 pb-3 border-b border-gray-200 border-dashed">
                        <div class="flex justify-between text-sm">
                            <span class="text-gray-500 font-medium">Subtotal</span>
                            <span class="font-bold text-gray-800">{{ formatCurrency(successTransaction.subtotal) }}</span>
                        </div>
                        <div v-if="successTransaction.tax_amount > 0" class="flex justify-between text-sm">
                            <span class="text-gray-500 font-medium">Pajak</span>
                            <span class="font-bold text-gray-800">{{ formatCurrency(successTransaction.tax_amount) }}</span>
                        </div>
                        <div v-if="successTransaction.discount_amount > 0" class="flex justify-between text-sm">
                            <span class="text-gray-500 font-medium">Diskon</span>
                            <span class="font-bold text-green-600">-{{ formatCurrency(successTransaction.discount_amount) }}</span>
                        </div>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-800 font-black uppercase tracking-wider">Total</span>
                        <span class="text-2xl font-black text-primary-600">{{ formatCurrency(successTransaction.total_amount) }}</span>
                    </div>
                </div>

                <div class="flex justify-between items-center bg-white border border-gray-200 rounded-xl p-4">
                    <div>
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Pembayaran</p>
                        <p class="font-bold text-gray-800">{{ successTransaction.payment_method?.name || 'Tunai' }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Status</p>
                        <span :class="['px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider', 
                            successTransaction.status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'
                        ]">
                            {{ successTransaction.status === 'pending' ? 'Belum Lunas' : 'Lunas' }}
                        </span>
                    </div>
                </div>
            </div>
        </div>

    </div>
</template>
