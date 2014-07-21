<?php

/**
 * refererTracker
 *
 * This class uses the $_SERVER['HTTP_REFERER'] global variable to track
 * refered traffic. The class also splits up referers based on their location
 * (internal or external) and attaches a timestamp to each one, so that a user's
 * behaviour is fully logged. The class also has the ability to return the
 * logged referals as an array.
 *
 * Example Usage:
 *
 * <code>
 *    $setSession = function($k, $v) { return \Session::put($k, $v); };
 *    $getSession = function($k) { return \Session::get($k); };
 *    $log = new refererTracker($getSession, $setSession);
 *    $log->log();
 * </code>
 *
 * @author John Hamelink <john@johnhamelink.com>
 * @version 0.01
 * @copyright 2012 Cyber-Duck Ltd. All rights reserved.
 *
 */
class refererTracker
{
    private $_referer;
    private $_path;
    private $_setterCallback;
    private $_getterCallback;

    /**
     * __construct
     *
     * Load the referer and path variables from the session,
     * set them as arrays if they aren't already.
     *
     */
    public function __construct($setterCallback, $getterCallback)
    {
        $this->_setterCallback = $setterCallback;
        $this->_getterCallback = $getterCallback;


        $this->_referer = $this->_sessionGet('referer');
        $this->_path    = $this->_sessionGet('path');

        if (!is_array($this->_path)) {
            $this->_path = array();
        }
        if (!is_array($this->_referer)) {
            $this->_referer = array();
        }
    }

    /**
     * _sessionSet
     *
     * This private method uses the callbacks defined in the constructor
     * to set the session. This makes this library framework agnostic.
     *
     * @param String $key   The key used to identify the data
     * @param String $value The value of the data you wish to store
     *
     * @return Mixed|NULL The return value of the callback
     *
     */
    private function _sessionSet($key, $value)
    {
        return call_user_func_array($this->_setterCallback, array($key, $value));
    }

    /**
     * _sessionGet
     *
     * This private method uses the callbacks defined in the constructor
     * to get session data. This makes this library framework agnostic.
     *
     * @param String $key The key used to identify the data
     *
     * @return Mixed|NULL The return value of the callback
     *
     */
    private function _sessionGet($key)
    {
        return call_user_func_array($this->_getterCallback, array($key));
    }

    /**
     * _appendToSession
     *
     * This private method appends a new path (in $cut) to the object ($paste).
     * If a referer limit is set, then the array will be sliced - oldest elements
     * first - until it matches the correct size.
     *
     * @param String   $cut    The Path you want to define in the referer
     * @param Array    &$paste Reference to the Array you are using to record
     *                         the session.
     * @param Int|NULL $limit  The max number of referers we want to keep
     *
     * @return Bool
     */
    private function _appendToSession($cut, &$paste, $limit=10)
    {
        $paste[] = array( 'path' => $cut, 'timestamp' => date('c') );
        if ($limit && count($paste) > $limit) {
            // We invert the number so that it starts at the begining - removing
            // the oldest elements first.
            $paste = array_slice($paste, -1 * $limit);
        }

        return true;
    }

    /**
     * _internalReferer
     *
     * This private method finds the path of the internal URL and adds
     * it to the $_path array to be stored in the session.
     *
     * @param Array $parts The array of "bits" that make up the URL
     *
     * @return Bool
     */
    private function _internalReferer($parts)
    {
        if (!empty($parts['path'])) {
            $path = $parts['path'];
        } else {
            $path = "/";
        }

        if (
            count($this->_path) == 0 ||
            $this->_path[count($this->_path) -1] != $path
        ) {
            $this->_appendToSession($path, $this->_path);
        }

        return true;
    }

    /**
     * _externalReferer
     *
     * This private mathod sets the referer to the last external referer.
     *
     * @param String $lastReferer Last external referer
     *
     * @return Bool
     */
    private function _externalReferer($lastReferer)
    {
        $this->_appendToSession($lastReferer, $this->_referer);

        return true;
    }


    /**
     * log
     *
     * This method checks for a HTTP referer, splits up the last referer, then
     * decides whether we're looking at an internal or external URL. After
     * everything's sorted, it sets the session to be picked up at the next page
     * load
     *
     * @return Bool
     *
     */
    public function log()
    {

        // Get the last referer from the $_SERVER global
        if (isset($_SERVER['HTTP_REFERER']) && !empty($_SERVER['HTTP_REFERER'])) {
            $lastReferer = $_SERVER['HTTP_REFERER'];
            // Clean it up
            $lastReferer = htmlentities(trim($lastReferer), ENT_QUOTES, 'UTF-8');
            // Chunk it into an array of parts
            $parts = parse_url($lastReferer);
            // Check that the referer host isn't actually this website.
            if (
                isset($parts['host']) &&
                mb_strpos($parts['host'], $_SERVER['SERVER_NAME']) === false
            ) {
                $this->_externalReferer($lastReferer);
            } else {
                $this->_internalReferer($parts);
            }
        }

        $this->_sessionSet('referer', $this->_referer);
        $this->_sessionSet('path', $this->_path);

        return true;
    }

    /**
     * retrieveInternal
     *
     * Returns all referers which are deemed
     * "internal" to this website's domain.
     *
     * @return Array
     *
     */
    public function retrieveInternal()
    {
        return $this->_path;
    }

    /**
     * retrieveExternal
     *
     * Returns all referers which are deemed "external" to this website's
     * domain.
     *
     * @return Array
     *
     */
    public function retrieveExternal()
    {
        return $this->_referer;
    }

    /**
     * retrieveAll
     *
     * Convenience method which returns a single, merged array comprising of the
     * result of retrieveInternal and retrieveExternal.
     *
     * @return Array
     *
     */
    public function retrieveAll()
    {
        return array_merge($this->retrieveExternal(), $this->retrieveInternal());
    }

}
