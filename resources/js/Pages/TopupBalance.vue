<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useResellerPanel } from '@/Composables/useResellerPanel';
import { Head } from '@inertiajs/vue3';

const panel = useResellerPanel();
panel.mountPanel();
</script>

<template>
    <Head title="Topup Balance" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="lyx-kicker">Topup balance</p>
                    <h2 class="mt-1 max-w-3xl text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        Manage wallet funding in one place.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Hantar topup request, semak status terkini, dan monitor balance semasa dari page khas.
                    </p>
                </div>
                <button type="button" class="lyx-secondary-button" @click="panel.refreshAll">
                    Refresh data
                </button>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-7xl space-y-4 px-4 sm:px-6 lg:px-8">
                <div class="space-y-3">
                    <div
                        v-for="item in panel.notifications"
                        :key="item.id"
                        class="rounded-2xl border px-4 py-3 text-sm shadow-sm"
                        :class="item.type === 'error'
                            ? 'border-rose-200 bg-rose-50/90 text-rose-700'
                            : 'border-emerald-200 bg-emerald-50/90 text-emerald-700'"
                    >
                        {{ item.message }}
                    </div>
                </div>

                <section class="grid gap-4 md:grid-cols-3">
                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Wallet balance</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            {{ panel.loadingWallet ? '...' : `RM ${panel.formatMoney(panel.walletBalance)}` }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">Available balance for current orders.</p>
                    </div>

                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Provider balance</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            {{ panel.loadingProviderBalance ? '...' : panel.providerBalance?.balance ?? '-' }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">Current provider-side balance.</p>
                    </div>

                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Latest topup</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight capitalize text-slate-950">{{ panel.latestTopupStatus }}</p>
                        <p class="mt-2 text-sm text-slate-500">Latest submitted topup request status.</p>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[1fr_1fr]">
                    <div class="lyx-card p-6">
                        <div>
                            <p class="lyx-kicker">Topup form</p>
                            <h3 class="mt-2 text-xl font-semibold tracking-tight text-slate-900">Submit topup request</h3>
                        </div>

                        <div class="mt-5 grid gap-4 md:grid-cols-2">
                            <input v-model.number="panel.topupForm.amount" type="number" min="10" step="1" class="lyx-glass-input" placeholder="Amount (RM)">
                            <select v-model="panel.topupForm.payment_method" class="lyx-glass-input">
                                <option value="duitnow">DuitNow</option>
                                <option value="bank-transfer">Bank Transfer</option>
                                <option value="tng">TNG eWallet</option>
                                <option value="manual">Manual</option>
                            </select>
                            <input v-model="panel.topupForm.reference" type="text" maxlength="120" class="lyx-glass-input" placeholder="Reference">
                            <div class="md:row-span-2">
                                <textarea v-model="panel.topupForm.notes" maxlength="500" rows="5" class="lyx-glass-input h-full resize-none" placeholder="Notes"></textarea>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="button" class="lyx-glass-button min-w-56" :disabled="panel.submittingTopup" @click="panel.submitTopupRequest">
                                {{ panel.submittingTopup ? 'Submitting request...' : 'Submit topup request' }}
                            </button>
                        </div>
                    </div>

                    <div class="lyx-card p-6">
                        <p class="lyx-kicker">Recent requests</p>
                        <h3 class="mt-2 text-xl font-semibold tracking-tight text-slate-900">Topup history</h3>

                        <div class="mt-5 space-y-3">
                            <div
                                v-if="panel.topupRequests.length === 0"
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-5 text-sm text-slate-500"
                            >
                                No topup request yet.
                            </div>

                            <div
                                v-for="item in panel.topupRequests"
                                :key="item.id"
                                class="rounded-2xl border border-slate-200 bg-white p-4"
                            >
                                <div class="flex items-start justify-between gap-3">
                                    <div>
                                        <p class="text-lg font-semibold text-slate-950">RM {{ panel.formatMoney(item.amount) }}</p>
                                        <p class="mt-1 text-sm capitalize text-slate-500">{{ item.payment_method }}</p>
                                    </div>
                                    <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="panel.topupStatusClass(item.status)">
                                        {{ item.status }}
                                    </span>
                                </div>
                                <div class="mt-3 grid gap-2 text-xs text-slate-500 sm:grid-cols-2">
                                    <p>Reference: {{ item.reference || '-' }}</p>
                                    <p>Notes: {{ item.notes || '-' }}</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
