-- ================================================================
-- SCRIPT SQL COMPLETO - TIENDA ONLINE
-- ================================================================

-- Crear la base de datos
CREATE DATABASE IF NOT EXISTS tienda_online;
USE tienda_online;

-- ========================= 
-- TABLA PROVEEDORES
-- ========================= 
CREATE TABLE IF NOT EXISTS proveedores (
    id INT AUTO_INCREMENT PRIMARY KEY,
    compania VARCHAR(100) NOT NULL,
    email VARCHAR(100),
    direccion VARCHAR(150),
    telefono VARCHAR(20),
    codigo_postal VARCHAR(10),
    provincia VARCHAR(50)
) ENGINE=InnoDB;

-- ========================= 
-- TABLA TIENDA
-- ========================= 
CREATE TABLE IF NOT EXISTS tienda (
    id INT AUTO_INCREMENT PRIMARY KEY,
    email VARCHAR(100),
    direccion VARCHAR(150),
    telefono VARCHAR(20),
    codigo_postal VARCHAR(10)
) ENGINE=InnoDB;

-- ========================= 
-- TABLA CATEGORIA
-- ========================= 
CREATE TABLE IF NOT EXISTS categoria (
    id INT AUTO_INCREMENT PRIMARY KEY,
    categoria VARCHAR(100) NOT NULL
) ENGINE=InnoDB;

-- ========================= 
-- TABLA PRODUCTOS
-- ========================= 
CREATE TABLE IF NOT EXISTS productos (
    id INT AUTO_INCREMENT PRIMARY KEY,
    codigo_producto VARCHAR(50) NOT NULL,
    nombre_producto VARCHAR(100) NOT NULL,
    descripcion TEXT,
    id_categoria INT,
    id_proveedor INT,
    id_tienda INT,
    FOREIGN KEY (id_categoria) REFERENCES categoria(id),
    FOREIGN KEY (id_proveedor) REFERENCES proveedores(id),
    FOREIGN KEY (id_tienda) REFERENCES tienda(id)
) ENGINE=InnoDB;

-- ========================= 
-- TABLA CLIENTES
-- ========================= 
CREATE TABLE IF NOT EXISTS clientes (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario VARCHAR(50) NOT NULL,
    contrasena VARCHAR(255) NOT NULL,
    direccion VARCHAR(150),
    telefono VARCHAR(20),
    ciudad VARCHAR(50)
) ENGINE=InnoDB;

-- ========================= 
-- TABLA PEDIDOS
-- ========================= 
CREATE TABLE IF NOT EXISTS pedidos (
    id_pedido INT AUTO_INCREMENT PRIMARY KEY,
    id_cliente INT,
    estado VARCHAR(50),
    direccion_envio VARCHAR(150),
    fecha_envio DATE,
    FOREIGN KEY (id_cliente) REFERENCES clientes(id)
) ENGINE=InnoDB;

-- ========================= 
-- TABLA LINEA_PEDIDO (N:M)
-- ========================= 
CREATE TABLE IF NOT EXISTS linea_pedido (
    id_pedido INT,
    id_producto INT,
    cantidad INT NOT NULL,
    precio_unidad DECIMAL(10,2) NOT NULL,
    PRIMARY KEY (id_pedido, id_producto),
    FOREIGN KEY (id_pedido) REFERENCES pedidos(id_pedido),
    FOREIGN KEY (id_producto) REFERENCES productos(id)
) ENGINE=InnoDB;

-- ================================================================
-- INSERTAR DATOS DE EJEMPLO
-- ================================================================

-- CATEGORÍAS
INSERT INTO categoria (categoria) VALUES 
('Ordenadores'),
('Componentes'),
('Periféricos'),
('Portátiles'),
('Accesorios');

-- PROVEEDORES
INSERT INTO proveedores (compania, email, direccion, telefono, codigo_postal, provincia) VALUES 
('Tech Solutions S.L.', 'info@techsolutions.com', 'Calle Tecnología 45', '912345678', '28001', 'Madrid'),
('Hardware Pro', 'ventas@hardwarepro.com', 'Avenida Digital 89', '923456789', '08001', 'Barcelona'),
('Digital Store', 'contacto@digitalstore.com', 'Plaza Informática 12', '934567890', '46001', 'Valencia'),
('Components & More', 'info@components.com', 'Calle PC 23', '941234567', '41001', 'Sevilla');

-- TIENDA
INSERT INTO tienda (email, direccion, telefono, codigo_postal) VALUES 
('info@techstore.com', 'Calle Principal 123', '912000000', '28001');

