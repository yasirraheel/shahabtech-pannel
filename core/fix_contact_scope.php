<?php
$f = "/home/u559276167/domains/shahabtech.com/public_html/omnireach/src/app/Models/Contact.php";
$c = file_get_contents($f);
$old = "static::addGlobalScope('user', function (Builder \$builder) {
            \$builder->where('user_id', auth()->id());
        });";

$new = "static::addGlobalScope('user', function (Builder \$builder) {
            if (auth()->check()) {
                \$builder->where('user_id', auth()->id());
            }
        });";

$c = str_replace($old, $new, $c);
file_put_contents($f, $c);
echo "CONTACT SCOPE UPDATED SUCCESSFULLY\n";
