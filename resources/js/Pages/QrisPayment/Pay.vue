<script setup>
import { Head } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import axios from 'axios';

const props = defineProps({
    transaction: Object,
    token: String,
});

// State
const step = ref('payment'); // 'payment' | 'pin' | 'processing' | 'error'
const pin = ref('');
const errorMsg = ref('');
const pinError = ref('');

const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const pinDisplay = computed(() => {
    return Array.from({ length: 6 }, (_, i) => pin.value[i] ? '●' : '○');
});

const openPinModal = () => {
    step.value = 'pin';
    pin.value = '';
    pinError.value = '';
};

const appendPin = (digit) => {
    if (pin.value.length < 6) {
        pin.value += digit;
    }
};

const deletePin = () => {
    pin.value = pin.value.slice(0, -1);
};

const clearPin = () => {
    pin.value = '';
};

const confirmPayment = async () => {
    if (pin.value.length < 6) {
        pinError.value = 'PIN harus 6 digit.';
        return;
    }

    step.value = 'processing';

    try {
        const response = await axios.post(`/qris/pay/${props.token}`, {
            pin: pin.value,
        });

        if (response.data.success) {
            // Redirect ke halaman sukses
            setTimeout(() => {
                window.location.href = response.data.redirect;
            }, 800);
        } else {
            step.value = 'error';
            errorMsg.value = response.data.message || 'Pembayaran gagal.';
        }
    } catch (err) {
        step.value = 'error';
        errorMsg.value = err.response?.data?.message || 'Terjadi kesalahan. Coba lagi.';
    }
};

const retryPayment = () => {
    step.value = 'payment';
    pin.value = '';
    errorMsg.value = '';
};
</script>

