<?php

return [
    'widgets' => [
        'local_network' => [
            'health' => [
                'label' => 'Local API',
                'checking' => 'Checking',
                'checking_description' => 'Requesting :url from the browser.',
                'success' => 'Online',
                'failure' => 'Failed',
                'no_url' => 'Unable to build the local network URL.',
                'request_failed' => 'Failed to request :url',
                'request_succeeded' => 'Health check OK at :url',
                'unexpected_response' => 'Unexpected API response: HTTP :status',
            ],
            'ip' => [
                'label' => 'Local IP',
                'unavailable' => 'Unavailable',
                'no_url' => 'Local network URL unavailable',
            ],
            'qr_code' => [
                'label' => 'Access QR Code',
                'alt' => 'QR Code to access :url',
                'unavailable' => 'Base URL unavailable for QR Code generation.',
                'unavailable_short' => 'Unavailable',
            ],
            'host' => [
                'label' => 'Host',
                'unknown' => 'Unknown host',
                'description' => ':os | :arch | port :port',
            ],
        ],
    ],
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
        'printing' => [
            'navigation' => [
                'group' => 'Printing',
            ],
        ],
        'queue_jobs' => [
            'navigation' => [
                'label' => 'Jobs',
            ],
            'labels' => [
                'singular' => 'Job',
                'plural' => 'Jobs',
            ],
            'table' => [
                'columns' => [
                    'id' => [
                        'label' => 'ID',
                    ],
                    'display_name' => [
                        'label' => 'Job',
                    ],
                    'queue' => [
                        'label' => 'Queue',
                    ],
                    'attempts' => [
                        'label' => 'Attempts',
                    ],
                    'available_at' => [
                        'label' => 'Available at',
                    ],
                    'created_at' => [
                        'label' => 'Created at',
                    ],
                ],
            ],
        ],
        'failed_jobs' => [
            'navigation' => [
                'label' => 'Failed jobs',
            ],
            'labels' => [
                'singular' => 'Failed job',
                'plural' => 'Failed jobs',
            ],
            'table' => [
                'columns' => [
                    'id' => [
                        'label' => 'ID',
                    ],
                    'display_name' => [
                        'label' => 'Job',
                    ],
                    'queue' => [
                        'label' => 'Queue',
                    ],
                    'exception' => [
                        'label' => 'Error',
                    ],
                    'failed_at' => [
                        'label' => 'Failed at',
                    ],
                ],
            ],
        ],
    ],
];
