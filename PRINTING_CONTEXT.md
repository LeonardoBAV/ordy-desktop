# Desktop Printing And UDP Discovery Context

Este documento registra o contexto técnico da implementação de impressão e descoberta UDP para uma próxima sessão de IA. Ele deve ser lido antes de alterar `PrintJob`, a API `print`, as configurações de impressão, a escuta UDP Android, ou a camada NativePHP/Electron.

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

## Scancode UDP Discovery

O app desktop também responde a descoberta automática feita pelos aplicativos Android na rede local.

Objetivo:

1. Android envia um broadcast UDP para descobrir o desktop.
2. Desktop escuta continuamente em uma porta fixa.
3. Desktop responde em unicast para o IP/porta de origem do Android.
4. Android usa a URL recebida para chamar a API local do desktop.

### Contrato De Rede

Entrada esperada:

```text
Protocolo: UDP
Destino Android -> Desktop: 255.255.255.255:34254
Encoding: UTF-8
Payload exato: SCANCODE_DISCOVERY_REQUEST
```

Importante:

- O payload não é JSON.
- Não há aspas.
- Não há quebra de linha.
- O desktop só deve responder quando o conteúdo for exatamente `SCANCODE_DISCOVERY_REQUEST`.

Resposta do desktop:

```json
{
  "service": "scancode-desktop",
  "url": "http://192.168.0.20:3333"
}
```

Na prática, a URL vem de:

1. `SCANCODE_DISCOVERY_URL`, se configurada.
2. Caso contrário, `App\Services\LocalNetworkService::baseUrl()`.

### Arquivos Principais Do Discovery

- `app/Console/Commands/ScancodeDiscoveryListenCommand.php`
- `app/Services/ScancodeDiscoveryService.php`
- `app/Services/ScancodeDiscoveryProcessService.php`
- `app/Filament/Widgets/ScancodeDiscoveryStatusWidget.php`
- `resources/views/filament/widgets/scancode-discovery-status-widget.blade.php`
- `tests/Feature/ScancodeDiscoveryServiceTest.php`
- `config/nativephp.php -> scancode_discovery`
- `.env.example -> SCANCODE_DISCOVERY_*`
- `lang/pt_BR/filamentphp-resources.php`
- `lang/en/filamentphp-resources.php`

### Configuração

Chaves adicionadas em `config/nativephp.php`:

```php
'scancode_discovery' => [
    'enabled' => env('SCANCODE_DISCOVERY_ENABLED', true),
    'host' => env('SCANCODE_DISCOVERY_HOST', '0.0.0.0'),
    'port' => (int) env('SCANCODE_DISCOVERY_PORT', 34254),
    'service' => env('SCANCODE_DISCOVERY_SERVICE', 'scancode-desktop'),
    'url' => env('SCANCODE_DISCOVERY_URL'),
],
```

Valores documentados no `.env.example`:

```text
SCANCODE_DISCOVERY_ENABLED=true
SCANCODE_DISCOVERY_HOST=0.0.0.0
SCANCODE_DISCOVERY_PORT=34254
SCANCODE_DISCOVERY_SERVICE=scancode-desktop
SCANCODE_DISCOVERY_URL=
```

Use `SCANCODE_DISCOVERY_URL` apenas quando for necessário forçar uma URL específica. Se ficar vazio, o app calcula o IP local e a porta do servidor NativePHP.

### Comando Listener

Comando Artisan:

```bash
php artisan app:scancode-discovery-listen
```

Comportamento:

- abre socket UDP em `SCANCODE_DISCOVERY_HOST:SCANCODE_DISCOVERY_PORT`;
- fica em loop contínuo via `stream_socket_recvfrom()`;
- responde via `stream_socket_sendto()` para o peer de origem;
- não encerra após responder;
- a opção `--once` existe apenas para teste manual.

O socket **não deve** usar `so_reuseaddr=true`. Essa opção pode permitir múltiplos listeners UDP na mesma porta em Linux e mascarar duplicações. Se outro processo já estiver ocupando `34254`, o listener deve falhar ao bindar e logar o erro.

### Processo NativePHP

O listener roda como processo filho do Electron/NativePHP com alias:

```text
scancode_discovery
```

O processo deve ser iniciado por:

```text
App\Services\ScancodeDiscoveryProcessService::ensureRunning()
```

Padrão desejado:

- usar `Native\Desktop\Facades\ChildProcess::artisan()`;
- `persistent: true`;
- nunca criar processo manualmente no plugin Electron;
- não iniciar em múltiplos lugares sem proteção.

Atualmente o start automático fica em:

```text
App\Providers\AppServiceProvider::bootScancodeDiscovery()
```

Ele só roda quando:

- `config('nativephp-internal.running')` é verdadeiro;
- a aplicação não está em console.

`NativeAppServiceProvider` deve abrir a janela NativePHP, mas **não** deve iniciar outro discovery listener. Manter o start em um único fluxo evita start duplicado.

### Proteção Contra Starts Duplicados

O `ScancodeDiscoveryProcessService` possui proteções porque o Electron registra o PID de forma assíncrona:

