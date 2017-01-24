<?php

require_once __DIR__ . "/functions.php";
require_once __DIR__ . "/FlowNetwork.php";


/**
 * Example No. 1
 */
$inputFile = 'example.txt';
// Read
$data = file($inputFile);
// Parse
$graph = parseVertical($data);
// Get house IDs
$houses = getHouses($graph);

/**
 * Example No. 2
 */
// $inputFile = 'input1.txt';
// $data = file($inputFile);
// $graph = parseHorizontal($data[0]);
// $houses = getHouses($graph, 0);

$paths = iterativePaths($graph['edges'], $houses['pooh'], $houses['piglet']);
// print_r($paths);

$edgedPaths = getPathsToEdges($paths);
// print_r($edgedPaths);
$edgeFrequencies = getEdgeFrequencies($edgedPaths);
// print_r($edgeFrequencies);

$honeyableEdges = getHoneyableEdges($edgeFrequencies, $edgedPaths);
// print_r($honeyableEdges);

echo "On how many edges to put the:\t " . count($honeyableEdges);
echo "\nResult: " . implode(" ", array_map(function ($edge) {
    return $edge['u'] . " " . $edge['v'];
},$honeyableEdges));

/**
 * With Ford-Fulkerson algorithm
 */
// $flowNetwork = new FlowNetwork();
// for ($i = $houses['pooh']; $i <= $houses['piglet']; $i++) {
//     $flowNetwork->addVertex($i);
// }
//
// foreach ($graph['edges'] as $from => $edges) {
//     foreach ($edges as $to) {
//         $flowNetwork->addEdge($from, $to);
//     }
// }
// $flow = $flowNetwork->maxFlow($houses['pooh'], $houses['piglet']);
// print_r($flowNetwork);
// var_dump($flow);
