<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';

defineProps<{
    auth: {
        user: {
            username: string;
        };
    };
}>();

function logout() {
    router.post('/logout');
}
</script>

<template>
    <Head title="Dashboard" />

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
                    <path
                        d="M23.5 2.5C23.5 1.4 24.4 0.5 25.5 0.5H35.5C40.5 0.5 44.5 4.5 44.5 9.5V19.5C44.5 20.6 43.6 21.5 42.5 21.5H32.5C27.5 21.5 23.5 17.5 23.5 12.5V2.5Z"
                        fill="currentColor"
                    />
                    <path
                        d="M0.5 29.5C0.5 28.4 1.4 27.5 2.5 27.5H12.5C17.5 27.5 21.5 31.5 21.5 36.5V46.5C21.5 47.6 20.6 48.5 19.5 48.5H9.5C4.5 48.5 0.5 44.5 0.5 39.5V29.5Z"
                        fill="currentColor"
                    />
                    <path
                        d="M0.5 2.5C0.5 1.4 1.4 0.5 2.5 0.5H12.5C13.6 0.5 14.5 1.4 14.5 2.5V21.5H2.5C1.4 21.5 0.5 20.6 0.5 19.5V2.5Z"
                        fill="currentColor"
                    />
                    <path
                        d="M23.5 32.5C23.5 31.4 24.4 30.5 25.5 30.5H44.5C45.6 30.5 46.5 31.4 46.5 32.5V46.5C46.5 47.6 45.6 48.5 44.5 48.5H25.5C24.4 48.5 23.5 47.6 23.5 46.5V32.5Z"
                        fill="currentColor"
                    />
                </svg>
                <span class="text-sm font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    Sistema de Autenticação
                </span>
            </div>

            <div class="flex items-center gap-4">
                <nav class="flex items-center gap-1 text-sm">
                    <a
                        href="/dashboard"
                        class="rounded-sm bg-[#f5f5f3] px-3 py-1.5 font-medium text-[#1b1b18] dark:bg-[#1e1e1c] dark:text-[#EDEDEC]"
                    >
                        Dashboard
                    </a>
                    <a
                        href="/admin/users"
                        class="rounded-sm px-3 py-1.5 text-[#706f6c] transition hover:bg-[#f5f5f3] hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:bg-[#1e1e1c] dark:hover:text-[#EDEDEC]"
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
        <main class="flex-1 p-6">
            <div class="mx-auto max-w-5xl">
                <!-- Welcome section -->
                <div class="mb-8">
                    <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                        Bem-vindo, {{ auth.user.username }}!
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Você está autenticado no sistema.
                    </p>
                </div>

                <!-- Cards de ação -->
                <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                    <!-- Card: Status da sessão -->
                    <div class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                        <div class="mb-4 flex items-center gap-3">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-green-100 dark:bg-green-950">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                </svg>
                            </div>
                            <div>
                                <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Sessão ativa</p>
                                <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">Autenticado com sucesso</p>
                            </div>
                            <div class="ml-auto">
                                <span class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-400">
                                    <span class="h-1.5 w-1.5 rounded-full bg-green-500"></span>
                                    Online
                                </span>
                            </div>
                        </div>

                        <div class="rounded-sm border border-[#e3e3e0] bg-[#f9f9f8] px-4 py-3 dark:border-[#3E3E3A] dark:bg-[#1a1a18]">
                            <div class="flex items-center gap-3">
                                <div class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1b1b18] text-xs font-semibold uppercase text-white dark:bg-[#EDEDEC] dark:text-[#1b1b18]">
                                    {{ auth.user.username.charAt(0) }}
                                </div>
                                <div>
                                    <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ auth.user.username }}
                                    </p>
                                    <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                        Usuário autenticado
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Card: Gerenciar usuários -->
                    <a
                        href="/admin/users"
                        class="group rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] transition hover:bg-[#f9f9f8] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d] dark:hover:bg-[#1a1a18]"
                    >
                        <div class="mb-4 flex items-center justify-between">
                            <div class="flex h-9 w-9 items-center justify-center rounded-full bg-[#f5f5f3] dark:bg-[#1e1e1c]">
                                <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 text-[#706f6c] dark:text-[#A1A09A]" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                    <path stroke-linecap="round" stroke-linejoin="round" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                            </div>
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-4 w-4 text-[#b5b3ad] transition group-hover:translate-x-0.5 dark:text-[#55544f]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="2"
                            >
                                <path stroke-linecap="round" stroke-linejoin="round" d="M9 5l7 7-7 7" />
                            </svg>
                        </div>
                        <p class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                            Gerenciar usuários
                        </p>
                        <p class="mt-0.5 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            Criar, editar e remover usuários do sistema.
                        </p>
                    </a>
                </div>
            </div>
        </main>
    </div>
</template>
