<script setup lang="ts">
import { Form, Head } from '@inertiajs/vue3';
import { User, Mail, Lock, UserPlus, ArrowRight } from '@lucide/vue';
import InputError from '@/components/InputError.vue';
import PasswordInput from '@/components/PasswordInput.vue';
import TextLink from '@/components/TextLink.vue';
import { Button } from '@/components/ui/button';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Spinner } from '@/components/ui/spinner';
import { login } from '@/routes';
import { store } from '@/routes/register';

defineProps<{
    passwordRules: string;
}>();

defineOptions({
    layout: {
        title: 'Create an Account',
        description: 'Enter your details below to join WebEra Nexus',
    },
});
</script>

<template>
    <Head title="Register" />

    <Form
        v-bind="store.form()"
        :reset-on-success="['password', 'password_confirmation']"
        v-slot="{ errors, processing }"
        class="flex flex-col gap-5"
    >
        <div class="grid gap-4">
            <div class="grid gap-2">
                <Label for="name" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Full Name
                </Label>
                <div class="relative flex items-center">
                    <User class="absolute left-3 size-4 text-slate-400 pointer-events-none" />
                    <Input
                        id="name"
                        type="text"
                        required
                        autofocus
                        :tabindex="1"
                        autocomplete="name"
                        name="name"
                        placeholder="John Doe"
                        class="pl-9 h-11 rounded-xl border-slate-200 dark:border-slate-800 focus-visible:ring-purple-500 bg-slate-50/50 dark:bg-slate-900/50"
                    />
                </div>
                <InputError :message="errors.name" />
            </div>

            <div class="grid gap-2">
                <Label for="email" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Email Address
                </Label>
                <div class="relative flex items-center">
                    <Mail class="absolute left-3 size-4 text-slate-400 pointer-events-none" />
                    <Input
                        id="email"
                        type="email"
                        required
                        :tabindex="2"
                        autocomplete="email"
                        name="email"
                        placeholder="email@example.com"
                        class="pl-9 h-11 rounded-xl border-slate-200 dark:border-slate-800 focus-visible:ring-purple-500 bg-slate-50/50 dark:bg-slate-900/50"
                    />
                </div>
                <InputError :message="errors.email" />
            </div>

            <div class="grid gap-2">
                <Label for="password" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Password
                </Label>
                <div class="relative flex items-center">
                    <Lock class="absolute left-3 size-4 text-slate-400 pointer-events-none z-10" />
                    <PasswordInput
                        id="password"
                        required
                        :tabindex="3"
                        autocomplete="new-password"
                        name="password"
                        placeholder="••••••••"
                        :passwordrules="passwordRules"
                        class="pl-9 h-11 rounded-xl border-slate-200 dark:border-slate-800 focus-visible:ring-purple-500 bg-slate-50/50 dark:bg-slate-900/50 w-full"
                    />
                </div>
                <InputError :message="errors.password" />
            </div>

            <div class="grid gap-2">
                <Label for="password_confirmation" class="text-xs font-semibold text-slate-700 dark:text-slate-300">
                    Confirm Password
                </Label>
                <div class="relative flex items-center">
                    <Lock class="absolute left-3 size-4 text-slate-400 pointer-events-none z-10" />
                    <PasswordInput
                        id="password_confirmation"
                        required
                        :tabindex="4"
                        autocomplete="new-password"
                        name="password_confirmation"
                        placeholder="••••••••"
                        :passwordrules="passwordRules"
                        class="pl-9 h-11 rounded-xl border-slate-200 dark:border-slate-800 focus-visible:ring-purple-500 bg-slate-50/50 dark:bg-slate-900/50 w-full"
                    />
                </div>
                <InputError :message="errors.password_confirmation" />
            </div>

            <Button
                type="submit"
                class="mt-3 h-11 w-full rounded-xl bg-gradient-to-r from-purple-600 via-indigo-600 to-violet-600 hover:from-purple-700 hover:to-indigo-700 text-white font-semibold shadow-lg shadow-purple-500/25 active:scale-[0.99] transition-all duration-200 text-sm flex items-center justify-center gap-2 group"
                tabindex="5"
                :disabled="processing"
                data-test="register-user-button"
            >
                <Spinner v-if="processing" />
                <span v-else class="flex items-center gap-2">
                    Create Account
                    <ArrowRight class="size-4 transition-transform duration-200 group-hover:translate-x-1" />
                </span>
            </Button>
        </div>

        <div class="text-center text-xs text-slate-500 dark:text-slate-400 mt-2">
            Already have an account?
            <TextLink
                :href="login()"
                class="ms-1 font-semibold text-purple-600 dark:text-purple-400 hover:underline"
                :tabindex="6"
            >
                Log in
            </TextLink>
        </div>
    </Form>
</template>
