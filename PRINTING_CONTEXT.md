# Printing Implementation Context

Este documento registra o contexto técnico da implementação de impressão para uma próxima sessão de IA. Ele deve ser lido antes de alterar `PrintJob`, a API `print`, as configurações de impressão, ou a camada NativePHP/Electron.

## Objetivo Do Fluxo

O app Android gera um PDF e envia para o desktop via API local Laravel/NativePHP.

Fluxo esperado:

1. Android chama `POST /api/print` com `multipart/form-data`.
2. Laravel recebe o arquivo no campo `file`.
3. Laravel valida que é PDF e salva em storage privado.
4. Laravel despacha `App\Jobs\PrintJob`.
5. `PrintJob` lê `PrintSetting` no banco.
6. `PrintJob` imprime usando o método configurado.

O arquivo PDF é salvo no disco `local`, que neste projeto aponta para `storage/app/private`, em:

```text
prints/YYYY/MM/{uuid}.pdf
```

## Arquivos Principais

- `app/Http/Controllers/Api/PrintController.php`
- `app/Http/Requests/StorePrintRequest.php`
- `app/Jobs/PrintJob.php`
- `app/Enums/PrintMethodEnum.php`
- `app/Models/PrintSetting.php`
- `database/migrations/2026_06_14_015507_create_print_settings_table.php`
- `app/Filament/Pages/PrintSettings.php`
- `resources/views/filament/pages/print-settings.blade.php`
- `nativephp/electron/electron-plugin/src/server/api/system.ts`
- `lang/pt_BR/filamentphp-resources.php`
- `lang/en/filamentphp-resources.php`

## API De Upload

Rota criada:

```text
POST /api/print
```

Payload recomendado:

```text
multipart/form-data
file=<PDF>
```

Decisão tomada: usar `multipart/form-data`, não base64, porque é o formato mais adequado para upload binário a partir do Android e evita inflar o tamanho do payload.

`StorePrintRequest` valida:

- campo `file` obrigatório
- tipo PDF
- tamanho máximo atual: `20 MB`

`PrintController` salva o arquivo e retorna `202 Accepted` com o path relativo salvo:

```json
{
  "path": "prints/2026/06/uuid.pdf"
}
```

## Configuração Persistida

Foi criada a entidade `PrintSetting`.

Tabela:

```text
print_settings
```

Campos:

- `id`
- `method`
- `copies`
- timestamps

Defaults:

- `method = electron`
- `copies = 1`

O model `PrintSetting` tem um método singleton:

```php
PrintSetting::current()
```

Ele retorna o primeiro registro ou cria um registro default.

## Métodos De Impressão

Os métodos ficam no enum `App\Enums\PrintMethodEnum`:

```php
Electron = 'electron'
NativeWindows = 'native_windows'
SystemCommand = 'system_command'
```

### 1. Electron Silencioso

Label:

```text
Electron silencioso
```

Este é o método padrão.

Como funciona:

- `PrintJob` chama a API interna do NativePHP/Electron:

```text
system/print-file
```

- A camada Electron recebe `filePath` e `copies`.
- Electron cria uma `BrowserWindow` escondida.
- Carrega o PDF via `file://`.
- Chama:

```ts
webContents.print({
  silent: true,
  printBackground: true,
})
```

Plataformas:

- Windows: sim
- Linux: sim
- macOS/BSD: em princípio sim, mas deve ser testado

Motivo da decisão:

- É o método mais alinhado ao stack NativePHP/Electron.
- Não exige ferramenta externa como SumatraPDF.
- Evita risco de licença GPL/AGPL do Sumatra.
- O projeto usa Electron `^38`, versão que contém correções recentes para impressão silenciosa de PDF.

Cuidados:

- Depende de impressora padrão configurada no sistema.
- Depende do driver da impressora.
- Precisa rodar dentro do NativePHP, pois usa `NATIVEPHP_API_URL` e `NATIVEPHP_SECRET`.
- Fora do NativePHP (`php artisan serve`, shell isolado), esse método falha com `NativePHP internal API is not available.`

### 2. Windows Nativo (PDFium/GDI)

Label:

```text
Windows nativo (PDFium/GDI)
```

Como funciona:

- `PrintJob` chama:

```text
system/print-file-native-windows
```

- Electron tenta importar dinamicamente:

```ts
windows-pdf-printer-native
```

- A lib usa PDFium para renderizar o PDF e APIs nativas do Windows (`GDI32`/`Winspool`) para imprimir.

Plataformas:

- Windows: sim, se a dependência estiver instalada/empacotada corretamente.
- Linux/macOS: não.

Estado atual:

- O endpoint e a chamada estão preparados no código.
- A dependência Node **não foi fixada no `package.json`** porque uma tentativa via `npm` acabou gravando no `package.json` da raiz por engano, e foi revertida.
- Para produção Windows, é necessário adicionar a dependência com cuidado no `nativephp/electron/package.json`, preferencialmente como `optionalDependencies`, e instalar/buildar em ambiente Windows.

Cuidados:

- Deve ser tratado como experimental até testar em Windows real.
- Exige validação de empacotamento do `pdfium.dll`.
- Se a dependência não existir, esse método vai falhar e a job deve ir para `failed_jobs`.

### 3. Comando Do Sistema

Label:

```text
Comando do sistema
```

Como funciona:

- Linux/macOS/BSD: usa `lp`.
- Windows: usa PowerShell com `Start-Process -Verb Print`.

Trechos equivalentes:

```php
Process::run(['lp', $path])->throw();
```

Windows:

```powershell
Start-Process -FilePath '<arquivo.pdf>' -Verb Print -WindowStyle Hidden -ErrorAction Stop
```

