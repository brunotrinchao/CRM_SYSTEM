# Session State — Kanban click não abre slideover

## Objetivo atual
Descobrir por que clicar no card do Kanban (deals-kanban.blade.php) não abre
o slideover/modal de visualização do Deal, enquanto o mesmo funciona na
Listagem (Tabela).

## Já feito
1. Primeira hipótese (ERRADA, testada e descartada): achei que Alpine `$dispatch`
   não marcava o evento com `__livewire`, então troquei para `$wire.dispatch`
   em `resources/views/livewire/deals-kanban.blade.php` linha 110. Rodei
   `php artisan view:clear`. Usuário confirmou que AINDA não abre.
2. Investiguei `vendor/livewire/livewire/dist/livewire.js` (`registerListeners`,
   `js/features/supportListeners.js` ~linha 14034): o handler NÃO exige
   `__livewire` para chamar `component.$wire.call("__dispatch", name, e.detail)`.
   Ou seja, tanto `$dispatch` (Alpine puro) quanto `$wire.dispatch` deveriam
   ter funcionado igualmente bem — minha hipótese original estava errada.
3. Montei ambiente de teste real com Playwright (chromium instalado em
   `~/.cache/ms-playwright`, pacote node em `/tmp/node_modules`). Criei
   usuário de teste no banco: `debug-test@example.com` / `debugpass123`,
   `profile = ADMIN` (id=7 no banco `users`... CUIDADO: não confundir com
   deal id=7 usado no teste, são tabelas diferentes). **Este usuário de teste
   deve ser removido depois**: `User::where('email','debug-test@example.com')->delete()`.
4. Script Playwright em `/tmp/kanban_click_test2.js` faz login, vai pra
   `/deals`, troca pra Kanban, clica no PRIMEIRO card escopado dentro de
   `div[x-show="activeView === 'kanban'"] .cursor-pointer`, captura toda
   requisição/resposta Livewire (`/livewire-2b333667/update`) e salva em
   `/tmp/captured_traffic.json`.
5. **EVIDÊNCIA CHAVE** (já capturada, ver `/tmp/captured_traffic.json`):
   - O clique DISPARA corretamente a call Livewire:
     `"calls":[{"method":"__dispatch","params":["open-deal-view",{"id":7}],"metadata":{}}]`
     → confirma que o fix do `$wire.dispatch` está funcionando em enviar o evento.
   - A resposta mostra `effects.dispatches` contendo
     `sync-action-modals` com `newActionNestingIndex: null` e
     `mountedActions` do componente ListDeals fica **vazio** (`[]`) na
     snapshot final.
   - Analisei `vendor/filament/actions/src/Concerns/InteractsWithActions.php`:
     `mountAction()` (linha 138) sempre dá `push` em `$this->mountedActions`
     primeiro, mas se `getMountedAction()` lançar `ActionNotResolvableException`
     (linha 148, vira `$action = null`), ou a action estiver `isDisabled()`
     (158), ou lançar `Halt`/`Cancel`/`ValidationException` (196-208), ele
     chama `unmountAction(cancelParentActions: false)` — **e `unmountAction()`
     TAMBÉM chama `syncActionModals()` no final (linha ~800)**, o que explica
     o `newActionNestingIndex: null` observado (mountedActions já vazio
     quando o evento foi disparado). CONCLUSÃO: a action `custom_view` está
     sendo montada e IMEDIATAMENTE desmontada por uma dessas condições de
     falha — o modal nunca chega a abrir. Isso é diferente da minha hipótese
     original.

## Próximo passo (NÃO EXECUTADO AINDA)
Adicionar instrumentação temporária em
`app/Filament/Resources/Deals/Pages/ListDeals.php::openDealView()`
envolvendo o `mountAction()` num try/catch que loga (`Log::error`/`dump`)
qualquer exceção ou o estado de `$this->mountedActions` antes/depois, para
descobrir QUAL das condições (action null / disabled / Halt / Cancel /
ValidationException) está derrubando a action. Depois reproduzir o clique
via `/tmp/kanban_click_test2.js` (ou variante) e ler `storage/logs/laravel.log`.

Hipótese mais provável a investigar primeiro: a action `custom_view`
registrada em `getActions()` da página pode não estar sendo reconhecida
como "mountable" (nome duplicado com a action `custom_view` do
`DealsTable.php::recordActions`), OU a closure `->record()` não está
resolvendo o Deal (ex.: `$action->getModel()` retorna null nesse contexto
de página vs contexto de tabela), fazendo `getMountedAction()` falhar
silenciosamente.

## Arquivos relevantes
- `resources/views/livewire/deals-kanban.blade.php` (linha 110, já com o
  fix `$wire.dispatch`, MANTER)
- `app/Livewire/DealsKanban.php` (método `openDealView()` não é mais
  usado pelo blade atual, dead code, NÃO mexer sem necessidade)
- `app/Filament/Resources/Deals/Pages/ListDeals.php` (linha 46-50,
  `openDealView(int $id)` chama `mountAction('custom_view', ['record'=>$id])`)
- `app/Filament/Actions/SimpleActions.php` (linha ~198-260,
  `getViewWithEditAndDelete()` cria a action `custom_view` com
  `->record(function(array $arguments, Action $action) use ($model): ?Model {...})`)
- `app/Filament/Resources/Deals/Tables/DealsTable.php` (linha 145-146,
  `->recordAction('custom_view')` + `->recordActions([...])` — este é o
  caminho que FUNCIONA na tabela)
- `vendor/filament/actions/src/Concerns/InteractsWithActions.php`
  (`mountAction()` linha 138, `syncActionModals()` linha ~821,
  `getMountedAction()` linha 501, `resolveActions()` linha 537 — ainda
  não lido em detalhe)

## Ferramentas de teste montadas (reaproveitar, não recriar)
- `php artisan serve` já rodando em `127.0.0.1:8000`
- Playwright instalado em `/tmp/node_modules` (`cd /tmp && node script.js`)
- Chromium baixado em `~/.cache/ms-playwright`
- Login de teste: `debug-test@example.com` / `debugpass123` (profile ADMIN)
- Script de reprodução: `/tmp/kanban_click_test2.js`
- Log completo da última captura: `/tmp/captured_traffic.json`

## Lembrete de limpeza (fazer no final)
- Deletar usuário de teste `debug-test@example.com` do banco.
- Remover instrumentação de debug temporária adicionada em ListDeals.php.
- Remover scripts em `/tmp/*.js` e `/tmp/node_modules` se quiser (opcional,
  fora do repo, não afeta o projeto).
