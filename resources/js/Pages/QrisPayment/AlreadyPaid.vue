<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
});

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};
</script>

<template>
    <Head title="Sudah Dibayar" />
    <div class="min-h-screen bg-gradient-to-b from-[#00A651] to-[#004D2A] flex items-center justify-center px-4 font-sans">
        <div class="w-full max-w-sm bg-white rounded-3xl shadow-2xl overflow-hidden text-center">
            <div class="bg-green-50 px-6 py-8">
                <div class="w-20 h-20 bg-[#00A651] rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg">
                    <svg class="w-10 h-10 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                    </svg>
                </div>
                <h1 class="text-2xl font-black text-gray-800">Sudah Dibayar</h1>
                <p class="text-gray-500 mt-2 text-sm">Transaksi ini sudah berhasil diproses sebelumnya.</p>
            </div>
            <div class="px-6 py-6">
                <div class="bg-gray-50 rounded-2xl p-4 mb-6 text-left space-y-2">
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Invoice</span>
                        <span class="font-bold text-gray-800">{{ transaction.invoice_number }}</span>
                    </div>
                    <div class="flex justify-between text-sm">
                        <span class="text-gray-500">Total</span>
                        <span class="font-bold text-[#00A651]">{{ formatCurrency(transaction.total_amount) }}</span>
                    </div>
                </div>
                <Link 
                    :href="route('tracking.show', transaction.invoice_number)" 
                    class="block w-full py-3 bg-[#00A651] text-white rounded-2xl font-bold hover:bg-[#007A3D] transition-colors"
                >
                    Lihat Status Pesanan
                </Link>
            </div>
        </div>
    </div>
</template>
