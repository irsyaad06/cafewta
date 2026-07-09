<script setup>
import { ref, computed } from 'vue';
import { Head, useForm, Link } from '@inertiajs/vue3';

const props = defineProps({
    menus: Array,
    categories: Array,
    cafeTables: Array,
    paymentMethods: Array,
    pendingOrdersCount: Number,
    errors: Object,
});

// State
const activeCategory = ref('');
const searchQuery = ref('');
const cart = ref([]);
const isCheckoutModalOpen = ref(false);
const isSuccessModalOpen = ref(false);
const successChangeAmount = ref(0);
const activeMobileTab = ref('menu'); // 'menu' or 'cart'

const form = useForm({
    cafe_table_id: '',
    payment_method_id: '',
    amount_paid: 0,
    cart: []
});

// Computed
const filteredMenus = computed(() => {
    let result = props.menus;
    
    if (activeCategory.value) {
        result = result.filter(menu => menu.category_id == activeCategory.value);
    }
    
    if (searchQuery.value) {
        const query = searchQuery.value.toLowerCase();
        result = result.filter(menu => menu.name.toLowerCase().includes(query));
    }
    
    return result;
});

const cartSubtotal = computed(() => {
    return cart.value.reduce((total, item) => total + (item.price * item.quantity), 0);
});

const cartTax = computed(() => {
    return 0; // Tax removed
});

const cartTotal = computed(() => {
    return cartSubtotal.value;
});

const changeAmount = computed(() => {
    if (!form.amount_paid) return 0;
    return Math.max(0, form.amount_paid - cartTotal.value);
});

// Methods
const formatCurrency = (value) => {
    return new Intl.NumberFormat('id-ID', {
        style: 'currency',
        currency: 'IDR',
        minimumFractionDigits: 0
    }).format(value);
};

const addToCart = (menu) => {
    const existingItem = cart.value.find(item => item.menu_id === menu.id);
    if (existingItem) {
        existingItem.quantity++;
    } else {
        cart.value.push({
            menu_id: menu.id,
            name: menu.name,
            price: parseFloat(menu.selling_price),
            quantity: 1,
        });
    }
};

const increaseQuantity = (item) => {
    item.quantity++;
};

const decreaseQuantity = (item) => {
    if (item.quantity > 1) {
        item.quantity--;
    } else {
        removeFromCart(item);
    }
};

const removeFromCart = (itemToRemove) => {
    cart.value = cart.value.filter(item => item.menu_id !== itemToRemove.menu_id);
};

const openCheckout = () => {
    if (cart.value.length === 0) return;
    form.amount_paid = cartTotal.value; // default to exact amount
    if (props.paymentMethods.length > 0) {
        form.payment_method_id = props.paymentMethods[0].id;
    }
    isCheckoutModalOpen.value = true;
};

const closeCheckout = () => {
    isCheckoutModalOpen.value = false;
    form.reset();
};

const submitCheckout = () => {
    successChangeAmount.value = changeAmount.value;
    
    form.cart = cart.value.map(item => ({
        menu_id: item.menu_id,
        quantity: item.quantity
    }));

    form.post(route('pos.store'), {
        preserveScroll: true,
        onSuccess: () => {
            cart.value = [];
            closeCheckout();
            isSuccessModalOpen.value = true;
        }
    });
};

const setActiveCategory = (categoryId) => {
    activeCategory.value = categoryId;
};

</script>

