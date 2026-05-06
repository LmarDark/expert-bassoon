<script setup lang="ts">
import { Head, router, usePage } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppHeader from '@/components/AppHeader.vue';

const props = defineProps<{
    auth: {
        user: {
            username: string;
            nickname?: string;
        };
    };
    apps: Array<{
        id: number;
        name: string;
        api_key: string;
        allowed_domains: string[];
        callback_url: string | null;
        active: boolean;
        created_at: string;
    }>;
}>();

const page = usePage<{ flash: { success?: string } }>();
const successMessage = computed(() => page.props.flash?.success);

const search = ref('');
const confirmDeleteId = ref<number | null>(null);
const copiedKey = ref<number | null>(null);

const filteredApps = computed(() =>
    props.apps.filter((a) =>
        a.name.toLowerCase().includes(search.value.toLowerCase()),
    ),
);

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
    });
}

function confirmDelete(id: number) {
    confirmDeleteId.value = id;
}

function cancelDelete() {
    confirmDeleteId.value = null;
}

function deleteApp() {
    if (confirmDeleteId.value === null) {
        return;
    }

    router.delete(`/admin/apps/${confirmDeleteId.value}`, {
        onFinish: () => (confirmDeleteId.value = null),
    });
}

async function copyKey(id: number, key: string) {
    await navigator.clipboard.writeText(key);
    copiedKey.value = id;
    setTimeout(() => (copiedKey.value = null), 2000);
}
</script>

