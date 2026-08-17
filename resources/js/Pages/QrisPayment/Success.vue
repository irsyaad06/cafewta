<script setup>
import { Head, Link } from '@inertiajs/vue3';
import { onMounted, ref } from 'vue';

const props = defineProps({
    transaction: Object,
});

const showContent = ref(false);
const showConfetti = ref(false);

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
        year: 'numeric', 
        month: 'long', 
        day: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });
};

// Random confetti particles
const confettiParticles = ref([]);
const colors = ['#00A651', '#FFD700', '#FF6B6B', '#4ECDC4', '#45B7D1', '#FFA07A', '#98D8C8'];

onMounted(() => {
    // Generate confetti particles
    confettiParticles.value = Array.from({ length: 50 }, (_, i) => ({
        id: i,
        left: Math.random() * 100,
        delay: Math.random() * 1.5,
        duration: 2 + Math.random() * 2,
        color: colors[Math.floor(Math.random() * colors.length)],
        size: 6 + Math.random() * 8,
    }));

    showConfetti.value = true;
    setTimeout(() => {
        showContent.value = true;
    }, 300);
});
</script>

<template>
    <Head title="Pembayaran Berhasil" />

    <div class="min-h-screen bg-gradient-to-b from-[#00A651] to-[#004D2A] flex flex-col items-center justify-center font-sans relative overflow-hidden px-4">
        
        <!-- Confetti Animation -->
        <div v-if="showConfetti" class="fixed inset-0 pointer-events-none overflow-hidden z-10">
            <div 
                v-for="particle in confettiParticles" 
                :key="particle.id"
                class="absolute animate-confetti rounded-sm"
                :style="{
                    left: particle.left + '%',
                    top: '-10px',
                    width: particle.size + 'px',
                    height: particle.size + 'px',
                    backgroundColor: particle.color,
                    animationDelay: particle.delay + 's',
                    animationDuration: particle.duration + 's',
                }"
            ></div>
        </div>

        <!-- Success Card -->
        <transition name="fade-up">
            <div v-if="showContent" class="w-full max-w-sm z-20">
                
                <!-- Success Icon -->
                <div class="flex flex-col items-center mb-6">
                    <div class="w-24 h-24 bg-white rounded-full flex items-center justify-center shadow-2xl mb-4 ring-4 ring-white/30">
                        <svg class="w-12 h-12 text-[#00A651]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"/>
                        </svg>
                    </div>
                    <h1 class="text-3xl font-black text-white text-center">Pembayaran Berhasil!</h1>
                    <p class="text-green-100 mt-1 text-center text-sm">Transaksi Anda telah dikonfirmasi</p>
                </div>

                <!-- Receipt Card -->
                <div class="bg-white rounded-3xl shadow-2xl overflow-hidden">
                    
                    <!-- Amount Header -->
                    <div class="bg-[#00A651]/10 px-6 py-5 text-center border-b border-green-100">
                        <p class="text-sm text-gray-400 font-semibold uppercase tracking-widest mb-1">Jumlah Dibayar</p>
                        <p class="text-4xl font-black text-[#00A651]">{{ formatCurrency(transaction.total_amount) }}</p>
                    </div>

                    <!-- Transaction Details -->
                    <div class="px-6 py-5 space-y-3">
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Merchant</span>
                            <span class="text-sm font-bold text-gray-800">Cafe WTA</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Invoice</span>
                            <span class="text-sm font-bold text-gray-800">{{ transaction.invoice_number }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Waktu</span>
                            <span class="text-sm font-bold text-gray-800">{{ formatDate(transaction.updated_at) }}</span>
                        </div>
                        <div class="flex justify-between items-center">
                            <span class="text-sm text-gray-500">Metode</span>
                            <span class="text-sm font-bold text-[#00A651]">QRIS / CaféPay</span>
                        </div>
                    </div>

                    <!-- Dashed Separator -->
                    <div class="mx-6 border-t border-dashed border-gray-200 relative">
                        <div class="absolute -left-9 -top-3 w-6 h-6 bg-gradient-to-b from-[#00A651] to-[#004D2A] rounded-full"></div>
                        <div class="absolute -right-9 -top-3 w-6 h-6 bg-gradient-to-b from-[#00A651] to-[#004D2A] rounded-full"></div>
                    </div>

                    <!-- Items -->
                    <div class="px-6 py-5">
                        <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-3">Detail Pesanan</p>
                        <ul class="space-y-2">
                            <li 
                                v-for="detail in transaction.transaction_details" 
                                :key="detail.id"
                                class="flex justify-between text-sm"
                            >
                                <span class="text-gray-600">{{ detail.quantity }}x {{ detail.menu_name }}</span>
                                <span class="font-semibold text-gray-800">{{ formatCurrency(detail.subtotal) }}</span>
                            </li>
                        </ul>
                    </div>

                    <!-- Status Badge -->
                    <div class="mx-6 mb-6 bg-green-50 rounded-2xl p-4 flex items-center justify-center gap-2 border border-green-100">
                        <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                        <span class="text-green-700 font-bold text-sm">Pesanan sedang diproses oleh kasir</span>
                    </div>

                    <!-- CTA -->
                    <div class="px-6 pb-6">
                        <Link 
                            :href="route('tracking.show', transaction.invoice_number)" 
                            class="block w-full text-center py-4 bg-[#00A651] text-white rounded-2xl font-black text-base hover:bg-[#007A3D] transition-colors shadow-lg"
                        >
                            Lihat Status Pesanan →
                        </Link>
                    </div>
                </div>

                <!-- Powered by -->
                <p class="text-center text-green-200/60 text-xs mt-6">Powered by CaféPay · Simulasi QRIS</p>

            </div>
        </transition>

    </div>
</template>

<style scoped>
@keyframes confetti-fall {
    0% {
        transform: translateY(-10px) rotate(0deg);
        opacity: 1;
    }
    100% {
        transform: translateY(100vh) rotate(720deg);
        opacity: 0;
    }
}

.animate-confetti {
    animation: confetti-fall linear forwards;
}

.fade-up-enter-active {
    transition: all 0.6s cubic-bezier(0.22, 1, 0.36, 1);
}
.fade-up-enter-from {
    opacity: 0;
    transform: translateY(30px);
}
.fade-up-enter-to {
    opacity: 1;
    transform: translateY(0);
}
</style>
