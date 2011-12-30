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
 
if ($fp = @fopen('PHPUnit/Autoload.php', 'r', true)) {
    require_once 'PHPUnit/Autoload.php';
} elseif ($fp = @fopen('PHPUnit/Framework.php', 'r', true)) {
    require_once 'PHPUnit/Framework.php';
    require_once 'PHPUnit/TextUI/TestRunner.php';
} else {
    die('skip could not find PHPUnit');
}
fclose($fp);

require_once 'Text/PathNavigator.php';

class PathNavigatorTest extends PHPUnit_Framework_TestCase {
    function setUp() {
        $this->p1 = new Text_PathNavigator('/files/client1/files/woot', '/');
        $this->p2 = new Text_PathNavigator('/files/client2/files/woot', '/');
        $this->byNumSegs = array(
            0 => '//////////',
            1 => '/////one////',
            2 => 'two/segments',
            3 => '/three/segment/path/',
            4 => '///path/with/four/segments////'
        );
    }
    function testArrayAccessImpl() {
        $path = new Text_PathNavigator($this->byNumSegs[0], '/');
        $this->assertEquals(null, $path[0]);
        $path = new Text_PathNavigator($this->byNumSegs[1], '/');
        $this->assertEquals('one', $path[0]);
        $path = new Text_PathNavigator($this->byNumSegs[2], '/');
        $this->assertEquals('two', $path[0]);
        $this->assertEquals('segments', $path[1]);
        $path = new Text_PathNavigator($this->byNumSegs[3], '/');
        $this->assertEquals('three', $path[0]);
        $this->assertEquals('segment', $path[1]);
        $this->assertEquals('path', $path[2]);
        $path = new Text_PathNavigator($this->byNumSegs[4], '/');
        $this->assertEquals('path', $path[0]);
        $this->assertEquals('with', $path[1]);
        $this->assertEquals('four', $path[2]);
        $this->assertEquals('segments', $path[3]);
    }
    function testCountableImpl() {
        foreach ($this->byNumSegs as $count => $path) {
            $path = new Text_PathNavigator($path, '/');
            $this->assertEquals($count, count($path));
        }
    }
    function testNullPathsAreEqual() {
        $this->assertEquals(new Text_PathNavigator(null), new Text_PathNavigator(null));
    }
    function testNullPathToStringIsEmpty() {
        $nullPath = (string)new Text_PathNavigator(null);
        $this->assertEquals(true, empty($nullPath));
    }
    function testNullPathsWithDifferentSlashesAreNotEqual() {
        $this->assertNotEquals(new Text_PathNavigator(null, '/'), new Text_PathNavigator(null, '\\'));
    }
    function testExtraSlashesAreTrimmed() {
        $this->assertEquals('path/sub', (string)new Text_PathNavigator('///path/sub//', '/'));
    }
    function testP1RelativeToP2IsCorrect() {
        $this->assertEquals('../../../client1/files/woot', (string)$this->p1->relativeTo($this->p2));
    }
    function testGreedyAfterMatch() {
        $this->assertEquals('woot', (string)$this->p1->after('f.*s'));
    }
    function testNonGreedyAfterMatch() {
        $this->assertEquals('client1/files/woot', (string)$this->p1->after('f.*?s'));
    }
    function testPositiveSlice() {
        $this->assertEquals('client1/files/woot', (string)$this->p1->slice(1));
    }
    function testNegativeSlice() {
        $this->assertEquals('files/woot', (string)$this->p1->slice(-2));
    }
    function testBetweenWithNegativeSlice() {
        $this->assertEquals('files', (string)$this->p1->between('files', 'woot')->slice(-1));
    }
    function testMapCanSkipSegments() {
        $uri = new Text_PathNavigator('/base/accounts/users/edit/1', '/');
        extract($uri->map('//controller/method/id'));
        $this->assertEquals('users', $controller);
        $this->assertEquals('edit', $method);
        $this->assertEquals('1', $id);
    }
    function testSegmentsAreListable() {
        $uri = new Text_PathNavigator('/users/edit/1', '/');
        list($controller, $method, $id) = iterator_to_array($uri);
        $this->assertEquals('users', $controller);
        $this->assertEquals('edit', $method);
        $this->assertEquals('1', $id);
    }
    function testCdFromP1ToParentIsCorrect() {
        $this->assertEquals('files/client1/files', (string)$this->p1->cd('..'));
    }
    function testCdFromP1BeyondParentsIsNullPath() {
        $this->assertEquals(new Text_PathNavigator(null, '/'), $this->p1->cd('../../../..'));
    }
    function testCdFromP1ToP2IsRelative() {
        $this->assertEquals("$this->p1/$this->p2", (string)$this->p1->cd($this->p2));
    }
    function testCdFromP1ToP2RelativeToP1GivesP2() {
        $p2RelativeToP1 = $this->p2->relativeTo($this->p1);
        $this->assertEquals($this->p2, $this->p1->cd($p2RelativeToP1));
    }
}
