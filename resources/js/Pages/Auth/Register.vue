<script setup>
import GuestLayout from '@/Layouts/GuestLayout.vue';
import InputError from '@/Components/InputError.vue';
import InputLabel from '@/Components/InputLabel.vue';
import PrimaryButton from '@/Components/PrimaryButton.vue';
import TextInput from '@/Components/TextInput.vue';
import { Head, Link, useForm } from '@inertiajs/vue3';

const form = useForm({
    name: '',
    email: '',
    password: '',
    password_confirmation: '',
});

const submit = () => {
    form.post(route('register'), {
        onFinish: () => form.reset('password', 'password_confirmation'),
    });
};
</script>

<template>
    <GuestLayout>
        <Head title="Register" />

        <div class="mb-6">
            <p class="text-xs font-semibold uppercase tracking-[0.14em] text-sky-600">Create Account</p>
            <h1 class="mt-2 text-2xl font-bold text-slate-900">Bina panel jualan anda sekarang</h1>
            <p class="mt-2 text-sm text-slate-600">Sign up untuk aktifkan order automation, wallet, dan report profit.</p>
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
            <span>or create account with email</span>
            <span class="h-px flex-1 bg-slate-200"></span>
        </div>

        <form @submit.prevent="submit" class="space-y-4">
            <div class="space-y-1">
                <InputLabel for="name" value="Name" />

                <TextInput
                    id="name"
                    type="text"
                    class="mt-1"
                    v-model="form.name"
                    required
                    autofocus
                    autocomplete="name"
                />

                <InputError class="mt-2" :message="form.errors.name" />
            </div>

            <div class="space-y-1">
                <InputLabel for="email" value="Email" />

                <TextInput
                    id="email"
                    type="email"
                    class="mt-1"
                    v-model="form.email"
                    required
                    autocomplete="username"
                />

                <InputError class="mt-2" :message="form.errors.email" />
            </div>

            <div class="space-y-1">
                <InputLabel for="password" value="Password" />

                <TextInput
                    id="password"
                    type="password"
                    class="mt-1"
                    v-model="form.password"
                    required
                    autocomplete="new-password"
                />

                <InputError class="mt-2" :message="form.errors.password" />
            </div>

            <div class="space-y-1">
                <InputLabel
                    for="password_confirmation"
                    value="Confirm Password"
                />

                <TextInput
                    id="password_confirmation"
                    type="password"
                    class="mt-1"
                    v-model="form.password_confirmation"
                    required
                    autocomplete="new-password"
                />

                <InputError
                    class="mt-2"
                    :message="form.errors.password_confirmation"
                />
            </div>

            <div class="flex items-center justify-between gap-3 pt-2">
                <Link
                    :href="route('login')"
                    class="rounded-md text-sm font-medium text-slate-600 underline decoration-slate-300 underline-offset-4 transition hover:text-slate-900 focus:outline-none"
                >
                    Already registered?
                </Link>

                <PrimaryButton
                    :class="{ 'opacity-25': form.processing }"
                    :disabled="form.processing"
                >
                    Register
                </PrimaryButton>
            </div>
        </form>
    </GuestLayout>
</template>