<template>
    <Head title="Kasir"  />

    <div class="flex flex-col md:flex-row h-screen bg-gray-50 overflow-hidden font-sans pb-16 md:pb-0">
        
        <!-- Left Side: Menus -->
        <div 
            :class="[activeMobileTab === 'menu' ? 'flex' : 'hidden md:flex']"
            class="w-full md:w-2/3 flex-col h-full bg-white"
        >
            
            <!-- Header/Filters -->
            <div class="p-4 md:p-6 border-b border-gray-200">
                <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center mb-4 gap-3">
                    <h1 class="text-xl md:text-2xl font-bold text-gray-800">Kasir</h1>
                    <Link :href="route('pos.orders')" class="inline-flex items-center justify-center space-x-2 bg-white border border-gray-200 hover:bg-gray-50 text-gray-700 px-4 py-2 rounded-xl transition-colors shadow-sm relative sm:w-auto w-full">
                        <svg class="w-5 h-5 text-primary-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"></path></svg>
                        <span class="font-bold">Pesanan</span>
                        <span v-if="pendingOrdersCount > 0" class="absolute -top-2 -right-2 flex h-6 w-6 items-center justify-center rounded-full bg-red-500 text-xs font-bold text-white shadow ring-2 ring-white">
                            {{ pendingOrdersCount }}
                        </span>
                    </Link>
                </div>
                
                <!-- Search & Category Filter -->
                <div class="flex flex-col sm:flex-row gap-3">
                    <div class="relative flex-1">
                        <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                            <svg class="h-5 w-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                        </div>
                        <input 
                            v-model="searchQuery" 
                            type="text" 
                            class="pl-10 w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2" 
                            placeholder="Cari menu..."
                        >
                    </div>
                    <div class="sm:w-1/3">
                        <select 
                            v-model="activeCategory" 
                            class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-2"
                        >
                            <option value="">Semua Kategori</option>
                            <option v-for="category in categories" :key="category.id" :value="category.id">
                                {{ category.name }}
                            </option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Menu Grid -->
            <div class="flex-1 overflow-y-auto p-6 bg-gray-50">
                <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-6">
                    <div 
                        v-for="menu in filteredMenus" 
                        :key="menu.id"
                        @click="addToCart(menu)"
                        class="bg-white rounded-2xl shadow-sm hover:shadow-lg transition-shadow duration-300 overflow-hidden cursor-pointer border border-gray-100 flex flex-col group"
                    >
                        <div class="h-32 bg-gray-200 flex items-center justify-center overflow-hidden">
                            <!-- Placeholder image if no image available -->
                            <img v-if="menu.image" :src="`/storage/${menu.image}`" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300" alt="menu" />
                            <svg v-else class="w-12 h-12 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                        </div>
                        <div class="p-4 flex-1 flex flex-col justify-between">
                            <div>
                                <h3 class="font-bold text-gray-800 text-sm mb-1 line-clamp-2">{{ menu.name }}</h3>
                                <p class="text-xs text-gray-500">{{ menu.category?.name }}</p>
                            </div>
                            <div class="mt-3 font-semibold text-primary-600">
                                {{ formatCurrency(menu.selling_price) }}
                            </div>
                        </div>
                    </div>
                </div>
                
                <div v-if="filteredMenus.length === 0" class="text-center py-20 text-gray-500">
                    Tidak ada menu di kategori ini.
                </div>
            </div>
        </div>

        <!-- Right Side: Cart -->
        <div 
            :class="[activeMobileTab === 'cart' ? 'flex' : 'hidden md:flex']"
            class="w-full md:w-1/3 flex-col h-full bg-white border-l border-gray-200 shadow-2xl z-10"
        >
            <!-- Cart Header -->
            <div class="p-6 border-b border-gray-200 bg-white">
                <h2 class="text-xl font-bold text-gray-800 flex justify-between items-center">
                    Pesanan Saat Ini
                    <span class="bg-primary-100 text-primary-800 text-xs px-2.5 py-1 rounded-full">{{ cart.length }} items</span>
                </h2>
            </div>

            <!-- Cart Items -->
            <div class="flex-1 overflow-y-auto p-4 space-y-4">
                <div v-if="cart.length === 0" class="flex flex-col items-center justify-center h-full text-gray-400">
                    <svg class="w-16 h-16 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    <p>Keranjang masih kosong</p>
                    <p class="text-sm">Silakan pilih menu</p>
                </div>

                <div v-for="item in cart" :key="item.menu_id" class="flex items-center p-3 bg-gray-50 rounded-xl border border-gray-100">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-semibold text-gray-800 text-sm truncate">{{ item.name }}</h4>
                        <p class="text-primary-600 font-medium text-sm mt-1">{{ formatCurrency(item.price) }}</p>
                    </div>
                    <div class="flex items-center space-x-3 ml-4">
                        <div class="flex items-center bg-white rounded-lg border border-gray-200">
                            <button @click="decreaseQuantity(item)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-l-lg transition-colors">&minus;</button>
                            <span class="w-8 text-center text-sm font-semibold text-gray-800">{{ item.quantity }}</span>
                            <button @click="increaseQuantity(item)" class="w-8 h-8 flex items-center justify-center text-gray-500 hover:text-primary-600 hover:bg-primary-50 rounded-r-lg transition-colors">&plus;</button>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Cart Summary & Actions -->
            <div class="p-6 bg-white border-t border-gray-200">
                <div class="space-y-3 mb-6">
                    <div class="flex justify-between text-sm text-gray-600">
                        <span>Subtotal</span>
                        <span class="font-medium">{{ formatCurrency(cartSubtotal) }}</span>
                    </div>

                    <div class="border-t border-dashed border-gray-300 pt-3 flex justify-between items-center">
                        <span class="font-bold text-gray-800 text-lg">Total</span>
                        <span class="font-bold text-primary-600 text-2xl">{{ formatCurrency(cartTotal) }}</span>
                    </div>
                </div>
                
                <button 
                    @click="openCheckout"
                    :disabled="cart.length === 0"
                    :class="['w-full py-4 rounded-xl font-bold text-lg shadow-lg transition-all duration-300 flex justify-center items-center', cart.length === 0 ? 'bg-gray-300 text-gray-500 cursor-not-allowed shadow-none' : 'bg-primary-600 text-white hover:bg-primary-700 hover:shadow-primary-500/30 transform hover:-translate-y-1']"
                >
                    <svg v-if="cart.length > 0" class="w-6 h-6 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 9V7a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2m2 4h10a2 2 0 002-2v-6a2 2 0 00-2-2H9a2 2 0 00-2 2v6a2 2 0 002 2zm7-5a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                    Bayar Sekarang
                </button>
            </div>
        </div>

        <!-- Checkout Modal -->
        <div v-if="isCheckoutModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm" @click="closeCheckout"></div>
            
            <!-- Modal Box -->
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-lg mx-4 overflow-hidden transform transition-all max-h-[95vh] flex flex-col">
                <div class="bg-primary-600 p-6 text-white text-center shrink-0">
                    <h3 class="text-xl font-bold">Proses Pembayaran</h3>
                    <p class="text-primary-200 text-sm mt-1">Lengkapi data transaksi di bawah ini</p>
                </div>
                
                <form @submit.prevent="submitCheckout" class="p-6 sm:p-8 overflow-y-auto">
                    
                    <div class="mb-5">
                        <div class="flex justify-between items-center p-4 bg-gray-50 rounded-xl border border-gray-200">
                            <span class="text-gray-600 font-medium">Total Tagihan</span>
                            <span class="text-2xl font-bold text-gray-800">{{ formatCurrency(cartTotal) }}</span>
                        </div>
                    </div>

                    <div class="space-y-5">
                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Pilih Meja (Opsional)</label>
                            <select v-model="form.cafe_table_id" class="w-full rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 py-3">
                                <option value="">-- Takeaway / Tidak Pilih Meja --</option>
                                <option v-for="table in cafeTables" :key="table.id" :value="table.id">
                                    Meja {{ table.table_number }} - {{ table.name }} (Kapasitas: {{ table.capacity }})
                                </option>
                            </select>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Metode Pembayaran</label>
                            <div class="grid grid-cols-2 gap-3">
                                <label 
                                    v-for="method in paymentMethods" 
                                    :key="method.id" 
                                    :class="['border rounded-xl p-3 cursor-pointer transition-all duration-200 text-center font-medium flex flex-col items-center justify-center gap-1', form.payment_method_id === method.id ? 'border-primary-600 bg-primary-50 text-primary-700 shadow-sm ring-1 ring-primary-600' : 'border-gray-200 text-gray-600 hover:bg-gray-50']"
                                >
                                    <input type="radio" :value="method.id" v-model="form.payment_method_id" class="sr-only">
                                    <span class="text-sm">{{ method.name }}</span>
                                </label>
                            </div>
                            <p v-if="form.errors.payment_method_id" class="text-red-500 text-xs mt-1">{{ form.errors.payment_method_id }}</p>
                        </div>

                        <div>
                            <label class="block text-sm font-semibold text-gray-700 mb-2">Jumlah Uang Diterima</label>
                            <div class="relative">
                                <div class="absolute inset-y-0 left-0 pl-4 flex items-center pointer-events-none">
                                    <span class="text-gray-500 font-medium">Rp</span>
                                </div>
                                <input 
                                    type="number" 
                                    v-model="form.amount_paid" 
                                    class="w-full pl-12 pr-4 py-3 rounded-xl border-gray-300 shadow-sm focus:border-primary-500 focus:ring-primary-500 text-lg font-semibold"
                                    placeholder="0"
                                    min="0"
                                >
                            </div>
                            <div class="flex gap-2 mt-2">
                                <button type="button" @click="form.amount_paid = cartTotal" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full transition-colors">Uang Pas</button>
                                <button type="button" @click="form.amount_paid = Math.ceil(cartTotal / 50000) * 50000" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full transition-colors">Bulatkan 50k</button>
                                <button type="button" @click="form.amount_paid = Math.ceil(cartTotal / 100000) * 100000" class="text-xs bg-gray-100 hover:bg-gray-200 text-gray-700 px-3 py-1 rounded-full transition-colors">Bulatkan 100k</button>
                            </div>
                            <p v-if="form.errors.amount_paid" class="text-red-500 text-xs mt-1">{{ form.errors.amount_paid }}</p>
                        </div>

                        <div class="pt-4 border-t border-gray-100">
                            <div class="flex justify-between items-center p-4 rounded-xl" :class="changeAmount >= 0 ? 'bg-green-50' : 'bg-red-50'">
                                <span class="font-medium" :class="changeAmount >= 0 ? 'text-green-700' : 'text-red-700'">Kembalian</span>
                                <span class="text-2xl font-bold" :class="changeAmount >= 0 ? 'text-green-700' : 'text-red-700'">
                                    {{ formatCurrency(changeAmount) }}
                                </span>
                            </div>
                            <p v-if="form.amount_paid < cartTotal" class="text-red-500 text-sm mt-2 text-center font-medium">Uang dibayarkan kurang dari total tagihan!</p>
                        </div>
                    </div>

                    <div class="mt-8 flex gap-3">
                        <button 
                            type="button" 
                            @click="closeCheckout"
                            class="w-1/3 py-3 rounded-xl font-bold text-gray-700 bg-gray-100 hover:bg-gray-200 transition-colors"
                        >
                            Batal
                        </button>
                        <button 
                            type="submit" 
                            :disabled="form.processing || form.amount_paid < cartTotal || !form.payment_method_id"
                            class="w-2/3 py-3 rounded-xl font-bold text-white shadow-lg transition-all"
                            :class="(form.processing || form.amount_paid < cartTotal || !form.payment_method_id) ? 'bg-gray-400 cursor-not-allowed shadow-none' : 'bg-primary-600 hover:bg-primary-700 hover:shadow-primary-500/30'"
                        >
                            <span v-if="form.processing">Memproses...</span>
                            <span v-else>Konfirmasi & Simpan</span>
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <!-- Success Modal -->
        <div v-if="isSuccessModalOpen" class="fixed inset-0 z-50 flex items-center justify-center">
            <!-- Backdrop -->
            <div class="absolute inset-0 bg-gray-900 bg-opacity-60 backdrop-blur-sm" @click="isSuccessModalOpen = false"></div>
            
            <!-- Modal Box -->
            <div class="relative bg-white rounded-3xl shadow-2xl w-full max-w-sm mx-4 overflow-hidden transform transition-all text-center">
                <div class="bg-green-500 p-8 relative overflow-hidden">
                    <div class="absolute inset-0 bg-[url('https://www.transparenttextures.com/patterns/cubes.png')] opacity-10 mix-blend-overlay"></div>
                    <div class="w-20 h-20 bg-white rounded-full flex items-center justify-center mx-auto mb-4 shadow-lg relative z-10">
                        <svg class="w-10 h-10 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="3" d="M5 13l4 4L19 7"></path></svg>
                    </div>
                    <h3 class="text-2xl font-extrabold text-white relative z-10 tracking-tight">Transaksi Berhasil!</h3>
                </div>
                
                <div class="p-6">
                    <p class="text-gray-500 mb-2 font-medium">Uang Kembalian</p>
                    <p class="text-3xl font-black text-green-600 mb-6">{{ formatCurrency(successChangeAmount) }}</p>
                    
                    <button 
                        @click="isSuccessModalOpen = false"
                        class="w-full py-3 rounded-xl font-bold text-white bg-primary-600 hover:bg-primary-700 hover:shadow-primary-500/30 shadow-lg transition-all"
                    >
                        Selesai & Lanjut
                    </button>
                </div>
            </div>
        </div>

    </div>

    <!-- Bottom Navigation (Mobile Only) -->
    <nav class="md:hidden fixed bottom-0 w-full bg-white border-t border-gray-200 flex shadow-[0_-4px_6px_-1px_rgba(0,0,0,0.1)] z-40">
        <button 
            @click="activeMobileTab = 'menu'" 
            :class="['flex-1 py-3 flex flex-col items-center justify-center space-y-1', activeMobileTab === 'menu' ? 'text-primary-600' : 'text-gray-500']"
        >
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2V6zM14 6a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2V6zM4 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2H6a2 2 0 01-2-2v-2zM14 16a2 2 0 012-2h2a2 2 0 012 2v2a2 2 0 01-2 2h-2a2 2 0 01-2-2v-2z"></path></svg>
            <span class="text-xs font-medium">Menu</span>
        </button>
        <button 
            @click="activeMobileTab = 'cart'" 
            :class="['flex-1 py-3 flex flex-col items-center justify-center space-y-1 relative', activeMobileTab === 'cart' ? 'text-primary-600' : 'text-gray-500']"
        >
            <div class="relative">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                <span v-if="cart.length > 0" class="absolute -top-1 -right-2 bg-red-500 text-white text-[10px] font-bold px-1.5 py-0.5 rounded-full min-w-[18px] text-center">{{ cart.length }}</span>
            </div>
            <span class="text-xs font-medium">Keranjang</span>
        </button>
    </nav>
</template>
