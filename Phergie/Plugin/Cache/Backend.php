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
 * @package   Phergie_Plugin_Cache
 * @author    Phergie Development Team <team@phergie.org>
 * @copyright 2008-2011 Phergie Development Team (http://phergie.org)
 * @license   http://phergie.org/license New BSD License
 * @link      http://pear.phergie.org/package/Phergie_Plugin_Cache
 */

/**
 * Interface for caching backends
 *
 * @category Phergie
 * @package  Phergie_Plugin_Cache
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie_Plugin_Cache
 */
interface Phergie_Plugin_Cache_Backend
{
    /**
     * Checks if the given key exists
     *
     * @param string $key Key to be checked if it exists
     *
     * @return bool
     */
    public function exists($key);

    /**
     * Stores a value in the backend cache
     *
     * @param string   $key    Key associate with the value
     * @param mixed    $data   Data to be stored
     * @param int|null $expire Time when cache expires or NULL if never expires
     *
     * @return bool
     */
    public function store($key, $data, $expire);

    /**
     * Fetches a previously stored value
     *
     * @param string $key Key associated with the value
     *
     * @return mixed Stored value or FALSE if no value or an expired value
     *         is associated with the specified key
     */
    public function fetch($key);

    /**
     * Expires a value that has exceeded its time to live
     *
     * @param string $key Key associated with the value to expire
     *
     * @return bool
     */
    public function expire($key);

    /**
     * Get the expire date of a given key
     *
     * @param string $key Key of the to be fetched expiration time
     *
     * @return int
     */
    public function getExpiration($key);

}
