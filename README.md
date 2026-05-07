# SSO Gateway

Gateway centralizado de autenticação construído com **Laravel 13**, **Inertia.js** e **Vue 3**. Múltiplas aplicações compartilham uma única sessão autenticada via cookie de domínio compartilhado, com validação delegada ao Nginx através do módulo `auth_request`. Para aplicações em domínios diferentes, oferece um fluxo JWT HS256 de uso único.

---

## Sumário

- [Visão geral](#visão-geral)
- [Stack](#stack)
- [Requisitos](#requisitos)
- [Instalação](#instalação)
- [Variáveis de ambiente](#variáveis-de-ambiente)
- [Estrutura do projeto](#estrutura-do-projeto)
- [Rotas](#rotas)
- [Fluxo de autenticação](#fluxo-de-autenticação)
- [SSO JWT — integração cross-domain](#sso-jwt--integração-cross-domain)
- [Painel administrativo](#painel-administrativo)
- [Primeiros passos — Setup inicial](#primeiros-passos--setup-inicial)
- [Comandos disponíveis](#comandos-disponíveis)
- [Testes](#testes)
- [Análise estática](#análise-estática)
- [Qualidade de código](#qualidade-de-código)
- [Deploy e Nginx](#deploy-e-nginx)
- [Docker](#docker)
- [Licença](#licença)

---

## Visão geral

O **SSO Gateway** atua como o ponto central de autenticação para um conjunto de aplicações em subdomínios do mesmo domínio. O fluxo funciona assim:

1. O usuário acessa qualquer aplicação protegida.
2. O Nginx dessa aplicação faz uma subrequisição interna ao endpoint `/auth/check` deste gateway, repassando o cookie de sessão.
3. O gateway responde `200` (sessão válida) ou `401` (não autenticado).
4. Em caso de `401`, o Nginx redireciona o usuário ao login com um parâmetro `return_to`, que devolve o usuário à aplicação original após autenticar.

```
Browser → app1.dominio.com.br
            │
            ├─ Nginx auth_request → auth.dominio.com.br/auth/check
            │       ├─ 200: serve a requisição normalmente
            │       └─ 401: redireciona para auth.dominio.com.br/login?return_to=...
            │
            └─ Usuário faz login → redirecionado de volta para app1.dominio.com.br
```

---

## Stack

| Camada | Tecnologia |
|--------|-----------|
| Backend | PHP 8.3+, Laravel 13 |
| Frontend | Vue 3, Inertia.js, TypeScript |
| Estilização | Tailwind CSS 4 |
| Build | Vite 8 |
| Banco de dados | SQLite (padrão) |
| Testes | Pest 4 |
| Análise estática | PHPStan nível 6, Larastan |
| Linting PHP | Laravel Pint |
| Linting JS/TS | ESLint + Prettier |

---

## Requisitos

- PHP **8.3** ou superior com extensões: `pdo_sqlite`, `mbstring`, `xml`, `curl`, `zip`, `pcntl`
- Composer **2.x**
- Node.js **20+** e pnpm **9+**
- Nginx **1.5.4+** (com `ngx_http_auth_request_module`) — apenas para uso como proxy SSO

---

## Instalação

### Instalação rápida (recomendado)

```bash
git clone <repo-url> sso-gateway
cd sso-gateway
composer run setup
```

O script executa em sequência:
1. `composer install`
2. Copia `.env.example` → `.env` (se não existir)
3. Gera a `APP_KEY`
4. Roda as migrations
5. Cria o symlink `public/storage`
6. `pnpm install`
7. `pnpm run build`

### Instalação manual

```bash
git clone <repo-url> sso-gateway
cd sso-gateway

composer install
cp .env.example .env
php artisan key:generate
touch database/database.sqlite
php artisan migrate
php artisan storage:link
pnpm install
pnpm run build
```

### Servidor de desenvolvimento

```bash
composer run dev
```

Esse comando sobe em paralelo:
- `php artisan serve` — servidor PHP na porta `8000`
- `php artisan queue:listen` — fila de jobs
- `php artisan pail` — log em tempo real
- `pnpm run dev` — Vite com HMR

---

## Variáveis de ambiente

Copie `.env.example` e ajuste os valores:

```dotenv
APP_NAME="SSO Gateway"
APP_ENV=local           # production em produção
APP_DEBUG=true          # false em produção
APP_URL=http://localhost

# Banco de dados (SQLite por padrão)
DB_CONNECTION=sqlite

# Sessão — configuração crítica para SSO
SESSION_DRIVER=database
SESSION_LIFETIME=120
SESSION_DOMAIN=null                  # produção: .seudominio.com.br
SESSION_SECURE_COOKIE=false          # produção: true
SESSION_SAME_SITE=lax

# Segurança no redirecionamento pós-login
# Aceita apenas URLs cujo host termine com este valor
ALLOWED_HOST_REDIRECT=               # ex: seudominio.com.br
```

### Variáveis para SSO em produção

| Variável | Valor | Descrição |
|----------|-------|-----------|
| `SESSION_DOMAIN` | `.seudominio.com.br` | Ponto na frente compartilha o cookie entre todos os subdomínios |
| `SESSION_SECURE_COOKIE` | `true` | Obrigatório com HTTPS — impede que o cookie seja enviado em HTTP |
| `SESSION_SAME_SITE` | `lax` | Permite envio do cookie em navegações cross-site (necessário para o `return_to`) |
| `ALLOWED_HOST_REDIRECT` | `seudominio.com.br` | Valida o host do `return_to` para evitar open redirect |

---

## Estrutura do projeto

```
app/
├── Console/Commands/
│   └── PruneSsoTokens.php          # sso:prune-tokens — limpa JWTs expirados
├── Http/
│   ├── Controllers/
│   │   ├── Admin/
│   │   │   ├── AppController.php       # CRUD de aplicações SSO + regenerateApiKey
│   │   │   ├── AuditController.php     # Log de auditoria (admin only)
│   │   │   ├── SetupController.php     # Configuração inicial (primeiro acesso)
│   │   │   ├── SettingsController.php  # Personalização da página de login
│   │   │   └── UserController.php      # CRUD de usuários (admin only)
│   │   ├── Auth/
│   │   │   └── AuthController.php      # Login e logout
│   │   ├── Sso/
│   │   │   └── TokenController.php     # GET /sso/token (emite JWT) + POST /sso/validate + GET /sso/logout
│   │   ├── HealthController.php        # GET /health — JSON com status do banco
│   │   └── ProfileController.php       # Perfil do usuário autenticado
│   ├── Middleware/
│   │   ├── CheckFirstSetup.php
│   │   ├── EnsureIsAdmin.php
│   │   └── UniversalAuth.php
│   └── Requests/Auth/
│       └── LoginRequest.php
├── Models/
│   ├── ActivityLog.php     # Log imutável de eventos
│   ├── App.php             # App SSO: api_key, allowed_domains, callback_url
│   ├── Setting.php         # Key-value de configurações
│   ├── SsoToken.php        # JTIs de uso único para replay protection
│   └── User.php            # username, nickname, is_admin
└── Services/Auth/
    ├── AuthService.php
    ├── JwtService.php               # HS256 puro, sem biblioteca externa
    └── UsernameValidationService.php

resources/js/
├── components/
│   ├── ActionCard.vue
│   ├── AppHeader.vue
│   ├── AppLogo.vue
│   └── PasswordInput.vue
├── pages/
│   ├── Auth/Login.vue
│   ├── Admin/
│   │   ├── Apps/{Index,Create,Edit}.vue
│   │   ├── Audit.vue
│   │   ├── Settings.vue
│   │   ├── Setup.vue
│   │   └── Users/{Index,Create,Edit}.vue
│   ├── Home.vue
│   └── Profile/Edit.vue
└── types/
    ├── auth.ts
    └── global.d.ts
```

---

## Rotas

| Método | URI | Descrição | Auth |
|--------|-----|-----------|------|
| `GET` | `/health` | Health check (JSON: status + banco) | — |
| `GET` | `/auth/check` | Validação de sessão para o Nginx `auth_request` | — |
| `GET/POST` | `/login` | Página e processamento do login | — |
| `POST` | `/logout` | Encerra a sessão | — |
| `GET/POST` | `/setup` | Configuração inicial do administrador | — |
| `GET` | `/home` | Home do usuário autenticado | Sim |
| `GET/PUT` | `/profile` | Perfil do usuário (nickname e senha) | Sim |
| `GET` | `/sso/token` | Emite JWT de uso único (throttle: 30/min) | Sim |
| `POST` | `/sso/validate` | Valida o JWT (throttle: 60/min; CSRF exempt) | — |
| `GET` | `/sso/logout` | Encerra sessão e redireciona para app | — |
| `GET/PUT` | `/admin/settings` | Personalização da página de login | Admin |
| `GET` | `/admin/audit` | Logs de auditoria paginados | Admin |
| `GET/POST/PUT/DELETE` | `/admin/users*` | CRUD de usuários | Admin |
| `GET/POST/PUT/DELETE` | `/admin/apps*` | CRUD de aplicações SSO | Admin |
| `POST` | `/admin/apps/{app}/regenerate-key` | Regenera a API Key da aplicação | Admin |

---

## Fluxo de autenticação

### Login

```
POST /login
  └── LoginRequest (validação)
        └── AuthService::attempt()
              └── Auth::attempt(['username', 'password'])
                    ├── Sucesso → redireciona para return_to ou /home
                    └── Falha   → volta com erro no campo "username"
```

O redirecionamento pós-login aceita um parâmetro `return_to` na query string ou na sessão (`url.intended`). O host do `return_to` é validado contra `ALLOWED_HOST_REDIRECT` para prevenir open redirect.

### Middleware de acesso

**`CheckFirstSetup`** — roda em toda requisição web (antes da autenticação):
- Se não há usuários no banco → redireciona para `/setup`
- Se há usuários e a rota é `/setup` → redireciona para `/login`

**`UniversalAuth`** — roda em todas as rotas nomeadas:
- Não autenticado tentando rota protegida → redireciona para `/login?return_to=...`
- Autenticado tentando acessar `/login` → redireciona para `/home`

**`EnsureIsAdmin`** — aplicado a todas as rotas `/admin/*`:
- `is_admin = false` → HTTP 403

---

## SSO JWT — integração cross-domain

Além do SSO via cookie (subdomínios), o gateway oferece um fluxo JWT para integrar aplicações em **domínios completamente diferentes**.

### Como funciona

```
1. Usuário está autenticado em auth.seudominio.com
2. Aplicação em outro-site.com redireciona o usuário para:
   GET https://auth.seudominio.com/sso/token
       ?app=<API_KEY_DA_APLICAÇÃO>
       &redirect=https://outro-site.com/sso/callback

3. O gateway valida a sessão, gera um JWT HS256 de uso único (TTL: 2 min)
   e redireciona para:
   https://outro-site.com/sso/callback?sso_token=<jwt>

4. O backend de outro-site.com valida o token:
   POST https://auth.seudominio.com/sso/validate
   Content-Type: application/json
   { "token": "<jwt>", "api_key": "<API_KEY_DA_APLICAÇÃO>" }

5. Resposta de sucesso:
   { "valid": true, "user": { "id": 1, "username": "joao", "nickname": "João", "is_admin": false } }
```

### Cadastrar uma aplicação

1. Acesse `/admin/apps` → **Nova aplicação**
2. Informe o nome e os **domínios permitidos** (um por linha)
3. Opcionalmente, informe a URL de callback padrão
4. A **API Key** é gerada automaticamente — guarde-a com segurança
5. Para rotacionar a chave, clique em **Regenerar** na tela de edição

### Payload do JWT

```json
{
  "sub": "joao",
  "user_id": 1,
  "nickname": "João Silva",
  "is_admin": false,
  "app": "Minha Aplicação",
  "jti": "<id único>",
  "iat": 1746500000,
  "exp": 1746500120
}
```

O JWT é assinado com **HS256** usando a API Key da aplicação. O `jti` é registrado no banco e invalidado após o primeiro uso — **tokens não podem ser reutilizados**.

### Limpeza de tokens expirados

Tokens expirados são limpos automaticamente pelo comando agendado diariamente:

```bash
php artisan sso:prune-tokens            # padrão: expirados há mais de 24h
php artisan sso:prune-tokens --hours=48 # customizável
```

---

## Painel administrativo

O painel administrativo (`/home` → seção "Ações do Administrador") centraliza todas as funções de gerenciamento.

### Módulos disponíveis

| Módulo | URL | Descrição |
|--------|-----|-----------|
| Gerenciar Usuários | `/admin/users` | CRUD completo de usuários |
| Gerenciar Aplicações | `/admin/apps` | Registro de apps SSO e gerenciamento de API Keys |
| Auditoria | `/admin/audit` | Histórico paginado de eventos do sistema |
| Personalizar Login | `/admin/settings` | Logo, cores e CSS da página de login |

### Usuários

- **Listagem** — tabela com todos os usuários; badge "admin" para administradores
- **Criar usuário** — usuário, apelido (opcional), senha, toggle de admin
- **Editar usuário** — atualiza dados; senha opcional na edição
- **Excluir usuário** — o admin logado não pode se auto-excluir
- **Toggle de admin** — desabilitado quando o admin está editando a si mesmo

### Aplicações SSO

- **Cadastro** — nome, domínios permitidos (um por linha), URL de callback e status
- **API Key** — gerada automaticamente; visível na tela de edição (pode ser regenerada)
- **Subdomínios** — se `app.seudominio.com` está na lista, todos os subdomínios são aceitos

### Personalizar Login

- Nome da aplicação exibido na página de login
- Logo personalizada (upload de imagem)
- Cor principal do SVG e cor de fundo da página
- CSS personalizado injetado apenas na página de login

---

## Primeiros passos — Setup inicial

```bash
# 1. Clone e instale
git clone <repo-url> sso-gateway && cd sso-gateway
composer run setup

# 2. Inicie o servidor de desenvolvimento
composer run dev

# 3. Acesse http://localhost:8000
#    → Será redirecionado automaticamente para /setup
#    → Defina o tipo de validação do usuário, usuário e senha do administrador
#    → Login automático → Home
```

---

## Comandos disponíveis

### Composer

| Comando | Descrição |
|---------|-----------|
| `composer run setup` | Instalação completa do zero |
| `composer run dev` | Servidor de desenvolvimento completo (PHP + Vite + queue + logs) |
| `composer run lint` | Corrige o estilo do código PHP com Pint |
| `composer run lint:check` | Verifica o estilo sem modificar arquivos |
| `composer run analyse` | Análise estática com PHPStan nível 6 |
| `composer run test` | Limpa cache, verifica lint e roda os testes |
| `composer run ci:check` | Verificação completa de CI (lint, format, types, testes) |

### pnpm

| Comando | Descrição |
|---------|-----------|
| `pnpm run dev` | Inicia o Vite com HMR |
| `pnpm run build` | Build de produção |
| `pnpm run lint` | Corrige problemas de ESLint |
| `pnpm run lint:check` | Verifica ESLint sem modificar |
| `pnpm run format` | Formata arquivos com Prettier |
| `pnpm run format:check` | Verifica formatação sem modificar |
| `pnpm run types:check` | Verifica tipos TypeScript com vue-tsc |

### Artisan

| Comando | Descrição |
|---------|-----------|
| `php artisan sso:prune-tokens` | Remove tokens JWT expirados há mais de 24h |
| `php artisan sso:prune-tokens --hours=48` | Remove tokens expirados há mais de N horas |

---

## Testes

O projeto usa **Pest 4** com o plugin Laravel.

```bash
# Rodar todos os testes
php artisan test

# Ou via composer (inclui lint e cache:clear)
composer run test

# Filtrar por classe ou método
php artisan test --filter UserControllerTest
```

A suíte cobre:

| Classe de teste | Módulo |
|----------------|--------|
| `AuthControllerTest` | Login, logout, redirecionamentos, eventos de auditoria |
| `SetupControllerTest` | Setup inicial, validação de CPF/celular/regex |
| `UserControllerTest` | CRUD de usuários, validação de username, regras de admin |
| `ProfileControllerTest` | Edição de perfil, troca de senha, senha atual obrigatória |
| `AuditControllerTest` | Listagem, filtros, paginação, acesso restrito a admin |
| `SettingsControllerTest` | Leitura e atualização de configurações de login |
| `AppControllerTest` | CRUD de aplicações SSO, regeneração de API Key |
| `TokenControllerTest` | Emissão e validação de JWT, replay protection, throttle |
| `HealthControllerTest` | Resposta JSON, status do banco |
| `PruneSsoTokensTest` | Limpeza de tokens por janela de tempo, output do comando |

---

## Análise estática

```bash
composer run analyse
# equivale a: ./vendor/bin/phpstan --level=6 analyse app/*
```

O projeto utiliza **PHPStan nível 6** com **Larastan** para cobertura completa das classes do Laravel. Nenhum erro deve ser reportado.

---

## Qualidade de código

### PHP — Laravel Pint

Configurado com o preset `laravel` e regras adicionais em `pint.json`. Principais convenções:

- `declare(strict_types=1)` obrigatório em todos os arquivos
- Classes finais por padrão (`final_class`)
- Imports totalmente qualificados (`fully_qualified_strict_types`)
- Sem `else` desnecessário (`no_useless_else`)
- Funções de string multibyte (`mb_str_functions`)

### TypeScript / Vue — ESLint + Prettier

Configurado em `eslint.config.js` com os plugins `eslint-plugin-vue`, `typescript-eslint` e `@stylistic/eslint-plugin`. Formatação gerenciada pelo Prettier com `prettier-plugin-tailwindcss`.

Convenções do frontend:
- Sem comentários em arquivos `.vue` ou `.ts`
- Nunca usar `v-html` — risco de XSS; usar `v-text` + função de decode pura para entidades HTML

---

## Deploy e Nginx

O diretório `nginx/` contém exemplos prontos de configuração:

| Arquivo | Uso |
|---------|-----|
| `nginx/sso-gateway.conf` | Servidor do gateway SSO (`auth.seudominio.com`) |
| `nginx/app-subdomain.conf` | App no mesmo domínio — usa cookie + `auth_request` |
| `nginx/app-crossdomain.conf` | App em domínio diferente — usa fluxo JWT |

### Configuração rápida para subdomínio

```nginx
# Em /etc/nginx/sites-enabled/minha-app.conf
server {
    listen 443 ssl;
    server_name app.seudominio.com;

    location = /auth/check {
        internal;
        proxy_pass              https://auth.seudominio.com/auth/check;
        proxy_pass_request_body off;
        proxy_set_header        Content-Length "";
        proxy_set_header        Cookie $http_cookie;
    }

    location / {
        auth_request /auth/check;
        error_page 401 = @login_redirect;
        proxy_pass http://localhost:3000;
    }

    location @login_redirect {
        return 302 https://auth.seudominio.com/login?return_to=$scheme://$host$request_uri;
    }
}
```

### Deploy manual

```bash
pnpm run build
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan storage:link
chown -R www-data:www-data /var/www/sso-gateway/storage /var/www/sso-gateway/bootstrap/cache
chmod -R 775 /var/www/sso-gateway/storage /var/www/sso-gateway/bootstrap/cache
```

---

## Docker

Um exemplo de configuração Docker está disponível em `docker-compose.example.yml`:

```bash
cp docker-compose.example.yml docker-compose.yml
# Edite as variáveis de ambiente no arquivo
docker compose up -d
```

Inclui três serviços:

| Serviço | Descrição |
|---------|-----------|
| `app` | PHP-FPM 8.3 com OPcache; executa migrations automaticamente na inicialização |
| `nginx` | Proxy reverso para o serviço `app` |
| `scheduler` | Executa `php artisan schedule:run` a cada 60s (necessário para `sso:prune-tokens`) |

O volume `sso_storage` é compartilhado entre `app` e `nginx` para servir os arquivos de `storage/` (logos, etc.).

---

## Licença

MIT License — © 2026 Lucas Matheus. Veja o arquivo [LICENSE](./LICENSE) para mais detalhes.
