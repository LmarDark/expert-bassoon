<script setup lang="ts">
import { Head, useForm } from '@inertiajs/vue3';
import { usePage } from '@inertiajs/vue3';
import { computed, ref } from 'vue';
import AppHeader from '@/components/AppHeader.vue';
import AppLogo from '@/components/AppLogo.vue';

type LoginSettings = {
    app_name: string;
    show_logo: boolean;
    primary_color: string;
    custom_css: string;
    logo_url: string | null;
    bg_color: string;
};

const props = defineProps<{
    auth: {
        user: {
            username: string;
            nickname?: string;
            is_admin: boolean;
        };
    };
    currentSettings: LoginSettings;
}>();

const page = usePage<{ flash: { success?: string } }>();
const successMessage = computed(() => page.props.flash?.success);

const logoPreviewUrl = ref<string | null>(props.currentSettings.logo_url);

const form = useForm({
    login_app_name:      props.currentSettings.app_name,
    login_show_logo:     props.currentSettings.show_logo,
    login_primary_color: props.currentSettings.primary_color,
    login_custom_css:    props.currentSettings.custom_css,
    login_bg_color:      props.currentSettings.bg_color || '#FDFDFC',
    login_logo:          null as File | null,
    login_logo_remove:   false,
});

function onLogoChange(e: Event) {
    const file = (e.target as HTMLInputElement).files?.[0] ?? null;
    form.login_logo = file;
    form.login_logo_remove = false;
    if (file) {
        logoPreviewUrl.value = URL.createObjectURL(file);
    }
}

function removeLogo() {
    form.login_logo = null;
    form.login_logo_remove = true;
    logoPreviewUrl.value = null;
    const input = document.getElementById('login_logo') as HTMLInputElement | null;
    if (input) input.value = '';
}

function submit() {
    form.put('/admin/settings');
}
</script>

