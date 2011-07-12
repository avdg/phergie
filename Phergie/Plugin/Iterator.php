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
 * Implements a filtering iterator for plugins.
 *
 * @category Phergie
 * @package  Phergie
 * @author   Phergie Development Team <team@phergie.org>
 * @license  http://phergie.org/license New BSD License
 * @link     http://pear.phergie.org/package/Phergie
 */
class Phergie_Plugin_Iterator extends FilterIterator
{
    /**
     * Array of filters to be used
     *
     * @var array
     */
    protected $filters = array();

    /**
     * Constructor -- We set up the filters to be used
     *
     * @param Iterator               $iterator            Iterator to filter
     * @param array                  $filters            Filters to be used
     *
     * @return void
     */
    public function __construct(Iterator $iterator, array $filters)
    {
        $this->filters = $filters;
        parent::__construct($iterator);
    }

    /**
     * Fixes a bug in FilterIterator
     *
     * @return mixed
     * @link http://bugs.php.net/bug.php?id=52560
     */
    public function current()
    {
        return $this->getInnerIterator()->current();
    }

    /**
     * Implements FilterIterator::accept().
     *
     * @return boolean TRUE to include the current item in those by returned
     *         during iteration, FALSE otherwise
     */
    public function accept()
    {
        if (empty($this->filters)) {
            return true;
        }
        $plugin = $this->current();
        foreach ($this->filters as $filter) {
            if (!$filter->accept($plugin)) {
                return false;
            }
        }
        return true;
    }
}
