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
use function Omega\Helpers\join_paths;
use Closure;
use Throwable;
use Dotenv\Dotenv;
use Omega\Container\Container;
use Omega\Environment\EnvironmentDetector;
use Omega\Http\Response;
use Omega\Routing\Router;
use Omega\Support\Str;

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

    /**
     * The Omega framework version.
     *
     * @var string
     */
    private const VERSION = '1.0.0';

    /**
     * The custom application path defined by the developer.
     *
     * @var string $appPath Holds the custom application path defined by developer.
     */
    protected string $appPath = '';

    /**
     * The base path for the OmegaCMS installation.
     *
     * @var string $basePath Holds the base path for the OmegaCMS installation.
     */
    protected string $basePath = '';

    /**
     * The custom application path defined by the developer.
     *
     * @var string $bootstrapPath Holds the custom application path defined by developer.
     */
    protected string $bootstrapPath = '';

    /**
     * The custom configuration path defined by the developer.
     *
     * @var string $configPath Holds the custom configuration path defined by the developer.
     */
    protected string $configPath = '';

    /**
     * The custom database path defined by the developer.
     *
     * @var string $databasePath Holds the custom database path defined by the develiper.
     */
    protected string $databasePath = '';

    /**
     * The environment file to load during bootstrapping.
     *
     * @var string $environmentFile Holds the environment file to load during bootstrapping.
     */
    protected string $environmentFile = '.env';

    /**
     * The custom environment path defined by the developer.
     *
     * @var string $environmentPath Holds the custom environment path defined by the developer.
     */
    protected string $environmentPath = '';

    /**
     * The custom language path defined by the developer.
     *
     * @var string $langPath Holds the custom language path defined by the developer.
     */
    protected string $langPath = '';

    /**
     * The custom public path defined by the developer.
     *
     * @var string $publicPath Holds the custom public path defined by the developer.
     */
    protected string $publicPath = '';

    /**
     * The custom storage path defined by the developer.
     * 
     * @var string $storagePath Holds the custom storage path defined by the developer.
     */
    protected string $storagePath = '';

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
        $providers = require $this->getBasePath() . "/config/providers.php";

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

        $routes = require $this->getBasePath() . "/routes/web.php";
        $routes( $router );

        $response = $router->dispatch();

        if ( ! $response instanceof Response ) {
            $response = $this->resolve( 'response' )->content( $response );
        }

        return $response;
    }

    /**
     * @inheritdoc
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
     * @param  string $path Holds the application 'app' path.
     * @return string Return the path for 'app' directory.
     */
    public function getAppPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->appPath ?: $this->getBasePath( 'app' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the application path.
     * @return string Return the path of OmegaCMS istallation.
     */
    public function getBasePath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->basePath, $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom bootstrap path defined by the developer.
     * @return string Return the path for bootstrap directory.
     */
    public function getBootstrapPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->bootstrapPath, $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom configutation path defined by the developer.
     * @return string Return the path for the configuration files.
     */
    public function getConfigPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->configPath ?: $this->getBasePath( 'config' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom dataase path defined by the developer.
     * @return string Return the path for the database files.
     */
    public function getDatabasePath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->databasePath ?: $this->getBasePath( 'database' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string|array ...$environments
     * @return string|bool
     */
    public function environment( string|array ...$environments ) : string|bool
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
        return $this->environmentFile ?: '.env';
    }

    /**
     * Get the fully qualified path to the environment file.
     *
     * @return string Return the fully qualified path to the environment file.
     */
    public function getEnvironmentFilePath() : string
    {
        return $this->getEnvironmentPath() . DIRECTORY_SEPARATOR . $this->getEnvironmentFile();
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
     * @inheritdoc
     *
     * @param  string $path Holds the custom language path defined by the developer.
     * @return string Return the path to the language file directory.
     */
    public function getLangPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->langPath, $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom public/web path defined by the developer.
     * @return string Return the path to the public/web path directory.
     */
    public function getPublicPath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->publicPath ?: $this->getBasePath( 'public' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  ?string $path Holds the application resources path.
     * @return string Return the path to the resources path directory.
     */
    public function getResourcePath( string $path = '' ) : string
    {
        return $this->joinPaths( $this->getBasePath( 'resources' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the storage path.
     * @return string Return the path to the storage path directory.
     */
    public function getStoragePath( string $path = '' ) : string
    {
        if ( isset( $_ENV[ 'OMEGA_STORAGE_PATH' ] ) ) {
            return $this->joinPaths( $this->storagePath ?: $_ENV[ 'OMEGA_STORAGE_PATH' ], $path );
        }

        return $this->joinPaths( $this->storagePath ?: $this->getBasePath( 'storage' ), $path );
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
    public function setAppPath( string $path ) : self
    {
        $this->appPath = $path;

        return $this;
    }

    /**
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
     * @inheritdoc
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
