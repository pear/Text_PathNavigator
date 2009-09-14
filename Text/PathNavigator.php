<?php
/**
 * Provides convenient access to path substrings  
 *
 * PHP versions 4 and 5
 *
 * @category   Text
 * @package    PathNavigator
 * @author     Denny Shimkoski <denny@bytebrite.com>
 * @copyright  2006 Denny Shimkoski
 * @license    http://www.opensource.org/licenses/mit-license.html MIT
 * @link       http://pear.php.net/package/Text_PathNavigator
 */

/**
 * The Text_PathNavigator class provides convenient access to "/path/sub/strings".
 *  
 * These strings can be accessed in various ways:
 *
 * 1. by numeric index (e.g., index 1 of /pages/staff.html returns "staff.html")
 * 2. by variable name (e.g., index "id" of /pages/id/1 returns 1)
 * 3. using the slice() method 
 * 4. using the toArray() method
 * 5. in PHP 5, using the getIterator() method
 * 
 * When retrieving values by variable name, it is assumed that the entry 
 * following the first occurence of the given string is the value
 * to be assigned.
 *
 * Example 1.
 * <code>
 * require_once 'Text/PathNavigator.php';
 * $p = new Text_PathNavigator('/the/golden/path', '/');
 * echo $p->get(2);          // prints 'path'
 * echo $p->get('golden');   // prints 'path'
 * echo $p->slice(1);        // prints 'golden/path'
 * echo $p->slice(-2, 1);    // prints 'golden'
 * echo $p->slice(-1);       // prints 'path'
 * </code>
 *    
 * @category   Text
 * @package    PathNavigator
 * @author     Denny Shimkoski <denny@bytebrite.com>
 * @copyright  2006 Denny Shimkoski
 * @license    http://www.opensource.org/licenses/mit-license.html MIT
 * @link       http://pear.php.net/package/Text_PathNavigator
 */
class Text_PathNavigator
{
    /**
     * @var string
     */
    var $path;
    /**
     * @var string
     */
    var $slash;
    /**
     * Path after explode()
     *         
     * @var array
     */
    var $vars;
    /**
     * Constructor
     *
     * @param $path array (e.g., array('test'=>'ok', 2, 3))
     *              or string (e.g., '/test/ok/2/3')
     * @param $slash
     * @return Text_PathNavigator
     */
    function Text_PathNavigator($path, $slash = DIRECTORY_SEPARATOR)
    {
        $this->slash = $slash;
        $this->load($path);
    }
    /**
     * Loads a new path into the object
     *
     * @return false|array
     * @access public     
     */
    function load($path)
    {
        $slash = $this->slash;
        if (is_array($path)) {
            foreach ($path as $i => $var) {
                $vars[] = is_int($i)
                        ? $var
                        : "$i$slash$var";
            }
            $path = implode($slash, $vars);
        }
        $path = str_replace(array('\\', '/'), $slash, $path);
        $this->path = substr($path, 0, 1) == $slash
                    ? substr($path, 1)
                    : $path;
        $this->vars = empty($this->path)
                    ? false
                    : explode($slash, $this->path);
        return $this->vars;
    }
    /**
     * Returns substrings by index or variable name
     * 
     * If $key appears to be a numeric index, attempts to return
     * the corresponding substring (i.e., get(1) returns "val" 
     * from "/key/val"). Otherwise, searches path for a substring
     * that matches $key and attempts to return the following entry.
     * If the following entry is non-existent, null is returned.
     * Returns false as a last resort.
     *           
     * @param string $key the numeric index or variable name
     * @return null|false|string
     * @access public
     */
    function get($key)
    {
        if (is_int($key)) {
            return $this->slice($key, 1);
        }
        if (($id = array_search($key, $this->vars)) !== false) {
            return isset($this->vars[$id + 1])
                   ? $this->vars[$id + 1]
                   : null;
        }
        return false;
    }
    /**
     * Returns an ArrayIterator, starting at offset (PHP 5 only)
     * 
     * @param int $offset the starting position
     * @return null|object
     * @access public
     */
    function getIterator($offset = 0)
    {
        if (class_exists('ArrayIterator')) {
            $slice = array_slice($this->vars, $offset);
            if (is_array($slice)) {
                return new ArrayIterator($slice);
            }
        }
        return null;
    }
    /**
     * Returns path as an associative array, starting at offset
     * 
     * Maps '/a/hello/b/world' to array('a' => 'hello', 'b' => 'world').
     *           
     * @param int $offset the starting position
     * @return false|array
     * @access public
     */
    function toArray($offset = 0)
    {
        $path = $offset ? $this->slice($offset) : $this->path;
        $path = explode($this->slash, $path);
        while ($var = current($path)) {
            $val = ($nextVal = next($path)) !== false
                 ? $nextVal
                 : null;
            $arr[$var] = $val;
            if ($val) next($path);
        }
        return isset($arr) ? $arr : false;
    }
    /**
     * Returns a slice from the path (see PHP's slice function for details) 
     *           
     * @param integer $offset
     * @param integer $length
     * @return false|string
     * @access public
     */
    function slice($offset, $length = null)
    {
        $a = $length 
           ? array_slice($this->vars, $offset, $length)
           : array_slice($this->vars, $offset);
        if (is_array($a)) return implode($this->slash, $a);
        return false;
    }
}

?> 