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
use function Omega\Helpers\env;
use function Omega\Helpers\join_paths;
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
class Application extends Container implements ApplicationInterface
{
    use SingletonTrait;
    //use WebApplicationTrait;

    /**
     * The Omega framework version.
     *
     * @var string
     */
    private const VERSION = '1.0.0';

    /**
     * The custom application path defined by the developer.
     * 
     * @var ?string $appPath Holds the custom application path defined by developer.
     */
    protected ?string $applicationPath;

    /**
     * The base path for the OmegaCMS installation.
     * 
     * @var ?string $basePath Holds the base path for the OmegaCMS installation.
     */
    protected ?string $basePath;

    /**
     * The custom application path defined by the developer.
     * 
     * @var ?string $bootstrapPath Holds the custom application path defined by developer.
     */
    protected ?string $bootstrapPath;

    /**
     * The custom configuration path defined by the developer.
     * 
     * @var ?string $configPath Holds the custom configuration path defined by the developer.
     */
    protected ?string $configPath;
    #endregion

    /**
     * The custom database path defined by the developer.
     * 
     * @var ?string $databasePath Holds the custom database path defined by the develiper.
     */
    protected ?string $databasePath;

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string $environmentFile Holds the environment file to load during bootstrapping.
     */
    protected string $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     * 
     * @var ?string $environmentPath Holds the custom environment path defined by the developer.
     */
    protected ?string $environmentPath;

    /**
     * The custom language path defined by the developer.
     * 
     * @var ?string $environmentPath Holds the custom language path defined by the developer.
     */
    protected ?string $langPath;

    /**
     * The custom public path defined by the developer.
     * 
     * @var ?string $environmentPath Holds the custom public path defined by the developer.
     */
    protected ?string $publicPath;

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
     * Get the version of the application.
     * 
     * @return string Return the version of the application.
     */
    public function getVersion() : string
    {
        return static::VERSION;
    }

    /**
     * Get the path to the application 'app' directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path for 'app' directory.
     */
    public function getApplicationPath( ?string $path = '' ) : string
    {
        return $this->joinPaths( $this->applicationPath ?: $this->basePath( 'app' ), $path );
    }

    /**
     * Get the base path of the OmegaCMS installation.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path of OmegaCMS istallation.
     */
    /**public function getBasePath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->basePath, $path );
    }*/

