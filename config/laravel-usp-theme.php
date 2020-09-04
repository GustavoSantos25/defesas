
<?php

$submenu1 =  [
    [
        'text' => 'Agendar Defesa',
        'url'  => '/agendamentos/create',
    ],
    [
        'text' => 'Listar Defesas',
        'url'  => '/agendamentos',
    ],
];

$submenu2 =  [
    [
        'text' => 'Cadastrar Docente',
        'url'  => '/docentes/create',
    ],
    [
        'text' => 'Listar Docentes',
        'url'  => '/docentes',
    ],
];

return [
    'title'=> 'DEFESAS',
    'dashboard_url' => '/',
    'logout_method' => 'GET',
    'logout_url' => '/logout',
    'login_url' => '/login',
    'menu' => [
        [
            'text'    => 'Configurações',
            'url' => '/configs',
            'can' => 'admin',
        ],
        [
            'text'    => 'Agendamentos',
            'submenu' => $submenu1,
            'can' => 'admin',
        ],
        [
            'text'    => 'Docentes',
            'submenu' => $submenu2,
            'can' => 'admin',
        ],
    ],
];
