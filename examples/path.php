<?php

require_once 'Text/PathNavigator.php';

$p = new Text_PathNavigator('/the/golden/path', '/');

echo "<b>$p->path</b>";

echo '<br><br>indexing methods';
echo '<br>get(2)='.$p->get(2);
echo '<br>get(golden)='.$p->get('golden');

echo '<br><br>slicing';
echo '<br>slice(1)='.$p->slice(1);
echo '<br>slice(-2,1)='.$p->slice(-2, 1);
echo '<br>slice(-1)='.$p->slice(-1);

echo '<br><br>normalizing path according to slash value "/"';
echo '<br>original path="\test\ok\2\3\"';
$p->load('\test\ok\2\3');
echo '<br>normalized path='.$p->path;

echo '<br><br>loading from arrays and strings';
$p->load(array('test'=>'ok', 2, 3));
echo '<br>from array='.$p->path;
$p->load('/test/ok/2/3');
echo '<br>from string='.$p->path;

echo '<br><br>toArray with no offset<br>';
print_r($p->toArray());

echo '<br><br>toArray with offset 1<br>';
print_r($p->toArray(1));

if ($nodes = $p->getIterator()) {
    echo '<br><br>using an ArrayIterator';
    foreach ($nodes as $i => $node) {
        echo "<br>$i = $node";
    }
}

/*

EXAMPLE OUTPUT

the/golden/path

indexing methods
get(2)=path
get(golden)=path

slicing
slice(1)=golden/path
slice(-2,1)=golden
slice(-1)=path

normalizing path according to slash value "/"
original path="\test\ok\2\3\"
normalized path=test/ok/2/3

loading from arrays and strings
from array=test/ok/2/3
from string=test/ok/2/3

toArray with no offset
Array ( [test] => ok [2] => 3 )

toArray with offset 1
Array ( [ok] => 2 [3] => )

using an ArrayIterator
0 = test
1 = ok
2 = 2
3 = 3

*/

?>