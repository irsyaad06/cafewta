<script setup>
import { ref, computed, onMounted, onUnmounted } from 'vue';
import { Head, router, Link } from '@inertiajs/vue3';
import html2canvas from 'html2canvas';

const props = defineProps({
    transaction: {
        type: Object,
        required: true
    }
});

const isInvoiceOpen = ref(false);
const invoiceElement = ref(null);
const isDownloading = ref(false);

const downloadInvoice = async () => {
    if (!invoiceElement.value) return;
    
    try {
        isDownloading.value = true;
        const canvas = await html2canvas(invoiceElement.value, {
            scale: 2,
            backgroundColor: '#ffffff',
            logging: false
        });
        
        const link = document.createElement('a');
        link.download = `Invoice-${props.transaction.invoice_number}.png`;
        link.href = canvas.toDataURL('image/png');
        link.click();
    } catch (error) {
        console.error('Failed to generate invoice image', error);
        alert('Gagal mengunduh invoice. Silakan coba lagi.');
    } finally {
        isDownloading.value = false;
    }
};

let intervalId = null;

onMounted(() => {
    // Polling setiap 10 detik untuk update status terbaru
    intervalId = setInterval(() => {
        router.reload({ only: ['transaction'], preserveScroll: true, preserveState: true });
    }, 10000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
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

const formatDateTime = (dateString) => {
    const date = new Date(dateString);
    return date.toLocaleDateString('id-ID', { 
        year: 'numeric', 
        month: 'short', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

const statuses = [
    { key: 'completed', label: 'Pesanan Terbayar', icon: 'M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z' },
    { key: 'cooking', label: 'Sedang Dimasak', icon: 'M17 14v6m-3-3h6M6 10h2a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2zm10 0h2a2 2 0 002-2V6a2 2 0 00-2-2h-2a2 2 0 00-2 2v2a2 2 0 002 2zM6 20h2a2 2 0 002-2v-2a2 2 0 00-2-2H6a2 2 0 00-2 2v2a2 2 0 002 2z' },
    { key: 'delivered', label: 'Tiba / Selesai', icon: 'M5 13l4 4L19 7' }
];

const currentStepIndex = computed(() => {
    const s = props.transaction.status;
    if (s === 'completed' || s === 'pending') return 0; // pending should theoretically not happen here but fallback
    if (s === 'cooking') return 1;
    if (s === 'delivered') return 2;
    return 0;
});

</script>

<template>
    <Head title="Progress Pesanan" />
    
    <div class="min-h-screen bg-gray-50 flex items-center justify-center p-4 font-sans">
        <div class="max-w-xl w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Header -->
            <div class="bg-gradient-to-r from-primary-600 to-primary-700 p-8 text-center relative overflow-hidden flex flex-col items-center">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                
                <!-- Logo -->
                <div class="relative z-10 w-20 h-20 bg-white rounded-full p-2.5 mb-4 shadow-lg flex items-center justify-center">
                    <img src="/logo.png" alt="Cafe Logo" class="w-full h-full object-contain" />
                </div>

                <h1 class="text-3xl font-extrabold text-white relative z-10 tracking-tight">Progress Pesanan</h1>
                <div class="flex flex-wrap justify-center items-center gap-3 mt-3 relative z-10">
                    <p class="text-primary-100 text-sm font-medium font-mono bg-white/20 px-4 py-1.5 rounded-full shadow-sm">
                        {{ transaction.invoice_number }}
                    </p>
                    <p v-if="transaction.cafe_table" class="text-white text-sm font-bold bg-primary-800/50 px-4 py-1.5 rounded-full shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"></path></svg>
                        Meja {{ transaction.cafe_table.table_number }}
                    </p>
                    <p v-else class="text-white text-sm font-bold bg-primary-800/50 px-4 py-1.5 rounded-full shadow-sm flex items-center gap-1.5">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z"></path></svg>
                        Takeaway
                    </p>
                </div>
            </div>

            <!-- Content -->
            <div class="p-6 md:p-8">
                
                <!-- Status Message -->
                <div class="text-center mb-10">
                    <h2 class="text-2xl font-bold text-gray-800 mb-2">
                        {{ statuses[currentStepIndex].label }}
                    </h2>
                    <p class="text-gray-500 font-medium">Pesanan Anda sedang diproses. Mohon tunggu sebentar.</p>
                </div>

                <!-- Stepper -->
                <div class="relative max-w-lg mx-auto mb-12">
                    <!-- Progress Line Container -->
                    <div class="absolute top-8 left-[16.666%] right-[16.666%] h-1 bg-gray-200 z-0">
                        <!-- Progress Line Active -->
                        <div class="h-full bg-primary-500 transition-all duration-700 ease-in-out" 
                             :style="{ width: currentStepIndex === 0 ? '0%' : (currentStepIndex === 1 ? '50%' : '100%') }"></div>
                    </div>

                    <!-- Steps -->
                    <div class="flex justify-between relative z-10">
                        <div v-for="(status, index) in statuses" :key="status.key" class="flex flex-col items-center gap-3 w-1/3">
                            <div 
                                :class="[
                                    'w-16 h-16 rounded-full shrink-0 flex items-center justify-center shadow-md transition-all duration-500 z-10',
                                    index <= currentStepIndex ? 'text-white bg-primary-500 shadow-primary-500/40 ring-4 ring-primary-50' : 'text-gray-400 bg-white border-2 border-gray-200'
                                ]"
                            >
                                <svg class="w-7 h-7" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" :d="status.icon"></path>
                                </svg>
                            </div>
                            <div class="text-center">
                                <p :class="['font-bold text-sm sm:text-base transition-colors leading-tight', index <= currentStepIndex ? 'text-gray-800' : 'text-gray-400']">{{ status.label }}</p>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Order Details -->
                <div class="bg-gray-50 rounded-2xl p-6 border border-gray-100 mt-6">
                    <h3 class="text-sm font-bold text-gray-400 uppercase tracking-wider mb-4 border-b border-gray-200 pb-2">Detail Pesanan</h3>
                    <ul class="space-y-3 mb-4">
                        <li v-for="item in transaction.transaction_details" :key="item.id" class="flex justify-between items-start text-sm">
                            <div class="flex items-start gap-2.5">
                                <span class="font-black text-primary-700 bg-primary-100 px-2 py-0.5 rounded text-xs">{{ item.quantity }}x</span>
                                <span class="text-gray-700 font-medium">{{ item.menu_name }}</span>
                            </div>
                            <span class="text-gray-800 font-bold shrink-0">{{ formatCurrency(item.subtotal) }}</span>
                        </li>
                    </ul>
                    
                    <div class="pt-4 border-t border-gray-200 border-dashed flex justify-between items-center">
                        <span class="font-bold text-gray-600">Total</span>
                        <span class="font-black text-xl text-primary-600">{{ formatCurrency(transaction.total_amount) }}</span>
                    </div>
                </div>

                <!-- Lihat Invoice Button -->
                <div class="mt-8">
                    <button @click="isInvoiceOpen = true" class="w-full flex items-center justify-center gap-2 py-3.5 bg-white border-2 border-primary-500 text-primary-600 rounded-xl font-bold hover:bg-primary-50 transition-colors shadow-sm">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                        Lihat Invoice
                    </button>
                </div>

            </div>
        </div>
        
        <!-- Backdrop Transition -->
        <Transition 
            enter-active-class="transition-opacity ease-out duration-300" 
            enter-from-class="opacity-0" 
            enter-to-class="opacity-100" 
            leave-active-class="transition-opacity ease-in duration-200" 
            leave-from-class="opacity-100" 
            leave-to-class="opacity-0"
        >
            <div v-if="isInvoiceOpen" class="fixed inset-0 z-40 bg-gray-900/60 backdrop-blur-sm" @click="isInvoiceOpen = false"></div>
        </Transition>
        
        <!-- Invoice Bottom Sheet Transition -->
        <Transition 
            enter-active-class="transition ease-out duration-300 transform" 
            enter-from-class="translate-y-full" 
            enter-to-class="translate-y-0" 
            leave-active-class="transition ease-in duration-200 transform" 
            leave-from-class="translate-y-0" 
            leave-to-class="translate-y-full"
        >
            <div v-if="isInvoiceOpen" class="fixed inset-x-0 bottom-0 z-50 flex flex-col justify-end pointer-events-none w-full max-w-lg mx-auto">
                <!-- Sheet Content -->
                <div class="relative bg-white w-full rounded-t-3xl shadow-2xl flex flex-col max-h-[85vh] pointer-events-auto">
                    <!-- Handle -->
                    <div class="flex justify-center p-3 shrink-0 cursor-pointer" @click="isInvoiceOpen = false">
                        <div class="w-12 h-1.5 bg-gray-300 rounded-full"></div>
                    </div>
                    
                    <div class="overflow-y-auto px-6 pb-8 hide-scrollbar">
                        
                        <div ref="invoiceElement" class="bg-white p-2 -m-2">
                            <div class="text-center mb-6 pt-2 flex flex-col items-center">
                                <div class="w-16 h-16 mb-2">
                                    <img src="/logo.png" alt="Cafe Logo" class="w-full h-full object-contain" />
                                </div>
                                <h2 class="text-2xl font-black text-gray-900 tracking-tight">INVOICE</h2>
                                <p class="text-gray-500 font-medium">{{ transaction.invoice_number }}</p>
                            </div>

                            <div class="flex justify-between items-end mb-6 pb-6 border-b border-gray-100">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Tanggal</p>
                                    <p class="font-bold text-gray-800">{{ formatDateTime(transaction.created_at) }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Tipe Pesanan</p>
                                    <p class="font-bold text-gray-800">{{ transaction.cafe_table ? `Meja ${transaction.cafe_table.table_number}` : 'Takeaway' }}</p>
                                </div>
                            </div>

                            <div class="mb-6">
                                <ul class="space-y-4">
                                    <li v-for="item in transaction.transaction_details" :key="item.id" class="flex justify-between items-start text-sm">
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
                                        <span class="font-bold text-gray-800">{{ formatCurrency(transaction.subtotal) }}</span>
                                    </div>
                                    <div v-if="transaction.tax_amount > 0" class="flex justify-between text-sm">
                                        <span class="text-gray-500 font-medium">Pajak</span>
                                        <span class="font-bold text-gray-800">{{ formatCurrency(transaction.tax_amount) }}</span>
                                    </div>
                                    <div v-if="transaction.discount_amount > 0" class="flex justify-between text-sm">
                                        <span class="text-gray-500 font-medium">Diskon</span>
                                        <span class="font-bold text-green-600">-{{ formatCurrency(transaction.discount_amount) }}</span>
                                    </div>
                                </div>
                                <div class="flex justify-between items-center">
                                    <span class="text-gray-800 font-black uppercase tracking-wider">Total</span>
                                    <span class="text-2xl font-black text-primary-600">{{ formatCurrency(transaction.total_amount) }}</span>
                                </div>
                            </div>

                            <div class="flex justify-between items-center bg-white border border-gray-200 rounded-xl p-4">
                                <div>
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Pembayaran</p>
                                    <p class="font-bold text-gray-800">{{ transaction.payment_method?.name || 'Tunai' }}</p>
                                </div>
                                <div class="text-right">
                                    <p class="text-xs text-gray-400 font-bold uppercase tracking-wider mb-1">Status</p>
                                    <span :class="['px-2.5 py-1 rounded-md text-xs font-black uppercase tracking-wider', 
                                        transaction.status === 'pending' ? 'bg-orange-100 text-orange-700' : 'bg-green-100 text-green-700'
                                    ]">
                                        {{ transaction.status === 'pending' ? 'Belum Lunas' : 'Lunas' }}
                                    </span>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-8 flex gap-3">
                            <button @click="downloadInvoice" :disabled="isDownloading" class="flex-1 py-3.5 bg-primary-600 text-white rounded-xl font-bold hover:bg-primary-700 transition-colors flex items-center justify-center gap-2 shadow-sm">
                                <svg v-if="!isDownloading" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path></svg>
                                <svg v-else class="animate-spin h-5 w-5 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                                </svg>
                                {{ isDownloading ? 'Memproses...' : 'Download' }}
                            </button>
                            <button @click="isInvoiceOpen = false" class="flex-1 py-3.5 bg-gray-100 text-gray-700 rounded-xl font-bold hover:bg-gray-200 transition-colors shadow-sm">Tutup</button>
                        </div>
                    </div>
                </div>
            </div>
        </Transition>

    </div>
</template>
