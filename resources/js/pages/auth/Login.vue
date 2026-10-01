<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { Mail, Lock, CheckCircle2, ArrowRight } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Checkbox } from '@/components/ui/checkbox';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { register } from '@/routes';
import { store } from '@/routes/login';
import { request } from '@/routes/password';

defineOptions({
    layout: {
        title: 'Welcome Back',
        description: 'Sign in to access your WebEra Nexus dashboard',
    },
});

defineProps<{
    status?: string;
    canResetPassword: boolean;
}>();
</script>

<template>
    <Head title="Log in" />

    <div
        v-if="status"
        class="mb-6 flex items-center gap-2 rounded-xl bg-emerald-500/10 border border-emerald-500/20 p-3.5 text-xs font-medium text-emerald-600 dark:text-emerald-400"
    >
        <CheckCircle2 class="size-4 shrink-0" />
        <span>{{ status }}</span>
    </div>

    <Form
        v-bind="store.form()"
        :reset-on-success="['password']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-5">
            <div class="grid gap-2">
                <Label for="email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Email Address
                </Label>
                <div class="relative flex items-center">
                    <Mail class="absolute left-3 size-4 text-slate-400 pointer-events-none" />
                    <Input
                        id="email"
                        type="email"
                        name="email"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="email"
                        placeholder="admin@webera.com"
                        class="pl-9 h-11 rounded-xl border-slate-200 dark:border-slate-800 focus-visible:ring-purple-500 bg-slate-50/50 dark:bg-slate-900/50"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <div class="flex items-center justify-between">
                    <Label for="password" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                        Password
                    </Label>
                    <TextLink
                        v-if="canResetPassword"
                        :href="request()"
                        class="text-xs font-medium text-purple-600 dark:text-purple-400 hover:text-purple-700 dark:hover:text-purple-300 transition-colors"
                        :tabindex="5"
                    >
                        Forgot password?
                    </TextLink>
                </div>
                <div class="relative flex items-center">
                    <Lock class="absolute left-3 size-4 text-slate-400 pointer-events-none z-10" />
                    <PasswordInput
                        id="password"
                        name="password"
                        required
                        :tabindex="2"
                        autocomplete="current-password"
                        placeholder="••••••••"
                        class="pl-9 h-11 rounded-xl border-slate-200 dark:border-slate-800 focus-visible:ring-purple-500 bg-slate-50/50 dark:bg-slate-900/50 w-full"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="flex items-center justify-between py-1">
                <Label for="remember" class="flex items-center space-x-2.5 cursor-pointer text-xs text-slate-600 dark:text-slate-400">
                    <Checkbox id="remember" name="remember" :tabindex="3" class="rounded-md border-slate-300 dark:border-slate-700 data-[state=checked]:bg-purple-600" />
                    <span class="font-medium">Remember for 30 days</span>
                </Label>
            </div>

            <Button
                type="submit"
                class="mt-2 h-11 w-full rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-violet-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold shadow-lg shadow-purple-500/25 active:scale-[0.99] transition-all duration-200 text-sm flex items-center justify-center gap-2 group"
                :tabindex="4"
                :disabled="processing"
                data-test="login-button"
            >
                <Spinner v-if="processing" />
                <span v-else class="flex items-center gap-2">
                    Sign in to Portal
                    <ArrowRight class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                </span>
            </Button>
        </div>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400 mt-2">
            Don't have an account?
            <TextLink
                :href="register()"
                :tabindex="5"
                class="ms-1 font-semibold text-purple-600 dark:text-purple-400 hover:underline"
            >
                Create an account
            </TextLink>
        </div>
    </Form>
</template>
