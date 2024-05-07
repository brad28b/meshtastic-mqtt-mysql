#!/usr/bin/php8.2
<?php

// Include required libraries
require 'vendor/autoload.php';

// Import necessary classes
use PhpMqtt\Client\MqttClient;

// Enable async signals
pcntl_async_signals(true);

// MQTT server configuration
$server = '192.168.X.X';  // IP address or hostname of your MQTT server
$port = 1883; // Port number of your MQTT server
$clientId = 'mesh-monitor'; // this isn't really necessary
$meshtastic_node = ""; // Unique node ID of the Meshtastic radio (Example: !da5ed0a0)

// MQTT topic to subscribe to
$topic = 'Meshtastic/2/json/LongFast/' . $meshtastic_node;

// MySQL database configuration
$servername = "localhost";
$username = "YOUR_MYSQL_USERNAME";
$password = "YOUR_MYSQL_PASSWORD";
$dbname = "meshtastic";

// You should not need to modify anything beyond here

// Create MySQL connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check MySQL connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Define function to process JSON message into MySQL tables
function process_JSON($data, $type) {
    global $conn;

    $sql_columns = "";
    $sql_values = "";
    $params = array();

    // Build the SQL column names and values
    foreach ($data as $field => $value) {
        if ($field !== 'payload') {
            $sql_columns .= "`$field`,";
            $sql_values .= "?,";
            $params[] = $value;
        }
    }

    foreach ($data['payload'] as $field => $value) {
        if (is_array($value)) {
            $field_str = '';
            foreach ($value as $item) {
                if (is_array($item)) {
                    $field_str .= "node_id:".$item['node_id'].":snr:".$item['snr']."|";
                } else {
                    $field_str .= $item."|";
                }
            }
            // Remove trailing '|'
            $field_str = rtrim($field_str, '|');
            $sql_columns .= "`payload_$field`,";
            $sql_values .= "?,";
            $params[] = $field_str;
        } else {
            $sql_columns .= "`payload_$field`,";
            $sql_values .= "?,";
            $params[] = $value;
        }
    }

    // Remove trailing commas
    $sql_columns = rtrim($sql_columns, ",");
    $sql_values = rtrim($sql_values, ",");

    // Prepare the SQL statement
    $sql = "INSERT INTO `$type` ($sql_columns) VALUES ($sql_values)";
    $stmt = $conn->prepare($sql);

    if (!$stmt) {
        echo "Error preparing statement: " . $conn->error;
        return;
    }

    // Bind parameters
    $types = str_repeat('s', count($params)); // Assuming all parameters are strings
    $stmt->bind_param($types, ...$params);

    // Execute SQL query
    if ($stmt->execute()) {
        echo "New record created successfully\n\n";
    } else {
        echo "Error executing statement: " . $stmt->error;
    }

    $stmt->close();
}

// Create MQTT client instance
$mqtt = new MqttClient($server, $port, $clientId);

// Handle interruption gracefully
pcntl_signal(SIGINT, function (int $signal, $info) use ($mqtt) {
    $mqtt->interrupt();
});

// Connect to MQTT broker
$mqtt->connect();

// Subscribe to MQTT topic
$mqtt->subscribe($topic, function ($topic, $message, $retained, $matchedWildcards) use ($conn) {
    // Decode JSON message
    echo "$message\n\n";
    $data = json_decode($message, TRUE);

    // Check if this JSON is of a type we know about. If it is, send it to the process_JSON function
    $known_types = array('traceroute', 'telemetry', 'text', 'neighborinfo', 'nodeinfo', 'position');
    if (in_array($data['type'], $known_types)) {
        process_JSON($data, $data['type']);
    }

}, 0);

// Start MQTT event loop
$mqtt->loop(true);

// Disconnect from MQTT broker
$mqtt->disconnect();

// Close MySQL connection
$conn->close();

?>