<template>
    <Head title="Pembayaran QRIS" />

    <!-- Simulasi tampilan aplikasi bank/e-wallet -->
    <div class="min-h-screen bg-gradient-to-b from-[#00A651] to-[#007A3D] flex flex-col items-center justify-start font-sans">
        
        <!-- Status Bar Area -->
        <div class="w-full max-w-sm">
            
            <!-- App Header -->
            <div class="px-6 pt-10 pb-6 text-center">
                <!-- Logo CaféPay -->
                <div class="flex items-center justify-center gap-2 mb-2">
                    <div class="w-10 h-10 bg-white rounded-full flex items-center justify-center shadow-lg">
                        <span class="text-[#00A651] font-black text-lg">C</span>
                    </div>
                    <span class="text-white font-black text-2xl tracking-tight">CaféPay</span>
                </div>
                <p class="text-green-100 text-sm font-medium">Pembayaran Digital</p>
            </div>

            <!-- Main Card -->
            <div class="mx-4 bg-white rounded-3xl shadow-2xl overflow-hidden">
                
                <!-- Merchant Info -->
                <div class="bg-gray-50 px-6 py-5 border-b border-gray-100">
                    <div class="flex items-center gap-4">
                        <div class="w-14 h-14 bg-[#00A651] rounded-2xl flex items-center justify-center shadow-md flex-shrink-0">
                            <svg class="w-7 h-7 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider">Merchant</p>
                            <h2 class="text-xl font-black text-gray-900">Cafe WTA</h2>
                            <p class="text-xs text-gray-500 mt-0.5">QRIS Verified ✓</p>
                        </div>
                    </div>
                </div>

                <!-- Amount -->
                <div class="px-6 py-6 text-center border-b border-gray-100">
                    <p class="text-sm text-gray-400 font-semibold uppercase tracking-widest mb-2">Total Pembayaran</p>
                    <div class="text-4xl font-black text-gray-900 mb-1">
                        {{ formatCurrency(transaction.total_amount) }}
                    </div>
                    <p class="text-xs text-gray-400">Invoice: {{ transaction.invoice_number }}</p>
                </div>

                <!-- Item Summary -->
                <div class="px-6 py-4 border-b border-gray-100">
                    <p class="text-xs text-gray-400 font-semibold uppercase tracking-wider mb-3">Rincian Pesanan</p>
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

                <!-- CTA Button -->
                <div class="px-6 py-6">
                    <button
                        v-if="step === 'payment'"
                        @click="openPinModal"
                        class="w-full py-4 bg-[#00A651] text-white font-black text-lg rounded-2xl shadow-lg hover:bg-[#007A3D] active:scale-95 transition-all duration-150"
                    >
                        Bayar Sekarang
                    </button>

                    <!-- Processing State -->
                    <div v-if="step === 'processing'" class="flex flex-col items-center py-4">
                        <div class="w-14 h-14 border-4 border-[#00A651] border-t-transparent rounded-full animate-spin mb-4"></div>
                        <p class="text-gray-600 font-semibold">Memproses pembayaran...</p>
                    </div>

                    <!-- Error State -->
                    <div v-if="step === 'error'" class="flex flex-col items-center py-2 gap-3">
                        <div class="w-14 h-14 bg-red-100 rounded-full flex items-center justify-center">
                            <svg class="w-8 h-8 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </div>
                        <p class="text-red-600 font-semibold text-center text-sm">{{ errorMsg }}</p>
                        <button @click="retryPayment" class="w-full py-3 bg-gray-100 text-gray-700 font-bold rounded-xl hover:bg-gray-200 transition-colors">
                            Coba Lagi
                        </button>
                    </div>
                </div>

            </div>

            <!-- Security Notice -->
            <div class="flex items-center justify-center gap-2 mt-5 mb-8 px-6">
                <svg class="w-4 h-4 text-green-200" fill="currentColor" viewBox="0 0 20 20">
                    <path fill-rule="evenodd" d="M5 9V7a5 5 0 0110 0v2a2 2 0 012 2v5a2 2 0 01-2 2H5a2 2 0 01-2-2v-5a2 2 0 012-2zm8-2v2H7V7a3 3 0 016 0z" clip-rule="evenodd"/>
                </svg>
                <p class="text-green-100 text-xs text-center">Transaksi ini aman dan terenkripsi</p>
            </div>
        </div>

        <!-- PIN Modal Overlay -->
        <transition name="slide-up">
            <div v-if="step === 'pin'" class="fixed inset-0 z-50 flex flex-col justify-end bg-black/60 backdrop-blur-sm">
                <div class="bg-white rounded-t-3xl shadow-2xl px-6 pt-6 pb-10">
                    
                    <!-- Modal Header -->
                    <div class="flex items-center justify-between mb-6">
                        <button @click="retryPayment" class="w-10 h-10 flex items-center justify-center text-gray-400 hover:text-gray-700 rounded-full hover:bg-gray-100 transition-colors">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                        <h3 class="text-lg font-black text-gray-900">Masukkan PIN</h3>
                        <div class="w-10"></div>
                    </div>

                    <!-- Merchant reminder -->
                    <div class="text-center mb-6">
                        <p class="text-sm text-gray-500">Membayar ke <strong class="text-gray-800">Cafe WTA</strong></p>
                        <p class="text-2xl font-black text-[#00A651] mt-1">{{ formatCurrency(transaction.total_amount) }}</p>
                    </div>

                    <!-- PIN Dots Display -->
                    <div class="flex justify-center gap-4 mb-2">
                        <div 
                            v-for="(dot, index) in pinDisplay" 
                            :key="index"
                            :class="[
                                'w-5 h-5 rounded-full transition-all duration-200',
                                dot === '●' ? 'bg-[#00A651] scale-110' : 'bg-gray-200'
                            ]"
                        ></div>
                    </div>
                    <p v-if="pinError" class="text-center text-red-500 text-sm mb-3 font-medium">{{ pinError }}</p>
                    <p v-else class="text-center text-xs text-gray-400 mb-5">PIN 6 digit CaféPay Anda</p>

                    <!-- Numeric Keypad -->
                    <div class="grid grid-cols-3 gap-3 max-w-xs mx-auto">
                        <button 
                            v-for="digit in ['1','2','3','4','5','6','7','8','9']" 
                            :key="digit"
                            @click="appendPin(digit)"
                            class="h-16 rounded-2xl bg-gray-50 hover:bg-gray-100 active:bg-gray-200 active:scale-95 text-2xl font-bold text-gray-800 transition-all duration-100 border border-gray-100"
                        >
                            {{ digit }}
                        </button>
                        <!-- Bottom row -->
                        <button 
                            @click="clearPin"
                            class="h-16 rounded-2xl bg-gray-50 hover:bg-gray-100 active:bg-gray-200 active:scale-95 text-sm font-bold text-gray-500 transition-all duration-100 border border-gray-100"
                        >
                            Hapus
                        </button>
                        <button 
                            @click="appendPin('0')"
                            class="h-16 rounded-2xl bg-gray-50 hover:bg-gray-100 active:bg-gray-200 active:scale-95 text-2xl font-bold text-gray-800 transition-all duration-100 border border-gray-100"
                        >
                            0
                        </button>
                        <button 
                            @click="deletePin"
                            class="h-16 rounded-2xl bg-gray-50 hover:bg-gray-100 active:bg-gray-200 active:scale-95 flex items-center justify-center transition-all duration-100 border border-gray-100"
                        >
                            <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2M3 12l6.414 6.414a2 2 0 001.414.586H19a2 2 0 002-2V7a2 2 0 00-2-2h-8.172a2 2 0 00-1.414.586L3 12z"/>
                            </svg>
                        </button>
                    </div>

                    <!-- Confirm Button -->
                    <button 
                        @click="confirmPayment"
                        :disabled="pin.length < 6"
                        :class="[
                            'w-full mt-6 py-4 font-black text-lg rounded-2xl transition-all duration-200',
                            pin.length >= 6 
                                ? 'bg-[#00A651] text-white shadow-lg hover:bg-[#007A3D] active:scale-95' 
                                : 'bg-gray-100 text-gray-400 cursor-not-allowed'
                        ]"
                    >
                        Konfirmasi Pembayaran
                    </button>
                </div>
            </div>
        </transition>

    </div>
</template>

<style scoped>
.slide-up-enter-active,
.slide-up-leave-active {
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.slide-up-enter-from,
.slide-up-leave-to {
    opacity: 0;
    transform: translateY(100%);
}
.slide-up-enter-to,
.slide-up-leave-from {
    opacity: 1;
    transform: translateY(0);
}
</style>
