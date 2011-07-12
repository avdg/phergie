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
 * Unit test suite for Phergie_Plugin_Iterator.
 *
 * @category Phergie
 * @package  Phergie_Tests
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie_Tests
 */
class Phergie_Plugin_IteratorTest extends PHPUnit_Framework_TestCase
{
    /**
     * Array of all the mock plugins loaded into the iterator
     *
     * @var array
     */
    protected $plugins;

    /**
     * Initializes the mock plugins
     *
     * @return void
     */
    public function setUp()
    {
        $this->plugins = array(
            $this->getMockPlugin('0'),
            $this->getMockPlugin('1'),
            $this->getMockPlugin('2'),
            $this->getMockPlugin('3'),
            $this->getMockPlugin('4'),
        );
    }

    /**
     * Returns a mock plugin instance.
     *
     * @param string $name    Optional short name for the mock plugin, defaults
     *        to 'TestPlugin'
     * @param array  $methods Optional list of methods to override
     *
     * @return Phergie_Plugin_Abstract
     */
    protected function getMockPlugin($name = 'TestPlugin', array $methods = array())
    {
        $methods[] = 'getName';
        $plugin = $this->getMock('Phergie_Plugin_Abstract', $methods);
        $plugin
            ->expects($this->any())
            ->method('getName')
            ->will($this->returnValue($name));
        return $plugin;
    }

    /**
     * Tests we get all plugins when no filters are set
     *
     * @return void
     */
    public function testIteratesAllPluginsWithNoFilters()
    {
        $expected = range(0, 4);
        $iterator = new Phergie_Plugin_Iterator(new ArrayIterator($this->plugins), array());
        $actual = array();
        foreach ($iterator as $plugin) {
            $actual[] = $plugin->getName();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests we get all plugins when filter accept
     *
     * @return void
     */
    public function testIteratesAllPluginsWhenFilterAccept()
    {
        $filter = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter
            ->expects($this->exactly(5))
            ->method('accept')
            ->will($this->returnValue(true))
            ;
        $expected = range(0, 4);
        $iterator = new Phergie_Plugin_Iterator(new ArrayIterator($this->plugins), array($filter));
        $actual = array();
        foreach ($iterator as $plugin) {
            $actual[] = $plugin->getName();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests we get no plugins when filter does not accept
     *
     * @return void
     */
    public function testIteratesAllPluginsWhenFilterDoesNotAccept()
    {
        $filter = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter
            ->expects($this->exactly(5))
            ->method('accept')
            ->will($this->returnValue(false))
            ;
        $expected = array();
        $iterator = new Phergie_Plugin_Iterator(new ArrayIterator($this->plugins), array($filter));
        $actual = array();
        foreach ($iterator as $plugin) {
            $actual[] = $plugin->getName();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests all filters are called when all accept the plugins
     *
     * @return void
     */
    public function testCallsAllFiltersIfAllAccept()
    {
        $filter1 = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter1
            ->expects($this->exactly(5))
            ->method('accept')
            ->will($this->returnValue(true))
            ;
        $filter2 = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter2
            ->expects($this->exactly(5))
            ->method('accept')
            ->will($this->returnValue(true))
            ;
        $filter3 = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter3
            ->expects($this->exactly(5))
            ->method('accept')
            ->will($this->returnValue(true))
            ;
        $expected = range(0, 4);
        $iterator = new Phergie_Plugin_Iterator(new ArrayIterator($this->plugins), array($filter1, $filter2, $filter3));
        $actual = array();
        foreach ($iterator as $plugin) {
            $actual[] = $plugin->getName();
        }
        $this->assertEquals($expected, $actual);
    }

    /**
     * Tests stop filtering a plugin upon first filter not accepting
     *
     * @return void
     */
    public function testStopFilteringPluginUponFirstReject()
    {
        $filter1 = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter1
            ->expects($this->exactly(5))
            ->method('accept')
            ->will($this->onConsecutiveCalls(true, true, true, false, true))
            ;
        $filter2 = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter2
            ->expects($this->exactly(4))
            ->method('accept')
            ->will($this->onConsecutiveCalls(true, false, true, false))
            ;
        $filter3 = $this->getMock('Phergie_Plugin_Filter_Abstract');
        $filter3
            ->expects($this->exactly(2))
            ->method('accept')
            ->will($this->onConsecutiveCalls(false, true))
            ;
        $expected = array(2);
        $iterator = new Phergie_Plugin_Iterator(new ArrayIterator($this->plugins), array($filter1, $filter2, $filter3));
        $actual = array();
        foreach ($iterator as $plugin) {
            $actual[] = $plugin->getName();
        }
        $this->assertEquals($expected, $actual);
    }
}
