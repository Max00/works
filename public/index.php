<?php

// Define path to application directory
defined('ROOT_PATH')
    || define('ROOT_PATH', realpath(dirname(__FILE__) . '/..'));

// Define path to application directory
defined('APPLICATION_PATH')
    || define('APPLICATION_PATH', realpath(ROOT_PATH . '/application'));

// Define path to custom validators/decorators/filters
defined('VDF_PATH')
    || define('VDF_PATH', realpath(ROOT_PATH . '/Vdf'));

// Define path to configs directory
defined('CONFIGS_PATH')
    || define('CONFIGS_PATH', APPLICATION_PATH . '/configs');

// Define path to resources directory
defined('RESOURCES_PATH')
    || define('RESOURCES_PATH', APPLICATION_PATH . '/../resources');

// Define application environment
defined('APPLICATION_ENV')
    || define('APPLICATION_ENV', (getenv('APPLICATION_ENV') ? getenv('APPLICATION_ENV') : 'development'));

// Expiration de la notice, en secondes
defined('NOTICE_EXPIRATION_SECS') || define('NOTICE_EXPIRATION_SECS', 60);

// Expiration du token, en secondes
defined('TOKEN_EXPIRATION_SECS') || define('TOKEN_EXPIRATION_SECS', 86400);     // 24H = 60*60*24

// Google Maps API key
defined('GMAPS_V3_API_KEY') || define('GMAPS_V3_API_KEY', 'AIzaSyAtImqADBOVYfbiOJblEG8b6bBi7IOhlzc');

// Ensure library/ is on include_path
set_include_path(implode(PATH_SEPARATOR, array(
    realpath(APPLICATION_PATH . '/../library'),
    realpath(ROOT_PATH),
    realpath(VDF_PATH),
    //get_include_path(),
)));

/** Zend_Application */
require_once 'Zend/Application.php';

// Create application, bootstrap, and run
$application = new Zend_Application(
    APPLICATION_ENV,
    APPLICATION_PATH . '/configs/application.ini'
);

$application->bootstrap()
            ->run();

/*
 * @todo
 * 
 * auth_token => auth_token_supervisor || auth_token_worker
 * => Update all token, based on AJAX - Set Done
 */