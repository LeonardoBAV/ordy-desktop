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
