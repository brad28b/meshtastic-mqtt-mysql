# meshtastic-mqtt-mysql
This PHP-CLI script, which is run as a shell script daemon, subscribes to your MQTT server on localhost, captures messages delivered from the mesh via MQTT, and imports them into MySQL tables.
Assumed infrastructure layout is: Your Meshtastic Node => Your local MQTT server
# Requirements:
This PHP-CLI script is designed to be run as a shell script in a LMPM configuration (Linux, MQTT, PHP-CLI, MySQL).

Dependencies are as follows:

php-cli8.2 see <a href='test'>here</a> for installation instructions 
php-mysql8.2
PhpMqtt
Your own local MQTT server (I use Mosquitto), configured to allow anonymous access
Your own local mysql-server

