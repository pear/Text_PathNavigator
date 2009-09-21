<?php

/**
 * Facilitates navigation of path strings
 *
 * PHP version 5
 *
 * @category  Text
 * @package   PathNavigator
 * @author    Denny Shimkoski <bytebrite@gmail.com>
 * @copyright 2006-2009 Denny Shimkoski
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Text_PathNavigator
 */
 
require_once 'PEAR.php';

/**
 * Text_PathNavigator
 *
 * @category Text
 * @package  PathNavigator
 * @author   Denny Shimkoski <bytebrite@gmail.com>
 * @license  http://www.opensource.org/licenses/mit-license.html MIT
 * @version  Release: @package_version@
 * @link     http://pear.php.net/package/Text_PathNavigator
 */

class Text_PathNavigator implements ArrayAccess, Countable, Iterator
{
    /**
     * Directory separator, i.e, forward or backward slash
     *
     * @var string
     */
    protected $slash;
    /**
     * Normalized path string
     *
     * @var string
     */
    protected $path;
    /**
     * Normalized path after explode() on $slash
     *
     * @var array
     */
    protected $segments = array();
    /**
     * Current path segment index (Iterator implementation)
     *
     * @var int
     */
    protected $currentIdx = 0;

    /**
     * Constructs a new Text_PathNavigator object
     *
     * @param array|string $path  Path string or array of path segments
     * @param string       $slash character used to separate path segments
     *
     * @return string
     * @access public
     */
    public function __construct($path, $slash = DIRECTORY_SEPARATOR)
    {
        $this->slash = $slash;
        $this->path = $this->normalizePath($path);
        if ($this->path !== null) {
            $this->segments = explode($this->slash, $this->path);
        }
    }
    /**
     * Returns path string
     *
     * @return string
     * @access public
     */
    public function __toString()
    {
        return implode($this->slash, $this->segments);
    }
    /**
     * Removes all leading and trailing slashes from given path string/segment array.
     *
     * @param array|string $path Path string or array of path segments
     *
     * @return string
     * @access protected
     */
    protected function normalizePath($path)
    {
        if (is_array($path)) {
            $path = implode($this->slash, $path);
        }
        
        $offset = 0;
        $len = strlen($path);
        
        // trim leading slashes
        while ($path{$offset} == $this->slash) {
            if (++$offset == $len) {
                return null;
            }
        }
        
        // trim trailing slashes
        while ($path{$len - 1} == $this->slash) {
            --$len;
        }
        
        return substr($path, $offset, $len - $offset);
    }
    /**
     * Slices current path and returns it as a new Text_PathNavigator object
     *
     * @param int $offset If offset is non-negative, the new path will start
                                           at that offset in the path segment array.
                                           If offset is negative, the new path will start
                                           that far from the end of the path segment array.
     * @param int $length If length is given and is positive, the new path
                                          will have that many segments in it. If length is given
                                          and is negative, the new path will stop that many
                                          segments from the end of the path segment array.
                                          If it is omitted, the new path will have everything
                                          from offset up until the end of the path segment array.
     *
     * @return PEAR_Text_PathNavigator
     * @access public 
     */
    public function slice($offset = 0, $length = null)
    {
        // PHP gets all confused if you pass a null into the $length param
        // of array_slice(). hence...
        $segments = $length
            ? array_slice($this->segments, $offset, $length)
            : array_slice($this->segments, $offset);
            
        $path = implode($this->slash, $segments);
        
        return new Text_PathNavigator($path, $this->slash);
    }
    /**
     * Returns path substring following the given regular expression
     *
     * @param string $pattern regular expression to use for matching
     *
     * @return Text_PathNavigator
     * @access public 
     */
    public function after($pattern)
    {
        $path = null;
        if (preg_match("!$pattern!", $this->path, $matches, PREG_OFFSET_CAPTURE)) {
            $path = substr($this->path, $matches[0][1] + strlen($matches[0][0]));
        }
        return new Text_PathNavigator($path, $this->slash);
    }
    /**
     * Returns path substring preceding the given regular expression
     *
     * @param string $pattern regular expression to use for matching
     *
     * @return Text_PathNavigator
     * @access public 
     */
    public function before($pattern)
    {
        $path = null;
        if (preg_match("!$pattern!", $this->path, $matches, PREG_OFFSET_CAPTURE)) {
            $path = substr($this->path, 0, $matches[0][1]);
        }
        return new Text_PathNavigator($path, $this->slash);
    }
    /**
     * Returns path substring between the given regular expressions $start and $end
     *
     * @param string $start regular expression
     * @param string $end   regular expression
     *
     * @return Text_PathNavigator
     * @access public 
     */
    public function between($start, $end)
    {
        return $this->after($start)->before($end);
    }
    /**
     * Returns this path relative to another one. Assumes both paths are absolute.
     *
     * If located in a path $p2, we could navigate to this one
     * using $p2->cd($this->relativeTo($p2))
     *
     * @param mixed $path new path will be expressed in relation to this
     *
     * @return Text_PathNavigator
     * @access public 
     */
    public function relativeTo($path)
    {
        // given path can also be a string or an array of segments,
        // although Text_PathNavigator is preferred
        if (!$path instanceof Text_PathNavigator) {
            $path = new Text_PathNavigator($path, $this->slash);
        }
        
        // array to hold new path segments
        $_segments = array();
        
        // make a copy of path segments for shift operation
        $segments = $this->segments;
        
        // enable shift
        $shift = true;
        
        // for each segment in the given path....
        foreach ($path as $i => $segment) {
            // this effectively truncates any "absolute" portion of the two paths,
            // i.e., beginning segments shared in common
            if ($shift && isset($this->segments[$i]) && $this->segments[$i] == $segment) {
                array_shift($segments);
            } else {
                // escape from the remaining elements of the given path
                $_segments[] = '..';
                // shifting no longer allowed since a differing segment was encountered
                $shift = false;
            }
        }
        
        // now just tack on any segments left over from the shift operation...
        foreach ($segments as $segment) {
            $_segments[] = $segment;
        }
        
        // ...and return a new Text_PathNavigator object
        return new Text_PathNavigator($_segments, $this->slash);
    }
    /**
     * Navigates from this path to another using the specified relative path.
     *
     * @param mixed $path relative path
     *
     * @return Text_PathNavigator
     * @access public 
     */
    public function cd($path)
    {
        // given path can also be a string or an array of segments
        // (probably a string, i.e., '../../some/dir')
        if (!$path instanceof Text_PathNavigator) {
            $path = new Text_PathNavigator($path, $this->slash);
        }
        
        // make a copy of path segments for pop operation
        $_segments = $this->segments;
        
        // for each segment in the given path....
        foreach ($path as $segment) {
            // navigate up and down the hierarchy
            if ($segment == '..') {
                array_pop($_segments);
            } else {
                $_segments[] = $segment;
            }
        }
        
        // ...and return a new Text_PathNavigator object
        return new Text_PathNavigator($_segments, $this->slash);
    }
    /**
     * Maps path segments to variable names given in $template.
     * Suitable for use with PHP's extract() function.
     *
     * @param string $template e.g., '/controller/method/'
     *
     * @return array
     * @access public 
     */
    public function map($template)
    {
        $map = explode($this->slash, $template);
        foreach ($map as $i => $key) {
            $segments[$key] = isset($this->segments[$i]) ? $this->segments[$i] : null;
        }
        return $segments;
    }
    /**
     * Check if a particular segment exists (ArrayAccess implementation)
     *
     * @param int $i path segment index
     *
     * @return boolean
     * @access public 
     */
    public function offsetExists($i)
    {
        return isset($this->segments[$i]);
    }
    /**
     * Get a particular segment (ArrayAccess implementation)
     *
     * @param int $i path segment index
     *
     * @return string
     * @access public 
     */
    public function offsetGet($i)
    {
        return $this->segments[$i];
    }
    /**
     * Throws Exception for the purpose of immutability (ArrayAccess implementation)
     *
     * @param int $i path segment index
     *
     * @throws Exception for the purpose of immutability
     * @return void
     * @access public 
     */
    public function offsetUnset($i)
    {
        throw new PEAR_Exception('Text_PathNavigator is immutable');
    }
    /**
     * Throws Exception for the purpose of immutability (ArrayAccess implementation)
     *
     * @param int    $i   path segment index
     * @param string $val path segment
     *
     * @throws Exception for the purpose of immutability
     * @return void
     * @access public 
     */
    public function offsetSet($i, $val)
    {
        throw new PEAR_Exception('Text_PathNavigator is immutable');
    }
    /**
     * Returns the number of path segments (Countable implementation)
     *
     * @return int
     * @access public 
     */
    public function count()
    {
        return count($this->segments);
    }
    /**
     * Returns current path segment (Iterator implementation)
     *
     * @return string
     * @access public 
     */
    public function current()
    {
        return $this->segments[$this->currentIdx];
    }
    /**
     * Returns index of current path segment (Iterator implementation)
     *
     * @return int
     * @access public 
     */
    public function key()
    {
        return $this->currentIdx;
    }
    /**
     * Advances to next path segment (Iterator implementation)
     *
     * @return void
     * @access public
     */
    public function next()
    {
        ++$this->currentIdx;
    }
    /**
     * Rewinds to first path segment (Iterator implementation)
     *
     * @return void
     * @access public 
     */
    public function rewind()
    {
        $this->currentIdx = 0;
    }
    /**
     * Checks if current path segment index is valid (Iterator implementation)
     *
     * @return bool
     * @access public 
     */
    public function valid()
    {
        return isset($this->segments[$this->currentIdx]);
    }
}
