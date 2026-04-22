<script setup>
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout.vue';
import { useResellerPanel } from '@/Composables/useResellerPanel';
import { Head } from '@inertiajs/vue3';

const panel = useResellerPanel();
panel.mountPanel();
</script>

<template>
    <Head title="New Order" />

    <AuthenticatedLayout>
        <template #header>
            <div class="flex flex-col gap-3 md:flex-row md:items-center md:justify-between">
                <div>
                    <p class="lyx-kicker">New order</p>
                    <h2 class="mt-1 max-w-3xl text-3xl font-bold tracking-tight text-slate-900 sm:text-4xl">
                        Create a new order with less friction.
                    </h2>
                    <p class="mt-3 max-w-2xl text-sm leading-6 text-slate-600">
                        Fokus page ini hanyalah pilih platform, pilih service, dan submit order.
                    </p>
                </div>
                <button type="button" class="lyx-secondary-button" @click="panel.refreshAll">
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

                <section class="lyx-card p-6 sm:p-7">
                    <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                        <div>
                            <p class="lyx-kicker">Primary action</p>
                            <h3 class="mt-2 text-2xl font-semibold tracking-tight text-slate-900">Simple 3-step ordering</h3>
                            <p class="mt-2 text-sm text-slate-500">Step 1 pilih social media, step 2 pilih service untuk platform tu, step 3 baru isi target link dan quantity.</p>
                        </div>
                        <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-right">
                            <p class="lyx-kicker">Selected platform</p>
                            <p class="mt-1 text-lg font-semibold text-slate-950">{{ panel.selectedPlatformLabel }}</p>
                            <p class="mt-1 text-sm text-slate-500">{{ panel.catalogPagination.total }} services found</p>
                        </div>
                    </div>

                    <div class="mt-6 grid gap-4 xl:grid-cols-[0.8fr_1.4fr]">
                        <div class="rounded-[24px] border border-slate-200 bg-slate-50/80 p-4">
                            <p class="lyx-kicker">Step 1</p>
                            <h4 class="mt-2 text-lg font-semibold tracking-tight text-slate-900">Choose platform</h4>
                            <div class="mt-4 space-y-2">
                                <div
                                    v-for="platform in panel.platformOptions"
                                    :key="platform.key"
                                    class="space-y-2"
                                >
                                    <button
                                        type="button"
                                        class="flex w-full items-center justify-between rounded-2xl border px-4 py-3 text-left text-sm font-semibold transition"
                                        :class="panel.serviceExplorer.platform === platform.key
                                            ? 'border-blue-200 bg-blue-50 text-blue-700'
                                            : 'border-slate-200 bg-white text-slate-700 hover:border-slate-300 hover:bg-slate-50'"
                                        @click="panel.selectPlatform(platform.key)"
                                    >
                                        <span>{{ platform.label }}</span>
                                    </button>

                                    <div
                                        v-if="panel.serviceExplorer.platform === platform.key"
                                        class="rounded-2xl border border-blue-100 bg-blue-50/50 p-3"
                                    >
                                        <label class="lyx-kicker">Service type</label>
                                        <select
                                            :value="panel.catalogFilters.category"
                                            class="lyx-glass-input mt-2"
                                            @change="panel.selectCategory($event.target.value)"
                                        >
                                            <option value="">All service types</option>
                                            <option
                                                v-for="category in panel.platformCategoryOptions"
                                                :key="category.value"
                                                :value="category.value"
                                            >
                                                {{ category.label }}
                                            </option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="rounded-[24px] border border-slate-200 bg-white p-4 shadow-sm">
                            <div class="flex flex-col gap-3 md:flex-row md:items-end md:justify-between">
                                <div>
                                    <p class="lyx-kicker">Step 2</p>
                                    <h4 class="mt-2 text-lg font-semibold tracking-tight text-slate-900">Choose service</h4>
                                    <p class="mt-1 text-sm text-slate-500">
                                        {{ panel.selectedPlatformLabel === 'Choose platform' ? 'Select a social media platform first.' : `Showing services for ${panel.selectedPlatformLabel}.` }}
                                    </p>
                                </div>

                                <div class="w-full md:w-64">
                                    <input
                                        v-model="panel.catalogFilters.search"
                                        type="text"
                                        placeholder="Search selected services"
                                        class="lyx-glass-input"
                                        @keyup.enter="panel.catalogFilters.page = 1; panel.loadCatalog()"
                                    >
                                </div>
                            </div>

                            <div class="mt-4 flex justify-end">
                                <span class="text-xs font-medium text-slate-400">
                                    Sorted by price: low to high
                                </span>
                            </div>

                            <div class="mt-4 max-h-[32rem] space-y-3 overflow-auto pr-1">
                                <div v-if="panel.loadingCatalog" class="rounded-2xl border border-slate-200 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                                    Loading services...
                                </div>

                                <div v-else-if="panel.catalog.length === 0" class="rounded-2xl border border-dashed border-slate-300 bg-slate-50 px-4 py-6 text-sm text-slate-500">
                                    No services found for this platform yet.
                                </div>

                                <button
                                    v-for="service in panel.catalog"
                                    :key="service.id"
                                    type="button"
                                    class="w-full rounded-2xl border px-4 py-4 text-left transition"
                                    :class="Number(panel.placeForm.catalog_service_id) === service.id
                                        ? 'border-blue-200 bg-blue-50'
                                        : 'border-slate-200 bg-white hover:border-slate-300 hover:bg-slate-50'"
                                    @click="panel.selectService(service)"
                                >
                                    <div class="flex items-start justify-between gap-4">
                                        <div>
                                            <p class="font-semibold text-slate-900">{{ service.name }}</p>
                                            <p class="mt-1 font-mono text-xs text-blue-600">#{{ service.external_service_id }}</p>
                                        </div>
                                        <p class="text-sm font-semibold text-slate-900">RM {{ Number(service.sell_rate).toFixed(6) }}</p>
                                    </div>
                                    <div class="mt-3 flex flex-wrap items-center gap-3 text-xs text-slate-500">
                                        <span class="rounded-full bg-slate-100 px-2.5 py-1">{{ service.category || '-' }}</span>
                                        <span>Min {{ service.min }}</span>
                                        <span>Max {{ service.max }}</span>
                                    </div>
                                </button>
                            </div>

                            <div class="mt-4 flex items-center justify-between">
                                <button type="button" class="lyx-secondary-button !rounded-xl !px-3 !py-2 !text-xs" :disabled="panel.catalogPagination.current_page <= 1" @click="panel.goCatalogPage(panel.catalogPagination.current_page - 1)">
                                    Previous
                                </button>
                                <p class="text-xs text-slate-500">
                                    Page {{ panel.catalogPagination.current_page }} / {{ panel.catalogPagination.last_page }}
                                </p>
                                <button type="button" class="lyx-secondary-button !rounded-xl !px-3 !py-2 !text-xs" :disabled="panel.catalogPagination.current_page >= panel.catalogPagination.last_page" @click="panel.goCatalogPage(panel.catalogPagination.current_page + 1)">
                                    Next
                                </button>
                            </div>
                        </div>
                    </div>

                    <div class="mt-6 rounded-[24px] border border-slate-200 bg-white p-6 shadow-sm">
                        <div class="flex flex-col gap-4 lg:flex-row lg:items-start lg:justify-between">
                            <div>
                                <p class="lyx-kicker">Step 3</p>
                                <h4 class="mt-2 text-xl font-semibold tracking-tight text-slate-900">Place order</h4>
                                <p class="mt-2 text-sm text-slate-500">Semak servis yang dipilih dulu, kemudian isi target link dan quantity.</p>
                            </div>
                            <div class="rounded-2xl border border-blue-100 bg-blue-50 px-4 py-3 text-left lg:text-right">
                                <p class="lyx-kicker">Estimated charge</p>
                                <p class="mt-1 text-2xl font-semibold text-slate-950">RM {{ panel.formatMoney(panel.estimatedOrderPrice) }}</p>
                            </div>
                        </div>

                        <div v-if="panel.selectedCatalog" class="mt-6 rounded-[24px] border border-blue-100 bg-slate-50/80 p-5">
                            <div class="flex flex-col gap-3 lg:flex-row lg:items-start lg:justify-between">
                                <div class="max-w-2xl">
                                    <p class="lyx-kicker">Selected service</p>
                                    <h5 class="mt-2 text-lg font-semibold text-slate-950">{{ panel.selectedCatalog.name }}</h5>
                                    <p class="mt-1 text-sm text-slate-500">Service ID #{{ panel.selectedCatalog.external_service_id }}</p>
                                    <p v-if="panel.selectedCatalog.description" class="mt-3 text-sm leading-6 text-slate-600">
                                        {{ panel.selectedCatalog.description }}
                                    </p>
                                </div>

                                <div class="grid gap-3 sm:grid-cols-3 lg:min-w-[28rem]">
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Category</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ panel.selectedCatalog.category || '-' }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Rate / 1000</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">RM {{ Number(panel.selectedCatalog.sell_rate).toFixed(6) }}</p>
                                    </div>
                                    <div class="rounded-2xl border border-slate-200 bg-white p-4">
                                        <p class="text-xs uppercase tracking-[0.16em] text-slate-500">Order range</p>
                                        <p class="mt-2 text-sm font-semibold text-slate-900">{{ panel.selectedCatalog.min }} - {{ panel.selectedCatalog.max }}</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div v-else class="mt-6 rounded-2xl border border-dashed border-slate-300 bg-slate-50/70 p-5 text-sm text-slate-500">
                            Select a service from Step 2 first.
                        </div>

                        <div class="mt-6 grid gap-4 lg:grid-cols-[1.5fr_0.9fr_1.1fr]">
                            <input v-model="panel.placeForm.link" type="text" placeholder="Target link" class="lyx-glass-input">
                            <input v-model.number="panel.placeForm.quantity" type="number" min="1" class="lyx-glass-input" placeholder="Quantity">
                            <div class="rounded-2xl border px-4 py-3 text-sm" :class="panel.quantityValid ? 'border-emerald-200 bg-emerald-50 text-emerald-700' : 'border-amber-200 bg-amber-50 text-amber-700'">
                                <p class="font-semibold">{{ panel.quantityRangeLabel }}</p>
                                <p class="mt-1 text-xs opacity-80">
                                    {{ panel.quantityValid ? 'Quantity is within service limit.' : 'Please keep quantity within the service limit.' }}
                                </p>
                            </div>
                        </div>

                        <div class="mt-5 flex justify-end">
                            <button type="button" class="lyx-glass-button min-w-52" :disabled="!panel.canSubmitOrder" @click="panel.placeResellerOrder">
                                {{ panel.placingOrder ? 'Submitting order...' : 'Place order' }}
                            </button>
                        </div>
                    </div>
                </section>
            </div>
        </div>
    </AuthenticatedLayout>
</template>
