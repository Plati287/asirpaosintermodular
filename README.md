# asirpaosintermodular

solucionar error not found en AWS

<img width="1317" height="177" alt="error not found" src="https://github.com/user-attachments/assets/8dd93a1c-28f8-4de2-a615-af3e71dffb5c" />

cd ~/tienda_online_last
sudo docker compose up -d --build

sudo docker exec -it tienda_online_last-db-1 mysql -uroot -proot -e "DROP DATABASE IF EXISTS tienda_online; CREATE DATABASE tienda_online CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

sudo docker exec -it tienda_online_last-db-1 mysql -uroot -proot -e "CREATE USER IF NOT EXISTS 'tienda_user'@'%' IDENTIFIED BY 'tienda_pass'; GRANT ALL PRIVILEGES ON tienda_online.* TO 'tienda_user'@'%'; FLUSH PRIVILEGES;"

sudo docker exec -i tienda_online_last-db-1 mysql -uroot -proot --default-character-set=utf8mb4 tienda_online < ~/tienda_online.sql

sudo chown -R www-data:www-data ~/tienda_online_last/app
sudo chmod -R 755 ~/tienda_online_last/app

sudo nano ~/tienda_online_last/nginx/default.conf

location ~* \.(css|js|jpg|jpeg|png|gif|ico|webp|svg|woff|woff2)$ {
    try_files $uri =404;
}

sudo docker compose restart nginx