<template>
    <Head title="Gerenciar Aplicações" />

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <AppHeader :user="auth.user" />

        <main class="flex-1 p-6">
            <div class="mx-auto max-w-5xl">
                <!-- Success banner -->
                <div
                    v-if="successMessage"
                    class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-800 dark:bg-green-950"
                >
                    <svg
                        xmlns="http://www.w3.org/2000/svg"
                        class="h-4 w-4 flex-shrink-0 text-green-600 dark:text-green-400"
                        fill="none"
                        viewBox="0 0 24 24"
                        stroke="currentColor"
                        stroke-width="2"
                    >
                        <path
                            stroke-linecap="round"
                            stroke-linejoin="round"
                            d="M5 13l4 4L19 7"
                        />
                    </svg>
                    <p
                        class="text-sm font-medium text-green-800 dark:text-green-300"
                    >
                        {{ successMessage }}
                    </p>
                </div>

                <!-- Page header -->
                <div class="mb-6 flex items-center justify-between">
                    <div>
                        <div class="mb-1 flex items-center gap-2">
                            <a
                                href="/home"
                                class="flex items-center gap-1.5 text-sm text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                            >
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    class="h-4 w-4"
                                    fill="none"
                                    viewBox="0 0 24 24"
                                    stroke="currentColor"
                                    stroke-width="2"
                                >
                                    <path
                                        stroke-linecap="round"
                                        stroke-linejoin="round"
                                        d="M15 19l-7-7 7-7"
                                    />
                                </svg>
                                Voltar
                            </a>
                        </div>
                        <h1
                            class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            Aplicações
                        </h1>
                        <p
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ apps.length }}
                            {{
                                apps.length === 1
                                    ? 'aplicação cadastrada'
                                    : 'aplicações cadastradas'
                            }}
                        </p>
                    </div>
                    <a
                        href="/admin/apps/create"
                        class="inline-flex items-center gap-2 rounded-sm border border-black bg-[#1b1b18] px-4 py-2 text-sm font-medium text-white transition hover:bg-black dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-4 w-4"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 4v16m8-8H4"
                            />
                        </svg>
                        Nova aplicação
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4">
                    <div class="relative">
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="pointer-events-none absolute top-1/2 left-3 h-4 w-4 -translate-y-1/2 text-[#b5b3ad] dark:text-[#55544f]"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"
                            />
                        </svg>
                        <input
                            v-model="search"
                            type="text"
                            placeholder="Buscar por nome..."
                            class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] py-2 pr-3 pl-9 text-sm text-[#1b1b18] transition outline-none placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                        />
                    </div>
                </div>

                <!-- Table card -->
                <div
                    class="overflow-hidden rounded-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <!-- Empty state -->
                    <div
                        v-if="filteredApps.length === 0"
                        class="flex flex-col items-center justify-center bg-white py-16 dark:bg-[#161615]"
                    >
                        <div
                            class="mb-4 flex h-12 w-12 items-center justify-center rounded-full bg-[#f5f5f3] dark:bg-[#1e1e1c]"
                        >
                            <svg
                                xmlns="http://www.w3.org/2000/svg"
                                class="h-6 w-6 text-[#706f6c] dark:text-[#A1A09A]"
                                fill="none"
                                viewBox="0 0 24 24"
                                stroke="currentColor"
                                stroke-width="1.5"
                            >
                                <path
                                    stroke-linecap="round"
                                    stroke-linejoin="round"
                                    d="M9 3H5a2 2 0 00-2 2v4m6-6h10a2 2 0 012 2v4M9 3v18m0 0h10a2 2 0 002-2V9M9 21H5a2 2 0 01-2-2V9m0 0h18"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            {{
                                search
                                    ? 'Nenhuma aplicação encontrada'
                                    : 'Nenhuma aplicação cadastrada'
                            }}
                        </p>
                        <p
                            class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{
                                search
                                    ? 'Tente outro termo de busca.'
                                    : 'Clique em "Nova aplicação" para começar.'
                            }}
                        </p>
                    </div>

                    <!-- Table -->
                    <table
                        v-else
                        class="w-full bg-white text-sm dark:bg-[#161615]"
                    >
                        <thead>
                            <tr
                                class="border-b border-[#e3e3e0] dark:border-[#3E3E3A]"
                            >
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    Nome
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    Domínios
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    API Key
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    Status
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    Criado em
                                </th>
                                <th
                                    class="px-6 py-3 text-right text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    Ações
                                </th>
                            </tr>
                        </thead>
                        <tbody
                            class="divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]"
                        >
                            <tr
                                v-for="app in filteredApps"
                                :key="app.id"
                                class="transition hover:bg-[#f9f9f8] dark:hover:bg-[#1a1a18]"
                            >
                                <td
                                    class="px-6 py-4 font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                >
                                    {{ app.name }}
                                </td>
                                <td
                                    class="px-6 py-4 text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    <div class="flex flex-wrap gap-1">
                                        <span
                                            v-for="domain in app.allowed_domains"
                                            :key="domain"
                                            class="rounded-full bg-[#f5f5f3] px-2 py-0.5 text-xs dark:bg-[#1e1e1c]"
                                        >
                                            {{ domain }}
                                        </span>
                                        <span
                                            v-if="
                                                app.allowed_domains.length === 0
                                            "
                                            class="text-xs italic"
                                        >
                                            Nenhum
                                        </span>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-2">
                                        <code
                                            class="max-w-[140px] truncate rounded bg-[#f5f5f3] px-2 py-0.5 font-mono text-xs text-[#1b1b18] dark:bg-[#1e1e1c] dark:text-[#EDEDEC]"
                                        >
                                            {{ app.api_key.substring(0, 16) }}…
                                        </code>
                                        <button
                                            type="button"
                                            class="text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                                            :title="
                                                copiedKey === app.id
                                                    ? 'Copiado!'
                                                    : 'Copiar API Key'
                                            "
                                            @click="
                                                copyKey(app.id, app.api_key)
                                            "
                                        >
                                            <svg
                                                v-if="copiedKey !== app.id"
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"
                                                />
                                            </svg>
                                            <svg
                                                v-else
                                                xmlns="http://www.w3.org/2000/svg"
                                                class="h-4 w-4 text-green-500"
                                                fill="none"
                                                viewBox="0 0 24 24"
                                                stroke="currentColor"
                                                stroke-width="2"
                                            >
                                                <path
                                                    stroke-linecap="round"
                                                    stroke-linejoin="round"
                                                    d="M5 13l4 4L19 7"
                                                />
                                            </svg>
                                        </button>
                                    </div>
                                </td>
                                <td class="px-6 py-4">
                                    <span
                                        class="inline-flex items-center rounded-full px-2 py-0.5 text-xs font-medium"
                                        :class="
                                            app.active
                                                ? 'bg-green-100 text-green-700 dark:bg-green-900 dark:text-green-400'
                                                : 'bg-[#f5f5f3] text-[#706f6c] dark:bg-[#1e1e1c] dark:text-[#A1A09A]'
                                        "
                                    >
                                        {{ app.active ? 'Ativa' : 'Inativa' }}
                                    </span>
                                </td>
                                <td
                                    class="px-6 py-4 text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ formatDate(app.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <a
                                            :href="`/admin/apps/${app.id}/edit`"
                                            class="rounded-sm border border-[#e3e3e0] bg-white px-3 py-1 text-xs font-medium text-[#1b1b18] transition hover:bg-[#f5f5f3] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1e1e1c]"
                                        >
                                            Editar
                                        </a>
                                        <button
                                            type="button"
                                            class="rounded-sm border border-red-200 bg-white px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:bg-[#161615] dark:text-red-400 dark:hover:bg-red-950"
                                            @click="confirmDelete(app.id)"
                                        >
                                            Excluir
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </main>
    </div>

    <!-- Modal de confirmação de exclusão -->
    <Teleport to="body">
        <div
            v-if="confirmDeleteId !== null"
            class="fixed inset-0 z-50 flex items-center justify-center bg-black/40 p-6 backdrop-blur-sm"
            @click.self="cancelDelete"
        >
            <div
                class="w-full max-w-sm rounded-lg bg-white shadow-xl dark:bg-[#161615]"
            >
                <div class="p-6">
                    <div
                        class="mb-4 flex h-10 w-10 items-center justify-center rounded-full bg-red-100 dark:bg-red-950"
                    >
                        <svg
                            xmlns="http://www.w3.org/2000/svg"
                            class="h-5 w-5 text-red-600 dark:text-red-400"
                            fill="none"
                            viewBox="0 0 24 24"
                            stroke="currentColor"
                            stroke-width="2"
                        >
                            <path
                                stroke-linecap="round"
                                stroke-linejoin="round"
                                d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"
                            />
                        </svg>
                    </div>
                    <h3
                        class="text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]"
                    >
                        Excluir aplicação
                    </h3>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Tem certeza que deseja excluir esta aplicação? Esta ação
                        não pode ser desfeita.
                    </p>
                </div>
                <div
                    class="flex items-center justify-end gap-3 border-t border-[#e3e3e0] px-6 py-4 dark:border-[#3E3E3A]"
                >
                    <button
                        type="button"
                        class="rounded-sm border border-[#e3e3e0] bg-white px-4 py-2 text-sm font-medium text-[#1b1b18] transition hover:bg-[#f5f5f3] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1e1e1c]"
                        @click="cancelDelete"
                    >
                        Cancelar
                    </button>
                    <button
                        type="button"
                        class="rounded-sm bg-red-600 px-4 py-2 text-sm font-medium text-white transition hover:bg-red-700 dark:bg-red-700 dark:hover:bg-red-600"
                        @click="deleteApp"
                    >
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
