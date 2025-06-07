<?php
// config/modules.php
return [
    'clienti' => [
        'name' => 'Gestione Clienti',
        'description' => 'Gestione completa della base clienti',
        'version' => '1.0.0',
        'enabled' => true,
        'dependencies' => [],
        'routes_file' => 'app/Modules/Clienti/routes.php',
        'migrations' => [
            'clienti' => '002_create_clienti_table.sql',
            'contatti' => '003_create_contatti_table.sql'
        ],
        'permissions' => [
            'clienti.view' => 'Visualizza clienti',
            'clienti.create' => 'Crea clienti', 
            'clienti.edit' => 'Modifica clienti',
            'clienti.delete' => 'Elimina clienti'
        ]
    ],
    
    'offerte' => [
        'name' => 'Gestione Offerte',
        'description' => 'Creazione e gestione offerte commerciali',
        'version' => '1.0.0', 
        'enabled' => true,
        'dependencies' => ['clienti'],
        'routes_file' => 'app/Modules/Offerte/routes.php',
        'migrations' => [
            'offerte' => '004_create_offerte_table.sql',
            'righe_offerte' => '005_create_righe_offerte_table.sql'
        ],
        'permissions' => [
            'offerte.view' => 'Visualizza offerte',
            'offerte.create' => 'Crea offerte',
            'offerte.edit' => 'Modifica offerte', 
            'offerte.delete' => 'Elimina offerte',
            'offerte.approve' => 'Approva offerte'
        ]
    ],
    
    'ordini' => [
        'name' => 'Gestione Ordini',
        'description' => 'Gestione ordini clienti e fornitori',
        'version' => '1.0.0',
        'enabled' => true,
        'dependencies' => ['clienti', 'offerte'],
        'routes_file' => 'app/Modules/Ordini/routes.php', 
        'migrations' => [
            'ordini_cliente' => '006_create_ordini_cliente_table.sql',
            'ordini_fornitore' => '007_create_ordini_fornitore_table.sql',
            'righe_ordini' => '008_create_righe_ordini_table.sql'
        ],
        'permissions' => [
            'ordini.view' => 'Visualizza ordini',
            'ordini.create' => 'Crea ordini',
            'ordini.edit' => 'Modifica ordini',
            'ordini.delete' => 'Elimina ordini'
        ]
    ],
    
    'progetti' => [
        'name' => 'Gestione Progetti',
        'description' => 'Pianificazione e controllo progetti',
        'version' => '1.0.0',
        'enabled' => true,
        'dependencies' => ['clienti', 'ordini'],
        'routes_file' => 'app/Modules/Progetti/routes.php',
        'migrations' => [
            'progetti' => '009_create_progetti_table.sql',
            'interventi' => '010_create_interventi_table.sql',
            'ore_lavorate' => '011_create_ore_lavorate_table.sql'
        ],
        'permissions' => [
            'progetti.view' => 'Visualizza progetti',
            'progetti.create' => 'Crea progetti',
            'progetti.edit' => 'Modifica progetti',
            'progetti.delete' => 'Elimina progetti',
            'interventi.view' => 'Visualizza interventi',
            'interventi.create' => 'Crea interventi',
            'interventi.edit' => 'Modifica interventi'
        ]
    ],
    
    'magazzino' => [
        'name' => 'Gestione Magazzino',
        'description' => 'Controllo giacenze e movimenti',
        'version' => '1.0.0',
        'enabled' => true,
        'dependencies' => [],
        'routes_file' => 'app/Modules/Magazzino/routes.php',
        'migrations' => [
            'materiali' => '012_create_materiali_table.sql',
            'movimenti_magazzino' => '013_create_movimenti_magazzino_table.sql'
        ],
        'permissions' => [
            'magazzino.view' => 'Visualizza magazzino',
            'magazzino.edit' => 'Modifica magazzino',
            'materiali.create' => 'Crea materiali',
            'materiali.edit' => 'Modifica materiali'
        ]
    ]
];