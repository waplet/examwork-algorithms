<?php

require_once __DIR__ . "/include/functions.php";
require_once __DIR__ . "/include/FlowNetwork.php";

/**
 * Preparing data
 */
$parameters = [];

parse_str(implode('&', array_slice($argv, 1)), $parameters);

if ($argc == 1) {
    die("No input file specified");
}

$inputFile = $argv[1];
if (!file_exists($inputFile)) {
    die("Input file not found");
}

$index = 0; // from which to start count edges

// rewrites
// Type
if (isset($parameters['type'])) {
    $type = (boolean) $parameters['type'];
}
// Index
if (isset($parameters['index'])) {
    $index = (boolean) $parameters['index'];
}

$graph = getGraph($inputFile);
$houses = getHouses($graph, $index);


/**
 * Algorithm part starts
 */
$paths = iterativePaths($graph['edges'], $houses['pooh'], $houses['piglet']);
$edgedPaths = getPathsToEdges($paths);
$honeyableEdges = getHoneyableEdges($edgedPaths);
printOutput($honeyableEdges, false);

$flowNetwork = new FlowNetwork();
for ($i = $houses['pooh']; $i <= $houses['piglet']; $i++) {
    $flowNetwork->addVertex($i);
}

foreach ($graph['edges'] as $from => $edges) {
    foreach ($edges as $to) {
        if ($from == $to) {
            continue; // skip loops
        }
        $flowNetwork->addEdge($from, $to);
    }
}
$flow = $flowNetwork->maxFlow($houses['pooh'], $houses['piglet']);
echo "\n";
// print_r($flowNetwork->flow);
// var_dump($flow);