<?php

return [
    'resources' => [
        'products' => [
            'navigation' => [
                'label' => 'Produtos',
            ],
            'labels' => [
                'singular' => 'Produto',
                'plural' => 'Produtos',
            ],
            'form' => [
                'fields' => [
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'name' => [
                        'label' => 'Nome',
                    ],
                    'stock_limit' => [
                        'label' => 'Limite de estoque',
                    ],
                    'unlimited' => [
                        'label' => 'Estoque ilimitado',
                    ],
                ],
            ],
            'table' => [
                'columns' => [
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'name' => [
                        'label' => 'Nome',
                    ],
                    'stock' => [
                        'label' => 'Estoque',
                    ],
                    'stock_limit' => [
                        'label' => 'Reservado',
                    ],
                    'used_quantity' => [
                        'label' => 'Utilizado',
                    ],
                    'available_quantity' => [
                        'label' => 'Disponível',
                        'unlimited' => 'Ilimitado',
                    ],
                    'unlimited' => [
                        'label' => 'Ilimitado',
                    ],
                    'created_at' => [
                        'label' => 'Criado em',
                    ],
                    'updated_at' => [
                        'label' => 'Atualizado em',
                    ],
                ],
            ],
            'infolist' => [
                'entries' => [],
            ],
            'filters' => [],
            'actions' => [
                'create' => [
                    'label' => 'Novo produto',
                ],
                'edit' => [
                    'label' => 'Editar',
                ],
                'delete' => [
                    'label' => 'Excluir',
                ],
                'delete_selected' => [
                    'label' => 'Excluir selecionados',
                ],
                'import' => [
                    'label' => 'Importar produtos',
                ],
            ],
            'import' => [
                'columns' => [
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'name' => [
                        'label' => 'Nome',
                    ],
                    'stock_limit' => [
                        'label' => 'Limite de estoque',
                    ],
                    'unlimited' => [
                        'label' => 'Ilimitado',
                    ],
                ],
                'notifications' => [
                    'completed' => [
                        'successful_rows' => '{0} Nenhum produto importado.|{1} :count produto importado.|[2,*] :count produtos importados.',
                        'failed_rows' => '{1} :count linha falhou.|[2,*] :count linhas falharam.',
                    ],
                ],
            ],
        ],
        'movements' => [
            'navigation' => [
                'label' => 'Movimentações',
            ],
            'labels' => [
                'singular' => 'Movimentação',
                'plural' => 'Movimentações',
            ],
            'form' => [
                'fields' => [
                    'product_id' => [
                        'label' => 'Produto',
                    ],
                    'movement_uuid' => [
                        'label' => 'UUID da movimentação',
                    ],
                    'qty' => [
                        'label' => 'Quantidade',
                    ],
                ],
            ],
            'table' => [
                'columns' => [
                    'product' => [
                        'label' => 'Produto',
                    ],
                    'movement_uuid' => [
                        'label' => 'UUID da movimentação',
                    ],
                    'qty' => [
                        'label' => 'Quantidade',
                    ],
                    'created_at' => [
                        'label' => 'Criado em',
                    ],
                    'updated_at' => [
                        'label' => 'Atualizado em',
                    ],
                ],
            ],
            'infolist' => [
                'entries' => [],
            ],
            'filters' => [],
            'actions' => [
                'create' => [
                    'label' => 'Nova movimentação',
                ],
                'edit' => [
                    'label' => 'Editar',
                ],
                'delete' => [
                    'label' => 'Excluir',
                ],
                'delete_selected' => [
                    'label' => 'Excluir selecionados',
                ],
            ],
        ],
    ],
];
