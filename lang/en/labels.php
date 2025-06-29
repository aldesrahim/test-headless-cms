<?php

return [
    'form' => [
        'action' => [
            'add' => 'Add :label',
            'save' => 'Save',
            'cancel' => 'Cancel',
            'delete' => 'Delete',
            'edit' => 'Edit',
        ],
        'event' => [
            'saved' => ':label successfully saved',
            'deleted' => ':label successfully deleted',
        ],
        'helper' => [
            'delete' => [
                'warn' => 'This action cannot be reversed.',
            ],
        ],
    ],
    'panel' => [
        'heading' => [
            'create' => 'Create :label',
            'edit' => 'Edit :label',
            'delete' => 'Delete :label',
        ],
    ],
    'menu' => [
        'categories' => [
            'plural' => 'Categories',
        ],
    ],
    'pagination' => [
        'overview' => '{1} Showing 1 result|[2,*] Showing :first to :last of :total results',
    ],
];
