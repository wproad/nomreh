<?php

use Nomreh\Core\Logger;

function nomreh_log($message){
    $logger = new Logger();
    $logger->log_debug($message);
}