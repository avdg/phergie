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
 * @package   Phergie_Plugin_Qdb
 * @author    Phergie Development Team <team@phergie.org>
 * @copyright 2008-2011 Phergie Development Team (http://phergie.org)
 * @license   http://phergie.org/license New BSD License
 * @link      http://pear.phergie.org/package/Phergie_Plugin_Qdb
 */

/**
 * Fetches quotes from Qdb.us.
 *
 * @category Phergie
 * @package  Phergie_Plugin_Qdb
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie_Plugin_Qdb
 * @uses     Phergie_Plugin_Command pear.phergie.org
 */
class Phergie_Plugin_Qdb extends Phergie_Plugin_Abstract
{
    /**
     * Checks for dependencies.
     *
     * @return void
     */
    public function onLoad()
    {
        $this->getPluginHandler()->getPlugin('Command');
    }

    /**
     * Handles a request for a Qdb quote by ID.
     *
     * <code>qdb 12345</code>
     *
     * Due to lack of real input validation, we get some interesting
     * features as a side effect:
     *
     * <code>qdb random</code>
     * <code>qdb latest</code>
     * <code>qdb search?q=keyword</code>
     *
     * @param string $id Qdb quote ID to retrieve
     *
     * @return void
     */
    public function onCommandQdb($id)
    {
        $data = file_get_contents('http://qdb.us/' . $id);
        if (preg_match('/<span class=qt id=qt(\S+)>(.*?)<\/span>/si', $data, $matches)) {
            $id = $matches[1];
            $quote = html_entity_decode($matches[2]);
            $quote = implode(' / ', array_map('trim', explode('<br />', $quote)));
            if (strlen($quote) > 400) {
                $quote = substr($quote, 0, 400) . ' ...';
            }
            $this->doPrivmsg($this->getEvent()->getSource(), 'qdb.us/' . $id . ': ' . $quote);
        }
    }
}
