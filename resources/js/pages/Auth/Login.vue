<script setup lang="ts">
import { Head, useForm, usePage } from '@inertiajs/vue3';
import { onMounted, onBeforeUnmount } from 'vue';
import AppLogo from '@/components/AppLogo.vue';
import PasswordInput from '@/components/PasswordInput.vue';

type LoginSettings = {
    app_name: string;
    show_logo: boolean;
    primary_color: string;
    custom_css: string;
    logo_url: string | null;
    bg_color: string;
};

const page = usePage<{ settings: { login: LoginSettings } }>();
const settings = page.props.settings?.login;

const form = useForm({
    username: '',
    password: '',
    remember: false,
});

function submit() {
    form.post('/login', {
        onFinish: () => form.reset('password'),
    });
}

let styleEl: HTMLStyleElement | null = null;

onMounted(() => {
    if (settings?.custom_css) {
        styleEl = document.createElement('style');
        styleEl.textContent = settings.custom_css;
        document.head.appendChild(styleEl);
    }
});

onBeforeUnmount(() => {
    styleEl?.remove();
});
</script>

<template>
    <Head title="Login" />

    <div
        class="flex min-h-screen flex-col items-center justify-center p-6 text-[#1b1b18] bg-[#FDFDFC] dark:bg-[#0a0a0a]"
        :style="settings?.bg_color ? { backgroundColor: settings.bg_color } : {}"
    >
        <div class="w-full max-w-md">
            <!-- Logo -->
            <div v-if="settings?.show_logo !== false" class="mb-8 flex justify-center">
                <img
                    v-if="settings?.logo_url"
                    :src="settings.logo_url"
                    alt="Logo"
                    class="h-8 object-contain"
                />
                <AppLogo
                    v-else
                    class="h-8"
                    :style="{ color: settings?.primary_color ?? '#F53003' }"
                />
            </div>

            <!-- Card -->
            <div class="rounded-lg bg-white px-8 py-10 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                <h1 class="mb-1 text-center text-xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                    {{ settings?.app_name ?? 'Sistema de Autenticação' }}
                </h1>
                <p class="mb-6 text-center text-sm text-[#706f6c] dark:text-[#A1A09A]">
                    Entre com sua conta para continuar
                </p>

                <form @submit.prevent="submit" class="flex flex-col gap-5">
                    <!-- Username -->
                    <div class="flex flex-col gap-1.5">
                        <label for="username" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                            Usuário
                        </label>
                        <input
                            id="username"
                            v-model="form.username"
                            type="text"
                            autocomplete="username"
                            required
                            placeholder="Seu Usuário"
                            class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] disabled:opacity-50 dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.username }"
                        />
                        <p v-if="form.errors.username" class="text-xs text-red-500">
                            {{ form.errors.username }}
                        </p>
                    </div>

                    <PasswordInput
                        id="password"
                        label="Senha"
                        :model-value="form.password"
                        :error="form.errors.password"
                        :required="true"
                        autocomplete="current-password"
                        @update:model-value="form.password = $event"
                    />

                    <!-- Lembrar-me -->
                    <label class="flex cursor-pointer items-center gap-2">
                        <input
                            v-model="form.remember"
                            type="checkbox"
                            class="h-4 w-4 rounded-sm border-[#e3e3e0] accent-[#1b1b18] dark:border-[#3E3E3A] dark:accent-[#EDEDEC]"
                        />
                        <span class="text-sm text-[#706f6c] dark:text-[#A1A09A]">
                            Lembrar de mim
                        </span>
                    </label>

                    <!-- Botão -->
                    <button
                        type="submit"
                        :disabled="form.processing"
                        class="inline-block rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm font-medium leading-normal text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                    >
                        <span v-if="form.processing">Entrando...</span>
                        <span v-else>Entrar</span>
                    </button>
                </form>
            </div>
        </div>
    </div>
</template>
