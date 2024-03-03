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
use Throwable;
use Dotenv\Dotenv;
use Omega\Application\Exceptions\SingletonException;
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
class Application extends Container implements ApplicationInterface
{
    /**
     * Singleton instance.
     *
     * @var mixed $instances Holds the singleton instances.
     */
    private static mixed $instances;

    /**
     * The application version.
     *
     * @var string
     */
    public const VERSION = '1.0.0';

    /**
     * The base path for the Omega installation.
     *
     * @var string $basePath Holds the base path for the Omega installation.
     */
    protected string $basePath;

    /**
     * Application class constructor.
     *
     * @param  ?string $basePath Holds the Omega application base path or null.
     * @return void
     */
    private function __construct( ?string $basePath = null )
    {
        if ( $basePath ) {
            $this->setBasePath( $basePath );
        }

        $this->alias( 'paths.base', fn() => $this->getBasePath() );

        $this->configure( $this->getBasePath() );
        $this->bindProviders( $this->getBasePath() );
    }

    /**
     * Get the singleton instance.
     *
     * This method returns the singleton instance of the class. If an instance
     * doesn't exist, it creates one and returns it.
     *
     * @param  ?string $basePath Holds the Omega application base path or null.
     * @return mixed Return the singleton instance.
     */
    public static function getInstance( ?string $basePath = null ) : mixed
    {
        $getCalledClass = get_called_class();

        if ( ! isset( self::$instances[ $getCalledClass ] ) ) {
            self::$instances[ $getCalledClass ] = new $getCalledClass( $basePath );
        }

        return self::$instances[ $getCalledClass ];
    }

    /**
     * @inheritdoc
     *
     * @return string Return the version number of application.
     */
    public function getVersion() : string
    {
        return static::VERSION;
    }

    /**
     * @inheritdoc
     *
     * @param  string $basePath Holds the base path for the application.
     * @return $this
     */
    public function setBasePath( string $basePath ) : self
    {
        $this->basePath = rtrim( $basePath, '\/' );

        return $this;
    }

    /**
     * @inheritdoc
     *
     * @return string Return the base path for the application.
     */
    public function getBasePath() : string
    {
        return (string)$this->basePath;
    }

    /**
     * Bootstrap the application.
     *
     * This method starts and runs the OSPress application. It handles the entire application lifecycle,
     * including session management, configuration setup, routing, and processing HTTP requests.
     *
     * @return Response Return an instance of Response representing the application's response.
     * @throws Throwable If an error occurs during application execution.
     */
    public function bootstrap() : Response
    {
        return $this->dispatch( $this->getBasePath() );
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
    private function bindProviders( string $basePath )
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

        $this->alias( Router::class, fn() => $router );

        $routes = require "{$basePath}/routes/web.php";
        $routes( $router );

        $response = $router->dispatch();

        if ( ! $response instanceof Response ) {
            $response = $this->resolve( 'response' )->content( $response );
        }

        return $response;
    }

    /**
     * Clone method.
     *
     * This method is overridden to prevent cloning of the singleton instance.
     * Cloning would create a second instance, which violates the Singleton pattern.
     *
     * @return void
     * @throws SingletonException If an attempt to clone the singleton is made.
     */
    public function __clone() : void
    {
        throw new SingletonException(
            'You can not clone a singleton.'
        );
    }

    /**
     * Wakeup method.
     *
     * This method is overridden to prevent deserialization of the singleton instance.
     * Deserialization would create a second instance, which violates the Singleton pattern.
     *
     * @return void
     * @throws SingletonException If an attempt at deserialization is made.
     */
    public function __wakeup() : void
    {
        throw new SingletonException(
            'You can not deserialize a singleton.'
        );
    }

    /**
     * Sleep method.
     *
     * This method is overridden to prevent serialization of the singleton instance.
     * Serialization would create a second instance, which violates the Singleton pattern.
     *
     * @return array Return the names of private properties in parent classes.
     * @throws SingletonException If an attempt at serialization is made.
     */
    public function __sleep() : array
    {
        throw new SingletonException(
            'You can not serialize a singleton.'
        );
    }
}
