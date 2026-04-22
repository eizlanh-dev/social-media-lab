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
    <Head title="Social Media Lab" />

    <div class="relative min-h-screen overflow-hidden px-4 py-8 sm:px-8">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top,rgba(191,219,254,0.72),rgba(255,255,255,0))]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-40 lyx-soft-grid"></div>

        <div class="relative mx-auto flex w-full max-w-7xl flex-col gap-12">
            <header class="flex items-center justify-between rounded-3xl border border-blue-100 bg-white px-4 py-3 shadow-sm sm:px-6">
                <div class="inline-flex items-center gap-3 rounded-full border border-blue-100 bg-white px-4 py-2 text-sm font-semibold text-slate-900">
                    <span class="flex h-8 w-8 items-center justify-center rounded-2xl bg-blue-600 text-xs font-bold text-white">SM</span>
                    <span class="tracking-[0.16em] text-[11px] uppercase text-slate-600">Social Media Lab</span>
                </div>

                <nav v-if="canLogin" class="flex items-center gap-2 sm:gap-3">
                    <Link
                        v-if="$page.props.auth.user"
                        :href="route('dashboard')"
                        class="lyx-secondary-button !rounded-xl !px-4 !py-2.5"
                    >
                        Dashboard
                    </Link>
                    <template v-else>
                        <Link
                            :href="route('login')"
                            class="lyx-secondary-button !rounded-xl !px-4 !py-2.5"
                        >
                            Log in
                        </Link>
                        <Link
                            v-if="canRegister"
                            :href="route('register')"
                            class="lyx-glass-button !rounded-xl !px-4 !py-2.5"
                        >
                            Register
                        </Link>
                    </template>
                </nav>
            </header>

            <section class="grid gap-12 lg:grid-cols-[1.15fr_0.85fr] lg:items-center">
                <div>
                    <p class="lyx-kicker">Social Media Lab</p>
                    <h1 class="mt-6 max-w-4xl text-5xl font-bold tracking-tighter text-blue-600 sm:text-7xl">
                        Dari reach ke engagement, kami gerakkan semuanya.
                    </h1>
                    <p class="mt-6 max-w-2xl text-lg leading-8 text-slate-700">
                        Social Media Lab bantu urus order servis, wallet funding, dan tracking customer dalam satu panel yang lebih jelas, cepat, dan meyakinkan.
                    </p>

                    <div class="mt-10 flex flex-wrap items-center gap-4">
                        <Link
                            :href="$page.props.auth.user ? route('dashboard') : route('login')"
                            class="lyx-glass-button px-6"
                        >
                            {{ $page.props.auth.user ? 'Open overview' : 'Start with overview' }}
                        </Link>
                        <Link
                            v-if="canRegister && !$page.props.auth.user"
                            :href="route('register')"
                            class="lyx-secondary-button px-6"
                        >
                            Create account
                        </Link>
                    </div>

                    <dl class="mt-12 grid grid-cols-2 gap-x-8 gap-y-8 sm:grid-cols-4">
                        <div>
                            <dt class="font-mono text-xs text-blue-600">Catalog</dt>
                            <dd class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">700+</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-xs text-blue-600">Tracking</dt>
                            <dd class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Realtime</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-xs text-blue-600">Wallet</dt>
                            <dd class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Integrated</dd>
                        </div>
                        <div>
                            <dt class="font-mono text-xs text-blue-600">Analytics</dt>
                            <dd class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Clear</dd>
                        </div>
                    </dl>
                </div>

                <div class="lyx-card p-6 sm:p-8">
                    <div class="grid gap-4 sm:grid-cols-2">
                        <div class="rounded-3xl bg-slate-900 p-5 text-white sm:col-span-2">
                            <p class="font-mono text-xs uppercase tracking-[0.2em] text-blue-300">Operations</p>
                            <p class="mt-3 text-3xl font-semibold tracking-tight">24/7 ordering workflow</p>
                            <p class="mt-3 max-w-md text-sm leading-6 text-slate-300">Customer orders, wallet balance, and refill actions managed from a cleaner Tailwind-style workspace.</p>
                        </div>

                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-mono text-xs text-blue-600">Service catalog</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Searchable</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Filter by category, rate, and order range.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-mono text-xs text-blue-600">Order control</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Bulk actions</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Check status, refill, or cancel selected orders.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-mono text-xs text-blue-600">Profit view</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Live margins</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Track sales, provider cost, and total profit clearly.</p>
                        </div>
                        <div class="rounded-2xl border border-slate-200 bg-slate-50 p-5">
                            <p class="font-mono text-xs text-blue-600">Wallet topup</p>
                            <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Request flow</p>
                            <p class="mt-2 text-sm leading-6 text-slate-600">Submit and monitor topup requests with reference notes.</p>
                        </div>
                    </div>
                </div>
            </section>
        </div>
    </div>
</template>
