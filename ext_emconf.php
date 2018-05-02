<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Flux content protector',
    'description' => 'Protects flux content elements to be created/moved/copied to restricted column. Try to fix this bug https://github.com/FluidTYPO3/flux/issues/1306',
    'category' => 'be',
    'author' => 'Ondrej Grosko',
    'author_email' => 'ondrej@digitalwerk.agency',
    'author_company' => 'Digitalwerk',
    'state' => 'stable',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.2',
    'constraints' => [
        'depends' => [
            'typo3' => '8.6.0-8.7.99',
            'flux' => '> 8.0.0',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
    'autoload' => [
        'psr-4' => [
            'Digitalwerk\\DwContentProtector\\' => 'Classes',
        ]
    ],
];
