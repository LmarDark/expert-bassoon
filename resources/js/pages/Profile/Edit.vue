<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
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
}>();

const page = usePage<{ flash: { success?: string } }>();
const successMessage = computed(() => page.props.flash?.success);

const form = useForm({
    nickname: props.auth.user.nickname ?? '',
    current_password: '',
    password: '',
    password_confirmation: '',
});

function submit() {
    form.put('/profile', {
        onSuccess: () =>
            form.reset('current_password', 'password', 'password_confirmation'),
    });
}
</script>

<template>
    <Head title="Meu Perfil" />

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
                        Meu Perfil
                    </h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Gerencie suas informações pessoais e senha.
                    </p>
                </div>

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

                <!-- Form card -->
                <div
                    class="rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]"
                >
                    <form
                        @submit.prevent="submit"
                        class="flex flex-col divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]"
                    >
                        <!-- Dados -->
                        <div class="px-8 py-6">
                            <h2
                                class="mb-4 text-sm font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Dados pessoais
                            </h2>
                            <div class="flex flex-col gap-5">
                                <!-- Username (read-only) -->
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        Usuário
                                    </label>
                                    <div
                                        class="flex items-center rounded-sm border border-[#e3e3e0] bg-[#f5f5f3] px-3 py-2 dark:border-[#3E3E3A] dark:bg-[#1e1e1c]"
                                    >
                                        <span
                                            class="text-sm text-[#706f6c] dark:text-[#A1A09A]"
                                            >{{ auth.user.username }}</span
                                        >
                                    </div>
                                    <p
                                        class="text-xs text-[#706f6c] dark:text-[#A1A09A]"
                                    >
                                        O usuário não pode ser alterado.
                                    </p>
                                </div>

                                <!-- Nickname -->
                                <div class="flex flex-col gap-1.5">
                                    <label
                                        for="nickname"
                                        class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]"
                                    >
                                        Apelido
                                        <span
                                            class="text-[#706f6c] dark:text-[#A1A09A]"
                                            >(Opcional)</span
                                        >
                                    </label>
                                    <input
                                        id="nickname"
                                        v-model="form.nickname"
                                        type="text"
                                        autocomplete="nickname"
                                        placeholder="Como prefere ser chamado"
                                        class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] transition outline-none placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                        :class="{
                                            'border-red-500 focus:border-red-500 focus:ring-red-500':
                                                form.errors.nickname,
                                        }"
                                    />
                                    <p
                                        v-if="form.errors.nickname"
                                        class="text-xs text-red-500"
                                    >
                                        {{ form.errors.nickname }}
                                    </p>
                                </div>
                            </div>
                        </div>

                        <!-- Segurança -->
                        <div class="px-8 py-6">
                            <h2
                                class="mb-1 text-sm font-semibold tracking-wide text-[#706f6c] uppercase dark:text-[#A1A09A]"
                            >
                                Alterar senha
                            </h2>
                            <p
                                class="mb-4 text-xs text-[#706f6c] dark:text-[#A1A09A]"
                            >
                                Deixe em branco para manter a senha atual.
                            </p>
                            <div class="flex flex-col gap-5">
                                <PasswordInput
                                    v-if="!auth.user.is_admin"
                                    id="current_password"
                                    label="Senha atual"
                                    :model-value="form.current_password"
                                    :error="form.errors.current_password"
                                    autocomplete="current-password"
                                    @update:model-value="
                                        form.current_password = $event
                                    "
                                />
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
                                    @update:model-value="
                                        form.password_confirmation = $event
                                    "
                                />
                            </div>
                        </div>

                        <!-- Actions -->
                        <div
                            class="flex items-center justify-end gap-3 px-8 py-5"
                        >
                            <button
                                type="submit"
                                :disabled="form.processing"
                                class="inline-flex items-center gap-2 rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm leading-normal font-medium text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                            >
                                <span v-if="form.processing">Salvando...</span>
                                <span v-else>Salvar alterações</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </main>
    </div>
</template>
