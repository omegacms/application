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
     * Get the version number of application.
     * 
     * @return string Return the version number of application.
     */
    public function getVersion() : string;
    /**
     * Set the base path for the application.
     * 
     * @param  string $basePath Holds the base path for the application.
     * @return $this
     */
    public function setBasePath( string $basePath ) : self;

    /**
     * Get the base path for the application.
     * 
     * @return string Return the base path for the application.
     */
    public function getBasePath() : string;

    /**
     * Get the path to the application configuration folder.
     * 
     * @param  ?string $path (Optionally) Holds the path to append config path.
     * @return string Return the path to the configuration folder. 
     */
    public function getConfigPath( ?string $path = '' ) : string;

    /**
     * Get the path to the database directory.
     * 
     * @param  ?string $path (Optionally) Holds the path to append database path.
     * @return string Return the path to the database folder.
     */
    public function getDatabasePath( ?string $path = '' ) : string;
}