    /**
     * Get the path to the bootstrap directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path for 'app' directory.
     */
    public function getBootstrapPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->bootstrapPath, $path );
    }

    /**
     * Get the path to the application configuration files.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path for the configuration files.
     */
    public function getConfigPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->configPath ?: $this->basePath( 'config' ), $path );
    }

    /**
     * Get the path to the database directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path for the configuration files.
     */
    public function getDatabasePath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->databasePath ?: $this->basePath( 'database' ), $path );
    }

    /**
     * Get or check the current application environment.
     * 
     * @param  string|array ...$environments
     * @return string|bool
     */
    public function environment( string|arrat ...$environments ) : string|bool
    {
        if ( count( $environments ) > 0 ) {
            $patterns = is_array( $environments[ 0 ] ) ? $environments[ 0 ] : $environments;

            return Str::is( $patterns, $this[ 'env' ] );
        }

        return $this[ 'env' ];
    }

    /**
     * Get the environment file the application is using.
     * 
     * @return string Return the environment file the application using.
     */
    public function getEnvironmentFile() : string
    {
        return $this-environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file.
     * 
     * @return string Return the fully qualified path to the environment file.
     */
    public function getEnvironmentFilePath() : string
    {
        return $this->environmentPath() . DIRECTORY_SEPARATOR . $this->environmentFile();
    }

    /**
     * Get the path to the environment file directory.
     * 
     * @return string Return the path to the environment file directory.
     */
    public function getEnvironmentPath() : string
    {
        return $this->environmentPath ?: $this->basePath;
    }

    /**
     * Get the path to the language file directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path to the language file directory.
     */
    public function getLangPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->langPath, $path );
    }

    /**
     * Get the path to the public / web directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path to the public / web file directory.
     */
    public function getPublicPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->publicPath ?: $this->basePath( 'public' ), $path );
    }

    /**
     * Get the path to the resources directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path to the resources file directory.
     */
    public function getResourcePath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->basePath( 'resources' ), $path );
    }

    /**
     * Get the path to the public / web directory.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path to the storage file directory.
     */
    public function getStoragePath( string $path = '' ) : string
    {
        if ( isset( $_ENV[ 'OMEGA_STORAGE_PATH' ] ) ) {
            return $this->joinPaths( $this->storagePath ?: $_ENV[ 'OMEGA_STORAGE_PATH' ], $path );
        }

        return $this->joinPaths( $this->storagePath ?: $this->basePath( 'storage' ), $path );
    }

    /**
     * Get the path of the view directory.
     * 
     * This method returns the first configured path in the array of view paths.
     * 
     * @param  string $path Holds the application path.
     * @return string Return the path to the view directory.
     */
    public function getViewPath( string $path = '' ) : string
    {
        $viewPath = rtrim( $this[ 'config' ]->get( 'view.paths' )[ 0 ], DIRECTORY_SEPARATOR );

        return $this->joinPaths( $viewPath, $path );
    }

    /**
     * Set the application directory.
     * 
     * @param  string $path Holds the path to set.
     * @return $this
     */
    public function setApplicationPath( string $path ) : self
    {
        $this->applicationPath = $path;

        return $this;
    }

    /**
     * Set the base path for OmegaCMS installation.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setBasePath( string $basePath ) : self
    {
        $this->basePath = rtrim( $basePath, '\/' );

        return $this;
    }

    /**
     * Set bootstrap file directory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setBootstrapPath( string $path ) : self
    {
        $this->bootstrapPath = $path;

        return $this;
    }

    /**
     * Set the configuration directory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setConfigPath( string $path ) : self
    {
        $this->configPath = $path;

        return $this;
    }

    /**
     * Set the database diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setDatabasePath( string $path ) : self
    {
        $this->databasePath = $path;

        return $this;
    }

    /**
     * Set the environment file to be loading during bootstrapping.
     * 
     * @param  string $file Holds the environment file to be loading during bootstrappng.
     * @return $this
     */
    public function setEnvironmentFile( string $file ) : self
    {
        $this->environmentFile = $file;

        return $this;
    }

    /**
     * Set the environment diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setEnvironmentPath( string $path ) : self
    {
        $this->environmentPath = $path;

        return $this;
    }

    /**
     * Set the lang diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setLangPath( string $path ) : self
    {
        $this->langPath = $path;

        return $this;
    }

    /**
     * Set the public diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setPublicPath( string $path ) : self
    {
        $this->publicPath = $path;

        return $this;
    }

    /**
     * Set the storage diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setStoragePath( string $path ) : self
    {
        $this->storagePath = $path;

        return $this;
    }

    /**
     * Determine if the application is in rhe local environment.
     * 
     * @return bool Return true if the application is in local environment.
     */
    public function isLocal() : bool
    {
        return $this[ 'env' ] === 'local';
    }

    /**
     * Determine if the application is in the production environment.
     * 
     * @return bool Return true if the application is in the production environment.
     */
    public function isProduction() : bool
    {
        return $this[ 'env' ] === 'production';
    }

    /**
     * Detect the application's current environment.
     * 
     * @param  Closure $callback
     * @return string
     */
    public function detectEnvironment( Closure $callback ) : string
    {
        $args = $_SERVER[ 'argv' ] ?? null;

        return $this[ 'env' ] = ( new EnvironmentDetector )->detect( $callback, $args );
    }

    /**
     * Join the given paths.
     * 
     * @param  ?string $basePath Holds the base path to join.
     * @param  string  $path     Holds the path to join.
     * @return string Return the joined paths.
     */
    public function joinPaths( ?string $basePath, string $path = '' ) : string
    {
        return join_paths( $basePath, $path );
    }
}
