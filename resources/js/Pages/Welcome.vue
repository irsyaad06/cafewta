<script setup>
import { Head, Link } from '@inertiajs/vue3';

defineProps({
    canLogin: {
        type: Boolean,
    },
    canRegister: {
        type: Boolean,
    },
});
</script>

<template>
    <Head title="Welcome" />

    <div class="min-h-screen bg-gray-50 text-gray-900 flex flex-col justify-center items-center relative overflow-hidden">
        <!-- Background Pattern -->
        <div class="absolute inset-0 bg-[url('data:image/svg+xml,%3Csvg width=\'60\' height=\'60\' viewBox=\'0 0 60 60\' xmlns=\'http://www.w3.org/2000/svg\'%3E%3Cg fill=\'none\' fill-rule=\'evenodd\'%3E%3Cg fill=\'%23e5e7eb\' fill-opacity=\'0.4\'%3E%3Cpath d=\'M36 34v-4h-2v4h-4v2h4v4h2v-4h4v-2h-4zm0-30V0h-2v4h-4v2h4v4h2V6h4V4h-4zM6 34v-4H4v4H0v2h4v4h2v-4h4v-2H6zM6 4V0H4v4H0v2h4v4h2V6h4V4H6z\'/%3E%3C/g%3E%3C/g%3E%3C/svg%3E')]"></div>
        
        <div class="relative z-10 text-center max-w-xl mx-auto px-4">
            <!-- Logo / Icon -->
            <div class="mb-8 flex justify-center">
                <div class="w-20 h-20 bg-primary-600 rounded-2xl flex items-center justify-center shadow-lg shadow-primary-500/30 transform rotate-12">
                    <svg class="w-10 h-10 text-white transform -rotate-12" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"></path>
                    </svg>
                </div>
            </div>

            <h1 class="text-4xl sm:text-5xl font-extrabold text-gray-900 mb-4 tracking-tight">
                Sistem Manajemen <span class="text-primary-600">Cafe</span>
            </h1>
            
            <p class="text-lg text-gray-600 mb-10 max-w-md mx-auto">
                Silakan masuk sesuai dengan role (peran) Anda di restoran ini.
            </p>

            <div class="flex flex-col sm:flex-row gap-4 justify-center items-stretch sm:items-center">
                <!-- Admin Login Button -->
                <a href="/admin/login" class="group relative px-6 py-4 bg-gray-900 text-white font-bold rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-3">
                    <div class="absolute inset-0 bg-white/20 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                    <span class="relative z-10">Masuk sebagai Admin</span>
                </a>

                <!-- Staff Login Button -->
                <Link :href="route('login')" class="group relative px-6 py-4 bg-white text-primary-600 font-bold rounded-2xl overflow-hidden shadow-xl hover:shadow-2xl transition-all duration-300 transform hover:-translate-y-1 flex items-center justify-center gap-3 border border-primary-100">
                    <div class="absolute inset-0 bg-primary-50 translate-y-full group-hover:translate-y-0 transition-transform duration-300 ease-out"></div>
                    <svg class="w-6 h-6 relative z-10" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <span class="relative z-10">Masuk sebagai Karyawan</span>
                </Link>
            </div>
            
            <div class="mt-12">
                <Link :href="route('simulasi-meja')" class="inline-flex items-center gap-2 text-sm text-gray-500 hover:text-primary-600 transition-colors font-medium">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                    Simulasi Customer (QR Order)
                </Link>
            </div>
        </div>
    </div>
</template>
