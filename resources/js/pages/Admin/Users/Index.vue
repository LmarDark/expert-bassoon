<script setup lang="ts">
import { Head, router } from '@inertiajs/vue3';
import { ref, computed } from 'vue';
import AppHeader from '@/components/AppHeader.vue';

const props = defineProps<{
    auth: {
        user: {
            username: string;
            nickname?: string;
        };
    };
    users: Array<{
        id: number;
        username: string;
        nickname?: string;
        is_admin: boolean;
        created_at: string;
    }>;
}>();

const search = ref('');
const confirmDeleteId = ref<number | null>(null);

const filteredUsers = computed(() =>
    props.users.filter((u) =>
        u.username.toLowerCase().includes(search.value.toLowerCase()),
    ),
);

function formatDate(dateStr: string): string {
    return new Date(dateStr).toLocaleDateString('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit',
    });
}

function confirmDelete(id: number) {
    confirmDeleteId.value = id;
}

function cancelDelete() {
    confirmDeleteId.value = null;
}

function deleteUser() {
    if (confirmDeleteId.value === null) {
        return;
    }

    router.delete(`/admin/users/${confirmDeleteId.value}`, {
        onFinish: () => (confirmDeleteId.value = null),
    });
}
</script>

<template>
    <Head title="Gerenciar Usuários" />

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <AppHeader :user="auth.user" />

        <!-- Content -->
        <main class="flex-1 p-6">
            <div class="mx-auto max-w-5xl">
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
                            Usuários
                        </h1>
                        <p
                            class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{ users.length }}
                            {{
                                users.length === 1
                                    ? 'usuário cadastrado'
                                    : 'usuários cadastrados'
                            }}
                        </p>
                    </div>
                    <a
                        href="/admin/users/create"
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
                        Novo usuário
                    </a>
                </div>

                <!-- Search -->
                <div class="mb-4 flex items-center gap-3">
                    <div class="relative flex-1">
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
                            placeholder="Buscar por usuário..."
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
                        v-if="filteredUsers.length === 0"
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
                                    d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"
                                />
                            </svg>
                        </div>
                        <p
                            class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                        >
                            {{
                                search
                                    ? 'Nenhum usuário encontrado'
                                    : 'Nenhum usuário cadastrado'
                            }}
                        </p>
                        <p
                            class="mt-1 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                        >
                            {{
                                search
                                    ? 'Tente outro termo de busca.'
                                    : 'Clique em "Novo usuário" para começar.'
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
                                    Usuário
                                </th>
                                <th
                                    class="px-6 py-3 text-left text-xs font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                                >
                                    Apelido
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
                                v-for="user in filteredUsers"
                                :key="user.id"
                                class="transition hover:bg-[#f9f9f8] dark:hover:bg-[#1a1a18]"
                            >
                                <td class="px-6 py-4">
                                    <div class="flex items-center gap-3">
                                        <div
                                            class="flex h-8 w-8 flex-shrink-0 items-center justify-center rounded-full bg-[#1b1b18] text-xs font-semibold text-white uppercase dark:bg-[#EDEDEC] dark:text-[#1b1b18]"
                                        >
                                            {{ user.username.charAt(0) }}
                                        </div>
                                        <span
                                            class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                        >
                                            {{ user.username }}
                                        </span>
                                        <span
                                            v-if="
                                                user.username ===
                                                auth.user.username
                                            "
                                            class="rounded-full bg-[#f5f5f3] px-2 py-0.5 text-xs text-[#706f6c] dark:bg-[#1e1e1c] dark:text-[#A1A09A]"
                                        >
                                            você
                                        </span>
                                        <span
                                            v-if="user.is_admin"
                                            class="rounded-full bg-amber-100 px-2 py-0.5 text-xs font-medium text-amber-700 dark:bg-amber-900 dark:text-amber-300"
                                        >
                                            admin
                                        </span>
                                    </div>
                                </td>
                                <td
                                    class="px-6 py-4 text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ user.nickname ?? 'Não definido' }}
                                </td>
                                <td
                                    class="px-6 py-4 text-[#706f6c] dark:text-[#A1A09A]"
                                >
                                    {{ formatDate(user.created_at) }}
                                </td>
                                <td class="px-6 py-4">
                                    <div
                                        class="flex items-center justify-end gap-2"
                                    >
                                        <a
                                            :href="`/admin/users/${user.id}/edit`"
                                            class="rounded-sm border border-[#e3e3e0] bg-white px-3 py-1 text-xs font-medium text-[#1b1b18] transition hover:bg-[#f5f5f3] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1e1e1c]"
                                        >
                                            Editar
                                        </a>
                                        <button
                                            v-if="
                                                user.username !==
                                                auth.user.username
                                            "
                                            type="button"
                                            class="rounded-sm border border-red-200 bg-white px-3 py-1 text-xs font-medium text-red-600 transition hover:bg-red-50 dark:border-red-900 dark:bg-[#161615] dark:text-red-400 dark:hover:bg-red-950"
                                            @click="confirmDelete(user.id)"
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
                        Excluir usuário
                    </h3>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Tem certeza que deseja excluir este usuário? Esta ação
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
                        @click="deleteUser"
                    >
                        Excluir
                    </button>
                </div>
            </div>
        </div>
    </Teleport>
</template>
