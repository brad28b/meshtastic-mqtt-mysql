Note:

If you prefer Python, I've ported this script to Python <a href='https://github.com/brad28b/meshtastic-mqtt-mysql-python/'>here</a>

# meshtastic-mqtt-mysql

This PHP-CLI script, which is run as a shell script daemon, runs permanently in the background, subscribes to the Meshtastic JSON topic on your MQTT server, captures messages delivered from the mesh via MQTT, and imports them into MySQL tables.

Assumed infrastructure layout is: Your Meshtastic Node => Your local MQTT server => PHP-CLI MQTT subscriber => MySQL server

This script will capture JSON mesh messages of the following types:

nodeinfo

neighborinfo

text

telemetry

position

traceroute

# Requirements:
This PHP-CLI script is designed to be run as a shell script in a LMPM configuration (Linux, MQTT, PHP-CLI, MySQL).

Dependencies are as follows:

A meshtastic ESP32V3 based node, for example, a Heltec V3 (nRF nodes don't support MQTT => JSON currently).

php8.2-cli (see <a href='https://php.watch/articles/install-php82-ubuntu-debian'>here</a> for installation instructions) 

php8.2-mysql (use "sudo apt install php8.2-mysql" after installing php8.2-cli)

PhpMqtt (use "composer require php-mqtt/client")

Your own local MQTT server (I use Mosquitto), configured to allow anonymous access

Your own local mysql-server - the database schema is provided in meshtastic.sql

# Instructions:
1) Setup your MQTT server, and configure it to allow anonymous access
2) Configure your Meshtastic node to publish MQTT messages to the IP address of your MQTT server, using the topic "Meshtastic". Make sure that the JSON option is enabled.
3) Make sure that your main (Longfast) channel (usually 0) is set to send MQTT messages from the mesh to your MQTT server (in your smart phone Meshtastic app, ensure "Uplink enabled" is checked for channel 0 / Longfast)
4) Create your MySQL database. Call the database 'meshtastic'. You can use the database schema found in file meshtastic.sql
5) Create a user on your MySQL server, and grant it the necessary permissions to read and write to the database.
6) Edit monitor_mesh.php, and add in the credential values for your MQTT and MYSQL servers, and your Meshtastic node ID.
7) Give monitor_mesh.php execute permissions, using "chmod +x monitor_mesh.php"
8) To run monitor_mesh.php:

If you want to run in background mode, detached from your shell: "nohup ./monitor_mesh.php &> /dev/null &disown"

If you want to run it and view what's happening, just run it like this: "./monitor_mesh.php"

# Caveats
Meshtastic JSON messages contain some fields/elements that are reserved keywords in MySQL (for example, 'from', 'timestamp', and 'to')

To get around any issues you have with interacting with these columns in MySQL, escape them with back ticks, like this:

"SELECT payload_text, \`from\` FROM text WHERE payload_text IS NOT NULL"

Fields of type 'timestamp' are stored in the format they are receieved (Unix Epoch). Select these in a friendly format like this:

"SELECT FROM_UNIXTIME(timestamp) FROM nodeinfo"
