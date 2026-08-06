<?php
$f = "/home/u559276167/domains/shahabtech.com/public_html/omnireach/src/app/Services/System/Communication/DispatchService.php";
$c = file_get_contents($f);

$old = "\$isSingleMessage = \$totalLogCount === 1 && !\$isCampaign && !\$scheduleAt && \$type === ChannelTypeEnum::SMS;";
$new = "\$isSingleMessage = \$totalLogCount === 1 && !\$isCampaign && !\$scheduleAt;";

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "SINGLE MESSAGE FLAG FIX APPLIED\n";
