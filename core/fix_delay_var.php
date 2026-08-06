<?php
$f = "/home/u559276167/domains/shahabtech.com/public_html/omnireach/src/app/Services/System/Communication/DispatchService.php";
$c = file_get_contents($f);

$oldBlock = "->map(function (\$chunk) use (\$gatewayId, \$dispatchId, \$dispatchType, \$userId, \$channel, \$pipe, &\$batches, \$delay, &\$logCounter) {

                                        \$logCounter++;
                                        \$ids      = collect(\$chunk)->pluck('id')->toArray();
                                        \$delay    = \$delay * \$logCounter;

                                        \$this->storeDispatchDelay(\$gatewayId, \$channel->value, \$dispatchId, \$dispatchType, \$delay, \$userId);
                                        \$job = ProcessDispatchLogBatch::dispatch(\$ids, \$channel, \$pipe, false)
                                                                           ->delay(now()->addSeconds(\$delay));
                                        \$batches[] = \$job;
                                   })->all();";

$newBlock = "->map(function (\$chunk) use (\$gatewayId, \$dispatchId, \$dispatchType, \$userId, \$channel, \$pipe, &\$batches, \$delayPerMessage, &\$cumulativeDelay, &\$logCounter) {

                                        \$logCounter++;
                                        \$ids      = collect(\$chunk)->pluck('id')->toArray();
                                        \$cumulativeDelay += (\$delayPerMessage * count(\$chunk));

                                        \$this->storeDispatchDelay(\$gatewayId, \$channel->value, \$dispatchId, \$dispatchType, \$cumulativeDelay, \$userId);
                                        \$job = ProcessDispatchLogBatch::dispatch(\$ids, \$channel, \$pipe, false)
                                                                           ->delay(now()->addSeconds(\$cumulativeDelay));
                                        \$batches[] = \$job;
                                   })->all();";

$c = str_replace($oldBlock, $newBlock, $c);
file_put_contents($f, $c);
echo "DELAY VARIABLE FIX APPLIED\n";
