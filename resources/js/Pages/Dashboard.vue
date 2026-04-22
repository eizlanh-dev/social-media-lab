<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useResellerPanel } from '@/Composables/useResellerPanel';
import { Head } from '@inertiajs/vue3';

const panel = useResellerPanel();
panel.mountPanel();
</script>

<template>
    <Head title="Overview" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="lyx-kicker">Overview</p>
                    <h2 class="mt-1 max-w-3xl text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        Keep the big picture clear.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Ringkasan balance, prestasi semasa, dan order management dalam satu overview page.
                    </p>
                </div>
                <button
                    type="button"
                    class="lyx-secondary-button"
                    @click="panel.refreshAll"
                >
                    Refresh data
                </button>
            </div>
        </template>

        <div class="py-8 sm:py-10">
            <div class="mx-auto max-w-7xl space-y-6 px-4 sm:px-6 lg:px-8">
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

                <section class="grid gap-4 md:grid-cols-2 xl:grid-cols-4">
                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Wallet balance</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            {{ panel.loadingWallet ? '...' : `RM ${panel.formatMoney(panel.walletBalance)}` }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">Available balance for new orders.</p>
                    </div>

                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Provider balance</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">
                            {{ panel.loadingProviderBalance ? '...' : panel.providerBalance?.balance ?? '-' }}
                        </p>
                        <p class="mt-2 text-sm text-slate-500">Current provider-side balance.</p>
                    </div>

                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Total sales</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">RM {{ panel.formatMoney(panel.profitSummary.total_sales) }}</p>
                        <p class="mt-2 text-sm text-slate-500">{{ panel.profitSummary.orders_count }} orders recorded.</p>
                    </div>

                    <div class="lyx-card p-5">
                        <p class="lyx-kicker">Pending attention</p>
                        <p class="mt-2 text-2xl font-semibold tracking-tight text-slate-950">{{ panel.pendingOrdersCount }}</p>
                        <p class="mt-2 text-sm text-slate-500">Orders on this page that still need follow-up.</p>
                    </div>
                </section>

                <section class="grid gap-6 xl:grid-cols-[0.9fr_1.1fr]">
                    <div class="lyx-card p-6">
                        <p class="lyx-kicker">Performance summary</p>
                        <div class="mt-4 space-y-4">
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <span class="text-sm text-slate-500">Total profit</span>
                                <span class="text-lg font-semibold text-slate-950">RM {{ panel.formatMoney(panel.profitSummary.total_profit) }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <span class="text-sm text-slate-500">Provider cost</span>
                                <span class="text-lg font-semibold text-slate-950">RM {{ panel.formatMoney(panel.profitSummary.total_cost) }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <span class="text-sm text-slate-500">Selected orders</span>
                                <span class="text-lg font-semibold text-slate-950">{{ panel.selectedOrdersCount }}</span>
                            </div>
                            <div class="flex items-center justify-between rounded-2xl border border-slate-200 bg-white px-4 py-4">
                                <span class="text-sm text-slate-500">Latest topup</span>
                                <span class="text-sm font-semibold capitalize text-slate-950">{{ panel.latestTopupStatus }}</span>
                            </div>
                        </div>
                    </div>

                    <div class="lyx-card p-6">
                        <div class="flex items-center justify-between gap-3">
                            <div>
                                <p class="lyx-kicker">Recent topup requests</p>
                                <h3 class="mt-2 text-lg font-semibold text-slate-950">Wallet activity</h3>
                            </div>
                        </div>

                        <div class="mt-5 space-y-3">
                            <div
                                v-if="panel.topupRequests.length === 0"
                                class="rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-5 text-sm text-slate-500"
                            >
                                No topup request yet.
                            </div>

                            <div
                                v-for="item in panel.topupRequests.slice(0, 5)"
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
                                <p class="mt-3 text-xs text-slate-500">Reference: {{ item.reference || '-' }}</p>
                            </div>
                        </div>
                    </div>
                </section>

                <section class="lyx-card p-6 sm:p-7">
                    <div class="flex flex-col gap-4 lg:flex-row lg:items-end lg:justify-between">
                        <div>
                            <p class="lyx-kicker">Operations</p>
                            <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Order management</h3>
                            <p class="mt-2 text-sm text-slate-500">Semak status order, pilih beberapa order, dan jalankan bulk action dengan lebih tersusun.</p>
                        </div>

                        <div class="grid gap-3 sm:grid-cols-2 xl:grid-cols-5">
                            <select
                                v-model="panel.orderFilters.status"
                                class="lyx-glass-input"
                                @change="panel.orderFilters.page = 1; panel.loadResellerOrders()"
                            >
                                <option value="">All status</option>
                                <option value="pending">Pending</option>
                                <option value="processing">Processing</option>
                                <option value="completed">Completed</option>
                                <option value="partial">Partial</option>
                                <option value="canceled">Canceled</option>
                            </select>

                            <button type="button" class="lyx-secondary-button" :disabled="panel.checkingBulkStatus" @click="panel.checkSelectedStatuses">
                                {{ panel.checkingBulkStatus ? 'Checking...' : 'Check selected' }}
                            </button>
                            <button type="button" class="lyx-secondary-button" :disabled="panel.checkingBulkRefill" @click="panel.refillSelectedOrders">
                                {{ panel.checkingBulkRefill ? 'Submitting...' : 'Refill selected' }}
                            </button>
                            <button type="button" class="lyx-secondary-button" :disabled="panel.checkingBulkCancel" @click="panel.cancelSelectedOrders">
                                {{ panel.checkingBulkCancel ? 'Submitting...' : 'Cancel selected' }}
                            </button>
                            <button type="button" class="lyx-glass-button" @click="panel.refreshAll">
                                Refresh all
                            </button>
                        </div>
                    </div>

                    <div class="mt-6 overflow-hidden rounded-[24px] border border-slate-200 bg-white shadow-sm">
                        <div class="overflow-x-auto">
                            <table class="min-w-full text-sm">
                                <thead class="border-b border-slate-200 bg-slate-50 text-left text-[11px] uppercase tracking-[0.16em] text-slate-500">
                                    <tr>
                                        <th class="px-4 py-4">Select</th>
                                        <th class="px-4 py-4">Order ID</th>
                                        <th class="px-4 py-4">Service</th>
                                        <th class="px-4 py-4">Qty</th>
                                        <th class="px-4 py-4">Customer</th>
                                        <th class="px-4 py-4">Cost</th>
                                        <th class="px-4 py-4">Profit</th>
                                        <th class="px-4 py-4">Status</th>
                                        <th class="px-4 py-4">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-slate-200/80">
                                    <tr v-if="panel.loadingOrders">
                                        <td class="px-4 py-6 text-slate-500" colspan="9">Loading orders...</td>
                                    </tr>
                                    <tr v-else-if="panel.resellerOrders.length === 0">
                                        <td class="px-4 py-6 text-slate-500" colspan="9">No reseller orders yet.</td>
                                    </tr>
                                    <tr v-for="order in panel.resellerOrders" :key="order.id" class="transition hover:bg-slate-50/80">
                                        <td class="px-4 py-4">
                                            <input
                                                v-if="order.external_order_id"
                                                type="checkbox"
                                                class="rounded border-slate-300 text-slate-900 focus:ring-slate-400"
                                                :checked="panel.selectedOrderIds.includes(Number(order.external_order_id))"
                                                @change="panel.toggleOrderSelection(order.external_order_id)"
                                            >
                                        </td>
                                        <td class="px-4 py-4 font-mono text-xs font-semibold text-slate-900">#{{ order.external_order_id }}</td>
                                        <td class="px-4 py-4"><span class="font-medium text-slate-900">{{ order.service_id }}</span></td>
                                        <td class="px-4 py-4 text-slate-600">{{ order.quantity }}</td>
                                        <td class="px-4 py-4 font-medium text-slate-900">RM {{ panel.formatMoney(order.customer_price) }}</td>
                                        <td class="px-4 py-4 text-slate-600">RM {{ panel.formatMoney(order.provider_cost) }}</td>
                                        <td class="px-4 py-4 font-semibold text-slate-950">RM {{ panel.formatMoney(order.profit) }}</td>
                                        <td class="px-4 py-4">
                                            <span class="rounded-full px-3 py-1 text-xs font-semibold capitalize" :class="panel.orderStatusClass(order.status)">
                                                {{ order.status || 'pending' }}
                                            </span>
                                        </td>
                                        <td class="px-4 py-4">
                                            <div class="flex flex-wrap gap-2">
                                                <button type="button" class="lyx-secondary-button !rounded-xl !px-3 !py-2 !text-xs" :disabled="panel.actingOnOrder" @click="panel.runOrderAction('status', order.external_order_id)">
                                                    Status
                                                </button>
                                                <button type="button" class="lyx-secondary-button !rounded-xl !px-3 !py-2 !text-xs" :disabled="panel.actingOnOrder" @click="panel.runOrderAction('refill', order.external_order_id)">
                                                    Refill
                                                </button>
                                                <button type="button" class="lyx-secondary-button !rounded-xl !px-3 !py-2 !text-xs" :disabled="panel.actingOnOrder" @click="panel.runOrderAction('cancel', order.external_order_id)">
                                                    Cancel
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="mt-5 flex items-center justify-between text-sm text-slate-500">
                        <p>{{ panel.orderPagination.total }} total orders</p>
                        <div class="flex items-center gap-3">
                            <button type="button" class="lyx-secondary-button" :disabled="panel.orderPagination.current_page <= 1" @click="panel.goOrdersPage(panel.orderPagination.current_page - 1)">
                                Previous
                            </button>
                            <button type="button" class="lyx-secondary-button" :disabled="panel.orderPagination.current_page >= panel.orderPagination.last_page" @click="panel.goOrdersPage(panel.orderPagination.current_page + 1)">
                                Next
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
