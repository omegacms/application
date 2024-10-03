<?php
/**
 * Part of Omega CMS - Application Package
 *
 * @link       https://omegacms.github.io
 * @author     Adriano Giovannini <omegacms@outlook.com>
 * @copyright  Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
use Closure;
use Throwable;
use Omega\Container\Container;
use Omega\Container\ServiceProvider\ServiceProviderInterface;
use Omega\Environment\Dotenv;
use Omega\Environment\EnvironmentDetector;
use Omega\Http\Response;
use Omega\Support\Facade\AliasLoader;
use Omega\Support\Facade\Facades\Router;
use Omega\Support\Singleton\SingletonTrait;
use Omega\Support\Str;

/**
 * Base application class.
 *
 * This `Application` class represents the main entry point of the Omega framework.
 * It manages the application's lifecycle, including configuration, routing, and
 * handling HTTP requests.
 *
 * @category    Omega
 * @package     Application
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
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
    protected const VERSION = '1.0.0';

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
     * Application configuration.
     *
     * @var array<string, string> $app Holds the application configuration.
     */
	private array $app = [];

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
        
        $this->app = require $this->getBasePath() . '/config/app.php';
        
        // Set the timezone during the application start
        date_default_timezone_set( $this->app[ 'timezone' ] );

        $this->configure( $this->getBasePath() );
        $this->bindProviders( $this->getBasePath() );
        $this->registerFacades();
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
        Dotenv::load( $basePath );
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
        $config = require $this->getBasePath() . "/config/app.php";
        $providers = $config[ 'providers' ];

        foreach ( $providers as $provider ) {
            $instance = new $provider;
            if ( $instance instanceof ServiceProviderInterface ) {
                $instance->bind( $this );
            }
        }
    }

    private function registerFacades() : void
    {
        $config  = require $this->getBasePath() . '/config/app.php';
        $aliases = $config[ 'facades' ];

        AliasLoader::getInstance( $aliases )->load();
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
        $routes = require $this->getBasePath() . "/routes/web.php";
        $routes( Router::class );

        $response = Router::dispatch();

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
        return $this->getJoinPaths( $this->appPath ?: $this->getBasePath( 'app' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the application path.
     * @return string Return the path of OmegaCMS istallation.
     */
    public function getBasePath( string $path = '' ) : string
    {
        return $this->getJoinPaths( $this->basePath, $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom bootstrap path defined by the developer.
     * @return string Return the path for bootstrap directory.
     */
    public function getBootstrapPath( string $path = '' ) : string
    {
        return $this->getJoinPaths( $this->bootstrapPath, $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom configutation path defined by the developer.
     * @return string Return the path for the configuration files.
     */
    public function getConfigPath( string $path = '' ) : string
    {
        return $this->getJoinPaths( $this->configPath ?: $this->getBasePath( 'config' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom dataase path defined by the developer.
     * @return string Return the path for the database files.
     */
    public function getDatabasePath( string $path = '' ) : string
    {
        return $this->getJoinPaths( $this->databasePath ?: $this->getBasePath( 'database' ), $path );
    }

    /**
     * @inheritdoc
     *
     * This method allows you to either retrieve the current environment or check if the application is running in a specific environment.
     * 
     * @param  string|string[] ...$environments One or more environment names to check against the current environment.
     * 
     * If no parameters are provided, the method returns the current environment as a string.
     * If one or more environment names are provided, the method returns `true` if the current environment matches any of the provided names; otherwise, it returns `false`.
     * 
     * @return string|bool The current environment as a string if no parameters are provided; `true` or `false` if checking against the provided environments.
     */
    public function environment( string|array ...$environments ) : string|bool
    {
        if ( count( $environments ) > 0 ) {
            $patterns = is_array( $environments[ 0 ] ) ? $environments[ 0 ] : $environments;

            return Str::is( $patterns, $this->app[ 'env' ] );
        }

        return $this->app[ 'env' ];
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
        return $this->getJoinPaths( $this->langPath, $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the custom public/web path defined by the developer.
     * @return string Return the path to the public/web path directory.
     */
    public function getPublicPath( string $path = '' ) : string
    {
        return $this->getJoinPaths( $this->publicPath ?: $this->getBasePath( 'public' ), $path );
    }

    /**
     * @inheritdoc
     *
     * @param  string $path Holds the application resources path.
     * @return string Return the path to the resources path directory.
     */
    public function getResourcePath( string $path = '' ) : string
    {
        return $this->getJoinPaths( $this->getBasePath( 'resources' ), $path );
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
            return $this->getJoinPaths( $this->storagePath ?: $_ENV[ 'OMEGA_STORAGE_PATH' ], $path );
        }

        return $this->getJoinPaths( $this->storagePath ?: $this->getBasePath( 'storage' ), $path );
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
     * @param  string $path Holds the application path.
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
     * @param  string $path Holds the application path.
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
     * @param  string $path Holds the application path.
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
     * @param  string $path Holds the application path.
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
     * @param  string $path Holds the application path.
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
     * @param  string $path Holds the application path.
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
     * @param  string $path Holds the application path.
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
        return $this->app[ 'env' ] === 'local';
    }

    /**
     * Determine if the application is in the production environment.
     *
     * @return bool Return true if the application is in the production environment.
     */
    public function isProduction() : bool
    {
        return $this->app[ 'env' ] === 'production';
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

        return $this->app[ 'env' ] = ( new EnvironmentDetector )->detect( $callback, $args );
    }

    /**
     * Join the given paths.
     *
     * @param  ?string $basePath Holds the base path to join.
     * @param  string  $path     Holds the path to join.
     * @return string Return the joined paths.
     */
    public function getJoinPaths( ?string $basePath, string $path = '' ) : string
    {
        $basePath = $basePath ??'';

        return $this->joinPaths( $basePath, $path );
    }

    /**
     * Join the given path.
     *
     * Concatenates a base path with additional paths and returns the result.
     *
     * @param  ?string $basePath Holds the base path to join.
     * @param  string  ...$paths Holds the paths to join.
     * @return string Return the joined paths.
     */
    public function joinPaths( ?string $basePath, string ...$paths ) : string
    {
        foreach ( $paths as $index => $path ) {
            if ( empty( $path ) ) {
                unset( $paths[ $index ] );
            } else {
                $paths[ $index ] = DIRECTORY_SEPARATOR . ltrim( $path, DIRECTORY_SEPARATOR );
            }
        }

        return $basePath . implode( '', $paths );
    }

	/**
 	 * @inheritdoc
 	 *
 	 * This method reads the timezone from the application configuration and sets it
 	 * as the default timezone for all date and time operations within the application.
 	 *
 	 * @return $this Returns a reference to the current object for method chaining.
 	 */
    public function setCurrentTimeZone() : self
    {
		date_default_timezone_set( $this->app[ 'timezone' ] );

		return $this;
    }

    /**
     * @inheritdoc
     *
     * @return string The formatted current date and time.
     */
    public function getCurrentTimeZone() : string
    {
        return date( 'Y-m-d H:i:s', time() );
    }
}

