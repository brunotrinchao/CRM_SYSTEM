<?php

namespace App\Filament\Actions;

use App\Enums\UserProfile;
use Filament\Actions\Action;
use Filament\Actions\ActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Support\Colors\Color;
use Filament\Support\Enums\Size;
use Filament\Support\Enums\Width;
use Filament\Support\Icons\Heroicon;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use ToneGabes\Filament\Icons\Enums\Phosphor;

class SimpleActions
{
    /**
     * Retorna uma Ação de Criação (Create) configurada para abrir em Modal ou SlideOver.
     *
     * @param Width $width Largura do modal.
     * @param callable $schemaCallback Retorna o array de componentes do formulário.
     * @param callable $actionCallback Lógica customizada de salvamento (opcional).
     * @param string $recordName Nome amigável do registro para exibição.
     * @param bool $modal Se verdadeiro abre em Modal central, se falso abre em SlideOver lateral.
     * @param string|null $model Modelo dono do formulário do modal (resolve relações corretamente).
     * @param callable|null $afterCreate Callback executado após criar, recebe (Model $record, object $livewire).
     *                                    Use para setar o campo no form pai (ex: selecionar o produto criado).
     */
    public static function getCreateModal(
        Width $width,
        callable $schemaCallback,
        ?callable $actionCallback = null,
        string $recordName = 'Novo',
        bool $modal = false,
        ?string $model = null,
        ?callable $afterCreate = null,
        array|callable|null $defaults = null,
        ?string $buttonColor = 'primary',
        string $name = 'custom_create',
        string $labelButton = 'Cadastrar',
        bool $disabled = false,
        Phosphor | Heroicon $iconButton = Phosphor::Plus
    ): Action {
        $modalIcon = new ($model);
        $action = Action::make($name)
            ->label("{$recordName}")
            ->disabled($disabled)
            ->modalWidth($width)
            ->modalIcon($modalIcon->getIcon())
            ->modalIconColor('primary')
            ->modalCancelAction(false)
            ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
            ->modalSubmitAction(
                fn($action) => $action
                    ->label($labelButton)
                    ->icon(Phosphor::Check)
                    ->color(Color::Blue)
                    ->size(Size::ExtraLarge)
            )
            ->modalCancelAction(false)
            ->icon($iconButton)
            ->color($buttonColor)
            ->schema($schemaCallback)
            ->stickyModalFooter()
            ->stickyModalHeader()
            ->slideOver(!$modal)
            ->successNotificationTitle("{$recordName} criado com sucesso.")
            ->failureNotificationTitle("Não foi possível criar {$recordName}.");

        // Define o modelo dono do formulário no modal.
        // Sem isso, o modal herda o modelo do form pai (ex: abrir "Novo Produto"
        // dentro do form de Deal faria o Select de categoria resolver em Deal, que
        // não tem a relação -> quebra com LogicException).
        if ($model) {
            $action->model($model);
        }

        // Se houver uma lógica customizada de salvamento, intercepta a criação
        if ($actionCallback) {
            $action->action(function (array $data, Action $action) use ($actionCallback, $recordName, $afterCreate, $defaults) {
                // Merge defaults (estáticos ou closures) no data antes do callback
                if ($defaults !== null) {
                    $resolved = is_callable($defaults) ? call_user_func($defaults) : $defaults;
                    $data = array_merge($resolved, $data);
                }

                $record = call_user_func($actionCallback, $data);

                // Após criar, permite setar campos no form pai (ex: produto selecionado no deal).
                // $action->getLivewire() = componente que abriu o modal
                // (ex: CreateDeal/EditDeal), cujo form contém o campo select.
                if ($afterCreate && $record instanceof \Illuminate\Database\Eloquent\Model) {
                    call_user_func($afterCreate, $record, $action->getLivewire());
                }

                $action->getLivewire()->dispatch('$refresh');
            });
        }

        return $action;
    }

    /**
     * Seta um campo no form do componente "pai" que abriu o modal de criação.
     *
     * Suporta dois contextos:
     * - Modal aninhado (ex: Deal → modal Produto → modal Categoria): o campo pertence
     *   ao form da AÇÃO PAI, que fica no state path "mountedActions.{i}.data". Detectamos
     *   via getMountedActions() e usamos data_set diretamente no livewire (a página top).
     * - Form direto da página (ex: CreateDeal/EditDeal): setamos via getForm('form').
     *
     * Isso evita "getForm does not exist" quando o livewire é uma página de listagem
     * (ListDeals/ListProducts) que não expõe form próprio.
     */
    public static function setFieldOnParentForm(object $livewire, string $field, mixed $value): void
    {
        $mounted = method_exists($livewire, 'getMountedActions')
            ? $livewire->getMountedActions()
            : [];

        // Modal aninhado: a última ação da pilha é a atual (modal aberto), a penúltima é o
        // form pai que contém o campo. Ex: [deal, product] → product montou; pai = deal(0).
        if (count($mounted) >= 2) {
            $parentIndex = count($mounted) - 2;

            data_set($livewire, "mountedActions.{$parentIndex}.data.{$field}", $value);

            return;
        }

        // Form direto da página (CreateDeal/EditDeal/...)
        if (method_exists($livewire, 'getForm') && ($form = $livewire->getForm('form'))) {
            $form->getComponent($field)?->state($value);
        }
    }

