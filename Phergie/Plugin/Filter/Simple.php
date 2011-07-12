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
 * @package   Phergie
 * @author    Phergie Development Team <team@phergie.org>
 * @copyright 2008-2011 Phergie Development Team (http://phergie.org)
 * @license   http://phergie.org/license New BSD License
 * @link      http://pear.phergie.org/package/Phergie
 */

/**
 * Implements a filter for limiting executing of methods across
 * a group of plugins
 *
 * @category Phergie
 * @package  Phergie
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie
 */
class Phergie_Plugin_Filter_Simple extends Phergie_Plugin_Filter_Abstract
{
    /**
     * List of short names of plugins to exclude when iterating
     *
     * @var array
     */
    protected $plugins = array();

    /**
     * List of method names where plugins with these methods will be
     * excluded when iterating
     *
     * @var array
     */
    protected $methods = array();

    /**
     * Adds to a list of plugins to exclude when iterating.
     *
     * @param mixed $plugins String containing the short name of a single
     *        plugin to exclude or an array of short names of multiple
     *        plugins to exclude
     *
     * @return Phergie_Plugin_Filter_Simple Provides a fluent interface
     */
    public function addPluginFilter($plugins)
    {
        if (is_array($plugins)) {
            $this->plugins = array_unique(
                array_merge($this->plugins, $plugins)
            );
        } else {
            $this->plugins[] = $plugins;
        }
        return $this;
    }

    /**
     * Adds to a list of method names where plugins defining these methods
     * will be excluded when iterating.
     *
     * @param mixed $methods String containing the name of a single method
     *        or an array containing the name of multiple methods
     *
     * @return Phergie_Plugin_Filter_Simple Provides a fluent interface
     */
    public function addMethodFilter($methods)
    {
        if (is_array($methods)) {
            $this->methods = array_merge($this->methods, $methods);
        } else {
            $this->methods[]= $methods;
        }
        return $this;
    }

    /**
     * Clears any existing plugin and methods filters.
     *
     * @return Phergie_Plugin_Filter_Simple Provides a fluent interface
     */
    public function clearFilters()
    {
        $this->plugins = array();
        $this->methods = array();
        return $this;
    }

    /**
     * Implements Phergie_Plugin_Filter_Abstract::accept().
     *
     * @param Phergie_Plugin_Abstract The plugin to filter
     *
     * @return bool TRUE to include the plugin, FALSE otherwise
     */
    public function accept(Phergie_Plugin_Abstract $plugin)
    {
        if (!$this->plugins && !$this->methods) {
            return true;
        }

        if (in_array($plugin->getName(), $this->plugins)) {
            return false;
        }

        foreach ($this->methods as $method) {
            if (method_exists($plugin, $method)) {
                return false;
            }
        }

        return true;
    }
}
