# Ordy Desktop

Aplicacao desktop criada com Laravel, Filament e NativePHP.

## Setup

Instale as dependencias do PHP e do Node:

```bash
composer install
npm install
```

Crie o arquivo de ambiente e gere a chave da aplicacao:

```bash
cp .env.example .env
php artisan key:generate
```

## Ambiente De Desenvolvimento

Para rodar o ambiente de desenvolvimento do NativePHP:

```bash
composer run native:dev
```

Esse comando inicia o app desktop NativePHP e o Vite em paralelo.

## Build De Producao

O instalador e gerado com o NativePHP/Electron. O build precisa rodar **na mesma plataforma do alvo**: Linux no Linux, Windows no Windows.

### Pre-requisitos

Conclua o setup inicial (`composer install`, `npm install`, `.env` configurado) e compile os assets antes do build:

```bash
npm run build
```

Se voce alterou o plugin Electron em `nativephp/electron/electron-plugin/`, compile-o tambem:

```bash
npm --prefix nativephp/electron run plugin:build
```

### Linux

No Linux, gere o AppImage e o pacote `.deb`:

```bash
php artisan native:build linux x64 --no-interaction
```

Para `arm64`:

```bash
php artisan native:build linux arm64 --no-interaction
```

Os artefatos ficam em `nativephp/electron/dist/`.

### Windows

No Windows, gere o instalador NSIS (somente `x64`):

```bash
php artisan native:build win x64 --no-interaction
```

O instalador fica em `nativephp/electron/dist/`.

### Observacoes

- O comando `native:build` roda `npm ci` em `nativephp/electron/` automaticamente.
- Use `--no-interaction` para evitar prompts interativos (util em CI).
- Para publicar com o updater configurado: `php artisan native:publish linux x64` ou `php artisan native:publish win x64`.
- Para limpar artefatos de build: `php artisan native:reset`.

## Banco De Dados

Para executar as migrations no banco padrao do Laravel:

```bash
php artisan migrate
```

Para executar as migrations no banco usado pelo NativePHP Desktop:

```bash
NATIVEPHP_RUNNING=true php artisan migrate
```

## Seeds

Para rodar os seeds no banco padrao do Laravel:

```bash
php artisan db:seed
```

Para rodar os seeds no banco usado pelo NativePHP Desktop:

```bash
NATIVEPHP_RUNNING=true php artisan db:seed
```

O seed cria o usuario inicial:

- Email: `ordy@ordy.com`
- Senha: `ordy`
