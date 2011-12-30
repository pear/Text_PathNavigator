<?php

/**
 * Unit tests for Text_PathNavigator
 *
 * PHP version 5
 *
 * @category  Text
 * @package   PathNavigator
 * @author    Denny Shimkoski <bytebrite@gmail.com>
 * @license   http://www.opensource.org/licenses/mit-license.html MIT
 * @version   SVN: $Id$
 * @link      http://pear.php.net/package/Text_PathNavigator
 */

if (!defined('PHPUnit_MAIN_METHOD')) {
    define('PHPUnit_MAIN_METHOD', 'Text_PathNavigator_AllTests::main');
}

if ($fp = @fopen('PHPUnit/Autoload.php', 'r', true)) {
    require_once 'PHPUnit/Autoload.php';
} elseif ($fp = @fopen('PHPUnit/Framework.php', 'r', true)) {
    require_once 'PHPUnit/Framework.php';
    require_once 'PHPUnit/TextUI/TestRunner.php';
} else {
    die('skip could not find PHPUnit');
}
fclose($fp);

class Text_PathNavigator_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Text_PathNavigator package');

        $dir = new GlobIterator(dirname(__FILE__) . '/*Test.php');
        $suite->addTestFiles($dir);

        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Text_PathNavigator_AllTests::main') {
    Text_PathNavigator_AllTests::main();
}

?>
