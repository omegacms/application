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
 * Application Interface Class.
 * 
 * @category    Omega
 * @package     Omega\Application
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
interface ApplicationInterface
{
    /**
     * Get the version of the application.
     * 
     * @return string Return the version of the application.
     */
    public function getVersion() : string;

    /**
     * Get the base path of the OmegaCMS installation.
     * 
     * @param  ?string $path Holds the application path.
     * @return string Return the path of OmegaCMS istallation.
     */
    public function getBasePath( ?string $path = '' ) : string;

    /**
     * Set the base path for OmegaCMS installation.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setBasePath( string $basePath ) : self;

    /**
     * Get the path to the bootstrap directory defined by the developer.
     * 
     * @param  string $path Holds the custom bootstrap path defined by the developer.
     * @return string Return the path for bootstrap directory.
     */
    public function getBootstrapPath( ?string $path = '' ) : string;

    /**
     * Set bootstrap file directory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setBootstrapPath( string $path ) : self;

    /**
     * Get the path to the configuration directory defined by the developer.
     * 
     * @param  string $path Holds the custom configuration path defined by the developer.
     * @return string Return the path for the configuration path.
     */
    public function getConfigPath( ?string $path = '' ) : string;

    /**
     * Set the configuration directory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setConfigPath( string $path ) : self;

    /**
     * Get the path to the database directory defined by the developer.
     * 
     * @param  string $path Holds the custom database path defined by the developer.
     * @return string Return the path for the database files.
     */
    public function getDatabasePath( ?string $path = '' ) : string;

    /**
     * Set the database diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setDatabasePath( string $path ) : self;

    /**
     * Get or check the current application environment.
     * 
     * @param  string|array ...$environments
     * @return string|bool
     */
    public function environment( string|array ...$environments ) : string|bool;

    /**
     * Get the path to the language directory defined by the developer.
     * 
     * @param  string $path Holds the custom language path defined by the developer.
     * @return string Return the path to the language file directory.
     */
    public function getLangPath( ?string $path = '' ) : string;

    /**
     * Set the lang diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setLangPath( string $path ) : self;

    /**
     * Get the path to the public/web directory defined by the developer.
     * 
     * @param  string $path Holds the custom public/web path defined by the developer.
     * @return string Return the path to the public/web path directory.
     */
    public function getPublicPath( ?string $path = '' ) : string;

    /**
     * Set the public diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setPublicPath( string $path ) : self;

    /**
     * Get the path to the resources directory.
     * 
     * @param  ?string $path Holds the application resources path.
     * @return string Return the path to the resources path directory.
     */
    public function getResourcePath( ?string $path = '' ) : string;

    /**
     * Get the path to the storage directory.
     * 
     * @param  string $path Holds the storage path.
     * @return string Return the path to the storage path directory.
     */
    public function getStoragePath( string $path = '' ) : string;

    /**
     * Set the storage diretory path.
     * 
     * @param  string $basePath Holds the application path.
     * @return $this
     */
    public function setStoragePath( string $path ) : self;
}