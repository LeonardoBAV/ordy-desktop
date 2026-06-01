<?php

return [
    'resources' => [
        'products' => [
            'navigation' => [
                'label' => 'Products',
            ],
            'labels' => [
                'singular' => 'Product',
                'plural' => 'Products',
            ],
            'form' => [
                'fields' => [
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'name' => [
                        'label' => 'Name',
                    ],
                    'stock_limit' => [
                        'label' => 'Stock limit',
                    ],
                    'unlimited' => [
                        'label' => 'Unlimited stock',
                    ],
                ],
            ],
            'table' => [
                'columns' => [
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'name' => [
                        'label' => 'Name',
                    ],
                    'stock' => [
                        'label' => 'Stock',
                    ],
                    'stock_limit' => [
                        'label' => 'Reserved',
                    ],
                    'used_quantity' => [
                        'label' => 'Used',
                    ],
                    'available_quantity' => [
                        'label' => 'Available',
                        'unlimited' => 'Unlimited',
                    ],
                    'unlimited' => [
                        'label' => 'Unlimited',
                    ],
                    'created_at' => [
                        'label' => 'Created at',
                    ],
                    'updated_at' => [
                        'label' => 'Updated at',
                    ],
                ],
            ],
            'infolist' => [
                'entries' => [],
            ],
            'filters' => [],
            'actions' => [
                'create' => [
                    'label' => 'New product',
                ],
                'edit' => [
                    'label' => 'Edit',
                ],
                'delete' => [
                    'label' => 'Delete',
                ],
                'delete_selected' => [
                    'label' => 'Delete selected',
                ],
                'import' => [
                    'label' => 'Import products',
                ],
            ],
            'import' => [
                'columns' => [
                    'sku' => [
                        'label' => 'SKU',
                    ],
                    'name' => [
                        'label' => 'Name',
                    ],
                    'stock_limit' => [
                        'label' => 'Stock limit',
                    ],
                    'unlimited' => [
                        'label' => 'Unlimited',
                    ],
                ],
                'notifications' => [
                    'completed' => [
                        'successful_rows' => '{0} No products imported.|{1} :count product imported.|[2,*] :count products imported.',
                        'failed_rows' => '{1} :count row failed.|[2,*] :count rows failed.',
                    ],
                ],
            ],
        ],
        'movements' => [
            'navigation' => [
                'label' => 'Movements',
            ],
            'labels' => [
                'singular' => 'Movement',
                'plural' => 'Movements',
            ],
            'form' => [
                'fields' => [
                    'product_id' => [
                        'label' => 'Product',
                    ],
                    'movement_uuid' => [
                        'label' => 'Movement UUID',
                    ],
                    'qty' => [
                        'label' => 'Quantity',
                    ],
                ],
            ],
            'table' => [
                'columns' => [
                    'product' => [
                        'label' => 'Product',
                    ],
                    'movement_uuid' => [
                        'label' => 'Movement UUID',
                    ],
                    'qty' => [
                        'label' => 'Quantity',
                    ],
                    'created_at' => [
                        'label' => 'Created at',
                    ],
                    'updated_at' => [
                        'label' => 'Updated at',
                    ],
                ],
            ],
            'infolist' => [
                'entries' => [],
            ],
            'filters' => [],
            'actions' => [
                'create' => [
                    'label' => 'New movement',
                ],
                'edit' => [
                    'label' => 'Edit',
                ],
                'delete' => [
                    'label' => 'Delete',
                ],
                'delete_selected' => [
                    'label' => 'Delete selected',
                ],
            ],
        ],
    ],
];
