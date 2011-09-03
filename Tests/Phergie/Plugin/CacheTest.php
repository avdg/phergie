<?php
/**
 * Phergie
 *
 * PHP version 5
 *
 * LICENSE
 *
 * This source file is subject to the new BSD license that is bundled
 * with this package in the file LICENSE.
 * It is also available through the world-wide-web at this URL:
 * http://phergie.org/license
 *
 * @category  Phergie
 * @package   Phergie_Tests
 * @author    Phergie Development Team <team@phergie.org>
 * @copyright 2008-2011 Phergie Development Team (http://phergie.org)
 * @license   http://phergie.org/license New BSD License
 * @link      http://pear.phergie.org/package/Phergie_Tests
 */

/**
 * Unit test suite for Phergie_Plugin_Cache.
 *
 * @category Phergie
 * @package  Phergie_Tests
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie_Tests
 */
class Phergie_Plugin_CacheTest extends Phergie_Plugin_TestCase
{
    public function testStoreAndFetch()
    {
        $this->assertEquals(false, $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'foo');
        $this->assertEquals('foo', $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'bar');
        $this->assertEquals('bar', $this->plugin->fetch('bar'));
    }

    public function testStoreTtl()
    {
        $this->assertEquals(false, $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'foo', 1);
        $this->assertEquals('foo', $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'bar', -1); // Small hack
        $this->assertEquals(false, $this->plugin->fetch('bar'));
    }

    public function testStoreDoesntOverwrite()
    {
        $this->assertEquals(false, $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'foo');
        $this->assertEquals('foo', $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'bar', null, false);
        $this->assertEquals('foo', $this->plugin->fetch('bar'));
    }

    public function testRegressionStoreShouldOverwriteWhenValueExpired()
    {
        $this->plugin->store('bar', 'bar');
        $this->assertEquals('bar', $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'bar', -1); // Small hack
        // Intentional no assert (fetch removes outdated key)
        $this->plugin->store('bar', 'foo', null, false);
        $this->assertEquals('foo', $this->plugin->fetch('bar'));
    }

    public function testExpire()
    {
        $this->plugin->store('bar', 'bar');
        $this->assertEquals('bar', $this->plugin->fetch('bar'));

        $this->plugin->expire('bar');
        $this->assertEquals(false, $this->plugin->fetch('bar'));

        $this->plugin->store('bar', 'foo');
        $this->assertEquals('foo', $this->plugin->fetch('bar'));
    }

    public function testExpireNonexistentKey()
    {
        $this->assertFalse($this->plugin->fetch('bar'));
        $this->assertFalse($this->plugin->expire('bar'));
    }

    public function testSetBackendsWithEmptyArray()
    {
        $this->plugin->addBackends(array());
        $this->assertEquals(array(), $this->plugin->getBackends());
    }

    public function testSetBackendsWithInvalidObject()
    {
        $this->plugin->addBackends(array(
            array('class' => new stdClass)
        ));
        $this->assertEquals(array(), $this->plugin->getBackends());
    }

    public function testSetBackendsByClassname()
    {
        $this->markTestIncomplete();
        $this->plugin->addBackends(array(
            array('class' => 'Test1')
        ));

        $output = $this->plugin->getBackends();
        $this->assertInternalType('array', $output);
        $this->assertEquals(1, count($output));
        $this->assertInstanceOf('Phergie_plugin_Cache_Backend_Test1', $output[0]);
    }

    public function testSetBackendsByObjects()
    {
        $this->plugin->addBackends(array(
            array('class' => new Phergie_Plugin_Cache_Backend_Test1)
        ));

        $output = $this->plugin->getBackends();
        $this->assertInternalType('array', $output);
        $this->assertEquals(1, count($output));
        $this->assertInstanceOf('Phergie_plugin_Cache_Backend_Test1', $output[0]);
    }

    public function testSetBackendsWithConfig()
    {
    }
}

class Phergie_Plugin_Cache_Backend_TestDummy implements Phergie_Plugin_Cache_Backend
{
    protected $backend = array();

    protected $recorder = array();

    /**
     * Checks if the given key exists
     *
     * @param string $key Key to be checked if it exists
     *
     * @return bool
     */
    public function exists($key)
    {
        $this->recorder[] = 'Exists ' . $key;
        return isset($this->backend[$key]);
    }

    /**
     * Fetches a previously stored value
     *
     * @param string $key Key associated with the value
     *
     * @return mixed Stored value or FALSE if no value or an expired value
     *         is associated with the specified key
     */
    public function fetch($key)
    {
        $this->recorder[] = 'Fetch ' . $key;

        if (!isset($this->backend[$key])) {
            return false;
        }

        return $this->backend[$key]['data'];
    }

    /**
     * Stores a value in the backend cache
     *
     * @param string   $key    Key associate with the value
     * @param mixed    $data   Data to be stored
     * @param int|null $expire Time when cache expires or NULL if never expires
     *
     * @return bool
     */
    public function store($key, $data, $expires)
    {
        $this->recorder[] = "Store $key $value $expires";
        $this->backend[$key] = array(
            'data'    => $data,
            'expires' => $expires,
        );
        return true;
    }

    /**
     * Expires a value that has exceeded its time to live
     *
     * @param string $key Key associated with the value to expire
     *
     * @return bool
     */
    public function expire($key)
    {
        $this->recorder[] = 'Expire ' . $key;

        if (!isset($this->backend[$key])) {
            return false;
        }

        unset($this->backend[$key]);
        return true;
    }

    /**
     * Get the expire date of a given key
     *
     * @param string $key Key of the to be fetched expiration time
     *
     * @return int
     */
    public function getExpiration($key)
    {
        $this->recorder[] = 'GetExpire ' . $key;

        if (!isset($this->backend[$key])) {
            return false;
        }

        return $this->backend[$key]['expiration'];
    }

    /**
     * Retrieve recorded actions
     *
     * @return array
     */
    public function getRecords()
    {
        return $this->recorder;
    }

    /**
     * Cleans up the recorder
     *
     * @return void
     */
    public function clearRecorder()
    {
        $this->recorder = array();
    }
}

class Phergie_Plugin_Cache_Backend_Test1 extends Phergie_Plugin_Cache_Backend_TestDummy
{
}

class Phergie_Plugin_Cache_Backend_Test2 extends Phergie_Plugin_Cache_Backend_TestDummy
{
}