<template>
    <Head title="Personalizar Login" />

    <div class="flex min-h-screen flex-col bg-[#FDFDFC] text-[#1b1b18] dark:bg-[#0a0a0a]">
        <AppHeader :user="auth.user" />

        <main class="flex-1 p-6">
            <div class="mx-auto max-w-5xl">
                <!-- Page title -->
                <div class="mb-6">
                    <div class="mb-1 flex items-center gap-2">
                        <a
                            href="/home"
                            class="flex items-center gap-1.5 text-sm text-[#706f6c] transition hover:text-[#1b1b18] dark:text-[#A1A09A] dark:hover:text-[#EDEDEC]"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                <path stroke-linecap="round" stroke-linejoin="round" d="M15 19l-7-7 7-7" />
                            </svg>
                            Voltar
                        </a>
                    </div>
                    <h1 class="text-2xl font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">Personalizar Login</h1>
                    <p class="mt-1 text-sm text-[#706f6c] dark:text-[#A1A09A]">
                        Customize a aparência da página de login do SSO.
                    </p>
                </div>

                <!-- Success banner -->
                <div
                    v-if="successMessage"
                    class="mb-6 flex items-center gap-3 rounded-lg border border-green-200 bg-green-50 px-4 py-3 dark:border-green-800 dark:bg-green-950"
                >
                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                        <path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7" />
                    </svg>
                    <p class="text-sm font-medium text-green-800 dark:text-green-300">{{ successMessage }}</p>
                </div>

                <div class="grid grid-cols-1 gap-6 lg:grid-cols-2">
                    <!-- Form -->
                    <div class="rounded-lg bg-white shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                        <form @submit.prevent="submit" class="flex flex-col divide-y divide-[#e3e3e0] dark:divide-[#3E3E3A]">
                            <!-- Aparência -->
                            <div class="px-8 py-6">
                                <h2 class="mb-4 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                                    Aparência
                                </h2>
                                <div class="flex flex-col gap-5">
                                    <!-- App name -->
                                    <div class="flex flex-col gap-1.5">
                                        <label for="login_app_name" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                            Nome da aplicação
                                        </label>
                                        <input
                                            id="login_app_name"
                                            v-model="form.login_app_name"
                                            type="text"
                                            placeholder="Sistema de Autenticação"
                                            class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                            :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.login_app_name }"
                                        />
                                        <p v-if="form.errors.login_app_name" class="text-xs text-red-500">{{ form.errors.login_app_name }}</p>
                                    </div>

                                    <!-- Primary color -->
                                    <div class="flex flex-col gap-1.5">
                                        <label for="login_primary_color" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                            Cor principal (logo SVG)
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input
                                                id="login_primary_color"
                                                v-model="form.login_primary_color"
                                                type="color"
                                                class="h-10 w-14 cursor-pointer rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-1 dark:border-[#3E3E3A] dark:bg-[#1a1a18]"
                                            />
                                            <input
                                                v-model="form.login_primary_color"
                                                type="text"
                                                placeholder="#F53003"
                                                maxlength="7"
                                                class="w-32 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 font-mono text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.login_primary_color }"
                                            />
                                        </div>
                                        <p v-if="form.errors.login_primary_color" class="text-xs text-red-500">{{ form.errors.login_primary_color }}</p>
                                    </div>

                                    <!-- Background color -->
                                    <div class="flex flex-col gap-1.5">
                                        <label for="login_bg_color" class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">
                                            Cor de fundo da página
                                        </label>
                                        <div class="flex items-center gap-3">
                                            <input
                                                id="login_bg_color"
                                                v-model="form.login_bg_color"
                                                type="color"
                                                class="h-10 w-14 cursor-pointer rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] p-1 dark:border-[#3E3E3A] dark:bg-[#1a1a18]"
                                            />
                                            <input
                                                v-model="form.login_bg_color"
                                                type="text"
                                                placeholder="#FDFDFC"
                                                maxlength="7"
                                                class="w-32 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 font-mono text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                                :class="{ 'border-red-500 focus:border-red-500 focus:ring-red-500': form.errors.login_bg_color }"
                                            />
                                        </div>
                                        <p v-if="form.errors.login_bg_color" class="text-xs text-red-500">{{ form.errors.login_bg_color }}</p>
                                    </div>

                                    <!-- Show logo -->
                                    <label class="flex cursor-pointer items-center gap-3">
                                        <div class="relative">
                                            <input v-model="form.login_show_logo" type="checkbox" class="sr-only" />
                                            <div
                                                class="h-5 w-9 rounded-full transition"
                                                :class="form.login_show_logo ? 'bg-[#1b1b18] dark:bg-[#EDEDEC]' : 'bg-[#e3e3e0] dark:bg-[#3E3E3A]'"
                                            ></div>
                                            <div
                                                class="absolute left-0.5 top-0.5 h-4 w-4 rounded-full bg-white shadow transition-transform"
                                                :class="form.login_show_logo ? 'translate-x-4' : 'translate-x-0'"
                                            ></div>
                                        </div>
                                        <span class="text-sm font-medium text-[#1b1b18] dark:text-[#EDEDEC]">Exibir logo na página de login</span>
                                    </label>
                                </div>
                            </div>

                            <!-- Logo upload -->
                            <div class="px-8 py-6">
                                <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                                    Logo personalizada
                                </h2>
                                <p class="mb-4 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    Substitui o logo SVG padrão. Formatos suportados: PNG, JPG, SVG, WebP (máx. 2 MB).
                                </p>

                                <!-- Current logo preview -->
                                <div v-if="logoPreviewUrl" class="mb-3 flex items-center gap-3">
                                    <img
                                        :src="logoPreviewUrl"
                                        alt="Logo atual"
                                        class="h-12 max-w-[120px] rounded border border-[#e3e3e0] bg-[#f7f7f5] object-contain p-1 dark:border-[#3E3E3A] dark:bg-[#1a1a18]"
                                    />
                                    <button
                                        type="button"
                                        @click="removeLogo"
                                        class="inline-flex items-center gap-1.5 rounded-sm border border-red-200 bg-red-50 px-3 py-1.5 text-xs font-medium text-red-700 transition hover:bg-red-100 dark:border-red-800 dark:bg-red-950 dark:text-red-400 dark:hover:bg-red-900"
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" class="h-3.5 w-3.5" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                            <path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        Remover logo
                                    </button>
                                </div>

                                <label
                                    for="login_logo"
                                    class="flex cursor-pointer items-center gap-2 rounded-sm border border-dashed border-[#e3e3e0] bg-[#FDFDFC] px-4 py-3 text-sm text-[#706f6c] transition hover:border-[#1b1b18] hover:text-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#A1A09A] dark:hover:border-[#EDEDEC] dark:hover:text-[#EDEDEC]"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" class="h-4 w-4 flex-shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                                        <path stroke-linecap="round" stroke-linejoin="round" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-8l-4-4m0 0L8 8m4-4v12" />
                                    </svg>
                                    {{ logoPreviewUrl ? 'Trocar imagem' : 'Selecionar imagem' }}
                                </label>
                                <input
                                    id="login_logo"
                                    type="file"
                                    accept="image/*"
                                    class="sr-only"
                                    @change="onLogoChange"
                                />
                                <p v-if="form.errors.login_logo" class="mt-1 text-xs text-red-500">{{ form.errors.login_logo }}</p>
                            </div>

                            <!-- CSS personalizado -->
                            <div class="px-8 py-6">
                                <h2 class="mb-1 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                                    CSS personalizado
                                </h2>
                                <p class="mb-4 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                    Injetado apenas na página de login. Use para sobrescrever estilos ou adicionar animações.
                                </p>
                                <textarea
                                    v-model="form.login_custom_css"
                                    rows="10"
                                    placeholder="/* Exemplo */&#10;body { background: #f0f0f0; }"
                                    spellcheck="false"
                                    class="w-full rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] px-3 py-2 font-mono text-sm text-[#1b1b18] outline-none transition placeholder:text-[#b5b3ad] focus:border-[#1b1b18] focus:ring-1 focus:ring-[#1b1b18] dark:border-[#3E3E3A] dark:bg-[#1a1a18] dark:text-[#EDEDEC] dark:placeholder:text-[#55544f] dark:focus:border-[#EDEDEC] dark:focus:ring-[#EDEDEC]"
                                ></textarea>
                            </div>

                            <!-- Actions -->
                            <div class="flex items-center justify-end px-8 py-5">
                                <button
                                    type="submit"
                                    :disabled="form.processing"
                                    class="inline-flex items-center gap-2 rounded-sm border border-black bg-[#1b1b18] px-5 py-2 text-sm font-medium leading-normal text-white transition hover:bg-black disabled:cursor-not-allowed disabled:opacity-60 dark:border-[#eeeeec] dark:bg-[#eeeeec] dark:text-[#1C1C1A] dark:hover:border-white dark:hover:bg-white"
                                >
                                    <span v-if="form.processing">Salvando...</span>
                                    <span v-else>Salvar configurações</span>
                                </button>
                            </div>
                        </form>
                    </div>

                    <!-- Preview -->
                    <div>
                        <h2 class="mb-3 text-sm font-semibold uppercase tracking-wide text-[#706f6c] dark:text-[#A1A09A]">
                            Pré-visualização
                        </h2>
                        <div class="overflow-hidden rounded-lg shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                            <div
                                class="flex flex-col items-center justify-center p-8"
                                :style="{ backgroundColor: form.login_bg_color || '#FDFDFC' }"
                            >
                                <!-- Logo preview -->
                                <div v-if="form.login_show_logo" class="mb-6 flex justify-center">
                                    <img
                                        v-if="logoPreviewUrl"
                                        :src="logoPreviewUrl"
                                        alt="Logo"
                                        class="h-8 object-contain"
                                    />
                                    <AppLogo
                                        v-else
                                        class="h-8"
                                        :style="{ color: form.login_primary_color || '#F53003' }"
                                    />
                                </div>
                                <!-- Card preview -->
                                <div class="w-full max-w-xs rounded-lg bg-white px-6 py-7 shadow-[inset_0px_0px_0px_1px_rgba(26,26,0,0.16)] dark:bg-[#161615] dark:shadow-[inset_0px_0px_0px_1px_#fffaed2d]">
                                    <h3 class="mb-1 text-center text-base font-semibold text-[#1b1b18] dark:text-[#EDEDEC]">
                                        {{ form.login_app_name || 'Sistema de Autenticação' }}
                                    </h3>
                                    <p class="mb-4 text-center text-xs text-[#706f6c] dark:text-[#A1A09A]">
                                        Entre com sua conta para continuar
                                    </p>
                                    <div class="flex flex-col gap-3">
                                        <div class="h-8 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#1a1a18]"></div>
                                        <div class="h-8 rounded-sm border border-[#e3e3e0] bg-[#FDFDFC] dark:border-[#3E3E3A] dark:bg-[#1a1a18]"></div>
                                        <div
                                            class="h-8 rounded-sm"
                                            :style="{ backgroundColor: form.login_primary_color || '#1b1b18' }"
                                        ></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <p class="mt-2 text-xs text-[#706f6c] dark:text-[#A1A09A]">
                            O CSS personalizado é aplicado apenas na página real de login.
                        </p>
                    </div>
                </div>
            </div>
        </main>
    </div>
</template>
