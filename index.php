<?php

include 'vendor/autoload.php';

use ShipMonk\Simulation\ShipMonkSimulation;

try {
    ShipMonkSimulation::run();
} catch (\Throwable $e) {
    echo "Simulation Error: " . $e->getMessage() . "\n";
    echo "   File: " . $e->getFile() . ":" . $e->getLine() . "\n";
}