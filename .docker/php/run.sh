#!/bin/bash

php app/Services/websocket.php start &

# Wait for any process to exit
wait -n