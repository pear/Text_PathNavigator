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

require_once 'PHPUnit/Framework.php';
require_once 'PHPUnit/TextUI/TestRunner.php';

chdir(dirname(__FILE__) . DIRECTORY_SEPARATOR . '..' . DIRECTORY_SEPARATOR);

require_once dirname(__FILE__) . '/PathNavigatorTest.php';

class Text_PathNavigator_AllTests
{
    public static function main()
    {
        PHPUnit_TextUI_TestRunner::run(self::suite());
    }

    public static function suite()
    {
        $suite = new PHPUnit_Framework_TestSuite('Text_PathNavigator package');
        $suite->addTestSuite('PathNavigatorTest');
        return $suite;
    }
}

if (PHPUnit_MAIN_METHOD == 'Text_PathNavigator_AllTests::main') {
    Text_PathNavigator_AllTests::main();
}

?>