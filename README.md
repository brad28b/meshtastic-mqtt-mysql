# meshtastic-mqtt-mysql
This PHP-CLI script, which is run as a shell script daemon. It runs permanently in the background, subscribes to the Meshtastic JSON topic on your MQTT server, captures messages delivered from the mesh via MQTT, and imports them into MySQL tables.
Assumed infrastructure layout is: Your Meshtastic Node => Your local MQTT server => PHP-CLI MQTT subscriber => MySQL server
This script will grab the JSON messages your node hears, and push them into MySQL.
# Requirements:
This PHP-CLI script is designed to be run as a shell script in a LMPM configuration (Linux, MQTT, PHP-CLI, MySQL).

Dependencies are as follows:

php8.2-cli (see <a href='https://php.watch/articles/install-php82-ubuntu-debian'>here</a> for installation instructions) 

php8.2-mysql (use "sudo apt install php8.2-mysql" after installing php8.2-cli)

PhpMqtt (use "composer require php-mqtt/client")

Your own local MQTT server (I use Mosquitto), configured to allow anonymous access

Your own local mysql-server - the database schema is provided below.

# Instructions:
1) Setup your MQTT server, and configure it to allow anonymous access
2) Configure your Meshtastic node to deliver MQTT
3) Create your MySQL database. Called the database 'meshtastic', and use the database schema found in file meshtastic.sql
4) Edit monitor_mesh.php, and addin the values for your MQTT and MYSQL credentials, and your Meshtastic node ID.
5) Give monitor_mesh.php execute permissions, using "chmod +x monitor_mesh.php"
6) To run monitor_mesh.php:

If you want to run in background mode, detached from your shell: "nohup ./monitor_mesh.php &> /dev/null &disown"
If you want to run in and view what's happening, just run it like this: "./monitor_mesh.php"

