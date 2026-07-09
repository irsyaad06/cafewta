<script setup>
import { Head, Link } from '@inertiajs/vue3';

const props = defineProps({
    tables: Array,
});
</script>

<template>
    <Head title="Simulasi Scan QR Meja" />

    <div class="min-h-screen bg-gray-100 p-8 font-sans">
        <div class="max-w-6xl mx-auto">
            <div class="text-center mb-10">
                <h1 class="text-3xl font-bold text-gray-800">Simulasi Pemesanan Mandiri (QR Code)</h1>
                <p class="text-gray-600 mt-2">Pilih meja untuk mensimulasikan proses scan QR Code oleh pelanggan.</p>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                <div 
                    v-for="table in tables" 
                    :key="table.id"
                    class="bg-white rounded-2xl shadow-sm border border-gray-200 overflow-hidden hover:shadow-md transition-shadow"
                >
                    <div class="bg-primary-600 text-white text-center py-3">
                        <h2 class="font-bold text-lg">Meja {{ table.table_number }}</h2>
                        <p class="text-xs text-primary-100">{{ table.name }}</p>
                    </div>
                    
                    <div class="p-6 flex flex-col items-center justify-center border-b border-gray-100">
                        <!-- We use api.qrserver.com to generate QR code image based on the table's qr_code value -->
                        <img 
                            :src="`https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=${encodeURIComponent(table.qr_code || '')}`" 
                            :alt="`QR Code Meja ${table.table_number}`"
                            class="w-40 h-40 object-contain p-2 border-2 border-dashed border-gray-300 rounded-xl"
                        />
                    </div>
                    
                    <div class="p-4 bg-gray-50 flex justify-center">
                        <!-- Direct link matching the QR code payload -->
                        <a 
                            :href="table.qr_code" 
                            target="_blank"
                            class="w-full py-2 bg-white text-primary-600 border border-primary-200 text-center rounded-lg font-semibold hover:bg-primary-50 transition-colors"
                        >
                            Simulasi Scan (Buka Link)
                        </a>
                    </div>
                </div>
                
                <div v-if="tables.length === 0" class="col-span-full text-center py-12 text-gray-500">
                    Tidak ada meja yang tersedia. Silakan tambahkan meja dari panel admin.
                </div>
            </div>
        </div>
    </div>
</template>