-- PRODUCTOS
INSERT INTO productos (codigo_producto, nombre_producto, descripcion, id_categoria, id_proveedor, id_tienda) VALUES 
-- Ordenadores
('PC-001', 'PC Gaming Ultra RTX 4090', 'Ordenador gaming de última generación con procesador Intel i9-13900K, 64GB RAM DDR5, SSD NVMe 2TB, y tarjeta gráfica NVIDIA RTX 4090 24GB. Refrigeración líquida y caja RGB premium. Perfecto para gaming en 4K y streaming profesional.', 1, 1, 1),
('PC-002', 'PC Workstation Pro', 'Estación de trabajo profesional con AMD Threadripper, 128GB RAM ECC, doble SSD NVMe 4TB, tarjeta gráfica profesional NVIDIA RTX A6000. Ideal para renderizado 3D, edición de video y tareas profesionales.', 1, 1, 1),
('PC-003', 'PC Office Essential', 'Ordenador de oficina eficiente con Intel i5, 16GB RAM, SSD 512GB. Perfecto para tareas de oficina, navegación web y multitarea básica. Incluye Windows 11 Pro.', 1, 2, 1),

-- Portátiles
('LAP-001', 'Portátil Dell XPS 15', 'Portátil premium con pantalla 4K OLED 15.6", Intel i9-13900H, 32GB RAM DDR5, SSD NVMe 1TB, NVIDIA RTX 4060. Diseño ultra-delgado en aluminio con batería de larga duración.', 4, 2, 1),
('LAP-002', 'MacBook Pro 16" M3 Max', 'El portátil más potente de Apple con chip M3 Max, 48GB RAM unificada, SSD 2TB, pantalla Liquid Retina XDR. Ideal para profesionales creativos y desarrolladores.', 4, 2, 1),
('LAP-003', 'Portátil Gaming ASUS ROG', 'Portátil gaming con pantalla 17.3" 360Hz, Intel i9, 32GB RAM, RTX 4080, SSD 2TB. Sistema de refrigeración avanzado y teclado RGB personalizable.', 4, 1, 1),

-- Componentes
('GPU-001', 'Tarjeta Gráfica RTX 4080', 'GPU de alta gama NVIDIA RTX 4080 con 16GB GDDR6X, ray tracing de última generación y DLSS 3. Perfecta para gaming en 4K y creación de contenido.', 2, 1, 1),
('GPU-002', 'Tarjeta Gráfica AMD RX 7900 XTX', 'GPU AMD con 24GB GDDR6, arquitectura RDNA 3, excelente rendimiento en gaming y productividad. Incluye 3 años de garantía.', 2, 1, 1),
('CPU-001', 'Procesador AMD Ryzen 9 7950X', 'CPU de 16 núcleos y 32 hilos, hasta 5.7GHz, arquitectura Zen 4. El procesador más potente para gaming y workstations. Incluye disipador Wraith Prism.', 2, 1, 1),
('CPU-002', 'Procesador Intel i9-14900K', 'CPU Intel de 24 núcleos (8P+16E), hasta 6.0GHz, socket LGA1700. Rendimiento excepcional en gaming y aplicaciones profesionales.', 2, 2, 1),
('SSD-001', 'SSD NVMe 2TB Samsung 980 Pro', 'Disco SSD M.2 NVMe Gen4 con velocidades de lectura de hasta 7000 MB/s. Perfecto para sistema operativo y juegos. Incluye disipador térmico.', 2, 2, 1),
('SSD-002', 'SSD SATA 4TB Crucial MX500', 'SSD SATA de alta capacidad, 560 MB/s lectura, perfecto para almacenamiento masivo de archivos. Incluye kit de clonación.', 2, 3, 1),
('RAM-001', 'Memoria RAM 32GB DDR5 6000MHz', 'Kit dual channel 2x16GB DDR5 con disipadores RGB, perfil XMP 3.0. Compatible con las últimas placas Intel y AMD.', 2, 1, 1),
('RAM-002', 'Memoria RAM 64GB DDR5 5600MHz', 'Kit 2x32GB DDR5 para workstations y gaming extremo. Latencias optimizadas y disipadores premium.', 2, 2, 1),