Plataformas:

- Linux/macOS/BSD: maduro se CUPS/`lp` estiver configurado.
- Windows: parcial; depende do aplicativo padrão associado ao PDF suportar `Print`.

Cuidados:

- Linux precisa de CUPS e impressora padrão.
- Windows pode abrir janela, depender do Adobe/Edge/leitor padrão, ou não ser totalmente silencioso.

## Maturidade Dos Métodos

Ordem prática considerada:

1. `lp` no Linux é o mais maduro para Linux, mas não é cross-platform.
2. Electron silencioso é o melhor default geral/cross-platform para este projeto.
3. Windows nativo PDFium/GDI é promissor no Windows, mas ainda exige empacotamento e testes.

Decisão atual:

```text
Default = Electron silencioso
```

Fallback recomendado:

- Windows com problema no Electron: testar `Windows nativo (PDFium/GDI)`.
- Linux com problema no Electron: testar `Comando do sistema`.

## Filament

Foi criada uma página de configuração singleton:

```text
admin/print-settings
```

Grupo de navegação:

```text
Impressão
```

Campos:

- método de impressão
- número de cópias

Também foram adicionados recursos de visualização para:

- tabela `jobs`
- tabela `failed_jobs`

Esses resources ficam no grupo `Impressão` e são somente leitura.

## Jobs E Filas

`PrintJob` implementa `ShouldQueue`.

O projeto já possui migration padrão para:

- `jobs`
- `job_batches`
- `failed_jobs`

Arquivo:

```text
database/migrations/0001_01_01_000002_create_jobs_table.php
```

O `config/queue.php` usa database por padrão:

```php
'default' => env('QUEUE_CONNECTION', 'database')
```

## Queue Worker

O app deve usar o suporte nativo do NativePHP para iniciar automaticamente o worker da fila.

Problema:

- `PrintController` salva o PDF.
- `PrintController` despacha `PrintJob`.
- Com `QUEUE_CONNECTION=database`, a job entra na tabela `jobs`.
- Se não houver `queue:work` ou `queue:listen` rodando, a impressão não acontece.

Configuração atual:

```text
config/nativephp.php -> queue_workers.default
```

O NativePHP sobe o processo com alias:

```text
queue_default
```

Em desenvolvimento ele usa `queue:listen`; em produção, `queue:work`. O processo é criado via API interna `child-process` e marcado como persistente, então o Electron reinicia o worker se ele morrer.

O widget `QueueWorkerStatusWidget` consulta:

```text
GET child-process/get/queue_default
```

e reinicia via:

```text
POST child-process/restart
```

Se o processo ainda não existir, o serviço chama `Native\Desktop\Facades\QueueWorker::up('default')`.

Não recriar worker manual no plugin Electron; usar `config/nativephp.php` e os endpoints nativos de `child-process`.

## Cuidados De Build E Runtime

### Compilar Plugin Electron

Sempre que alterar:

```text
nativephp/electron/electron-plugin/src/**
```

rodar:

```bash
npm --prefix nativephp/electron run plugin:build
```

Depois reiniciar:

```bash
composer native:dev
```

Sem isso, os endpoints internos novos não entram no app.

### Migration No Banco NativePHP

Para aplicar no banco desktop:

```bash
NATIVEPHP_RUNNING=true php artisan migrate
```

O NativePHP usa banco próprio em runtime, não necessariamente o mesmo banco do Laravel rodando fora do desktop.

### API Interna Do NativePHP

`PrintJob` depende de:

```text
NATIVEPHP_API_URL
NATIVEPHP_SECRET
```

Essas variáveis são injetadas pelo NativePHP quando o PHP é iniciado pelo Electron.

Se chamar `PrintJob` fora do desktop, os métodos `electron` e `native_windows` vão falhar.

### Impressora Padrão

Todos os métodos atuais assumem impressora padrão do sistema, porque ainda não foi criada configuração para escolher impressora específica.

Possível evolução:

- usar endpoint já existente `GET /api/system/printers`;
- salvar `printer_name` em `print_settings`;
- enviar `printer` para os endpoints `print-file`/`print-file-native-windows`.

### Método Windows Nativo

Antes de liberar em produção:

- adicionar `windows-pdf-printer-native` em `nativephp/electron/package.json`;
- instalar/buildar no Windows;
- confirmar inclusão de `pdfium.dll`;
- testar com impressora real;
- validar licença do pacote e do PDFium no build final.

### Método Comando Do Sistema

Linux:

- precisa de `lp`;
- precisa de CUPS;
- precisa de impressora padrão configurada.

Windows:

- usa PowerShell e associação padrão de PDF;
- pode não ser realmente silencioso;
- depende do leitor PDF instalado.

## Comandos De Verificação Já Rodados

Foram rodados com sucesso durante a implementação:

```bash
vendor/bin/pint --dirty --format agent
npm --prefix nativephp/electron run plugin:build
php artisan route:list --path=print-settings --except-vendor
```

Também foram verificados lints nos arquivos alterados, sem erros.

## Observações Para Próxima IA

- Não remover os métodos de impressão sem alinhar com o usuário.
- O default deve continuar sendo `PrintMethodEnum::Electron`.
- Antes de alterar `PrintJob`, considerar que ela roda em queue e falhas devem ir para `failed_jobs`.
- O queue worker deve continuar usando o suporte nativo do NativePHP (`config/nativephp.php` -> `queue_workers`) e o alias `queue_default`.
- Não mexer em `vendor/nativephp/desktop`; customizações devem ficar em `nativephp/electron/`.
- Depois de mexer no plugin Electron, sempre rodar `npm --prefix nativephp/electron run plugin:build`.
