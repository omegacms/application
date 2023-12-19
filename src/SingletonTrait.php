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
use function get_called_class;
use Omega\Application\Exceptions\SingletonException;

/**
 * Singleton trait.
 *
 * This `SingletonTrait`provides a convenient way to implement the Singleton design
 * pattern in PHP. When a class uses this trait, it ensures that only one instance
 * of the class is created and provides a static method to access that instance.
 *
 * Usage:
 * - Simply add `use Singleton;` to your class.
 * - Access the singleton instance using `YourClass::getInstance()`.
 *
 * @category    Omega
 * @package     Omega\Application
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2022 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
trait SingletonTrait
{
    /**
     * Singleton instance.
     *
     * @var mixed $instances Holds the singleton instances.
     */
    private static mixed $instances;

    /**
     * Get the singleton instance.
     *
     * This method returns the singleton instance of the class. If an instance
     * doesn't exist, it creates one and returns it.
     *
     * @return mixed Return the singleton instance.
     */
    public static function getInstance() : mixed
    {
        $getCalledClass = get_called_class();

        if ( ! isset( self::$instances[ $getCalledClass ] ) ) {
            self::$instances[ $getCalledClass ] = new $getCalledClass();
        }

        return self::$instances[ $getCalledClass ];
    }

    /**
     * SingletonTrait private constructor.
     *
     * This constructor is declared as private to prevent external instantiation
     * of the class. The singleton instance should be accessed via `getInstance()`.
     *
     * @return void
     */
    protected function __construct() {}

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