-- Periféricos
('KB-001', 'Teclado Mecánico Corsair K95 RGB', 'Teclado mecánico gaming con switches Cherry MX Speed, retroiluminación RGB por tecla, reposamuñecas magnético y 6 teclas macro programables.', 3, 3, 1),
('KB-002', 'Teclado Logitech MX Keys', 'Teclado inalámbrico premium para productividad, teclas retroiluminadas con sensor de proximidad, conectividad multi-dispositivo.', 3, 3, 1),
('MOU-001', 'Ratón Gaming Logitech G Pro Wireless', 'Ratón gaming profesional ultra-ligero (63g), sensor HERO 25K, batería de 60 horas, switches mecánicos de 50 millones de clics.', 3, 3, 1),
('MOU-002', 'Ratón Razer DeathAdder V3', 'Ratón ergonómico gaming con sensor óptico Focus Pro 30K, switches ópticas Gen-3, RGB personalizable.', 3, 3, 1),
('MON-001', 'Monitor Gaming LG 27" 240Hz', 'Monitor gaming IPS 27" QHD 2560x1440, 240Hz, 1ms, G-Sync Compatible, HDR10. Panel Nano IPS con 98% DCI-P3.', 3, 2, 1),
('MON-002', 'Monitor 4K Dell UltraSharp 32"', 'Monitor profesional 4K UHD IPS, 99% sRGB, puerto USB-C con 90W Power Delivery, altura ajustable, ideal para diseño y fotografía.', 3, 2, 1),
('HEAD-001', 'Auriculares SteelSeries Arctis Pro', 'Auriculares gaming Hi-Res con DAC, doble conexión inalámbrica 2.4GHz y Bluetooth, batería intercambiable, micrófono retráctil.', 3, 4, 1),

-- Accesorios
('CASE-001', 'Caja Corsair 5000D Airflow RGB', 'Caja ATX con panel de cristal templado, 3 ventiladores RGB incluidos, excelente flujo de aire, gestión de cables optimizada.', 5, 3, 1),
('CASE-002', 'Caja NZXT H9 Flow', 'Caja Mid-Tower premium con panel de cristal, soporte para refrigeración líquida 360mm, ventiladores silenciosos incluidos.', 5, 3, 1),
('PSU-001', 'Fuente Modular 850W 80+ Gold', 'Fuente de alimentación totalmente modular, certificación 80+ Gold, ventilador silencioso de 135mm, protecciones completas.', 5, 1, 1),
('COOL-001', 'Refrigeración Líquida AIO 360mm', 'Sistema de refrigeración líquida todo-en-uno, radiador 360mm, ventiladores RGB, bomba silenciosa, compatible Intel y AMD.', 5, 3, 1),
('WEBCAM-001', 'Webcam Logitech Brio 4K', 'Webcam 4K Ultra HD con HDR, autofocus, corrección de luz automática, doble micrófono con reducción de ruido.', 5, 3, 1),
('MIC-001', 'Micrófono USB Blue Yeti', 'Micrófono de condensador profesional USB, 4 patrones polares, calidad de estudio, ideal para streaming y podcasting.', 5, 4, 1);

-- CLIENTE DE PRUEBA (usuario: admin, contraseña: admin123)
INSERT INTO clientes (usuario, contrasena, direccion, telefono, ciudad) VALUES 
('admin', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Calle Test 123, 3º B', '666777888', 'Madrid'),
('usuario1', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Avenida Principal 45', '655443322', 'Barcelona'),
('maria_garcia', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'Plaza Mayor 7, 1º A', '644332211', 'Valencia');

-- PEDIDOS DE EJEMPLO
INSERT INTO pedidos (id_cliente, estado, direccion_envio, fecha_envio) VALUES 
(1, 'Entregado', 'Calle Test 123, 3º B, Madrid', '2026-01-15'),
(1, 'En tránsito', 'Calle Test 123, 3º B, Madrid', '2026-01-22'),
(2, 'Pendiente', 'Avenida Principal 45, Barcelona', '2026-01-23');

-- LÍNEAS DE PEDIDO
INSERT INTO linea_pedido (id_pedido, id_producto, cantidad, precio_unidad) VALUES 
-- Pedido 1
(1, 1, 1, 2499.99),  -- PC Gaming
(1, 7, 1, 899.99),   -- RTX 4080
(1, 17, 1, 149.99),  -- Teclado Corsair

-- Pedido 2
(2, 4, 1, 1899.99),  -- Dell XPS
(2, 19, 1, 99.99),   -- Ratón Logitech

-- Pedido 3
(3, 21, 1, 549.99),  -- Monitor Gaming
(3, 13, 1, 189.99),  -- RAM 32GB
(3, 11, 1, 299.99);  -- SSD Samsung

-- ================================================================
-- CONSULTAS ÚTILES PARA VERIFICAR LOS DATOS
-- ================================================================

-- Ver todos los productos con su categoría
-- SELECT p.*, c.categoria FROM productos p LEFT JOIN categoria c ON p.id_categoria = c.id;

-- Ver pedidos con información del cliente
-- SELECT pe.*, cl.usuario FROM pedidos pe JOIN clientes cl ON pe.id_cliente = cl.id;

-- Ver detalle completo de un pedido
-- SELECT pe.id_pedido, pe.estado, pe.fecha_envio, pr.nombre_producto, lp.cantidad, lp.precio_unidad
-- FROM pedidos pe
-- JOIN linea_pedido lp ON pe.id_pedido = lp.id_pedido
-- JOIN productos pr ON lp.id_producto = pr.id
-- WHERE pe.id_pedido = 1;
