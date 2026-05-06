<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import AppHeader from '@/components/AppHeader.vue';

defineProps<{
    auth: {
        user: {
            username: string;
            nickname?: string;
        };
    };
}>();

const form = useForm({
    name: '',
    allowed_domains: '',
    callback_url: '',
    active: true,
});

function submit() {
    form.post('/admin/apps');
}
</script>

<template>
    <Head title="Nova Aplicação" />

    <div
        class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]"
    >
        <AppHeader :user="auth.user" />

        <main class="flex flex-1 justify-center p-6">
            <div class="w-full max-w-2xl">
                <!-- Page title -->
                <div class="mb-6">
                    <div class="mb-1 flex items-center gap-2">
                        <a
                            href="/admin/apps"
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
                        Nova aplicação
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Registre uma nova aplicação para usar o SSO. A API Key
                        será gerada automaticamente.
                    </p>
                </div>

                <!-- Form card -->
                <div
                    class="rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <form
                        @submit.prevent="submit"
                        class="flex flex-col divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]"
                    >
                        <div class="px-8 py-6">
                            <h2
                                class="mb-4 text-sm font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Informações da aplicação
                            </h2>
                            <div class="flex flex-col gap-5">
                                <!-- Name -->
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="name"
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        Nome <span class="text-red-500">*</span>
                                    </label>
                                    <input
                                        id="name"
                                        v-model="form.name"
                                        type="text"
                                        placeholder="Minha Aplicação"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] transition outline-none placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-500':
                                                form.errors.name,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.name"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.name }}
                                    </p>
                                </div>

                                <!-- Allowed domains -->
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="allowed_domains"
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        Domínios permitidos
                                        <span class="text-red-500">*</span>
                                    </label>
                                    <textarea
                                        id="allowed_domains"
                                        v-model="form.allowed_domains"
                                        rows="3"
                                        placeholder="app1.exemplo.com&#10;app2.exemplo.com"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 font-mono text-sm text-[#1b1b18] transition outline-none placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-500':
                                                form.errors.allowed_domains,
                                        }"
                                    />
                                    <p
                                        class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        Um domínio por linha. Subdomínios são
                                        aceitos automaticamente.
                                    </p>
                                    <p
                                        v-if="form.errors.allowed_domains"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.allowed_domains }}
                                    </p>
                                </div>

                                <!-- Callback URL -->
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="callback_url"
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        URL de callback
                                        <span
                                            class="text-[#706f6c] dark:text-[#A1A09A]"
                                            >(Opcional)</span
                                        >
                                    </label>
                                    <input
                                        id="callback_url"
                                        v-model="form.callback_url"
                                        type="url"
                                        placeholder="https://app.exemplo.com/sso/callback"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] transition outline-none placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-500':
                                                form.errors.callback_url,
                                        }"
                                    />
                                    <p
                                        class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        URL padrão para redirecionar após a
                                        autenticação SSO.
                                    </p>
                                    <p
                                        v-if="form.errors.callback_url"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.callback_url }}
                                    </p>
                                </div>

                                <!-- Active -->
                                <div class="flex items-center gap-3">
                                    <input
                                        id="active"
                                        v-model="form.active"
                                        type="checkbox"
                                        class="h-4 w-4 rounded border-[#e3e3e0] text-[#1b1b18] focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18]"
                                    />
                                    <label
                                        for="active"
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        Aplicação ativa
                                    </label>
                                </div>
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-end gap-3 px-8 py-5"
                        >
                            <a
                                href="/admin/apps"
                                class="inline-flex items-center gap-2 rounded-sm border border-[#e3e3e0] bg-white px-5 py-2 text-sm leading-normal font-medium text-[#1b1b18] transition hover:bg-[#f5f5f3] dark:border-[#3E3E3A] dark:bg-[#161615] dark:text-[#EDEDEC] dark:hover:bg-[#1e1e1c]"
                            >
                                Cancelar
                            </a>
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm leading-normal font-medium text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                            >
                                <span v-if="form.processing">Criando...</span>
                                <span v-else>Criar aplicação</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>
