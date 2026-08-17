<div class="p-2 space-y-6 text-slate-800 dark:text-slate-200">
    {{-- Banner de Boas-Vindas --}}
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 dark:from-primary-900 dark:to-primary-950 p-4 rounded-xl text-white shadow-sm flex items-center justify-between gap-4">
        <div class="space-y-1">
            <h3 class="font-extrabold text-base flex items-center gap-2">
                <x-filament::icon icon="heroicon-o-book-open" class="h-5 w-5 text-primary-200 shrink-0" />
                Guia de Regras & Fluxos de Negócios
            </h3>
            <p class="text-xs text-primary-100">
                Entenda o fluxo completo, indicadores visuais, regras de edição e automações do CRM.
            </p>
        </div>
        <div class="hidden sm:flex shrink-0">
            <span class="bg-white/10 text-white text-xs px-3 py-1 rounded-full border border-white/20 font-semibold">
                CRM Helper v1.0
            </span>
        </div>
    </div>

    {{-- Seção 1: Estágios & Ciclo de Vida do Negócio --}}
    <div class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4 text-primary-500" />
            1. Estágios & Ciclo de Vida do Negócio
        </h4>
        
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-3">
            {{-- Pendente --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700">
                        <span class="w-2 h-2 rounded-full bg-slate-500"></span> Pendente
                    </span>
                    <span class="text-[10px] text-slate-400 font-semibold">Fase Inicial</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Negócio recém-criado. Permite alterar o <strong>Cliente</strong> e editar <strong>Produtos</strong>.
                </p>
            </div>

            {{-- Negociação --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-amber-200/60 dark:border-amber-900/40 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500"></span> Negociação
                    </span>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Em Andamento</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Ativado automaticamente ao <strong>solicitar desconto</strong> ou <strong>adicionar nota/contato</strong>. Permite enviar proposta por WhatsApp.
                </p>
            </div>

            {{-- Ganho --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-emerald-200/60 dark:border-emerald-900/40 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500"></span> Ganho
                    </span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Concluído</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Venda fechada com sucesso! Registra automaticamente a <strong>data real de fechamento</strong>.
                </p>
            </div>

            {{-- Perdido --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-rose-200/60 dark:border-rose-900/40 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                        <span class="w-2 h-2 rounded-full bg-rose-500"></span> Perdido
                    </span>
                    <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold">Não Fechado</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Negócio perdido. Exige que a <strong>chave de confirmação</strong> seja ativada no formulário para salvar.
                </p>
            </div>

            {{-- Cancelado --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 shadow-sm space-y-1.5 col-span-1 md:col-span-2">
                <div class="flex items-center justify-between">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-100 border border-slate-400 dark:border-slate-600">
                        <span class="w-2 h-2 rounded-full bg-slate-700"></span> Cancelado
                    </span>
                    <span class="text-[10px] text-rose-500 font-extrabold uppercase">Irreversível</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Negócio cancelado. Exige confirmação explícita de ação irreversível. <strong>O botão de editar é permanentemente bloqueado</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Seção 2: Regras e Permissões de Edição --}}
    <div class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-lock-closed" class="h-4 w-4 text-primary-500" />
            2. Regras de Edição de Campos
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <x-filament::icon icon="heroicon-o-user" class="h-4 w-4 text-blue-500 shrink-0" />
                    <span>Edição do Cliente</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    O <strong>Cliente</strong> só pode ser trocado enquanto o negócio estiver no status <strong class="text-slate-800 dark:text-slate-200">Pendente</strong>. Em outros estágios, o campo fica congelado.
                </p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <x-filament::icon icon="heroicon-o-cube" class="h-4 w-4 text-amber-500 shrink-0" />
                    <span>Edição dos Produtos</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400">
                    Os <strong>Produtos</strong> (itens e quantidades) só podem ser alterados nos estágios <strong class="text-slate-800 dark:text-slate-200">Pendente</strong> e <strong class="text-amber-600 dark:text-amber-400">Negociação</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Seção 3: Indicadores de Borda da Tabela (Solicitações de Desconto) --}}
    <div class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-swatch" class="h-4 w-4 text-primary-500" />
            3. Cores das Bordas na Tabela (Estágio da Solicitação de Desconto)
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-3">
            <div class="border-l-4 border-l-amber-500 bg-amber-50/40 dark:bg-amber-950/20 border border-slate-200 dark:border-slate-800 p-3 rounded-r-xl space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-xs text-amber-900 dark:text-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500"></span>
                    <span>Borda Amarela</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-400">
                    Existe uma solicitação de desconto <strong>Pendente</strong> aguardando aprovação da gerência.
                </p>
            </div>

            <div class="border-l-4 border-l-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 border border-slate-200 dark:border-slate-800 p-3 rounded-r-xl space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-900 dark:text-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500"></span>
                    <span>Borda Verde</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-400">
                    Solicitação de desconto <strong>Aprovada</strong> pela gerência/administração.
                </p>
            </div>

            <div class="border-l-4 border-l-rose-500 bg-rose-50/40 dark:bg-rose-950/20 border border-slate-200 dark:border-slate-800 p-3 rounded-r-xl space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-xs text-rose-900 dark:text-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500"></span>
                    <span>Borda Vermelha</span>
                </div>
                <p class="text-[11px] text-slate-600 dark:text-slate-400">
                    Solicitação de desconto <strong>Rejeitada</strong> pela gerência.
                </p>
            </div>
        </div>
    </div>

    {{-- Seção 4: Automações e Ações Rápidas --}}
    <div class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-bolt" class="h-4 w-4 text-primary-500" />
            4. Automações & Ferramentas Rápidas
        </h4>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-3 text-xs">
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Enviar Proposta via WhatsApp</h5>
                    <p class="text-slate-500 dark:text-slate-400">
                        Disponível em <strong>Negociação</strong>. Monta a proposta completa formatada com produtos, subtotais, descontos e fotos para envio direto no WhatsApp Web.
                    </p>
                </div>
            </div>

            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Adicionar Contato</h5>
                    <p class="text-slate-500 dark:text-slate-400">
                        Registra interações (Ligação, Reunião, WhatsApp, E-mail, Visita). Se o negócio estiver em <strong>Pendente</strong>, ele avança automaticamente para <strong>Negociação</strong>.
                    </p>
                </div>
            </div>

            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-tag" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Solicitar Desconto</h5>
                    <p class="text-slate-500 dark:text-slate-400">
                        Envia solicitação em valor ou % para o gerente. Se o negócio estiver em <strong>Pendente</strong>, ele avança automaticamente para <strong>Negociação</strong>.
                    </p>
                </div>
            </div>

            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Transferir & Histórico</h5>
                    <p class="text-slate-500 dark:text-slate-400">
                        Admins e gerentes podem reatribuir o vendedor responsável. A timeline registra todo o histórico de auditoria do negócio.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
