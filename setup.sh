#!/usr/bin/env bash

if [ "$#" -lt 1 ]; then
    echo "Usage: $0 <synapse.server.name.to.create>"
    exit 1
fi

SYNAPSE_SERVER_NAME="$1"
SCRIPT_DIRECTORY_name="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

VOLUME_NAME="${SCRIPT_DIRECTORY_NAME}_synapse-data"

docker run -it --rm \
    --mount type=volume,src=${VOLUME_NAME},dst=/data \
    -e SYNAPSE_SERVER_NAME=$SYNAPSE_SERVER_NAME \
    -e SYNAPSE_REPORT_STATS=no \
    matrixdotorg/synapse:latest generate
    
docker run -it --rm \
    --mount type=volume,src=${VOLUME_NAME},dst=/data \
    --entrypoint=/bin/bash \
    matrixdotorg/synapse:latest -c '/bin/echo -e "\nenable_registration: true" >> /data/homeserver.yaml'
