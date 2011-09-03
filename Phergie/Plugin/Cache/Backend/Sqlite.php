<?php

class Phergie_Plugin_Cache_Backend_Sqlite implements Phergie_Plugin_Cache_Backend
{
    protected $db;
    protected $prepare = array();

    /**
     * Constructor
     */
    public function __construct()
    {
        if (!extension_loaded('PDO') || !extension_loaded('pdo_sqlite')) {
            throw new Phergie_Exception('PDO and pdo_sqlite extensions must be installed');
        }

        $defaultDir    = dirname(__FILE__);
        $defaultDbName = 'cache.db';

        $this->prepareDatabase('sqlite:' . $defaultDir . '/' . $defaultDbName);
    }

    /**
     * Sets up db connections and prepared statements
     *
     * @param string $location Location of the db
     *
     * @return void
     */
    public function prepareDatabase($location)
    {
        $this->db = new PDO($location);
        $this->db->exec('CREATE TABLE phergie_cache (
            id INT UNIQUE PRIMARY KEY, expiration TIME, key VARCHAR(120), value BLOB
        );');

        $query = array(
            'Exists'        => 'SELECT count(id) FROM phergie_cache
            WHERE key=:key AND expiration > :time',

            'StoreUpdate'   => 'UPDATE phergie_simple_cache
            set value=:value, expiration=:expire where key=:key',

            'StoreInsert'   => 'INSERT Into phergie_simple_cache
            (key, value, expiration)
            VALUES (:key, :value, :expiration)',

            'Fetch'         => 'SELECT value FROM phergie_cache
            WHERE key=:key AND expiration > :time',

            'Expire'        => 'DELETE FROM phergie_cache WHERE key=:key',

            'GetExpiration' => 'SELECT expiration FROM phergie_cache
            WHERE key=:key AND expiration > :time',
        );

        $this->prepare = array(
            'Exists'        => $this->db->prepare($query['Exists']),
            'StoreUpdate'   => $this->db->prepare($query['StoreUpdate']),
            'StoreInsert'   => $this->db->prepare($query['StoreInsert']),
            'Fetch'         => $this->db->prepare($query['Fetch']),
            'Expire'        => $this->db->prepare($query['Expire']),
            'GetExpiration' => $this->db->prepare($query['GetExpiration']),
        );
    }

    /**
     * Checks if the given key exists
     *
     * @param string $key Key to be checked if it exists
     *
     * @return bool
     */
    public function exists($key)
    {
        $prepare = $this->prepare['Exists']->execute(array(
            ':key'       => $key,
            ':time'      => time(),
        ));

        return $prepare->fetch() === 1;
    }

    /**
     * Stores a value in the backend cache
     *
     * @param string   $key    Key associate with the value
     * @param mixed    $data   Data to be stored
     * @param int|null $expire Expiration time in seconds or NULL for forever
     *
     * @return bool
     */
    public function store($key, $data, $expire)
    {
        $values = array(
            ':key'        => $key,
            ':value'      => $value,
            ':expiration' => $expire,
        );

        if ($this->issetBackend($key)) {
            $this->prepare['StoreUpdate']->execute($values);
        } else {
            $this->prepare['StoreInsert']->execute($values);
        }

        return true;
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
        $prepare = $this->prepare['Fetch']->execute(array(
            ':key'  => $key,
            ':time' => time(),
        ));

        if ($prepare->columnCount() === 1) {
            $results = $prepare->fetch();
            return $results[0];
        }

        return false;
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
        $this->prepare['Expire']->execute(array(
            ':key' => $key,
        ));
    }

    /**
     * Get the expire date of a given key
     *
     * @param string $key Key of the to be fetched expiration time
     *
     * @return bool
     */
    public function getExpiration($key)
    {
        $prepare = $this->prepare['GetExpiration']->execute(array(
            ':key'  => $key,
            ':time' => time(),
        ));

        if ($prepare->columnCount() === 1) {
            $results = $prepare->fetch();
            return $results[0];
        }

        return false;
    }
}
