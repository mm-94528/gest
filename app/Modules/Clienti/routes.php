<?php
return [
    'GET /clienti' => 'ClientiController@index',
    'GET /clienti/create' => 'ClientiController@create',
    'POST /clienti' => 'ClientiController@store',
    'GET /clienti/{id}' => 'ClientiController@show',
    'GET /clienti/{id}/edit' => 'ClientiController@edit',
    'PUT /clienti/{id}' => 'ClientiController@update',
    'DELETE /clienti/{id}' => 'ClientiController@destroy',
];