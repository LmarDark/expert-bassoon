<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { computed } from 'vue';
import AppHeader from '@/components/AppHeader.vue';
import PasswordInput from '@/components/PasswordInput.vue';

const props = defineProps<{
    auth: {
        user: {
            username: string;
            nickname?: string;
            is_admin: boolean;
        };
    };
    user: {
        id: number;
        username: string;
        nickname?: string;
        is_admin: boolean;
        created_at: string;
    };
    usernameValidationType: string;
}>();

const isSelf = computed(() => props.auth.user.username === props.user.username);

const usernameHint = computed(() => {
    switch (props.usernameValidationType) {
        case 'cpf':           return 'Informe um CPF válido (Ex: 000.000.000-00)';
        case 'celular':       return 'Informe um celular válido (Ex: (00) 90000-0000)';
        case 'personalizado': return 'Siga o padrão de usuário definido no sistema';
        default:              return '';
    }
});

const form = useForm({
    nickname:              props.user.nickname ?? '',
    username:              props.user.username,
    password:              '',
    password_confirmation: '',
    is_admin:              props.user.is_admin,
});

function submit() {
    form.put(`/admin/users/${props.user.id}`);
}
</script>

<template>
    <Head title="Editar Usuário" />

    <div class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <AppHeader :user="auth.user" />

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
                        Editar usuário
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Atualize as informações de
                        <span class="font-medium text-[#1b1b18] dark:text-[#EDEDEC]">{{ user.username }}</span>.
                    </p>
                </div>

                <!-- Form card -->
                <div class="rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                    <form @submit.prevent="submit" class="flex flex-col divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                        <!-- Seção: Dados -->
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
                                    <p v-if="usernameHint && !form.errors.username" class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                        {{ usernameHint }}
                                    </p>
                                    <p v-if="form.errors.username" class="text-xs text-red-500">{{ form.errors.username }}</p>
                                </div>

                                <!-- Nickname -->
                                <div class="flex flex-col gap-1.5">
                                    <label for="nickname" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                        Apelido
                                    </label>
                                    <input
                                        id="nickname"
                                        v-model="form.nickname"
                                        type="text"
                                        placeholder="Nome de exibição (opcional)"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                        :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.nickname }"
                                    />
                                    <p v-if="form.errors.nickname" class="text-xs text-red-500">{{ form.errors.nickname }}</p>
                                </div>

                                <!-- is_admin -->
                                <div class="flex flex-col gap-1.5">
                                    <label class="flex cursor-pointer items-center gap-3" :class="{ 'cursor-not-allowed opacity-60': isSelf }">
                                        <div class="relative">
                                            <input
                                                v-model="form.is_admin"
                                                type="checkbox"
                                                :disabled="isSelf"
                                                class="sr-only"
                                            />
                                            <div
                                                class="h-5 w-9 rounded-full transition"
                                                :class="form.is_admin ? 'bg-amber-500' : 'bg-[#e3e3e0] dark:bg-[#3E3E3A]'"
                                            ></div>
                                            <div
                                                class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform"
                                                :class="form.is_admin ? 'translate-x-4' : 'translate-x-0'"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Administrador</span>
                                    </label>
                                    <p v-if="isSelf" class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                        Você não pode alterar seu próprio privilégio de administrador.
                                    </p>
                                    <p v-if="form.errors.is_admin" class="text-xs text-red-500">{{ form.errors.is_admin }}</p>
                                </div>
                            </div>
                        </div>

                        <!-- Seção: Senha -->
                        <div class="px-8 py-6">
                            <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                                Alterar senha
                            </h2>
                            <p class="mb-4 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Deixe em branco para manter a senha atual.
                            </p>
                            <div class="flex flex-col gap-5">
                                <PasswordInput
                                    id="password"
                                    label="Nova senha"
                                    :model-value="form.password"
                                    :error="form.errors.password"
                                    autocomplete="new-password"
                                    @update:model-value="form.password = $event"
                                />
                                <PasswordInput
                                    id="password_confirmation"
                                    label="Confirmar nova senha"
                                    :model-value="form.password_confirmation"
                                    :error="form.errors.password_confirmation"
                                    autocomplete="new-password"
                                    @update:model-value="form.password_confirmation = $event"
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div class="flex items-center justify-between px-8 py-5">
                            <p class="text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                Criado em {{ new Date(user.created_at).toLocaleDateString('pt-BR') }}
                            </p>
                            <div class="flex items-center gap-3">
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
                                    <span v-if="form.processing">Salvando...</span>
                                    <span v-else>Salvar alterações</span>
                                </button>
                            </div>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>
