<?php

/** @var \Laravel\Lumen\Routing\Router $router */

$router->get('/', function () use ($router) {
    return $router->app->version();
});

/* =========================== AUTENTICAÇÃO ===========================  */

$router->group(['prefix' => 'api/v1/auth'], function () use ($router) 
{
   $router->post('register', 'AuthController@register');
   $router->post('session', 'AuthController@login');
   $router->delete('session', 'AuthController@logout');
});

/* =========================== PARA USUÁRIOS NÃO LOGADOS/LOGADOS ===========================  */

//CATEGORIA
$router->group(['prefix' => 'api/v1/product'], function ($router) 
{
    $router->get('category', 'CategoryController@list');
});

//PRODUTO
$router->group(['prefix' => 'api/v1/product'], function ($router) 
{
    $router->get('item', 'ProductController@search');

    $router->group(['middleware' => 'auth'], function ($router) {
        $router->post('item', 'ProductController@sell');
        $router->put('item/{id}', 'ProductController@edit');
        $router->delete('deactivate', 'AuthController@deactivate');
    });
});

/* =========================== PARA USUÁRIOS AUTENTICADOS E (pelo menos) COMUNS ===========================  */

$router->group(['middleware' => 'auth', 'prefix' => 'api/v1/profile'], function ($router) 
{
    $router->get('data', 'UserController@show');
    $router->post('pass', 'UserController@resetPassword');
});

/* =========================== PARA USUÁRIOS AUTENTICADOS E (pelo menos) MODERADORES ===========================  */

$router->group(['middleware' => 'auth_minlevel_moderator', 'prefix' => 'api/v1/manager/moderator'], function ($router) 
{
    $router->get('user', 'ModeratorController@userlist');
    $router->get('user/{id}', 'ModeratorController@showUserResume');
    $router->delete('user/{id}', 'ModeratorController@remove');
    $router->patch('user/{id}', 'ModeratorController@activate');
});

/* =========================== PARA USUÁRIOS AUTENTICADOS E ADMINS ===========================  */

$router->group(['middleware' => 'auth_minlevel_admin', 'prefix' => 'api/v1/manager/admin'], function ($router) 
{
    $router->post('category', 'CategoryController@add');
    $router->put('category/{id}', 'CategoryController@edit');
    $router->delete('category/{id}', 'CategoryController@exclude');
    $router->patch('category/{id}', 'CategoryController@reenable');

    $router->post('moneyResume', 'AdminController@showProductsResume');
});
