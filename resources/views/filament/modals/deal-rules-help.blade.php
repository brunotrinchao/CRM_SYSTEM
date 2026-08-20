<div x-data="{ activeTab: 'estagios' }" class="p-1 sm:p-2 space-y-4 sm:space-y-5 text-slate-800 dark:text-slate-200 max-w-full overflow-hidden">
    {{-- Banner de Boas-Vindas --}}
    <div class="bg-gradient-to-r from-primary-600 to-primary-700 dark:from-primary-900 dark:to-primary-950 p-3.5 sm:p-4 rounded-xl text-white shadow-sm flex items-center justify-between gap-3">
        <div class="space-y-1 min-w-0">
            <h3 class="font-extrabold text-sm sm:text-base flex items-center gap-2 truncate">
                <x-filament::icon icon="heroicon-o-book-open" class="h-5 w-5 text-primary-200 shrink-0" />
                <span>Guia de Regras & Fluxos de Negócios</span>
            </h3>
            <p class="text-[11px] sm:text-xs text-primary-100 leading-snug">
                Entenda o fluxo completo, transições de status, indicadores visuais, regras de edição e automações do CRM.
            </p>
        </div>
        <div class="hidden md:flex shrink-0">
            <span class="bg-white/10 text-white text-xs px-3 py-1 rounded-full border border-white/20 font-semibold">
                CRM Helper v1.1
            </span>
        </div>
    </div>

    {{-- Navegação por Abas (Tabs) - Scroll Horizontal Suave em Telas Pequenas --}}
    <div class="flex items-center gap-1.5 p-1 bg-slate-100 dark:bg-slate-800/80 rounded-xl border border-slate-200/80 dark:border-slate-700/80 text-xs font-bold overflow-x-auto no-scrollbar scroll-smooth">
        <button
            type="button"
            @click="activeTab = 'estagios'"
            :class="activeTab === 'estagios' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold'"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all shrink-0 cursor-pointer text-xs"
        >
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4 shrink-0" />
            <span>Estágios & Ciclo</span>
        </button>

        <button
            type="button"
            @click="activeTab = 'transicoes'"
            :class="activeTab === 'transicoes' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold'"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all shrink-0 cursor-pointer text-xs"
        >
            <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-4 w-4 shrink-0" />
            <span>Mudança de Status</span>
        </button>

        <button
            type="button"
            @click="activeTab = 'edicao'"
            :class="activeTab === 'edicao' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold'"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all shrink-0 cursor-pointer text-xs"
        >
            <x-filament::icon icon="heroicon-o-lock-closed" class="h-4 w-4 shrink-0" />
            <span>Regras de Edição</span>
        </button>

        <button
            type="button"
            @click="activeTab = 'bordas'"
            :class="activeTab === 'bordas' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold'"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all shrink-0 cursor-pointer text-xs"
        >
            <x-filament::icon icon="heroicon-o-swatch" class="h-4 w-4 shrink-0" />
            <span>Indicadores de Bordas</span>
        </button>

        <button
            type="button"
            @click="activeTab = 'automacoes'"
            :class="activeTab === 'automacoes' ? 'bg-white dark:bg-slate-900 text-primary-600 dark:text-primary-400 shadow-sm font-extrabold' : 'text-slate-600 dark:text-slate-400 hover:text-slate-900 dark:hover:text-slate-200 font-semibold'"
            class="flex items-center gap-1.5 px-3 py-2 rounded-lg transition-all shrink-0 cursor-pointer text-xs"
        >
            <x-filament::icon icon="heroicon-o-bolt" class="h-4 w-4 shrink-0" />
            <span>Automações & Ferramentas</span>
        </button>
    </div>

    {{-- Aba 1: Estágios & Ciclo de Vida do Negócio --}}
    <div x-show="activeTab === 'estagios'" x-cloak class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-arrow-path" class="h-4 w-4 text-primary-500 shrink-0" />
            <span>Estágios & Ciclo de Vida do Negócio</span>
        </h4>
        
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
            {{-- Pendente --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-100 text-slate-800 dark:bg-slate-800 dark:text-slate-200 border border-slate-300 dark:border-slate-700">
                        <span class="w-2 h-2 rounded-full bg-slate-500 shrink-0"></span> Pendente
                    </span>
                    <span class="text-[10px] text-slate-400 font-semibold">Fase Inicial</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Negócio recém-criado. Permite alterar o <strong>Cliente</strong> e editar <strong>Produtos</strong>.
                </p>
            </div>

            {{-- Negociação --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-amber-200/60 dark:border-amber-900/40 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-amber-100 text-amber-900 dark:bg-amber-950 dark:text-amber-300 border border-amber-300 dark:border-amber-800">
                        <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span> Negociação
                    </span>
                    <span class="text-[10px] text-amber-600 dark:text-amber-400 font-semibold">Em Andamento</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Ativado automaticamente ao <strong>solicitar desconto</strong> ou <strong>adicionar nota/contato</strong>. Permite enviar proposta por WhatsApp.
                </p>
            </div>

            {{-- Ganho --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-emerald-200/60 dark:border-emerald-900/40 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-emerald-100 text-emerald-900 dark:bg-emerald-950 dark:text-emerald-300 border border-emerald-300 dark:border-emerald-800">
                        <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span> Ganho
                    </span>
                    <span class="text-[10px] text-emerald-600 dark:text-emerald-400 font-semibold">Concluído</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Venda fechada com sucesso! Registra automaticamente a <strong>data real de fechamento</strong>.
                </p>
            </div>

            {{-- Perdido --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-rose-200/60 dark:border-rose-900/40 shadow-sm space-y-1.5">
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-rose-100 text-rose-900 dark:bg-rose-950 dark:text-rose-300 border border-rose-300 dark:border-rose-800">
                        <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span> Perdido
                    </span>
                    <span class="text-[10px] text-rose-600 dark:text-rose-400 font-semibold">Não Fechado</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Negócio perdido. Exige que a <strong>chave de confirmação</strong> seja ativada no formulário para salvar.
                </p>
            </div>

            {{-- Cancelado --}}
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-300 dark:border-slate-700 shadow-sm space-y-1.5 sm:col-span-2 lg:col-span-2">
                <div class="flex items-center justify-between gap-2 flex-wrap sm:flex-nowrap">
                    <span class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-full text-xs font-bold bg-slate-200 text-slate-900 dark:bg-slate-800 dark:text-slate-100 border border-slate-400 dark:border-slate-600">
                        <span class="w-2 h-2 rounded-full bg-slate-700 shrink-0"></span> Cancelado
                    </span>
                    <span class="text-[10px] text-rose-500 font-extrabold uppercase">Irreversível</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Negócio cancelado. Exige confirmação explícita de ação irreversível. <strong>O botão de editar é permanentemente bloqueado</strong>.
                </p>
            </div>
        </div>
    </div>

    {{-- Aba 2: Regras de Mudança de Status --}}
    <div x-show="activeTab === 'transicoes'" x-cloak class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-4 w-4 text-primary-500 shrink-0" />
            <span>Regras de Mudança & Transição de Status</span>
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-slate-500 shrink-0"></span>
                    <span>A partir de Pendente</span>
                </div>
                <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-1 list-disc list-inside leading-relaxed">
                    <li>Pode avançar para <strong>Negociação</strong>.</li>
                    <li>Pode ser <strong>Cancelado</strong> (exige confirmação de ação irreversível).</li>
                    <li><strong class="text-rose-600 dark:text-rose-400">Bloqueio:</strong> Não pode ir direto para Ganho ou Perdido sem antes estar em Negociação.</li>
                </ul>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1.5">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-amber-500 shrink-0"></span>
                    <span>A partir de Negociação</span>
                </div>
                <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-1 list-disc list-inside leading-relaxed">
                    <li>Pode ser finalizado como <strong class="text-emerald-600 dark:text-emerald-400">Ganho</strong>.</li>
                    <li>Pode ser finalizado como <strong class="text-rose-600 dark:text-rose-400">Perdido</strong> (exige confirmação).</li>
                    <li>Pode ser <strong class="text-slate-700 dark:text-slate-300">Cancelado</strong> (exige confirmação).</li>
                    <li><strong class="text-rose-600 dark:text-rose-400">Bloqueio:</strong> Não pode retornar para Pendente.</li>
                </ul>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1.5 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <span class="w-2.5 h-2.5 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Negócios Concluídos</span>
                </div>
                <ul class="text-xs text-slate-600 dark:text-slate-400 space-y-1 list-disc list-inside leading-relaxed">
                    <li><strong>Vendedores:</strong> Não podem mover negócios já concluídos (Ganho/Perdido/Cancelado).</li>
                    <li><strong>Admins e Gerentes:</strong> Podem mover ou alterar status mediante modal de confirmação.</li>
                    <li><strong>Data de Fechamento:</strong> Preenchida automaticamente ao marcar como Ganho.</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Aba 3: Regras de Edição de Campos --}}
    <div x-show="activeTab === 'edicao'" x-cloak class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-lock-closed" class="h-4 w-4 text-primary-500 shrink-0" />
            <span>Regras de Edição de Campos</span>
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <x-filament::icon icon="heroicon-o-user" class="h-4 w-4 text-blue-500 shrink-0" />
                    <span>Edição do Cliente</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    O <strong>Cliente</strong> só pode ser trocado enquanto o negócio estiver no status <strong class="text-slate-800 dark:text-slate-200">Pendente</strong>. Em outros estágios, o campo fica congelado.
                </p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <x-filament::icon icon="heroicon-o-cube" class="h-4 w-4 text-amber-500 shrink-0" />
                    <span>Edição dos Produtos</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Os <strong>Produtos</strong> (itens e quantidades) só podem ser alterados nos estágios <strong class="text-slate-800 dark:text-slate-200">Pendente</strong> e <strong class="text-amber-600 dark:text-amber-400">Negociação</strong>.
                </p>
            </div>

            <div class="bg-slate-50 dark:bg-slate-900/60 border border-slate-200 dark:border-slate-800 p-3.5 rounded-xl space-y-1 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-2 font-bold text-xs text-slate-900 dark:text-slate-100">
                    <x-filament::icon icon="heroicon-o-user-circle" class="h-4 w-4 text-emerald-500 shrink-0" />
                    <span>Vendedor Responsável</span>
                </div>
                <p class="text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Para vendedores, o usuário é atribuído automaticamente. <strong>Admins</strong> e <strong>Gerentes</strong> podem definir ou transferir o responsável.
                </p>
            </div>
        </div>
    </div>

    {{-- Aba 4: Indicadores de Bordas na Tabela --}}
    <div x-show="activeTab === 'bordas'" x-cloak class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-swatch" class="h-4 w-4 text-primary-500 shrink-0" />
            <span>Cores das Bordas na Tabela (Solicitações de Desconto)</span>
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-2.5 sm:gap-3">
            <div class="border-l-4 border-l-amber-500 bg-amber-50/40 dark:bg-amber-950/20 border border-slate-200 dark:border-slate-800 p-3 rounded-r-xl space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-xs text-amber-900 dark:text-amber-200">
                    <span class="w-2 h-2 rounded-full bg-amber-500 shrink-0"></span>
                    <span>Borda Amarela</span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Existe uma solicitação de desconto <strong>Pendente</strong> aguardando aprovação da gerência.
                </p>
            </div>

            <div class="border-l-4 border-l-emerald-500 bg-emerald-50/40 dark:bg-emerald-950/20 border border-slate-200 dark:border-slate-800 p-3 rounded-r-xl space-y-1">
                <div class="flex items-center gap-1.5 font-bold text-xs text-emerald-900 dark:text-emerald-200">
                    <span class="w-2 h-2 rounded-full bg-emerald-500 shrink-0"></span>
                    <span>Borda Verde</span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Solicitação de desconto <strong>Aprovada</strong> pela gerência/administração.
                </p>
            </div>

            <div class="border-l-4 border-l-rose-500 bg-rose-50/40 dark:bg-rose-950/20 border border-slate-200 dark:border-slate-800 p-3 rounded-r-xl space-y-1 sm:col-span-2 lg:col-span-1">
                <div class="flex items-center gap-1.5 font-bold text-xs text-rose-900 dark:text-rose-200">
                    <span class="w-2 h-2 rounded-full bg-rose-500 shrink-0"></span>
                    <span>Borda Vermelha</span>
                </div>
                <p class="text-[11px] sm:text-xs text-slate-600 dark:text-slate-400 leading-normal">
                    Solicitação de desconto <strong>Rejeitada</strong> pela gerência.
                </p>
            </div>
        </div>
    </div>

    {{-- Aba 5: Automações e Ferramentas Rápidas --}}
    <div x-show="activeTab === 'automacoes'" x-cloak class="space-y-3">
        <h4 class="text-xs font-extrabold uppercase tracking-wider text-slate-500 dark:text-slate-400 flex items-center gap-1.5">
            <x-filament::icon icon="heroicon-o-bolt" class="h-4 w-4 text-primary-500 shrink-0" />
            <span>Automações & Ferramentas Rápidas</span>
        </h4>

        <div class="grid grid-cols-1 sm:grid-cols-2 gap-2.5 sm:gap-3 text-xs">
            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-emerald-100 text-emerald-700 dark:bg-emerald-950 dark:text-emerald-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-chat-bubble-left-right" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5 min-w-0">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Enviar Proposta via WhatsApp</h5>
                    <p class="text-slate-500 dark:text-slate-400 leading-normal">
                        Disponível em <strong>Negociação</strong>. Monta a proposta completa formatada com produtos, subtotais, descontos e fotos para envio direto no WhatsApp Web.
                    </p>
                </div>
            </div>

            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-blue-100 text-blue-700 dark:bg-blue-950 dark:text-blue-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-pencil-square" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5 min-w-0">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Adicionar Contato</h5>
                    <p class="text-slate-500 dark:text-slate-400 leading-normal">
                        Registra interações (Ligação, Reunião, WhatsApp, E-mail, Visita). Se o negócio estiver em <strong>Pendente</strong>, ele avança automaticamente para <strong>Negociação</strong>.
                    </p>
                </div>
            </div>

            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-amber-100 text-amber-700 dark:bg-amber-950 dark:text-amber-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-tag" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5 min-w-0">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Solicitar Desconto</h5>
                    <p class="text-slate-500 dark:text-slate-400 leading-normal">
                        Envia solicitação em valor ou % para o gerente. Se o negócio estiver em <strong>Pendente</strong>, ele avança automaticamente para <strong>Negociação</strong>.
                    </p>
                </div>
            </div>

            <div class="border rounded-xl p-3 bg-white dark:bg-slate-900 border-slate-200 dark:border-slate-800 flex items-start gap-3">
                <div class="p-2 rounded-lg bg-indigo-100 text-indigo-700 dark:bg-indigo-950 dark:text-indigo-400 shrink-0">
                    <x-filament::icon icon="heroicon-o-arrows-right-left" class="h-5 w-5" />
                </div>
                <div class="space-y-0.5 min-w-0">
                    <h5 class="font-bold text-slate-900 dark:text-slate-100">Transferir & Histórico</h5>
                    <p class="text-slate-500 dark:text-slate-400 leading-normal">
                        Admins e gerentes podem reatribuir o vendedor responsável. A timeline registra todo o histórico de auditoria do negócio.
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>
