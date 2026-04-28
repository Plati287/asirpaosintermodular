#!/bin/bash

SERVIDOR_WEB="172.31.27.214"
APP_DIR="/home/ubuntu/tienda_online_last/app"
BACKUP_DIR="/home/ubuntu/backups"
LOG="/home/ubuntu/sync.log"
FECHA=$(date +%Y%m%d_%H%M%S)

mkdir -p $BACKUP_DIR

echo "$(date) - Sincronizando archivos..." >> $LOG
rsync -avz --delete --exclude='includes/config.php' ubuntu@$SERVIDOR_WEB:$APP_DIR/ $APP_DIR/
echo "$(date) - Sincronización completada" >> $LOG

HORA=$(date +%H)
if [ "$HORA" = "02" ]; then
    echo "$(date) - Haciendo backup de BD..." >> $LOG
    ssh ubuntu@$SERVIDOR_WEB "sudo docker exec tienda_online_last-db-1 mysqldump -uroot -proot tienda_online" > $BACKUP_DIR/bd_$FECHA.sql
    echo "$(date) - Backup BD completado: bd_$FECHA.sql" >> $LOG
    find $BACKUP_DIR -name "*.sql" -mtime +7 -delete
fi

if [ "$HORA" = "02" ]; then
    echo "$(date) - Haciendo backup de archivos web..." >> $LOG
    tar -czf $BACKUP_DIR/web_$FECHA.tar.gz $APP_DIR/
    echo "$(date) - Backup web completado: web_$FECHA.tar.gz" >> $LOG
    find $BACKUP_DIR -name "*.tar.gz" -mtime +7 -delete
fi
