<?php
/**
 * Part of Omega CMS - Application Test Package
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
namespace Omega\Application\Tests;

/**
 * @use
 */
use Omega\Application\Exceptions\SingletonException;
use Omega\Application\SingletonTrait;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\Attributes\TestDox;
use Omega\Testing\TestCase;

/**
 * Class SingletonTest
 * 
 * The `SingletonTest` class contains a series of tests to verify the correct implementation of 
 * the Singleton pattern on two example classes: `Sample1Test` and `Sample2Test.` It includes 
 * tests for instance management, prevention of `cloning`, `serialization`, `deserialization`, 
 * and the `__wakeup` method.
 *
 * @category    Omega
 * @package     Omega\Application
 * @subpackage  Omega\Application\Tests
 * @link        https://omegacms.github.io
 * @author      Adriano Giovannini <omegacms@outlook.com>
 * @copyright   Copyright (c) 2024 Adriano Giovannini. (https://omegacms.github.io)
 * @license     https://www.gnu.org/licenses/gpl-3.0-standalone.html     GPL V3.0+
 * @version     1.0.0
 */
class SingletonTest extends TestCase
{
    /**
     * Singleton test.
     *
     * This test verifies that the Sample1Test and Sample2Test classes follow the Singleton pattern.
     * 
     * - Ensures that the same instance of Sample1Test is returned on multiple calls.
     * - Verifies that changes to the properties of the instance are retained across all accesses.
     * - Verifies the same behavior for the Sample2Test instance.
     * - Concludes by ensuring that both Singleton instances maintain their modified properties.
     * 
     * @return void
     */
    #[Test]
    #[TestDox('Singleton test.

    This test verifies that the Sample1Test and Sample2Test classes follow the Singleton pattern.

        - Ensures that the same instance of Sample1Test is returned on multiple calls.
        - Verifies that changes to the properties of the instance are retained across all accesses.
        - Verifies the same behavior for the Sample2Test instance.
        - Concludes by ensuring that both Singleton instances maintain their modified properties.
    ')]
    public function singleton() : void
    {
        $sample1 = Sample1Test::getInstance();
        $this->assertEquals( 10, $sample1->property );
    
        $sample1->property = 20;
        $this->assertEquals( 20, Sample1Test::getInstance()->property );
    
        $sample1->property = 40;
        $this->assertEquals(40, Sample1Test::getInstance()->property);
    
        $sample2 = Sample2Test::getInstance();
        $sample2->property2 = 50;
        $this->assertEquals( 50, $sample2->property2 );
    
        $sample2->property2 = 90;
        $this->assertEquals( 90, Sample2Test::getInstance()->property2 );
        $this->assertEquals( 40, Sample1Test::getInstance()->property );
    }
    
    /**
     * Cloning test.
     *
     * This test verifies that attempting to clone a Singleton instance throws a SingletonException.
     * 
     * @return void
     */
    #[Test]
    #[TestDox('Cloning test.
    
    This test verifies that attempting to clone a Singleton instance throws a SingletonException.
    ')]
    public function clone() : void
    {
        $this->expectException( SingletonException::class );
    
        $sample1     = Sample1Test::getInstance();
        $sampleClone = clone $sample1;
    }

    /**
     * Serialization test.
     *
     * This test ensures that attempting to serialize a Singleton instance throws a SingletonException.
     * 
     * @return void
     */
    #[Test]
    #[TestDox('Serialization test.
    
    This test ensures that attempting to serialize a Singleton instance throws a SingletonException.
    ')]
    public function serialize() : void
    {
        $this->expectException( SingletonException::class );
    
        $sample1 = Sample1Test::getInstance();
        
        serialize( $sample1 );
    }

    /**
     * Deserialization test.
     *
     * This test verifies that deserializing a Singleton instance (which calls __wakeup()) 
     * throws a SingletonException.
     * 
     * @return void
     */
    #[Test]
    #[TestDox('Deserialization Test.

    This test verifies that deserializing a Singleton instance (which calls __wakeup()) 
    throws a SingletonException.
    ')]
    public function unserialize() : void
    {
        $this->expectException( SingletonException::class );

        $sample1      = Sample1Test::getInstance();
        $serialized   = serialize( $sample1 );
        $unserialized = unserialize($serialized);
    }

    /**
     * Wakeup test.
     *
     * This test directly calls the __wakeup() method on a Singleton instance to ensure 
     * it throws a SingletonException.
     * 
     * @return void
     */
    #[Test]
    #[TestDox('Wakeup test.

    This test directly calls the __wakeup() method on a Singleton instance to ensure 
    it throws a SingletonException.
    ')]
    public function wakeup() : void
    {
        $this->expectException( SingletonException::class );
    
        $sample1 = Sample1Test::getInstance();
        $sample1->__wakeup();
    }
}

class Sample1Test
{
    use SingletonTrait;

    public $property;

    private function __construct()
    {
        $this->property = 10;
    }
}

class Sample2Test
{
    use SingletonTrait;

    public $property2;
}