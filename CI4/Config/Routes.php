<?php 
namespace Config;

// Create a new instance of our RouteCollection class.
$routes = Services::routes();

// Load the system's routing file first, so that the app and ENVIRONMENT
// can override as needed.
if (file_exists(SYSTEMPATH . 'Config/Routes.php'))
{
    require SYSTEMPATH . 'Config/Routes.php';
}

/**
 * --------------------------------------------------------------------
 * Router Setup
 * --------------------------------------------------------------------
 */
$routes->setDefaultNamespace('App\Controllers');
$routes->setDefaultController('Home');
$routes->setDefaultMethod('index');
$routes->setTranslateURIDashes(false);
$routes->set404Override();
$routes->setAutoRoute(false);

/**
 * --------------------------------------------------------------------
 * Route Definitions
 * --------------------------------------------------------------------
 */

// We get a performance increase by specifying the default

/* Use GeniusCardSaveAuthFilter filter in routes to allow it for specific Roles, e.g: 
    
    -------------------------------------------------------------------
    -------------------------------------------------------------------

    1. You will need to pass the Roles that are allowed to access the route, by default it will be admin. 
    2. Params are passed like this: GeniusCardSaveAuthFilter:Admin,User,etc. you may refer the e.g: 2 on how to pass params. 

    e.g 1: $routes->put('update-user-profile', '\Modules\Base\Controllers\Base_controller::saveUserProfileData',['filter' => 'GeniusCardSaveAuthFilter']);

    e.g 2: $routes->put('update-user-profile', '\Modules\Base\Controllers\Base_controller::saveUserProfileData',['filter' => 'GeniusCardSaveAuthFilter:Admin,User']);

    -------------------------------------------------------------------
    -------------------------------------------------------------------

*/


/** Authenticated APIs **/
$routes->group('api', function ($routes) {
    $routes->post('upload-file', '\App\Controllers\S3_controller::uploadFile');
    $routes->add('upload-file/(:any)', '\App\Controllers\S3_controller::uploadFile/$1',['PATCH','HEAD','DELETE']);
});




/**
 * --------------------------------------------------------------------
 * Additional Routing
 * --------------------------------------------------------------------
 *
 * There will often be times that you need additional routing and you
 * need it to be able to override any defaults in this file. Environment
 * based routes is one such time. require() additional route files here
 * to make that happen.
 *
 * You will have access to the $routes object within that file without
 * needing to reload it.
 */
if (file_exists(APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php'))
{
    require APPPATH . 'Config/' . ENVIRONMENT . '/Routes.php';
}
