<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Flux content protector',
    'description' => 'Try to fix this bug https://github.com/FluidTYPO3/flux/issues/1306',
    'category' => 'plugin',
    'author' => 'Ondrej Grosko',
    'author_email' => 'ondrej@digitalwerk.agency',
    'state' => 'beta',
    'internal' => '',
    'uploadfolder' => '0',
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'version' => '0.0.1',
    'constraints' => [
        'depends' => [
            'typo3' => '8.6.0-8.7.99',
            'flux' => '*',
        ],
        'conflicts' => [],
        'suggests' => [],
    ],
];