    public static function getViewEditAction(
        Width $width,
        callable $schemaCallback,
        callable $actionCallback,
        ?string $model = null,
        string $recordName = 'Visualizar',
        bool $modal = false,
        array $relations = []
    ): Action {
        $modalIcon = new ($model);
        $action = Action::make('edit_modal_action')
            ->label("Editar")
            ->stickyModalHeader()
            ->modalHeading("Editar")
            ->modalWidth($width)
            ->modalIcon($modalIcon->getIcon())
            ->modalIconColor(Color::Orange)
            ->modalCancelAction(false)
            ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
            ->modalSubmitAction(
                fn($action) => $action
                    ->label('Salvar')
                    ->icon(Phosphor::Check)
                    ->color(Color::Green)
                    ->size(Size::ExtraLarge)
            )
            ->icon(Phosphor::PencilLine)
            ->color('warning')
            ->fillForm(fn(Model $record): array => $record->load($relations)->toArray())
            ->schema($schemaCallback)
            ->stickyModalFooter()
            ->stickyModalHeader()
            ->action(function (Model $record, array $data, \Filament\Schemas\Schema $schema, $livewire) use ($actionCallback, $recordName, $relations) {
                // Executa a lógica de salvamento/atualização passada por callback
                call_user_func($actionCallback, $record, $data);

                // Action::make não chama saveRelationships automaticamente (só EditAction faz isso).
                $schema->model($record)->saveRelationships();

                // Atualiza mountedActions.0.data (ViewAction pai) com o record fresco,
                // para que o infolist re-renderize com os dados atualizados sem fechar o modal.
                $record->refresh();
                if ($relations) {
                    $record->load($relations);
                }
                data_set($livewire, 'mountedActions.0.data', $record->toArray());

                $livewire->dispatch('$refresh');
            })
            ->slideOver(!$modal)
            ->successNotificationTitle("{$recordName} editado com sucesso.")
            ->failureNotificationTitle("Não foi possível editar {$recordName}.");

        if ($model) {
            $action->model($model);
        }

        return $action;
    }

