<script setup lang="ts">
import { Head } from '@inertiajs/vue3';
import ActionCard from '@/components/ActionCard.vue';
import AppHeader from '@/components/AppHeader.vue';

defineProps<{
    auth: {
        user: {
            username: string;
            nickname?: string;
            is_admin: boolean;
        };
    };
}>();
</script>

<template>
    <Head title="Página Inícial" />

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <AppHeader :user="auth.user" />

        <!-- Content -->
        <main class="flex-1 p-6">
            <div class="mx-auto max-w-5xl">
                <div class="mb-8">
                    <h1
                        class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Bem-vindo,
                        {{
                            auth.user.nickname
                                ? `${auth.user.nickname} (${auth.user.username})`
                                : auth.user.username
                        }}!
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Você está autenticado no sistema.
                    </p>
                </div>

                <div class="grid grid-cols-1 gap-10 sm:grid-cols-1">
                    <div
                        class="rounded-lg bg-white p-6 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                    >
                        <div class="mb-4 flex items-center gap-3">
                            <div
                                class="flex h-9 w-9 items-center justify-center rounded-full bg-green-100 dark:bg-green-950"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-green-600 dark:text-green-400"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"
                                    />
                                </svg>
                            </div>
                            <div>
                                <p
                                    class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                >
                                    Sessão ativa
                                </p>
                                <p
                                    class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    Autenticado com sucesso
                                </p>
                            </div>
                            <div class="ml-auto flex gap-3">
                                <span
                                    class="inline-flex items-center gap-1.5 rounded-full bg-green-100 px-2.5 py-0.5 text-xs font-medium text-green-700 dark:bg-green-900 dark:text-green-400"
                                >
                                    <span
                                        class="h-1.5 w-1.5 animate-ping rounded-full bg-green-500"
                                    ></span>
                                    <span
                                        class="absolute h-1.5 w-1.5 rounded-full bg-green-500"
                                    ></span>
                                    Online
                                </span>
                            </div>
                        </div>

                        <div
                            class="rounded-sm border border-[#e3e3e0] bg-[#f9f9f8] px-4 py-3 dark:border-[#3E3E3A] dark:bg-[#1a1a18]"
                        >
                            <div class="flex items-center gap-3">
                                <div
                                    class="flex h-8 w-8 items-center justify-center rounded-full bg-[#1b1b18] text-xs font-semibold text-white uppercase dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                >
                                    {{
                                        auth.user.nickname?.charAt(0) ??
                                        auth.user.username.charAt(0)
                                    }}
                                </div>
                                <div>
                                    <p
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        {{
                                            auth.user.nickname
                                                ? `${auth.user.nickname} (${auth.user.username})`
                                                : auth.user.username
                                        }}
                                    </p>
                                    <p
                                        class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        Usuário autenticado
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div>
                    <div class="my-8">
                        <h1
                            class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Ações do
                            <span v-if="auth.user.is_admin">Administrador</span>
                            <span v-else>Usuário</span>
                        </h1>
                        <p
                            v-if="auth.user.is_admin"
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Configure, audite e personalize o sistema por
                            completo.
                        </p>
                        <p
                            v-else
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            Configure e personalize o que você tiver acesso.
                        </p>
                    </div>

                    <div class="grid grid-cols-1 gap-10 sm:grid-cols-2">
                        <ActionCard
                            v-if="auth.user.is_admin"
                            href="/admin/audit"
                            title="Auditoria"
                            description="Monitoramento geral da aplicação."
                        >
                            <template #icon>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-[#706f6c] dark:text-[#A1A09A]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-3 7h3m-3 4h3m-6-4h.01M9 16h.01"
                                    />
                                </svg>
                            </template>
                        </ActionCard>

                        <ActionCard
                            v-if="auth.user.is_admin"
                            href="/admin/users"
                            title="Gerenciar usuários"
                            description="Criar, editar e remover usuários do sistema."
                        >
                            <template #icon>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-[#706f6c] dark:text-[#A1A09A]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                    />
                                </svg>
                            </template>
                        </ActionCard>

                        <ActionCard
                            v-if="auth.user.is_admin"
                            href="/admin/apps"
                            title="Gerenciar aplicações"
                            description="Registrar e configurar apps integrados ao SSO."
                        >
                            <template #icon>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-[#706f6c] dark:text-[#A1A09A]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"
                                    />
                                </svg>
                            </template>
                        </ActionCard>

                        <ActionCard
                            v-if="auth.user.is_admin"
                            href="/admin/settings"
                            title="Personalizar login"
                            description="Alterar aparência da página de login do SSO."
                        >
                            <template #icon>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-[#706f6c] dark:text-[#A1A09A]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"
                                    />
                                </svg>
                            </template>
                        </ActionCard>

                        <ActionCard
                            href="/profile"
                            title="Configurar perfil"
                            description="Configure e personalize o seu perfil."
                        >
                            <template #icon>
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4 text-[#706f6c] dark:text-[#A1A09A]"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"
                                    />
                                </svg>
                            </template>
                        </ActionCard>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
