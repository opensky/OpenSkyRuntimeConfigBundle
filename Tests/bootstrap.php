<?php

if (!($loader = @include __DIR__.'/../vendor/autoload.php')) {
    echo "\nYou need to install the project dependencies using Composer\n\n";
    exit(1);
}