- flag estática por request;
- cache `scancode_discovery_starting`;
- lock `scancode_discovery:start`;
- checagem `child-process/get/scancode_discovery` antes de iniciar.

Motivo:

Quando a UI faz refresh ou navega entre páginas, várias requisições podem ocorrer próximas. Sem proteção, o app poderia tentar iniciar o listener várias vezes antes do Electron registrar o primeiro PID.

### Widget De Status

Widget:

```text
App\Filament\Widgets\ScancodeDiscoveryStatusWidget
```

View:

```text
resources/views/filament/widgets/scancode-discovery-status-widget.blade.php
```

Estados:

- `Ativa`: processo `scancode_discovery` tem PID registrado.
- `Parada`: discovery está habilitado, mas o processo não está rodando.
- `Desativada`: `SCANCODE_DISCOVERY_ENABLED=false`.
- `Indisponível`: API interna do NativePHP não está disponível, normalmente fora do app desktop.

Importante:

`Parada` não significa desativada. Se estiver parada, o desktop não está escutando UDP e não responderá aos Androids.

O widget deve consultar status, mas não deve iniciar o processo a cada renderização. O start automático fica no serviço/provider. O botão do widget pode chamar `restart()` para iniciar/reiniciar manualmente.

### Diagnóstico Do Discovery UDP

Verificar quem ocupa a porta:

```bash
ss -lunp 'sport = :34254'
```

Esperado quando o Ordy estiver saudável:

```text
php ... artisan app:scancode-discovery-listen
```

Se aparecer outro processo, por exemplo:

```text
node scripts/scancode-desktop-discovery-listener.js 34254 ...
```

então a porta está ocupada por outro listener e o processo PHP do Ordy não conseguirá estabilizar.

Ver processos relacionados:

```bash
ps -eo pid,ppid,pgid,sid,stat,lstart,cmd | awk '/scancode-discovery-listen|scancode_discovery|queue:listen|queue:work/ && !/awk/ {print}'
```

Ver cgroup de um PID:

```bash
cat /proc/<PID>/cgroup
```

Se um processo órfão de sandbox do Cursor/Chromium ficar preso e `kill`/`sudo kill` falhar, conferir o cgroup owner. Em cgroup v2, quando o usuário for dono do cgroup, é possível matar todos os processos do cgroup com:

```bash
echo 1 > /sys/fs/cgroup/<cgroup-path>/cgroup.kill
```

Exemplo que ocorreu durante a implementação:

```bash
echo 1 > /sys/fs/cgroup/user.slice/user-1000.slice/user@1000.service/app.slice/app-org.chromium.Chromium-4564.scope/cgroup.kill
```

Use esse comando com cuidado: ele mata todos os processos daquele cgroup, não apenas o listener.

### Logs

Logs esperados em `storage/logs/laravel.log`:

- `Starting scancode discovery process.`
- `Scancode discovery process start requested.`
- `Scancode discovery UDP listener started.`
- `Unable to bind scancode discovery UDP socket.`
- `Scancode discovery response sent.`

Se o PID muda a cada refresh, investigar primeiro:

1. outra aplicação ocupando `34254`;
2. listener caindo por erro de bind;
3. watchdog `persistent: true` reiniciando processo após falha;
4. start duplicado em providers/widgets.

### Testes

Teste principal:

```bash
php artisan test --compact --filter=ScancodeDiscoveryServiceTest
```

Esse teste cobre:

- payload exato `SCANCODE_DISCOVERY_REQUEST`;
- rejeição de payload com quebra de linha, espaço ou JSON;
- geração do JSON de resposta;
- override por `SCANCODE_DISCOVERY_URL`.

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

- Antes de mexer em impressão, filas, local network ou discovery UDP, ler este arquivo inteiro.
- Não remover os métodos de impressão sem alinhar com o usuário.
- O default deve continuar sendo `PrintMethodEnum::Electron`.
- Antes de alterar `PrintJob`, considerar que ela roda em queue e falhas devem ir para `failed_jobs`.
- O queue worker deve continuar usando o suporte nativo do NativePHP (`config/nativephp.php` -> `queue_workers`) e o alias `queue_default`.
- O discovery UDP deve continuar usando o alias `scancode_discovery`, a porta default `34254`, e o payload exato `SCANCODE_DISCOVERY_REQUEST`.
- `Parada` no widget de discovery significa processo ausente, não funcionalidade desativada.
- Se o PID do discovery muda em loop, verificar porta ocupada com `ss -lunp 'sport = :34254'` antes de alterar código.
- Não usar `so_reuseaddr=true` no socket UDP do discovery; isso pode mascarar listeners duplicados.
- O start automático do discovery deve ficar centralizado no `AppServiceProvider`, não duplicado no widget nem no `NativeAppServiceProvider`.
- Não mexer em `vendor/nativephp/desktop`; customizações devem ficar em `nativephp/electron/`.
- Depois de mexer no plugin Electron, sempre rodar `npm --prefix nativephp/electron run plugin:build`.
