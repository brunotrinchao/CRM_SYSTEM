<div wire:poll.30s class="flex items-center">
    @inject('carbon', 'Carbon\Carbon')
    {{-- Botão de Calendário no Header --}}
    <x-filament::icon-button wire:click="openAgenda" icon="heroicon-o-calendar-days" size="lg" color="gray"
        badge-color="gray" title="Agenda de Contatos e Retornos">
        {{-- <div class="relative flex items-center justify-center">
            <x-filament::icon icon="heroicon-o-calendar-days"
                class="w-5 h-5 text-gray-600 dark:gray-mute-400 group-hover:scale-110 transition-transform" />
            @if ($overdueCount > 0)
            <span class="absolute -botton-1 -left-1 flex h-2.5 w-2.5">
                <span
                    class="animate-ping absolute inline-flex h-full w-full rounded-full bg-rose-400 opacity-75"></span>
            </span>
            @endif
        </div>

        <span class="hidden sm:inline">Agenda</span> --}}
        @if ($overdueCount > 0)
            <x-slot name="badge">
                <span class="fi-color fi-color-danger fi-text-color-600 dark:fi-text-color-200 fi-badge fi-size-sm">

                    {{ $overdueCount }}
                </span>
            </x-slot>

        @endif
    </x-filament::icon-button>

    {{-- Slideover da Agenda utilizando x-filament::modal --}}
    <x-filament::modal id="agenda-slideover" slide-over :width="$isFullWidth ? 'full' : '4xl'" sticky-header
        sticky-footer>
        <x-slot name="heading">
            <div class="flex items-center justify-between gap-4 w-full">
                <div class="flex items-center gap-2">
                    <x-filament::icon icon="heroicon-o-calendar-days"
                        class="w-6 h-6 text-amber-600 dark:text-amber-400" />

                    <span class="text-base font-bold text-slate-900 dark:text-white">Agenda & Central de Retornos</span>
                </div>

                <div class="flex items-center gap-2 pr-6">
                    <button type="button" wire:click="toggleFullWidth"
                        class="p-1.5 text-slate-500 hover:text-slate-700 dark:hover:text-slate-200 hover:bg-slate-100 dark:hover:bg-slate-800 rounded-lg transition-colors"
                        title="{{ $isFullWidth ? 'Restaurar Tamanho Padrão' : 'Expandir para Tela Cheia' }}">
                        <x-filament::icon :icon="$isFullWidth ? 'heroicon-o-arrows-pointing-in' : 'heroicon-o-arrows-pointing-out'" class="w-5 h-5" />
                    </button>
                </div>
            </div>
        </x-slot>

        {{-- Navegação por Abas do Slideover --}}
        <div class="mb-4">
            <x-filament::tabs label="Agenda">
                <x-filament::tabs.item :active="$activeTab === 'atrasados'" wire:click="setActiveTab('atrasados')"
                    icon="heroicon-o-exclamation-triangle" badge="{{ $overdueCount }}" badge-color="danger">
                    Atrasados
                </x-filament::tabs.item>

                <x-filament::tabs.item :active="$activeTab === 'proximo'" wire:click="setActiveTab('proximo')"
                    icon="heroicon-o-calendar">
                    Retornos
                </x-filament::tabs.item>
            </x-filament::tabs>
        </div>

        {{-- Formulário de Lançamento de Retorno (Overlay Modal Interno) --}}
        @if ($showReturnForm && $selectedNote)
            <div
                class="mb-6 p-4 bg-amber-50 dark:bg-amber-950/40 border border-amber-200 dark:border-amber-800 rounded-xl space-y-4">
                <div class="flex items-center justify-between">
                    <h4 class="text-sm font-bold text-amber-900 dark:text-amber-200 flex items-center gap-2">
                        <x-filament::icon icon="heroicon-o-pencil-square" class="w-4 h-4" />
                        Registrar Retorno para {{ $selectedNote->deal?->client?->name ?? 'Cliente' }}
                    </h4>
                    <button type="button" wire:click="closeReturnForm"
                        class="text-xs text-slate-500 hover:text-slate-700 dark:hover:text-slate-300">
                        Cancelar
                    </button>
                </div>

                <div class="grid grid-cols-1 sm:grid-cols-2 gap-3 text-xs">
                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Canal de
                            Comunicação</label>
                        <x-filament::input.wrapper wire:model="interactionType">
                            <x-filament::input.select wire:model="status">
                                <option value="WHATSAPP">WhatsApp</option>
                                <option value="CALL">Ligação telefônica</option>
                                <option value="MEETING">Reunião</option>
                                <option value="EMAIL">E-mail</option>
                                <option value="VISIT">Visita presencial</option>
                                <option value="OTHER">Outros</option>
                            </x-filament::input.select>
                        </x-filament::input.wrapper>

                    </div>

                    <div>
                        <label class="block font-semibold mb-1 text-slate-700 dark:text-slate-300">Próximo Retorno
                            Agendado (Opcional)</label>
                        <x-filament::input.wrapper>
                            <x-filament::input type="datetime-local" wire:model="newNextFollowUpDate" />
                        </x-filament::input.wrapper>
                    </div>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-slate-700 dark:text-slate-300">Resumo da
                        Conversa / Observações</label>
                    <textarea wire:model="followUpContent" rows="2" placeholder="Descreva os detalhes da conversa..."
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900"></textarea>
                </div>

                <div>
                    <label class="block text-xs font-semibold mb-1 text-slate-700 dark:text-slate-300">Próxima Ação (Ex:
                        Enviar Proposta)</label>
                    <input type="text" wire:model="newNextAction" placeholder="Descreva a ação futura..."
                        class="w-full text-xs rounded-lg border-slate-300 dark:border-slate-700 dark:bg-slate-900">
                </div>

                <div class="flex justify-end gap-2">
                    <button type="button" wire:click="closeReturnForm"
                        class="px-3 py-1.5 text-xs font-semibold text-slate-600 bg-slate-200 dark:bg-slate-800 rounded-lg hover:bg-slate-300">
                        Cancelar
                    </button>
                    <button type="button" wire:click="saveContactReturn"
                        class="px-3 py-1.5 text-xs font-bold text-white bg-amber-600 hover:bg-amber-700 rounded-lg shadow-sm">
                        Salvar Retorno
                    </button>
                </div>
            </div>
        @endif

        {{-- Conteúdo da Aba 1: ATRASADOS --}}
        @if ($activeTab === 'atrasados')
            @if ($overdueCount === 0)
                <x-filament::empty-state icon="heroicon-o-check-circle">
                    <x-slot name="heading">
                        Nenhum contato em atraso!
                    </x-slot>
                    <x-slot name="description">
                        Todos os agendamentos estão em dia.
                    </x-slot>
                </x-filament::empty-state>
            @else
                <div class="grid grid-cols-1 {{ $isFullWidth ? 'lg:grid-cols-2' : '' }} gap-3 overflow-y-auto pr-1 max-h-[65vh]">
                    @foreach ($overdueContacts as $note)
                        @php
                            $dueDate = \Illuminate\Support\Carbon::parse($note->next_follow_up_date)->startOfDay();
                            $today = now()->startOfDay();
                            $daysOverdue = (int) $dueDate->diffInDays($today);
                            $clientName = $note->deal?->client?->name ?? 'Cliente sem nome';
                            $dealTitle = $note->deal?->title ?? 'Negócio sem título';
                            $sellerName = $note->user?->name ?? 'Vendedor';
                            $channelLabel = $note->interaction_type instanceof \App\Enums\ChannelNote ? $note->interaction_type->getLabel() : $note->interaction_type;
                        @endphp
                        <x-filament::section compact>
                            <x-slot name="heading">
                                <div class="flex items-center justify-between gap-2 w-full min-w-0">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white truncate min-w-0" title="{{ $clientName }}">{{ $clientName }}</span>
                                    <div class="shrink-0">
                                        <x-filament::badge size="sm" color="danger">
                                            Atrasado {{ $daysOverdue === 0 ? 'hoje' : ($daysOverdue === 1 ? 'há 1 dia' : "há {$daysOverdue} dias") }}
                                        </x-filament::badge>
                                    </div>
                                </div>
                            </x-slot>

                            <x-slot name="description">
                                <div class="flex items-center justify-between text-[11px] text-slate-500 dark:text-slate-400 gap-2 min-w-0">
                                    <span class="truncate min-w-0">Canal: {{ $channelLabel }}</span>
                                    <span class="shrink-0">Agendado: {{ \Illuminate\Support\Carbon::parse($note->next_follow_up_date)->format('d/m/Y H:i') }}</span>
                                </div>
                            </x-slot>

                            <div class="space-y-2 text-xs">
                                <div class="flex items-center justify-between gap-2 min-w-0">
                                    <p class="text-slate-700 dark:text-slate-300 truncate min-w-0" title="{{ $dealTitle }}">
                                        <strong>Negócio:</strong> {{ $dealTitle }}
                                    </p>
                                    @if (auth()->user()?->profile !== \App\Enums\UserProfile::USER && $sellerName)
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1 shrink-0">
                                            <x-filament::icon icon="heroicon-o-user" class="w-3.5 h-3.5" />
                                            {{ $sellerName }}
                                        </p>
                                    @endif
                                </div>

                                @if ($note->next_action)
                                    <div class="text-xs text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 p-2 rounded-lg border border-amber-200/50 dark:border-amber-900/50">
                                        <strong>Ação agendada:</strong> {{ $note->next_action }}
                                    </div>
                                @endif
                            </div>

                            <x-slot name="footer">
                                <div class="flex items-center justify-end gap-2">
                                    <x-filament::button
                                        wire:click="mountAction('viewDealAction', { record: {{ $note->deal?->id }} })"
                                        size="sm"
                                        color="gray">
                                        Negócio
                                    </x-filament::button>
                                    <x-filament::button
                                        wire:click="mountAction('addNoteAction', { deal_id: {{ $note->deal?->id }} })"
                                        size="sm">
                                        Registrar Retorno
                                    </x-filament::button>
                                </div>
                            </x-slot>
                        </x-filament::section>
                    @endforeach
                </div>
            @endif
        @endif

        {{-- Conteúdo da Aba 2: PRÓXIMO (Visão Semanal) --}}
        @if ($activeTab === 'proximo')
            <div class="space-y-4 relative">
                {{-- Seletor de Semanas --}}
                <div class="flex items-center justify-between bg-slate-50 dark:bg-slate-900 p-2.5 rounded-xl border border-slate-200 dark:border-slate-800">
                    <button type="button" wire:click="navigateWeek(-1)"
                        class="px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg">
                        <x-filament::icon icon="heroicon-o-arrow-left" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </button>

                    <div class="text-xs font-bold text-slate-800 dark:text-slate-200 flex items-center gap-2">
                        <span>Semana: {{ $weeklySchedule['startOfWeek'] }} até {{ $weeklySchedule['endOfWeek'] }}</span>
                        @if ($weekOffset !== 0)
                            <button type="button" wire:click="resetWeek" class="text-[11px] text-amber-600 underline">
                                Voltar para Hoje
                            </button>
                        @endif
                    </div>

                    <button type="button" wire:click="navigateWeek(1)"
                        class="px-2.5 py-1 text-xs font-medium text-slate-700 dark:text-slate-200 hover:bg-slate-200 dark:hover:bg-slate-800 rounded-lg">
                        <x-filament::icon icon="heroicon-o-arrow-right" class="w-5 h-5 text-gray-600 dark:text-gray-400" />
                    </button>
                </div>

                {{-- Matriz Semanal de Agendamentos (Dias x Horários) --}}
                <div class="overflow-x-auto border border-slate-200 dark:border-slate-800 rounded-xl">
                    <table class="w-full text-xs text-left border-collapse">
                        <thead class="bg-slate-100 dark:bg-slate-900 sticky top-0 z-2">
                            <tr>
                                <th class="p-2 border-b border-r border-slate-200 dark:border-slate-800 w-16 text-center text-slate-500">Hora</th>
                                @foreach ($weeklySchedule['days'] as $day)
                                    <th class="p-2 border-b border-r border-slate-200 dark:border-slate-800 text-center min-w-[100px] {{ $day['isToday'] ? 'bg-amber-100 dark:bg-amber-950/60 text-amber-900 dark:text-amber-200 font-bold' : '' }}">
                                        <div>{{ $day['dayName'] }}</div>
                                        <div class="text-[10px] text-slate-400">{{ $day['dayNumber'] }}</div>
                                    </th>
                                @endforeach
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-slate-100 dark:divide-slate-800">
                            @foreach ($weeklySchedule['hours'] as $hour)
                                <tr>
                                    <td class="p-2 border-r border-slate-200 dark:border-slate-800 font-mono text-[11px] text-center bg-slate-50 dark:bg-slate-900/50 text-slate-500">
                                        {{ sprintf('%02d:00', $hour) }}
                                    </td>
                                    @foreach ($weeklySchedule['days'] as $day)
                                        @php
                                            $dayDate = $day['date'];
                                            $contactsInSlot = $weeklySchedule['matrix'][$dayDate][$hour] ?? [];
                                            $slotCount = count($contactsInSlot);
                                            $slotKey = "{$dayDate}_{$hour}";
                                            $isSelectedSlot = $selectedSlotKey === $slotKey;
                                        @endphp
                                        <td wire:click="selectSlot('{{ $dayDate }}', {{ $hour }})"
                                            class="p-1 border-r border-slate-200 dark:border-slate-800 align-top transition-colors cursor-pointer {{ $slotCount > 0 ? 'bg-amber-50/50 dark:bg-amber-950/20 hover:bg-amber-100/50' : 'hover:bg-slate-50 dark:hover:bg-slate-800/40' }} {{ $isSelectedSlot ? 'ring-2 ring-amber-500' : '' }}">
                                            @if ($slotCount > 0)
                                                <div class="space-y-1">
                                                    @foreach (array_slice($contactsInSlot, 0, 2) as $c)
                                                        @php
                                                            $cName = $c->deal?->client?->name ?? 'Cliente';
                                                            $cChannel = $c->interaction_type instanceof \App\Enums\ChannelNote ? $c->interaction_type->getLabel() : $c->interaction_type;
                                                        @endphp
                                                        <div class="p-1 text-[10px] font-semibold rounded bg-white dark:bg-slate-800 border border-amber-200 dark:border-amber-800 shadow-2xs truncate"
                                                            title="{{ $cName }} - {{ $cChannel }}">
                                                            <div class="truncate text-slate-900 dark:text-slate-100">{{ $cName }}</div>
                                                            @if (auth()->user()?->profile !== \App\Enums\UserProfile::USER && $c->user)
                                                                <div class="text-[9px] text-slate-400 truncate">{{ $c->user->name }}</div>
                                                            @endif
                                                        </div>
                                                    @endforeach

                                                    @if ($slotCount > 2)
                                                        <div class="text-[9px] font-extrabold text-sky-700 dark:text-sky-300 text-center bg-sky-200 dark:bg-sky-900/60 rounded px-1 py-0.5">
                                                            +{{ $slotCount - 2 }} agendamentos
                                                        </div>
                                                    @endif
                                                </div>
                                            @else
                                                <div class="h-8"></div>
                                            @endif
                                        </td>
                                    @endforeach
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Modal de Agendamentos do Horário utilizando <x-filament::modal> --}}
                <x-filament::modal id="slot-contacts-modal" width="lg" sticky-header sticky-footer>
                    <x-slot name="heading">
                        <div class="flex items-center gap-2">
                            <x-filament::icon icon="heroicon-o-clock" class="w-5 h-5 text-amber-600 dark:text-amber-400" />
                            <span class="text-base font-bold text-slate-900 dark:text-white">
                                {{ count($slotContacts) }} Agendamento(s) no Horário Selecionado
                            </span>
                        </div>
                    </x-slot>

                    <div class="space-y-3 max-h-[55vh] overflow-y-auto pr-1">
                        @foreach ($slotContacts as $c)
                            <x-filament::section compact>
                                <x-slot name="heading">
                                    <span class="text-sm font-bold text-slate-900 dark:text-white">
                                        {{ $c->deal?->client?->name ?? 'Cliente sem nome' }}
                                    </span>
                                </x-slot>

                                <x-slot name="description">
                                    <span class="text-xs text-slate-500">
                                        Negócio: {{ $c->deal?->title ?? 'Sem negócio' }}
                                    </span>
                                </x-slot>

                                <div class="space-y-2 text-xs">
                                    @if (auth()->user()?->profile !== \App\Enums\UserProfile::USER && $c->user)
                                        <p class="text-[11px] text-slate-500 dark:text-slate-400 flex items-center gap-1">
                                            <x-filament::icon icon="heroicon-o-user" class="w-3.5 h-3.5" />
                                            Vendedor: {{ $c->user->name }}
                                        </p>
                                    @endif

                                    @if ($c->next_action)
                                        <div class="text-xs text-amber-800 dark:text-amber-300 bg-amber-50 dark:bg-amber-950/50 p-2 rounded-lg border border-amber-200/50 dark:border-amber-900/50">
                                            <strong>Ação agendada:</strong> {{ $c->next_action }}
                                        </div>
                                    @endif
                                </div>

                                <x-slot name="footer">
                                    <div class="flex items-center justify-end gap-2">
                                        <x-filament::button
                                            wire:click="mountAction('viewDealAction', { record: {{ $c->deal?->id }} })"
                                            size="sm"
                                            color="gray">
                                            Negócio
                                        </x-filament::button>
                                        <x-filament::button
                                            wire:click="mountAction('addNoteAction', { deal_id: {{ $c->deal?->id }} })"
                                            size="sm">
                                            Registrar Retorno
                                        </x-filament::button>
                                    </div>
                                </x-slot>
                            </x-filament::section>
                        @endforeach
                    </div>

                    <x-slot name="footer">
                        <div class="flex justify-end">
                            <x-filament::button color="gray" @click="$dispatch('close-modal', { id: 'slot-contacts-modal' })">
                                Fechar
                            </x-filament::button>
                        </div>
                    </x-slot>
                </x-filament::modal>
            </div>
        @endif

<x-slot name="footer">
    <div class="flex justify-end">
        <x-filament::button color="gray" @click="$dispatch('close-modal', { id: 'agenda-slideover' })">
            Fechar
        </x-filament::button>
    </div>
</x-slot>
</x-filament::modal>

<x-filament-actions::modals />
</div>