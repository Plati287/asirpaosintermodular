#!/bin/bash

SERVIDOR_PRINCIPAL="98.87.237.212"
ELASTIC_IP_ALLOC="eipalloc-0df8c6f7b3571f595"
NAS_INSTANCE_ID="i-0c2c7b849b33a6df4"
LOG="/home/ubuntu/failover.log"
ESTADO="/home/ubuntu/.failover_estado"

if curl -sk --max-time 5 https://$SERVIDOR_PRINCIPAL > /dev/null 2>&1; then
    if [ -f "$ESTADO" ] && [ "$(cat $ESTADO)" = "failover" ]; then
        echo "$(date) - Servidor principal recuperado. Devolviendo Elastic IP..." >> $LOG
        WEB_INSTANCE_ID="i-03648d71e9dfcc4b6"
        aws ec2 associate-address --instance-id $WEB_INSTANCE_ID --allocation-id $ELASTIC_IP_ALLOC
        sudo docker compose -f /home/ubuntu/tienda_online_last/docker-compose.yml stop nginx php
        echo "normal" > $ESTADO
    fi
else
    if [ ! -f "$ESTADO" ] || [ "$(cat $ESTADO)" = "normal" ]; then
        echo "$(date) - Servidor principal caido. Tomando Elastic IP..." >> $LOG
        aws ec2 associate-address --instance-id $NAS_INSTANCE_ID --allocation-id $ELASTIC_IP_ALLOC
        sudo docker compose -f /home/ubuntu/tienda_online_last/docker-compose.yml start nginx php
        echo "failover" > $ESTADO
    fi
fi
