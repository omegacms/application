<?php
/**
 * Part of Omega CMS - Application Package
 *
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license    https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 */

/**
 * @declare
 */
declare( strict_types = 1 );

/**
 * @namespace
 */
namespace Omega\Application;

/**
 * @use
 */
use function method_exists;
use function session_start;
use function session_status;
use Throwable;
use Dotenv\Dotenv;
use Omega\Container\Container;
use Omega\Http\Response;
use Omega\Routing\Router;

/**
 * Base application class.
 *
 * This `Application` class represents the main entry point of the Omega framework.
 * It manages the application's lifecycle, including configuration, routing, and
 * handling HTTP requests.
 *
 * @category    Omega
 * @package     Omega\Application
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class Application extends Container
{
    use SingletonTrait;

    /**
     * Run the application.
     *
     * This method starts and runs the OSPress application. It handles the entire application lifecycle,
     * including session management, configuration setup, routing, and processing HTTP requests.
     *
     * @return Response Return an instance of Response representing the application's response.
     * @throws Throwable If an error occurs during application execution.
     */
    public function run() : Response
    {
        if ( session_status() !== PHP_SESSION_ACTIVE ) {
            session_start();
        }

        $basePath = $this->resolve( 'paths.base' );

        $this->configure( $basePath );
        $this->bindProviders( $basePath );

        return $this->dispatch( $basePath );
    }

    /**
     * Configure the application.
     *
     * This method sets up the application's configuration by loading environment
     * variables from Dotenv.
     *
     * @param  string $basePath Holds the base path of the application.
     * @return void
     */
    private function configure( string $basePath ) : void
    {
        $dotenv = Dotenv::createImmutable( $basePath );
        $dotenv->load();
    }

    /**
     * Bind providers to the application.
     *
     * This method binds service providers to the application, allowing them
     * to register services and perform any necessary setup.
     *
     * @param  string $basePath The base path of the application.
     * @return void
     */
    private function bindProviders( string $basePath ) : void
    {
        $providers = require "{$basePath}/config/providers.php";

        foreach ( $providers as $provider ) {
            $instance = new $provider;

            if ( method_exists( $instance, 'bind' ) ) {
                $instance->bind( $this );
            }
        }
    }

    /**
     * Dispatch the application.
     *
     * This method dispatches the application, including routing setup and
     * handling of HTTP requests.
     *
     * @param  string $basePath The base path of the application.
     * @return Response An instance of Response representing the application's response.
     * @throws Throwable If an error occurs during dispatching.
     */
    private function dispatch( string $basePath ) : Response
    {
        $router = new Router();

        $this->bind( Router::class, fn() => $router );

        $routes = require "{$basePath}/routes/web.php";
        $routes( $router );

        $response = $router->dispatch();

        if ( ! $response instanceof Response ) {
            $response = $this->resolve( 'response' )->content( $response );
        }

        return $response;
    }
}