# TIENDA ONLINE - TECHSTORE
### Proyecto de 2º ASIR - Tienda de Informática

---

## 📋 REQUISITOS

- XAMPP (Apache + MySQL + PHP)
- Visual Studio Code
- Navegador web

---

## 🚀 INSTALACIÓN

### 1. Configurar XAMPP

1. Instala XAMPP si no lo tienes
2. Inicia Apache y MySQL desde el panel de control de XAMPP

### 2. Crear la Base de Datos

1. Abre **phpMyAdmin** en tu navegador: `http://localhost/phpmyadmin`
2. Crea una nueva base de datos llamada `tienda_online`
3. Selecciona la base de datos
4. Ve a la pestaña **SQL**
5. Copia y pega todo el contenido del script SQL proporcionado
6. Haz clic en **Continuar**

### 3. Instalar el Proyecto

1. Copia la carpeta `tienda_online` a `C:\xampp\htdocs\`
2. La ruta final debe ser: `C:\xampp\htdocs\tienda_online\`

### 4. Configurar las Imágenes

1. Dentro de la carpeta `tienda_online`, crea una carpeta llamada `img`
2. Coloca las imágenes de los productos en esta carpeta
3. Nombra las imágenes según el código del producto (ejemplo: `PC-001.jpg`, `LAP-001.jpg`, etc.)
4. Crea también una imagen llamada `no-image.jpg` para productos sin foto

---

## 🌐 ACCEDER A LA TIENDA

Abre tu navegador y ve a: **http://localhost/tienda_online**

---

## 🔐 USUARIOS DE PRUEBA

### Usuario administrador:
- **Usuario:** admin
- **Contraseña:** admin123

### Otros usuarios:
- **Usuario:** usuario1
- **Contraseña:** admin123

- **Usuario:** maria_garcia
- **Contraseña:** admin123

---

## 📁 ESTRUCTURA DEL PROYECTO

```
tienda_online/
│
├── index.php              # Página principal
├── productos.php          # Catálogo de productos
├── producto.php           # Detalle del producto
├── login.php              # Inicio de sesión
├── registro.php           # Registro de usuarios
├── logout.php             # Cerrar sesión
├── carrito.php            # Carrito de compras
├── agregar-carrito.php    # Añadir productos al carrito
├── checkout.php           # Finalizar compra
├── mis-pedidos.php        # Historial de pedidos
│
├── css/
│   └── estilos.css        # Estilos de la web
│
├── includes/
│   ├── config.php         # Configuración de BD
│   └── funciones.php      # Funciones auxiliares
│
└── img/                   # Carpeta de imágenes (crear)
    ├── PC-001.jpg
    ├── LAP-001.jpg
    ├── no-image.jpg
    └── ...
```

---

## ✨ FUNCIONALIDADES

### ✅ Sistema de Usuarios
- Registro de nuevos usuarios
- Inicio de sesión
- Cerrar sesión
- Contraseñas encriptadas

### ✅ Catálogo de Productos
- Ver todos los productos
- Filtrar por categoría
- Buscar productos
- Ver detalle del producto

### ✅ Carrito de Compras
- Añadir productos al carrito
- Modificar cantidades
- Eliminar productos
- Calcular total automáticamente
- Envío gratis en pedidos > 50€

### ✅ Proceso de Compra
- Revisar pedido
- Confirmar dirección de envío
- Realizar pedido
- Ver historial de pedidos
- Ver estado del envío

---

## 🔧 CONFIGURACIÓN

Si necesitas cambiar la configuración de la base de datos, edita el archivo `includes/config.php`:

```php
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'tienda_online');
```

---

## 📸 IMÁGENES DE PRODUCTOS

Los nombres de las imágenes deben coincidir con el código del producto:

- PC-001.jpg → PC Gaming Ultra RTX 4090
- PC-002.jpg → PC Workstation Pro
- LAP-001.jpg → Portátil Dell XPS 15
- GPU-001.jpg → Tarjeta Gráfica RTX 4080
- etc.

Si falta una imagen, se mostrará automáticamente `no-image.jpg`

---

## 🐛 SOLUCIÓN DE PROBLEMAS

### Error: "No se puede conectar a la base de datos"
- Verifica que MySQL esté corriendo en XAMPP
- Comprueba que la base de datos `tienda_online` exista
- Revisa la configuración en `includes/config.php`

### Error: "Call to undefined function mysqli_connect"
- Habilita la extensión mysqli en php.ini
- Reinicia Apache

### Las imágenes no se muestran
- Verifica que la carpeta `img/` exista
- Comprueba que las imágenes tengan el nombre correcto
- Asegúrate de que `no-image.jpg` existe

---

## 📝 NOTAS IMPORTANTES

- Este proyecto es para fines educativos (nivel ASIR)
- Las contraseñas se encriptan con `password_hash()` de PHP
- Los precios están simulados (no están en la BD)
- El sistema usa sesiones de PHP para el carrito y autenticación
- Compatible con PHP 7.4 o superior

---

## 👨‍💻 DESARROLLO

Proyecto creado para el módulo de Aplicaciones Web de 2º de ASIR.

**Tecnologías utilizadas:**
- PHP 8.x
- MySQL 8.x
- HTML5
- CSS3
- JavaScript

---

¡Disfruta de tu tienda online! 🎉
