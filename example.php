<?php
/**
 * This file can be used as a starting point to understand way OrientDB-PHP
 * works.
 * @author Anton Terekhov <anton@netmonsters.ru>
 * @copyright Copyright Anton Terekhov, NetMonsters LLC, 2011
 * @license https://github.com/AntonTerekhov/OrientDB-PHP/blob/master/LICENSE
 * @link https://github.com/AntonTerekhov/OrientDB-PHP
 * @package OrientDB-PHP
 * @subpackage Example
 */

$rootPassword = '60F3D52B4374C22B19F2EA5AD2812A45FB1C34985C2532D60E267AADB9E3E130';
$rootPassword = 'E1E7C5F7D645B20234AD2CD916B33FA5E0AE46F15C5653C8A0881238A3C33E3E';
$dbName = 'example';
$clusterName = 'default';

require_once 'OrientDB/OrientDB.php';

echo 'Connecting to server...' . PHP_EOL;
try {
    $db = new OrientDB('localhost', 2424);
}
catch (Exception $e) {
    die('Failed to connect: ' . $e->getMessage());
}

echo 'Connecting as root...' . PHP_EOL;
try {
    $connect = $db->connect('root', $rootPassword);
}
catch (OrientDBException $e) {
    die('Failed to connect(): ' . $e->getMessage());
}

try {
    $exists = $db->DBExists($dbName);
}
catch (OrientDBException $e) {
    die('Failed to execute DBExists(): ' . $e->getMessage());
}
if ($exists) {
    echo 'Deleting DB (in case of previous run failed)...' . PHP_EOL;
    try {
        $db->DBDelete($dbName);
    }
    catch (OrientDBException $e) {
        die('Failed to DBDelete(): ' . $e->getMessage());
    }
}

echo 'Creating DB...' . PHP_EOL;

try {
    $result = $db->DBCreate($dbName, OrientDB::DB_TYPE_LOCAL);

    echo 'Opening DB...' . PHP_EOL;
    $clusters = $db->DBOpen($dbName, 'writer', 'writer');
    foreach ($clusters['clusters'] as $cluster) {
        if ($cluster->name === $clusterName) {
            $clusterID = $cluster->id;
        }
    }
    if (empty($clusterID)) {
        throw new Exception("cluster {$clusterName} not found in \$clusters returns by \$db->DBOpen()");
    }

    echo 'Create record...' . PHP_EOL;
    $record = new OrientDBRecord();
    $record->data->FirstName = 'Bruce';
    $record->data->LastName = 'Wayne';
    $record->data->appearance = 1938;
    $record->data->addresses = array( // embedded document
        "shipping" => array(
            'Street' => '1, Boulevard de la plage',
            'ZipCode' => '0600',
            'City' => 'Nice',
            'Country' => 'France',
        ),
        "billing" => array(
            'Street' => '12, Rue romantique',
            'ZipCode' => '75001',
            'City' => 'Paris',
            'Country' => 'France',
        ),
    );

    $recordPos = $db->recordCreate($clusterID, $record);

    echo 'Created record position: ' . $recordPos . PHP_EOL . PHP_EOL;

    echo 'Load record...' . PHP_EOL;

    $recordLoaded = $db->recordLoad($clusterID . ':' . $recordPos);
    echo 'Load record result: ' . $recordLoaded . PHP_EOL . PHP_EOL;

    printf('%1$s %2$s first appears in %3$d' . PHP_EOL . PHP_EOL, $recordLoaded->data->FirstName, $recordLoaded->data->LastName, $recordLoaded->data->appearance);

    print_r($record->data->addresses); // embedded document correctly decoded
    echo "\n";

    echo 'Update record...' . PHP_EOL;

    $recordLoaded->data->appearance = 1939;
    $version = $db->recordUpdate($recordLoaded->recordID, $recordLoaded);

    echo 'Updated record version: ' . $version . PHP_EOL . PHP_EOL;

	$recordReLoaded = $db->recordLoad($clusterID . ':' . $recordPos);

    printf('No, %1$s %2$s first appears in %3$d!' . PHP_EOL . PHP_EOL, $recordReLoaded->data->FirstName, $recordReLoaded->data->LastName, $recordReLoaded->data->appearance);

    echo 'Delete record with old version (' . $recordLoaded->version . ') ...' . PHP_EOL;

    $result = $db->recordDelete($recordLoaded->recordID, $recordLoaded->version);

    echo 'Delete record with correct version (' . $version . ') ...' . PHP_EOL;

    $result = $db->recordDelete($recordLoaded->recordID, $version);
    echo 'Delete record result: ' . var_export($result, true) . PHP_EOL . PHP_EOL;

    echo 'Retry load record...' . PHP_EOL;

    $recordLoaded2 = $db->recordLoad($recordLoaded->recordID);
    echo 'Load record result: ' . var_export($recordLoaded2, true) . PHP_EOL . PHP_EOL;
}
catch (OrientDBException $e) {
    echo $e->getMessage() . PHP_EOL;
}


echo 'Deleting DB...' . PHP_EOL;
try {
    $db->DBDelete($dbName);
}
catch (OrientDBException $e) {
    die('Failed to DBDelete(): ' . $e->getMessage() . PHP_EOL);
}