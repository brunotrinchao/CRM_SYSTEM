## Current Objective
Regra: botão Excluir (Deal) não pode aparecer pra perfil USER nunca; pra outros perfis (ADMIN/MANAGER) só aparece se status === PENDING.

## Files Read
- app/Filament/Actions/SimpleActions.php — helper genérico `getViewWithEditAndDelete()`.
  DeleteAction e EditAction hoje usam o MESMO gate `$isEditable` (vindo do param `$recordAction`,
  ex: Deal usa `fn (Deal $record) => $record->status !== DealStatus::CANCELLED`). Preciso separar:
  edit continua com $isEditable; delete precisa de regra própria (mais restrita).
- Call sites de `SimpleActions::getViewWithEditAndDelete(`:
  - app/Filament/Resources/Users/Tables/UsersTable.php:54
  - app/Filament/Resources/Categories/Tables/CategoriesTable.php:48
  - app/Filament/Resources/Deals/Pages/ViewDeal.php:42
  - app/Filament/Resources/Deals/Pages/ListDeals.php:216 (getCustomViewAction, reusado por DealsKanban)
  - app/Filament/Resources/Deals/Tables/DealsTable.php:187
  - app/Filament/Resources/Clients/Tables/ClientsTable.php:91
  - app/Filament/Resources/Products/Tables/ProductsTable.php:65
  - app/Filament/Widgets/DealsPendingContactWidget.php:104
  → 4 call sites específicas de Deal (ViewDeal, ListDeals::getCustomViewAction, DealsTable, DealsPendingContactWidget)
  precisam da MESMA regra de exclusão — DRY importa.

## Decisions Taken
- Adicionar novo param opcional em `SimpleActions::getViewWithEditAndDelete()`:
  `?callable $deleteAction = null` — se null, cai no comportamento atual ($isEditable), preservando
  TODOS os outros callers (Users/Categories/Clients/Products) sem quebra.
- Regra de negócio (quem pode excluir) deve morar no Model `App\Models\Deal` (ex: método
  `canBeDeleted(): bool` usando `Auth::user()->profile` + `$this->status`), não duplicada em 4 call sites.
- UserProfile enum já importado em SimpleActions.php (linha 5) — confirma padrão de acoplamento
  app-specific já aceito nesse helper, mas prefiro manter regra no Model mesmo assim (mais correto).

## Next Steps
1. Checar app/Models/Deal.php (imports, enums já usados) pra ver onde encaixar `canBeDeleted()`.
2. Editar SimpleActions.php: no bloco `if ($isEditable) { $actions[] = DeleteAction::make()... }`
   trocar o gate de visibilidade da Delete pra usar `$deleteAction` (novo param) quando fornecido,
   senão fallback pro `$isEditable` atual.
3. Implementar `Deal::canBeDeleted(): bool`: `Auth::user()?->profile !== UserProfile::USER
   && $this->status === DealStatus::PENDING`.
4. Passar `deleteAction: fn (Deal $record) => $record->canBeDeleted()` nos 4 call sites Deal.
5. `php -l` em todos arquivos editados. Testar com Livewire/tinker se der (perfil USER != PENDING
   deve ocultar; ADMIN+PENDING deve mostrar; ADMIN+NEGOTIATING deve ocultar).
