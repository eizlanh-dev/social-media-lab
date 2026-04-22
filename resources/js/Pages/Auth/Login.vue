<script setup>
import Checkbox from '@/Components/Checkbox.vue';
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

defineProps({
    canResetPassword: {
        type: Boolean,
    },
    status: {
        type: String,
    },
});

const form = useForm({
    email: '',
    password: '',
    remember: false,
});

const submit = () => {
    form.post(route('login'), {
        onFinish: () => form.reset('password'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Log in" />

        <div class="mb-8">
            <p class="lyx-kicker">Welcome back</p>
            <h1 class="mt-3 text-3xl font-bold tracking-tight text-slate-900">Log in to manage customer orders.</h1>
            <p class="mt-3 text-sm leading-6 text-slate-600">Access your wallet, service catalog, and live order tracking from one dashboard.</p>
        </div>

        <div v-if="status" class="mb-4 rounded-2xl border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm font-medium text-emerald-700">
            {{ status }}
        </div>

        <div class="mb-5">
            <a
                :href="route('auth.google.redirect')"
                class="flex w-full items-center justify-center gap-3 rounded-2xl border border-slate-200 bg-white px-4 py-3 text-sm font-semibold text-slate-700 shadow-sm transition hover:border-slate-300 hover:bg-slate-50"
            >
                <svg viewBox="0 0 24 24" class="h-5 w-5" aria-hidden="true">
                    <path fill="#EA4335" d="M12 10.2v3.9h5.4c-.2 1.3-1.5 3.9-5.4 3.9-3.2 0-5.9-2.7-5.9-6s2.7-6 5.9-6c1.8 0 3.1.8 3.8 1.5l2.6-2.5C16.8 3.5 14.6 2.6 12 2.6A9.4 9.4 0 0 0 2.6 12 9.4 9.4 0 0 0 12 21.4c5.4 0 9-3.8 9-9 0-.6-.1-1.1-.2-1.6H12Z"/>
                    <path fill="#34A853" d="M3.7 7.5l3.2 2.4C7.8 7.8 9.7 6 12 6c1.8 0 3.1.8 3.8 1.5l2.6-2.5C16.8 3.5 14.6 2.6 12 2.6 8.4 2.6 5.2 4.7 3.7 7.5Z"/>
                    <path fill="#FBBC05" d="M12 21.4c2.5 0 4.7-.8 6.3-2.3l-2.9-2.4c-.8.6-1.9 1.1-3.4 1.1-3.8 0-5.2-2.5-5.4-3.8l-3.2 2.5c1.5 2.9 4.6 4.9 8.6 4.9Z"/>
                    <path fill="#4285F4" d="M21 12.4c0-.6-.1-1.1-.2-1.6H12v3.9h5.4c-.3 1.3-1.3 2.4-2.6 3.1l2.9 2.4c1.7-1.6 2.7-4 2.7-6.8Z"/>
                </svg>
                Continue with Google
            </a>
        </div>

        <div class="mb-5 flex items-center gap-3 text-xs uppercase tracking-[0.14em] text-slate-400">
            <span class="h-px flex-1 bg-slate-200"></span>
            <span>or continue with email</span>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <form @submit.prevent="submit" class="space-y-5">
            <div class="space-y-2">
                <InputLabel for="email" value="Email address" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1"
                    v-model="form.email"
                    required
                    autofocus
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-2">
                <div class="flex items-center justify-between gap-3">
                    <InputLabel for="password" value="Password" />
                    <Link
                        v-if="canResetPassword"
                        :href="route('password.request')"
                        class="text-sm font-medium text-blue-600 transition hover:text-blue-500 focus:outline-none"
                    >
                        Forgot password?
                    </Link>
                </div>

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1"
                    v-model="form.password"
                    required
                    autocomplete="current-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <label class="flex items-center gap-3 rounded-2xl border border-slate-200 bg-slate-50 px-4 py-3">
                <Checkbox name="remember" v-model:checked="form.remember" />
                <span class="text-sm text-slate-600">Remember me on this device</span>
            </label>

            <PrimaryButton
                class="w-full"
                :class="{ 'opacity-25': form.processing }"
                :disabled="form.processing"
            >
                {{ form.processing ? 'Signing in...' : 'Log in' }}
            </PrimaryButton>
        </form>
    </GuestLayout>
</template>