    public static function getViewWithEditAndDelete(
        Width $width,
        callable $schemaCallback,       // Schema do Form (Edição)
        callable $actionCallback,
        ?string $model = null,
        ?callable $schemaViewCallback = null, // Schema do Infolist (Visualização)
        string $recordName = 'Registro',
        ?callable $recordAction = null,
        bool $modal = false,
        array $relations = [],
        array $extraFooterActions = []  // Actions adicionais no rodapé (ex: timeline)
    ): ViewAction {
        $modalIcon = new ($model);
        $action = ViewAction::make('custom_view')
            ->label("Visualizar")
            ->modalHeading("Visualizar")
            ->modalWidth($width)
            ->modalIcon($modalIcon->getIcon())
            ->modalCancelAction(false)
            ->icon('heroicon-o-information-circle')
            ->color(Color::Neutral)
            ->record(function (array $arguments, Action $action) use ($model): ?Model {
                $recordId = $arguments['record'] ?? $arguments['recordKey'] ?? null;
                if ($recordId) {
                    $modelClass = $action->getModel() ?? $model;
                    if ($modelClass && class_exists($modelClass)) {
                        return $modelClass::find($recordId);
                    }
                }
                return null;
            })
            // Se houver um schema de visualização específico (Infolist), usa ele; senão usa o form
            ->schema($schemaViewCallback ?? $schemaCallback)
            ->fillForm(fn(?Model $record): array => $record ? $record->load($relations)->toArray() : [])
            ->slideOver(!$modal)
            ->stickyModalFooter()
            ->stickyModalHeader()
            ->modalSubmitAction(false)
            ->modalCancelAction(false)
            ->modalFooterActionsAlignment(\Filament\Support\Enums\Alignment::End)
            // extraModalFooterActions suporta ActionGroup (prepareModalActionGroup).
            // modalFooterActions NÃO suporta — chama prepareModalAction(Action) sem checar ActionGroup.
            ->extraModalFooterActions(function (?Model $record) use ($actionCallback, $recordAction, $recordName, $width, $schemaCallback, $modal, $model, $relations, $extraFooterActions) {
                if (! ($record instanceof Model) || ! $record->exists) {
                    return [];
                }

                $record->load($relations);

                $isEditable = true;
                if ($recordAction) {
                    try {
                        $isEditable = (bool) call_user_func($recordAction, $record);
                    } catch (\Throwable $e) {
                        $isEditable = true;
                    }
                }

                // Ações agrupadas: extras (tudo exceto excluir/salvar/cancelar)
                $grouped = [];
                foreach ($extraFooterActions as $extraAction) {
                    try {
                        $res = $extraAction instanceof \Closure
                            ? call_user_func($extraAction, $record)
                            : $extraAction;
                        if ($res) {
                            $grouped[] = $res;
                        }
                    } catch (\Throwable $e) {
                        // Ignore individual footer action closure failure
                    }
                }

                // Footer usa flex-row-reverse (align-end): último no DOM = mais à esquerda visual.
                // Ordem DOM: ActionGroup → Edit → Delete
                // Ordem visual (invertida): Delete | Edit | ActionGroup — todos agrupados à direita.
                $actions = [];

                if ($isEditable) {
                    $actions[] = DeleteAction::make()
                        ->size(Size::ExtraLarge)
                        ->modalHeading(fn(Model $rec) => "Excluir {$recordName}")
                        ->icon(Phosphor::Trash)
                        ->color(Color::Red)
                        ->successNotificationTitle("{$recordName} excluído com sucesso.")
                        ->failureNotificationTitle("Não foi possível excluir {$recordName}.")
                        ->after(fn ($livewire) => $livewire->dispatch('$refresh'));
                }

                if ($isEditable) {
                    $actions[] = self::getViewEditAction(
                        width: $width,
                        schemaCallback: $schemaCallback,
                        actionCallback: $actionCallback,
                        model: $model,
                        recordName: $recordName,
                        modal: $modal,
                        relations: $relations
                    )
                    // Sem ->record($record), getMountedActionSchemaModel resolve Deal::class (string)
                    // → getModelInstance() = new Deal(exists=false) → guard "!record.exists" no
                    // saveRelationships() barra o callback antes de getState() sobrescrever o estado.
                    ->record($record)
                    ->size(Size::ExtraLarge);
                }

                if (! empty($grouped)) {
                    $actions[] = ActionGroup::make($grouped)
                        ->color(Color::Neutral)
                        ->size(Size::ExtraLarge)
                        ->label('Ações')
                        ->button()
                        ->dropdownPlacement('top-start');
                }

                return $actions;
            });

        if ($model) {
            $action->model($model);
        }

        return $action;
    }

    public static function getViewInfolist(
        Width $width,
        ?callable $schemaViewCallback = null, // Schema do Infolist (Visualização)
        ?string $model = null,
        string $recordName = 'Registro',
        bool $modal = false,
        array $relations = []
    ): ViewAction {
        $modalIcon = new ($model);
        $action = ViewAction::make('custom_view')
            ->label("Visualizar")
            ->modalHeading("Visualizar")
            ->modalWidth($width)
            ->modalIcon($modalIcon->getIcon())
            ->icon('heroicon-o-information-circle')
            ->color(Color::Neutral)
            // Resolve o record correto (ex: client do infolist) para que a autorização
            // (getViewAuthorizationResponse) e o schema do modal tenham um Model válido.
            ->record(function (Action $action): ?Model {
                $schemaComponent = $action->getSchemaComponent();

                if (!$schemaComponent) {
                    return null;
                }

                $record = $schemaComponent->getRecord();

                // Quando o action é suffix de uma entry de relação (ex: client.name),
                // o record do container é o model pai (Deal); resolvemos o relacionado
                // pelo nome da relação (primeiro segmento do nome da entry).
                // Usamos o accessor Eloquent para que o lazy-load funcione mesmo sem
                // a relação ter sido eager-loaded pelo infolist.
                if ($record instanceof Model) {
                    $relationName = (string) str($schemaComponent->getName())->before('.');

                    if (filled($relationName) && $record->getRelationValue($relationName) instanceof Model) {
                        return $record->getRelationValue($relationName);
                    }
                }

                return $record;
            })
            // Schema de visualização (Infolist)
            ->schema($schemaViewCallback)
            ->fillForm(fn(Model $record): array => $record->load($relations)->toArray())
            ->slideOver(!$modal)
            ->stickyModalFooter()
            ->stickyModalHeader();

        if ($model) {
            $action->model($model);
        }

        return $action;
    }
}