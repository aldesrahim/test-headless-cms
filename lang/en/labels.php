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
        'posts' => [
            'plural' => 'Posts',
        ],
        'pages' => [
            'plural' => 'Pages',
        ],
    ],
    'pagination' => [
        'overview' => '{1} Showing 1 result|[2,*] Showing :first to :last of :total results',
    ],
    'fields' => [
        'markdown_editor' => [
            'toolbar_buttons' => [
                'attach_files' => 'Attach files',
                'blockquote' => 'Blockquote',
                'bold' => 'Bold',
                'bullet_list' => 'Bullet list',
                'code_block' => 'Code block',
                'heading' => 'Heading',
                'italic' => 'Italic',
                'link' => 'Link',
                'ordered_list' => 'Numbered list',
                'redo' => 'Redo',
                'strike' => 'Strikethrough',
                'table' => 'Table',
                'undo' => 'Undo',
            ],
        ],
    ],
];
