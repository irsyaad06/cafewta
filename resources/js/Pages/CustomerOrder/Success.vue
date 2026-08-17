<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, onUnmounted, computed, ref } from 'vue';
import { router } from '@inertiajs/vue3';

const props = defineProps({
    transaction: Object,
    table_number: String,
});

const qrCodeUrl = ref('');

const isQris = computed(() => {
    return props.transaction?.payment_method?.type === 'qris';
});

const isPendingQris = computed(() => {
    return props.transaction?.status === 'pending_qris';
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

let intervalId = null;

onMounted(() => {
    // Generate QR code URL jika metode QRIS
    if (isQris.value && props.transaction?.qris_token) {
        const qrisPayUrl = `${window.location.origin}/qris/pay/${props.transaction.qris_token}`;
        // Gunakan Google Chart API untuk generate QR code (no dependency needed)
        qrCodeUrl.value = `https://api.qrserver.com/v1/create-qr-code/?size=250x250&data=${encodeURIComponent(qrisPayUrl)}&color=000000&bgcolor=ffffff&margin=10`;
    }

    // Polling setiap 3 detik untuk cek status
    intervalId = setInterval(() => {
        if (props.transaction.status === 'pending' || props.transaction.status === 'pending_qris') {
            router.reload({ only: ['transaction'], preserveScroll: true, preserveState: true });
        } else {
            clearInterval(intervalId);
        }
    }, 3000);
});

onUnmounted(() => {
    if (intervalId) clearInterval(intervalId);
});
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

                <div class="bg-gray-50 p-5 rounded-2xl border border-gray-100 mb-6">
                    <div class="flex justify-between items-center mb-2">
                        <span class="text-gray-500 font-medium">Metode Pembayaran</span>
                        <span class="font-bold text-gray-800">{{ transaction.payment_method?.name || 'Tunai' }}</span>
                    </div>
                    <div class="flex justify-between items-center pt-2 border-t border-gray-200 mt-2">
                        <span class="text-gray-600 font-bold uppercase tracking-wider text-sm">Total Tagihan</span>
                        <span class="text-2xl font-black text-primary-600">{{ formatCurrency(transaction.total_amount) }}</span>
                    </div>
                </div>

                <!-- QRIS Payment Section -->
                <div v-if="isQris && isPendingQris" class="mb-6">
                    <div class="bg-blue-50 border border-blue-200 rounded-2xl p-5 text-center">
                        <div class="flex items-center justify-center gap-2 mb-4">
                            <!-- QRIS Icon -->
                            <svg class="w-6 h-6 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v1m6 11h2m-6 0h-2v4m0-11v3m0 0h.01M12 12h4.524M16 20h4M4 12h4m12 0h.01M5 8h2a1 1 0 001-1V5a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1zm12 0h2a1 1 0 001-1V5a1 1 0 00-1-1h-2a1 1 0 00-1 1v2a1 1 0 001 1zM5 20h2a1 1 0 001-1v-2a1 1 0 00-1-1H5a1 1 0 00-1 1v2a1 1 0 001 1z"/>
                            </svg>
                            <h3 class="text-lg font-extrabold text-blue-800">Scan untuk Bayar via QRIS</h3>
                        </div>

                        <!-- QR Code -->
                        <div class="flex justify-center mb-4">
                            <div class="p-3 bg-white rounded-2xl shadow-md border-2 border-blue-100 inline-block">
                                <img 
                                    v-if="qrCodeUrl"
                                    :src="qrCodeUrl" 
                                    alt="QR Code Pembayaran QRIS" 
                                    class="w-52 h-52 object-contain"
                                />
                                <div v-else class="w-52 h-52 flex items-center justify-center bg-gray-100 rounded-xl">
                                    <div class="animate-spin w-8 h-8 border-4 border-blue-500 border-t-transparent rounded-full"></div>
                                </div>
                            </div>
                        </div>

                        <p class="text-sm font-semibold text-blue-700 mb-1">Arahkan kamera HP Anda ke QR di atas</p>
                        <p class="text-xs text-blue-500">QR ini akan otomatis memproses pembayaran setelah di-scan</p>
                        
                        <!-- Animasi loading menunggu pembayaran -->
                        <div class="mt-4 flex items-center justify-center gap-2 text-blue-600">
                            <div class="flex gap-1">
                                <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 0ms"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 150ms"></span>
                                <span class="w-2 h-2 bg-blue-400 rounded-full animate-bounce" style="animation-delay: 300ms"></span>
                            </div>
                            <span class="text-sm font-medium">Menunggu pembayaran...</span>
                        </div>

                        <!-- Tombol alternatif untuk testing lokal -->
                        <div class="mt-5 pt-4 border-t border-blue-200">
                            <p class="text-xs text-blue-400 mb-3">Tidak bisa scan? Klik tombol di bawah untuk membuka halaman pembayaran.</p>
                            <a
                                :href="`/qris/pay/${transaction.qris_token}`"
                                class="inline-flex items-center gap-2 px-5 py-2.5 bg-blue-600 hover:bg-blue-700 text-white text-sm font-bold rounded-xl transition-all duration-200 active:scale-95 shadow-md hover:shadow-lg"
                            >
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8l4 4m0 0l-4 4m4-4H3"/>
                                </svg>
                                Lanjut ke Halaman Pembayaran
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Status pending biasa (bukan QRIS) -->
                <div v-else-if="transaction.status === 'pending'" class="bg-orange-50 border border-orange-100 p-4 rounded-xl flex items-start gap-3 mb-6">
                    <svg class="w-6 h-6 text-orange-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    <p class="text-sm font-medium text-orange-800 leading-snug">Mohon segera selesaikan pembayaran di kasir agar pesanan Anda dapat segera kami proses.</p>
                </div>
                
                <!-- Status completed -->
                <div v-else-if="transaction.status === 'completed'" class="bg-green-50 border border-green-100 p-5 rounded-xl flex flex-col gap-4 mb-6">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-500 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        <p class="text-sm font-bold text-green-800 leading-snug">Pesanan sudah dibayar dan sedang diproses!</p>
                    </div>
                    <Link :href="route('tracking.show', transaction.invoice_number)" class="w-full text-center py-3 bg-green-500 text-white rounded-xl font-bold hover:bg-green-600 transition-colors shadow-sm">
                        Lihat Progress Pesanan
                    </Link>
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
