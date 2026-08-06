<?php
$f = "/home/u559276167/domains/shahabtech.com/public_html/omnireach/src/app/Services/System/Communication/DispatchService.php";
$c = file_get_contents($f);
$c = str_replace('LazyCollection::make($groups)', 'collect($groups)', $c);
file_put_contents($f, $c);
echo "REPLACED LAZYCOLLECTION SUCCESSFUL\n";
