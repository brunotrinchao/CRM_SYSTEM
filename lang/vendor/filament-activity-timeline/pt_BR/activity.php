<?php

declare(strict_types=1);

return [
    'model' => [
        'singular' => 'Atividade',
        'plural' => 'Atividades',
    ],
    'sections' => [
        'changes' => 'Alterações',
    ],
    'fields' => [
        'event' => 'Evento',
        'created_at' => 'Data',
        'subject' => 'Registro',
        'subject_type' => 'Tipo',
        'causer' => 'Usuário',
        'log_name' => 'Log',
        'description' => 'Descrição',
        'date_from' => 'De',
        'date_until' => 'Até',
        // Deal fields
        'status' => 'Status',
        'title' => 'Título',
        'notes' => 'Anotações',
        'product_id' => 'Produto',
        'quantity' => 'Quantidade',
        'client_id' => 'Cliente',
        'discount' => 'Desconto',
        'total_value' => 'Valor Total',
        'probability' => 'Probabilidade',
        'expected_close_date' => 'Previsão de Fechamento',
        'actual_close_date' => 'Fechamento Real',
        'last_contact_date' => 'Último Contato',
        'loss_reason' => 'Motivo da Perda',
        'user_id' => 'Responsável',
        // Client fields
        'name' => 'Nome',
        'email' => 'E-mail',
        'phone' => 'Telefone',
        'cellphone' => 'Celular',
        'origin' => 'Origem',
        'description' => 'Descrição',
        // Product fields
        'category_id' => 'Categoria',
        'sku' => 'SKU',
        'price' => 'Preço',
        'observation' => 'Observação',
        'current_stock' => 'Estoque Atual',
        'minimum_stock' => 'Estoque Mínimo',
        'active' => 'Ativo',
        // DealNote fields
        'interaction_type' => 'Tipo de Interação',
        'content' => 'Conteúdo',
        'next_follow_up_date' => 'Próximo Contato',
        'next_action' => 'Próxima Ação',
        'contact_date' => 'Data do Contato',
        // DiscountRequest fields
        'requested_by' => 'Solicitado por',
        'amount' => 'Valor',
        'reason' => 'Motivo',
        'reviewed_by' => 'Revisado por',
        'reviewed_at' => 'Data da Revisão',
        'review_note' => 'Nota da Revisão',
        'original_discount' => 'Desconto Original',
        'type' => 'Tipo de Desconto',
        'deal_id' => 'Negócio',
    ],
    'events' => [
        'created' => 'criou',
        'updated' => 'atualizou',
        'deleted' => 'excluiu',
        'restored' => 'restaurou',
    ],
    'boolean' => [
        'true' => 'sim',
        'false' => 'não',
    ],
    'action' => [
        'label' => 'Atividade',
        'heading' => 'Histórico de Atividades',
        'close' => 'Fechar',
    ],
    'resource' => [
        'navigation' => [
            'label' => 'Atividades',
        ],
    ],
    'placeholder' => '—',
];
