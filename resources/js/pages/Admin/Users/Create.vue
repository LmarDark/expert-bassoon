<script setup lang="ts">
import { Head, useForm, router } from '@inertiajs/vue3';
import { ref } from 'vue';

const showPassword = ref(false);
const showPasswordConfirmation = ref(false);

const form = useForm({
    username: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.post('/admin/users');
}

function logout() {
    router.post('/logout');
}

defineProps<{
    auth: {
        user: {
            username: string;
        };
    };
}>();
</script>

<template>
    <Head title="Criar Usuário" />

    <div class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <!-- Header -->
        <header
            class="flex items-center justify-between border-b border-[#e3e3e0] bg-white px-6 py-4 shadow-sm dark:border-[#3E3E3A] dark:bg-[#161615]"
        >
            <div class="flex items-center gap-3">
                <svg
                    class="h-6 text-[#F53003] dark:text-[#FF4433]"
                    viewBox="0 0 53 54"
                    fill="none"
                    xmlns="http://www.w3.org/2000/svg"
                >
                    <path d="M23.5 2.5C23.5 1.4 24.4 0.5 25.5 0.5H35.5C40.5 0.5 44.5 4.5 44.5 9.5V19.5C44.5 20.6 43.6 21.5 42.5 21.5H32.5C27.5 21.5 23.5 17.5 23.5 12.5V2.5Z" fill="currentColor" />
                    <path d="M0.5 29.5C0.5 28.4 1.4 27.5 2.5 27.5H12.5C17.5 27.5 21.5 31.5 21.5 36.5V46.5C21.5 47.6 20.6 48.5 19.5 48.5H9.5C4.5 48.5 0.5 44.5 0.5 39.5V29.5Z" fill="currentColor" />
                    <path d="M0.5 2.5C0.5 1.4 1.4 0.5 2.5 0.5H12.5C13.6 0.5 14.5 1.4 14.5 2.5V21.5H2.5C1.4 21.5 0.5 20.6 0.5 19.5V2.5Z" fill="currentColor" />
                    <path d="M23.5 32.5C23.5 31.4 24.4 30.5 25.5 30.5H44.5C45.6 30.5 46.5 31.4 46.5 32.5V46.5C46.5 47.6 45.6 48.5 44.5 48.5H25.5C24.4 48.5 23.5 47.6 23.5 46.5V32.5Z" fill="currentColor" />
                </svg>
                <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Sistema de Autenticação
                </span>
            </div>

            <div class="flex items-center gap-4">
                <nav class="flex items-center gap-1 text-sm">
                    <a
                        href="/dashboard"
                        class="rounded-sm px-3 py-1.5 text-[#706f6c] transition hover:bg-[#f5f5f3] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:bg-[#1e1e1c] dark:hover:text-[#EDEDEC]"
                    >
                        Dashboard
                    </a>
                    <a
                        href="/admin/users"
                        class="rounded-sm px-3 py-1.5 font-medium text-[#706f6c] transition hover:bg-[#f5f5f3] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:bg-[#1e1e1c] dark:hover:text-[#EDEDEC]"
                    >
                        Usuários
                    </a>
                </nav>

                <div class="h-4 w-px bg-[#e3e3e0] dark:bg-[#3E3E3A]"></div>

                <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    {{ auth.user.username }}
                </span>
                <button
                    type="button"
                    class="rounded-sm border border-[#e3e3e0] bg-white px-4 py-1.5 text-sm font-medium text-[#1b1b18] transition hover:bg-[#f5f5f3] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1e1e1c]"
                    @click="logout"
                >
                    Sair
                </button>
            </div>
        </header>

        <!-- Content -->
        <main class="flex flex-1 justify-center p-6">
            <div class="w-full max-w-2xl">
                <!-- Page title -->
                <div class="mb-6">
                    <div class="mb-1 flex items-center gap-2">
                        <a
                            href="/admin/users"
                            class="flex items-center gap-1.5 text-sm text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Voltar
                        </a>
                    </div>
                    <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Criar Usuário
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Preencha os dados abaixo para criar um novo usuário no sistema.
                    </p>
                </div>

                <!-- Form card -->
                <div
                    class="rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <form @submit.prevent="submit" class="flex flex-col divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        <!-- Dados -->
                        <div class="px-8 py-6">
                            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                                Dados do usuário
                            </h2>
                            <div class="flex flex-col gap-5">
                                <!-- Username -->
                                <div class="flex flex-col gap-1.5">
                                    <label for="username" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Usuário
                                        <span class="ml-0.5 text-red-500">*</span>
                                    </label>
                                    <input
                                        id="username"
                                        v-model="form.username"
                                        type="text"
                                        required
                                        placeholder="Ex: joaosilva"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.username }"
                                    />
                                    <p v-if="form.errors.username" class="text-xs text-red-500">
                                        {{ form.errors.username }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Segurança -->
                        <div class="px-8 py-6">
                            <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                                Segurança
                            </h2>
                            <div class="flex flex-col gap-5">
                                <!-- Senha -->
                                <div class="flex flex-col gap-1.5">
                                    <label for="password" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Senha
                                        <span class="ml-0.5 text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="password"
                                            v-model="form.password"
                                            :type="showPassword ? 'text' : 'password'"
                                            required
                                            placeholder="••••••••"
                                            class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 pr-10 text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.password }"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                                            @click="showPassword = !showPassword"
                                        >
                                            <svg v-if="showPassword" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password" class="text-xs text-red-500">
                                        {{ form.errors.password }}
                                    </p>
                                </div>

                                <!-- Confirmar senha -->
                                <div class="flex flex-col gap-1.5">
                                    <label for="password_confirmation" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Confirmar senha
                                        <span class="ml-0.5 text-red-500">*</span>
                                    </label>
                                    <div class="relative">
                                        <input
                                            id="password_confirmation"
                                            v-model="form.password_confirmation"
                                            :type="showPasswordConfirmation ? 'text' : 'password'"
                                            required
                                            placeholder="••••••••"
                                            class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 pr-10 text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.password_confirmation }"
                                        />
                                        <button
                                            type="button"
                                            class="absolute right-3 top-1/2 -translate-y-1/2 text-[#706f6c] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                                            @click="showPasswordConfirmation = !showPasswordConfirmation"
                                        >
                                            <svg v-if="showPasswordConfirmation" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M13.875 18.825A10.05 10.05 0 0112 19c-4.478 0-8.268-2.943-9.543-7a9.97 9.97 0 011.563-3.029m5.858.908a3 3 0 114.243 4.243M9.878 9.878l4.242 4.242M9.88 9.88l-3.29-3.29m7.532 7.532l3.29 3.29M3 3l3.59 3.59m0 0A9.953 9.953 0 0112 5c4.478 0 8.268 2.943 9.543 7a10.025 10.025 0 01-4.132 5.411m0 0L21 21" />
                                            </svg>
                                            <svg v-else xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                                                <path stroke-linecap="round" stroke-linejoin="round" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z" />
                                            </svg>
                                        </button>
                                    </div>
                                    <p v-if="form.errors.password_confirmation" class="text-xs text-red-500">
                                        {{ form.errors.password_confirmation }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-end gap-3 px-8 py-5">
                            <a
                                href="/admin/users"
                                class="rounded-sm border border-[#e3e3e0] bg-white px-5 py-2 text-sm font-medium text-[#1b1b18] transition hover:bg-[#f5f5f3] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1e1e1c]"
                            >
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm font-medium leading-normal text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                            >
                                <svg v-if="!form.processing" xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M12 4v16m8-8H4" />
                                </svg>
                                <span v-if="form.processing">Criando...</span>
                                <span v-else>Criar Usuário</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>
