<script setup>
import ApplicationLogo from '@/Components/ApplicationLogo.vue';
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div
        class="flex min-h-screen flex-col items-center justify-center bg-slate-50 p-6 relative overflow-hidden font-sans"
    >
        <!-- Animated Background Blobs (Light Theme) -->
        <div class="absolute top-0 left-0 w-full h-full overflow-hidden z-0 pointer-events-none">
            <div class="absolute -top-[10%] -left-[10%] w-[40%] h-[40%] rounded-full bg-primary-200/40 blur-3xl mix-blend-multiply animate-blob"></div>
            <div class="absolute top-[20%] -right-[10%] w-[50%] h-[50%] rounded-full bg-blue-200/40 blur-3xl mix-blend-multiply animate-blob animation-delay-2000"></div>
            <div class="absolute -bottom-[10%] left-[20%] w-[60%] h-[60%] rounded-full bg-sky-200/40 blur-3xl mix-blend-multiply animate-blob animation-delay-4000"></div>
        </div>

        <div class="z-10 flex flex-col items-center w-full">
            <div class="mb-8 transform transition-all duration-500 hover:scale-110">
                <Link href="/">
                    <ApplicationLogo class="h-24 w-24 fill-current text-primary-600 drop-shadow-md" />
                </Link>
            </div>

            <div
                class="w-full sm:max-w-md overflow-hidden bg-white/80 backdrop-blur-2xl px-8 py-10 shadow-xl ring-1 ring-black/5 sm:rounded-3xl relative"
            >
                <slot />
            </div>
        </div>
    </div>
</template>

<style>
@keyframes blob {
    0% { transform: translate(0px, 0px) scale(1); }
    33% { transform: translate(30px, -50px) scale(1.1); }
    66% { transform: translate(-20px, 20px) scale(0.9); }
    100% { transform: translate(0px, 0px) scale(1); }
}
.animate-blob {
    animation: blob 7s infinite;
}
.animation-delay-2000 {
    animation-delay: 2s;
}
.animation-delay-4000 {
    animation-delay: 4s;
}
</style>
