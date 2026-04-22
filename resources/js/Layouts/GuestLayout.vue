<script setup>
import { Link } from '@inertiajs/vue3';
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-transparent px-4 py-12 sm:px-6 lg:px-8">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[32rem] bg-[radial-gradient(circle_at_top,rgba(191,219,254,0.72),rgba(255,255,255,0))]"></div>
        <div class="pointer-events-none absolute inset-0 opacity-40 lyx-soft-grid"></div>

        <div class="relative mx-auto grid min-h-[calc(100vh-6rem)] w-full max-w-7xl items-center gap-10 lg:grid-cols-[1.05fr_0.95fr]">
            <div class="hidden lg:block">
                <div class="max-w-xl">
                    <p class="lyx-kicker">Social Media Lab</p>
                    <h1 class="mt-4 text-5xl font-bold tracking-tighter text-blue-600">
                        Dari reach ke engagement, kami gerakkan semuanya.
                    </h1>
                    <p class="mt-6 text-lg leading-8 text-slate-700">
                        Platform order yang lebih kemas untuk submit servis, urus wallet, dan semak status customer dalam satu workspace yang lebih moden.
                    </p>

                    <dl class="mt-10 grid grid-cols-2 gap-x-8 gap-y-8">
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
                            <dt class="font-mono text-xs text-blue-600">Actions</dt>
                            <dd class="mt-1 text-2xl font-semibold tracking-tight text-slate-900">Bulk tools</dd>
                        </div>
                    </dl>
                </div>
            </div>

            <div class="relative w-full max-w-lg lg:justify-self-end">
                <Link href="/" class="mb-6 inline-flex items-center gap-3 rounded-full border border-blue-100 bg-white px-4 py-2 text-sm font-semibold text-slate-900 shadow-sm">
                    <span class="flex h-8 w-8 items-center justify-center rounded-2xl bg-blue-600 text-xs font-bold text-white">SM</span>
                    <span class="tracking-[0.16em] text-[11px] uppercase text-slate-600">Social Media Lab</span>
                </Link>

                <div class="lyx-card overflow-hidden px-6 py-6 sm:px-8 sm:py-8">
                    <slot />
                </div>
            </div>
        </div>
    </div>
</template>
