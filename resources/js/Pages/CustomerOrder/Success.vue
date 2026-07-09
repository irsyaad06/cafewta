<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
    table_number: String,
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
    return date.toLocaleDateString('id-ID', { 
        weekday: 'long', 
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};
</script>

<template>
    <Head title="Pesanan Berhasil" />

    <div class="min-h-screen bg-gray-50 py-10 px-4 sm:px-6 lg:px-8 flex flex-col items-center justify-center font-sans">
        
        <div class="max-w-md w-full bg-white rounded-3xl shadow-xl overflow-hidden border border-gray-100">
            <!-- Header Success -->
            <div class="bg-green-500 p-8 text-center relative overflow-hidden">
                <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg relative z-10">
                    <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                </div>
                <h1 class="text-2xl font-extrabold text-white relative z-10 tracking-tight">Pesanan Diterima!</h1>
                <p class="text-green-50 mt-2 font-medium relative z-10">Terima kasih, pesanan Anda sedang kami siapkan.</p>
            </div>

            <!-- Transaction Info -->
            <div class="p-6 sm:p-8">
                <div class="flex justify-between items-end mb-6 pb-6 border-b border-gray-100">
                    <div>
                        <p class="text-sm text-gray-500 font-bold uppercase tracking-wider mb-1">Nomor Invoice</p>
                        <p class="font-extrabold text-lg text-gray-800">{{ transaction.invoice_number }}</p>
                    </div>
                    <div class="text-right">
                        <p class="text-sm text-gray-500 font-bold uppercase tracking-wider mb-1">Meja</p>
                        <p class="font-black text-xl text-primary-600">{{ table_number }}</p>
                    </div>
                </div>

                <div class="mb-6">
                    <p class="text-sm text-gray-500 font-bold mb-4">{{ formatDate(transaction.created_at) }}</p>
                    <ul class="space-y-4">
                        <li v-for="detail in transaction.transaction_details" :key="detail.id" class="flex justify-between items-start text-sm">
                            <div class="flex items-start gap-3">
                                <span class="font-black text-gray-800 bg-gray-100 px-2 py-0.5 rounded text-xs">{{ detail.quantity }}x</span>
                                <span class="text-gray-700 font-medium">{{ detail.menu_name }}</span>
                            </div>
                            <span class="text-gray-800 font-bold">{{ formatCurrency(detail.subtotal) }}</span>
                        </li>
                    </ul>
                </div>

                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 mb-8">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-500 font-medium">Metode Pembayaran</span>
                        <span class="font-bold text-gray-800">{{ transaction.payment_method?.name || 'Tunai' }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200 mt-2">
                        <span class="text-gray-600 font-bold uppercase tracking-wider text-sm">Total Tagihan</span>
                        <span class="text-2xl font-black text-primary-600">{{ formatCurrency(transaction.total_amount) }}</span>
                    </div>
                </div>

                <div class="bg-orange-50 border border-orange-100 p-4 rounded-xl flex items-start gap-3">
                    <svg class="w-6 h-6 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="text-sm font-medium text-orange-800 leading-snug">Mohon segera selesaikan pembayaran di kasir agar pesanan Anda dapat segera kami proses.</p>
                </div>

            </div>
        </div>

        <div class="mt-8">
            <Link :href="route('simulasi-meja')" class="text-primary-600 font-bold hover:text-primary-700 hover:underline">
                &larr; Kembali ke Daftar Meja
            </Link>
        </div>

    </div>
</template>
