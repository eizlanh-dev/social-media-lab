<script setup>
import { ref } from 'vue';
import Dropdown from '@/Components/Dropdown.vue';
import DropdownLink from '@/Components/DropdownLink.vue';
import NavLink from '@/Components/NavLink.vue';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink.vue';
import { Link } from '@inertiajs/vue3';

const showingNavigationDropdown = ref(false);
</script>

<template>
    <div class="relative min-h-screen overflow-hidden bg-transparent">
        <div class="pointer-events-none absolute inset-x-0 top-0 h-[30rem] bg-[radial-gradient(circle_at_top,rgba(191,219,254,0.65),rgba(255,255,255,0))]"></div>
        <div class="pointer-events-none absolute inset-x-0 top-0 h-full opacity-40 lyx-soft-grid"></div>

        <div class="relative min-h-screen bg-transparent">
            <nav class="sticky top-4 z-30 px-4 sm:px-6 lg:px-8">
                <div class="mx-auto max-w-7xl rounded-[22px] border border-slate-200 bg-white shadow-[0_16px_36px_-28px_rgba(15,23,42,0.25)]">
                    <div class="flex h-[3.25rem] justify-between px-2.5 sm:px-4">
                        <div class="flex">
                            <div class="flex shrink-0 items-center">
                                <Link :href="route('dashboard')" class="inline-flex items-center gap-2.5 rounded-lg border border-slate-200 bg-white px-2.5 py-1.5 text-sm font-semibold text-slate-900 shadow-sm">
                                    <span class="flex h-[1.625rem] w-[1.625rem] items-center justify-center rounded-lg bg-blue-600 text-[10px] font-bold text-white">SM</span>
                                    <span class="tracking-[0.14em] text-[10px] uppercase text-slate-600">Social Media Lab</span>
                                </Link>
                            </div>

                            <div
                                class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex"
                            >
                                <NavLink
                                    :href="route('dashboard')"
                                    :active="route().current('dashboard')"
                                >
                                    Overview
                                </NavLink>
                                <NavLink
                                    :href="route('new-order')"
                                    :active="route().current('new-order')"
                                >
                                    New Order
                                </NavLink>
                                <NavLink
                                    :href="route('topup-balance')"
                                    :active="route().current('topup-balance')"
                                >
                                    Topup Balance
                                </NavLink>
                            </div>
                        </div>

                        <div class="hidden items-center gap-3 sm:ms-6 sm:flex">
                            <div class="rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-[10px] font-semibold uppercase tracking-[0.14em] text-blue-700">
                                Social Media Lab
                            </div>
                            <div class="relative ms-3">
                                <Dropdown align="right" width="48">
                                    <template #trigger>
                                        <span class="inline-flex rounded-md">
                                            <button
                                                type="button"
                                                class="inline-flex items-center rounded-lg border border-slate-200 bg-white px-3 py-1.5 text-sm font-semibold leading-4 text-slate-700 shadow-sm transition hover:bg-slate-50 hover:text-slate-900 focus:outline-none"
                                            >
                                                {{ $page.props.auth.user.name }}

                                                <svg
                                                    class="-me-0.5 ms-2 h-4 w-4 text-slate-500"
                                                    xmlns="http://www.w3.org/2000/svg"
                                                    viewBox="0 0 20 20"
                                                    fill="currentColor"
                                                >
                                                    <path
                                                        fill-rule="evenodd"
                                                        d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                                        clip-rule="evenodd"
                                                    />
                                                </svg>
                                            </button>
                                        </span>
                                    </template>

                                    <template #content>
                                        <DropdownLink
                                            :href="route('profile.edit')"
                                        >
                                            Profile
                                        </DropdownLink>
                                        <DropdownLink
                                            :href="route('logout')"
                                            method="post"
                                            as="button"
                                        >
                                            Log Out
                                        </DropdownLink>
                                    </template>
                                </Dropdown>
                            </div>
                        </div>

                        <div class="-me-2 flex items-center sm:hidden">
                            <button
                                @click="
                                    showingNavigationDropdown =
                                        !showingNavigationDropdown
                                "
                                class="inline-flex items-center justify-center rounded-2xl border border-blue-100 bg-white p-2 text-slate-500 transition hover:bg-slate-50 hover:text-slate-700 focus:bg-slate-50 focus:text-slate-700 focus:outline-none"
                            >
                                <svg
                                    class="h-6 w-6"
                                    stroke="currentColor"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                >
                                    <path
                                        :class="{
                                            hidden: showingNavigationDropdown,
                                            'inline-flex':
                                                !showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M4 6h16M4 12h16M4 18h16"
                                    />
                                    <path
                                        :class="{
                                            hidden: !showingNavigationDropdown,
                                            'inline-flex':
                                                showingNavigationDropdown,
                                        }"
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        stroke-width="2"
                                        d="M6 18L18 6M6 6l12 12"
                                    />
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>

                <div
                    :class="{
                        block: showingNavigationDropdown,
                        hidden: !showingNavigationDropdown,
                    }"
                    class="sm:hidden"
                    >
                    <div class="space-y-1 pb-3 pt-2">
                        <ResponsiveNavLink
                            :href="route('dashboard')"
                            :active="route().current('dashboard')"
                        >
                            Overview
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('new-order')"
                            :active="route().current('new-order')"
                        >
                            New Order
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            :href="route('topup-balance')"
                            :active="route().current('topup-balance')"
                        >
                            Topup Balance
                        </ResponsiveNavLink>
                    </div>

                    <div
                        class="border-t border-gray-200 pb-1 pt-4"
                    >
                        <div class="px-4">
                            <div
                                class="text-base font-medium text-gray-800"
                            >
                                {{ $page.props.auth.user.name }}
                            </div>
                            <div class="text-sm font-medium text-gray-500">
                                {{ $page.props.auth.user.email }}
                            </div>
                        </div>

                        <div class="mt-3 space-y-1">
                            <ResponsiveNavLink :href="route('profile.edit')">
                                Profile
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                :href="route('logout')"
                                method="post"
                                as="button"
                            >
                                Log Out
                            </ResponsiveNavLink>
                        </div>
                    </div>
                </div>
            </nav>

            <header
                class="mx-auto mt-8 max-w-7xl px-4 sm:px-6 lg:px-8"
                v-if="$slots.header"
            >
                <div class="lyx-card px-5 py-4 sm:px-6 sm:py-5">
                    <slot name="header" />
                </div>
            </header>

            <main class="relative pb-10">
                <slot />
            </main>
        </div>
    </div>
</template>
