# TechStore — Proyecto Intermodular

Tienda online PHP desplegada en AWS con alta disponibilidad, balanceo de carga y monitorización.

## Arquitectura
- **HAProxy** — Balanceador de carga, monitorización (Grafana + Prometheus) y automatización (Ansible)
- **Servidor Web** — Nodo principal con MySQL Master
- **Servidor NAS** — Nodo de respaldo con MySQL Slave

## Acceso
- Web: https://techstores.duckdns.org
- HAProxy stats: http://98.87.237.212:8404/stats
- Grafana: http://98.87.237.212:3000

## Estructura
- `haproxy/` — Configuración HAProxy, Grafana, Prometheus y Ansible
- `servidor-web/` — Código PHP, Docker Compose y Nginx del nodo principal
- `servidor-nas/` — Código PHP, Docker Compose, Nginx y scripts de sincronización del nodo de respaldo
