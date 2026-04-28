<?php
require "includes/config.php";
require "includes/funciones.php";

// Solo admin puede acceder
if (!estaLogueado() || obtenerNombreUsuario() !== "admin") {
    header("Location: index.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

// ELIMINAR PRODUCTO
if (isset($_POST["action"]) && $_POST["action"] === "eliminar") {
    $id = intval($_POST["id"]);
    $stmt = mysqli_prepare($conn, "DELETE FROM productos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    if (mysqli_stmt_execute($stmt)) {
        $mensaje = "Producto eliminado correctamente.";
        $tipo_mensaje = "exito";
    } else {
        $mensaje = "Error al eliminar el producto.";
        $tipo_mensaje = "error";
    }
}

// Función para subir imagen
function subirImagen($archivo, $codigo) {
    if (!isset($archivo) || $archivo["error"] === UPLOAD_ERR_NO_FILE) {
        return ["ok" => true, "msg" => ""]; // sin imagen, no es error
    }
    if ($archivo["error"] !== UPLOAD_ERR_OK) {
        return ["ok" => false, "msg" => "Error al subir la imagen."];
    }
    $tipos_permitidos = ["image/jpeg", "image/jpg", "image/png", "image/webp", "image/gif"];
    if (!in_array($archivo["type"], $tipos_permitidos)) {
        return ["ok" => false, "msg" => "Formato no permitido. Usa JPG, PNG, WEBP o GIF."];
    }
    if ($archivo["size"] > 5 * 1024 * 1024) {
        return ["ok" => false, "msg" => "La imagen no puede superar 5 MB."];
    }
    $destino = "img/" . $codigo . ".jpg";
    if (!move_uploaded_file($archivo["tmp_name"], $destino)) {
        return ["ok" => false, "msg" => "No se pudo guardar la imagen. Verifica permisos de la carpeta img/."];
    }
    return ["ok" => true, "msg" => ""];
}

// AÑADIR PRODUCTO
if (isset($_POST["action"]) && $_POST["action"] === "añadir") {
    $nombre      = limpiarEntrada($_POST["nombre"]);
    $codigo      = limpiarEntrada($_POST["codigo"]);
    $descripcion = limpiarEntrada($_POST["descripcion"]);
    $precio      = floatval($_POST["precio"]);
    $stock       = intval($_POST["stock"]);
    $id_cat      = intval($_POST["id_categoria"]);

    // Subir imagen si se proporcionó
    $img_result = subirImagen($_FILES["imagen"] ?? null, $codigo);
    if (!$img_result["ok"]) {
        $mensaje = $img_result["msg"];
        $tipo_mensaje = "error";
    } else {
        $stmt = mysqli_prepare($conn, "INSERT INTO productos (nombre_producto, codigo_producto, descripcion, precio, stock, id_categoria) VALUES (?, ?, ?, ?, ?, ?)");
        mysqli_stmt_bind_param($stmt, "sssdii", $nombre, $codigo, $descripcion, $precio, $stock, $id_cat);
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "Producto añadido correctamente." . ($img_result["msg"] ? " " . $img_result["msg"] : "");
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al añadir el producto: " . mysqli_error($conn);
            $tipo_mensaje = "error";
        }
    }
}

// EDITAR PRODUCTO
if (isset($_POST["action"]) && $_POST["action"] === "editar") {
    $id          = intval($_POST["id"]);
    $nombre      = limpiarEntrada($_POST["nombre"]);
    $codigo      = limpiarEntrada($_POST["codigo"]);
    $descripcion = limpiarEntrada($_POST["descripcion"]);
    $precio      = floatval($_POST["precio"]);
    $stock       = intval($_POST["stock"]);
    $id_cat      = intval($_POST["id_categoria"]);

    // Subir imagen si se proporcionó
    $img_result = subirImagen($_FILES["imagen"] ?? null, $codigo);
    if (!$img_result["ok"]) {
        $mensaje = $img_result["msg"];
        $tipo_mensaje = "error";
    } else {
        $stmt = mysqli_prepare($conn, "UPDATE productos SET nombre_producto=?, codigo_producto=?, descripcion=?, precio=?, stock=?, id_categoria=? WHERE id=?");
        mysqli_stmt_bind_param($stmt, "sssdiii", $nombre, $codigo, $descripcion, $precio, $stock, $id_cat, $id);
        if (mysqli_stmt_execute($stmt)) {
            $mensaje = "Producto actualizado correctamente." . ($img_result["msg"] ? " " . $img_result["msg"] : "");
            $tipo_mensaje = "exito";
        } else {
            $mensaje = "Error al actualizar el producto.";
            $tipo_mensaje = "error";
        }
    }
}

// Cargar producto a editar
$producto_editar = null;
if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $stmt = mysqli_prepare($conn, "SELECT * FROM productos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $producto_editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

// Listar productos
$categorias = obtenerCategorias($conn);

$buscar_q = isset($_GET["buscar"]) ? $_GET["buscar"] : "";
$cat_q    = isset($_GET["categoria"]) ? intval($_GET["categoria"]) : 0;

$where  = "WHERE 1=1";
$params = [];
if ($buscar_q) {
    $where   .= " AND (p.nombre_producto LIKE ? OR p.codigo_producto LIKE ?)";
    $b        = "%" . $buscar_q . "%";
    $params[] = $b;
    $params[] = $b;
}
if ($cat_q) {
    $where   .= " AND p.id_categoria = ?";
    $params[] = $cat_q;
}

$sql  = "SELECT p.*, c.categoria FROM productos p LEFT JOIN categoria c ON p.id_categoria = c.id $where ORDER BY p.id DESC";
$stmt = mysqli_prepare($conn, $sql);
if (!empty($params)) {
    $types = str_repeat("s", count($params));
    mysqli_stmt_bind_param($stmt, $types, ...$params);
}
mysqli_stmt_execute($stmt);
$result = mysqli_stmt_get_result($stmt);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Gestionar Productos - TechStore</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        /* ── Tabla ── */
        .tabla-productos {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 8px;
            overflow: hidden;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-top: 20px;
        }
        .tabla-productos th {
            background-color: #2C3E50;
            color: white;
            padding: 12px 15px;
            text-align: left;
            font-size: 14px;
        }
        .tabla-productos td {
            padding: 10px 15px;
            border-bottom: 1px solid #eee;
            font-size: 14px;
            vertical-align: middle;
        }
        .tabla-productos tr:last-child td { border-bottom: none; }
        .tabla-productos tr:hover td { background-color: #f9f9f9; }
        .tabla-productos img {
            width: 50px;
            height: 50px;
            object-fit: cover;
            border-radius: 4px;
        }

        /* ── Botones de acción ── */
        .btn-editar {
            background-color: #f39c12;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
            text-decoration: none;
            display: inline-block;
        }
        .btn-editar:hover { background-color: #e67e22; text-decoration: none; }
        .btn-eliminar {
            background-color: #e74c3c;
            color: white;
            padding: 6px 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 13px;
        }
        .btn-eliminar:hover { background-color: #c0392b; }
        .btn-success {
            background-color: #27ae60;
        }
        .btn-success:hover { background-color: #219a52; }

        /* ── Formulario ── */
        .form-panel {
            background: white;
            border-radius: 8px;
            padding: 25px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            margin-bottom: 20px;
        }
        .form-panel h2 {
            color: #2C3E50;
            margin-bottom: 20px;
            padding-bottom: 10px;
            border-bottom: 2px solid #3498db;
        }
        .form-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 15px;
        }
        .form-group {
            display: flex;
            flex-direction: column;
            gap: 5px;
        }
        .form-group.full { grid-column: 1 / -1; }
        .form-group label {
            font-size: 13px;
            font-weight: bold;
            color: #555;
        }
        .form-group input,
        .form-group select,
        .form-group textarea {
            padding: 9px 12px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
            font-family: Arial, sans-serif;
        }
        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #3498db;
        }
        .form-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        .form-actions .btn { width: auto; padding: 10px 25px; }

        /* ── Mensajes ── */
        .mensaje {
            padding: 12px 18px;
            border-radius: 6px;
            margin-bottom: 20px;
            font-weight: bold;
        }
        .mensaje.exito { background-color: #d5f5e3; color: #1e8449; border: 1px solid #a9dfbf; }
        .mensaje.error { background-color: #fadbd8; color: #922b21; border: 1px solid #f1948a; }

        /* ── Filtros ── */
        .filtros-admin {
            background: white;
            padding: 15px 20px;
            border-radius: 8px;
            margin-bottom: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.1);
            display: flex;
            align-items: center;
            gap: 12px;
            flex-wrap: wrap;
        }
        .filtros-admin input,
        .filtros-admin select {
            padding: 8px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 14px;
        }

        /* ── Badge stock ── */
        .badge-stock {
            display: inline-block;
            padding: 3px 8px;
            border-radius: 12px;
            font-size: 12px;
            font-weight: bold;
        }
        .badge-stock.ok  { background: #d5f5e3; color: #1e8449; }
        .badge-stock.low { background: #fef9e7; color: #d4ac0d; }
        .badge-stock.out { background: #fadbd8; color: #922b21; }

        /* ── Modal confirm ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.5);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: white;
            border-radius: 8px;
            padding: 30px;
            max-width: 400px;
            width: 90%;
            text-align: center;
            box-shadow: 0 10px 30px rgba(0,0,0,0.3);
        }
        .modal-box h3 { color: #2C3E50; margin-bottom: 10px; }
        .modal-box p  { color: #666; margin-bottom: 20px; }
        .modal-btns   { display: flex; gap: 10px; justify-content: center; }
        .modal-btns .btn { width: auto; padding: 10px 20px; }

        /* ── Área subida imagen ── */
        .upload-area {
            border: 2px dashed #bdc3c7;
            border-radius: 6px;
            padding: 15px;
            text-align: center;
            cursor: pointer;
            min-height: 100px;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: border-color 0.2s, background 0.2s;
        }
        .upload-area:hover {
            border-color: #3498db;
            background: #eaf4fd;
        }
        .upload-area.drag-over {
            border-color: #27ae60;
            background: #eafaf1;
        }

        @media (max-width: 768px) {
            .form-grid { grid-template-columns: 1fr; }
            .tabla-productos th:nth-child(3),
            .tabla-productos td:nth-child(3),
            .tabla-productos th:nth-child(5),
            .tabla-productos td:nth-child(5) { display: none; }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo">
                <h1>TechStore</h1>
            </div>
            <?php require_once "includes/nav_helper.php"; generarNav(); generarUserInfo(); ?>
        </div>
    </header>

    <div class="container">

        <?php if ($mensaje): ?>
            <div class="mensaje <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
        <?php endif; ?>

        <!-- ── FORMULARIO AÑADIR / EDITAR ── -->
        <div class="form-panel">
            <h2><?php echo $producto_editar ? "✏️ editar producto" : "➕ añadir nuevo producto"; ?></h2>
            <form method="POST" action="gestionar-productos.php" enctype="multipart/form-data">
                <input type="hidden" name="action" value="<?php echo $producto_editar ? 'editar' : 'añadir'; ?>">
                <?php if ($producto_editar): ?>
                    <input type="hidden" name="id" value="<?php echo $producto_editar['id']; ?>">
                <?php endif; ?>

                <div class="form-grid">
                    <div class="form-group">
                        <label for="nombre">nombre del producto *</label>
                        <input type="text" id="nombre" name="nombre" required
                               value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['nombre_producto']) : ''; ?>"
                               placeholder="ej: Intel Core i9-13900K">
                    </div>
                    <div class="form-group">
                        <label for="codigo">código del producto *</label>
                        <input type="text" id="codigo" name="codigo" required
                               value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['codigo_producto']) : ''; ?>"
                               placeholder="ej: CPU-003">
                    </div>
                    <div class="form-group">
                        <label for="precio">precio (€) *</label>
                        <input type="number" id="precio" name="precio" step="0.01" min="0" required
                               value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['precio'] ?? '') : ''; ?>"
                               placeholder="0.00">
                    </div>
                    <div class="form-group">
                        <label for="stock">stock *</label>
                        <input type="number" id="stock" name="stock" min="0" required
                               value="<?php echo $producto_editar ? htmlspecialchars($producto_editar['stock']) : ''; ?>"
                               placeholder="0">
                    </div>
                    <div class="form-group">
                        <label for="id_categoria">categoría *</label>
                        <select id="id_categoria" name="id_categoria" required>
                            <option value="">selecciona categoría</option>
                            <?php foreach ($categorias as $cat): ?>
                                <option value="<?php echo $cat['id']; ?>"
                                    <?php echo ($producto_editar && $producto_editar['id_categoria'] == $cat['id']) ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($cat['categoria']); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label for="imagen">foto del producto <?php echo $producto_editar ? '(dejar vacío para mantener la actual)' : '(opcional)'; ?></label>
                        <div class="upload-area" id="uploadArea" onclick="document.getElementById('imagen').click()">
                            <?php if ($producto_editar): ?>
                                <img id="previewImg"
                                     src="img/<?php echo htmlspecialchars($producto_editar['codigo_producto']); ?>.jpg"
                                     onerror="this.style.display='none'; document.getElementById('uploadPlaceholder').style.display='flex';"
                                     style="max-height:120px; border-radius:4px; object-fit:contain;">
                                <div id="uploadPlaceholder" style="display:none; flex-direction:column; align-items:center; gap:6px; color:#95a5a6;">
                                    <span style="font-size:36px;">📷</span>
                                    <span style="font-size:13px;">click para subir imagen</span>
                                </div>
                            <?php else: ?>
                                <img id="previewImg" style="display:none; max-height:120px; border-radius:4px; object-fit:contain;">
                                <div id="uploadPlaceholder" style="display:flex; flex-direction:column; align-items:center; gap:6px; color:#95a5a6;">
                                    <span style="font-size:36px;">📷</span>
                                    <span style="font-size:13px;">click para subir imagen</span>
                                    <span style="font-size:11px;">JPG, PNG, WEBP · máx. 5 MB</span>
                                </div>
                            <?php endif; ?>
                        </div>
                        <input type="file" id="imagen" name="imagen" accept="image/*" style="display:none"
                               onchange="previsualizarImagen(this)">
                        <p id="nombreArchivo" style="font-size:12px; color:#666; margin-top:5px;"></p>
                    </div>
                    <div class="form-group full">
                        <label for="descripcion">descripción *</label>
                        <textarea id="descripcion" name="descripcion" rows="3" required
                                  placeholder="descripción del producto..."><?php echo $producto_editar ? htmlspecialchars($producto_editar['descripcion']) : ''; ?></textarea>
                    </div>
                </div>

                <div class="form-actions">
                    <button type="submit" class="btn <?php echo $producto_editar ? '' : 'btn-success'; ?>">
                        <?php echo $producto_editar ? "💾 guardar cambios" : "➕ añadir producto"; ?>
                    </button>
                    <?php if ($producto_editar): ?>
                        <a href="gestionar-productos.php" class="btn btn-secondary">cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- ── FILTROS ── -->
        <form method="GET" action="gestionar-productos.php">
            <div class="filtros-admin">
                <strong>buscar:</strong>
                <input type="text" name="buscar" placeholder="nombre o código..."
                       value="<?php echo htmlspecialchars($buscar_q); ?>">
                <select name="categoria">
                    <option value="">todas las categorías</option>
                    <?php foreach ($categorias as $cat): ?>
                        <option value="<?php echo $cat['id']; ?>"
                            <?php echo ($cat_q == $cat['id']) ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($cat['categoria']); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
                <button type="submit" class="btn btn-compact">filtrar</button>
                <?php if ($buscar_q || $cat_q): ?>
                    <a href="gestionar-productos.php" class="btn btn-secondary btn-compact btn-inline">limpiar</a>
                <?php endif; ?>
                <span style="margin-left:auto; color:#666; font-size:13px;">
                    <?php echo mysqli_num_rows($result); ?> producto(s)
                </span>
            </div>
        </form>

        <!-- ── TABLA DE PRODUCTOS ── -->
        <?php if (mysqli_num_rows($result) > 0): ?>
        <table class="tabla-productos">
            <thead>
                <tr>
                    <th>imagen</th>
                    <th>nombre</th>
                    <th>código</th>
                    <th>categoría</th>
                    <th>precio</th>
                    <th>stock</th>
                    <th>acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($p = mysqli_fetch_assoc($result)): ?>
                <tr>
                    <td>
                        <img src="img/<?php echo htmlspecialchars($p['codigo_producto']); ?>.jpg"
                             alt="<?php echo htmlspecialchars($p['nombre_producto']); ?>"
                             onerror="this.src='img/no-image.jpg'">
                    </td>
                    <td><strong><?php echo htmlspecialchars($p['nombre_producto']); ?></strong></td>
                    <td><code><?php echo htmlspecialchars($p['codigo_producto']); ?></code></td>
                    <td><?php echo htmlspecialchars($p['categoria'] ?? '—'); ?></td>
                    <td>
                        <?php
                        $precio_mostrar = isset($p['precio']) && $p['precio'] > 0 ? $p['precio'] : 99.99;
                        echo formatearPrecio($precio_mostrar);
                        ?>
                    </td>
                    <td>
                        <?php
                        $s = intval($p['stock']);
                        if ($s == 0)      echo '<span class="badge-stock out">sin stock</span>';
                        elseif ($s <= 5)  echo '<span class="badge-stock low">' . $s . ' uds</span>';
                        else              echo '<span class="badge-stock ok">' . $s . ' uds</span>';
                        ?>
                    </td>
                    <td>
                        <div style="display:flex; gap:6px; flex-wrap:wrap;">
                            <a href="gestionar-productos.php?editar=<?php echo $p['id']; ?>"
                               class="btn-editar">✏️ editar</a>
                            <button class="btn-eliminar"
                                    onclick="confirmarEliminar(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre_producto']); ?>')">
                                🗑️ eliminar
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
        <?php else: ?>
            <div class="carrito-vacio">
                <h2>no se encontraron productos</h2>
                <p>intenta con otros filtros de búsqueda</p>
            </div>
        <?php endif; ?>
    </div>

    <!-- ── MODAL CONFIRMAR ELIMINACIÓN ── -->
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-box">
            <h3>⚠️ confirmar eliminación</h3>
            <p id="modalTexto">¿Estás seguro de que quieres eliminar este producto?</p>
            <div class="modal-btns">
                <form method="POST" action="gestionar-productos.php" id="formEliminar">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="id" id="modalId">
                    <div style="display:flex; gap:10px;">
                        <button type="submit" class="btn btn-eliminar" style="width:auto; padding:10px 20px;">sí, eliminar</button>
                        <button type="button" class="btn btn-secondary" style="width:auto; padding:10px 20px;"
                                onclick="cerrarModal()">cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function previsualizarImagen(input) {
            const preview = document.getElementById("previewImg");
            const placeholder = document.getElementById("uploadPlaceholder");
            const nombreArchivo = document.getElementById("nombreArchivo");
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = e => {
                    preview.src = e.target.result;
                    preview.style.display = "block";
                    placeholder.style.display = "none";
                };
                reader.readAsDataURL(input.files[0]);
                nombreArchivo.textContent = "📎 " + input.files[0].name;
            }
        }

        // Drag & drop
        const uploadArea = document.getElementById("uploadArea");
        uploadArea.addEventListener("dragover", e => { e.preventDefault(); uploadArea.classList.add("drag-over"); });
        uploadArea.addEventListener("dragleave", () => uploadArea.classList.remove("drag-over"));
        uploadArea.addEventListener("drop", e => {
            e.preventDefault();
            uploadArea.classList.remove("drag-over");
            const input = document.getElementById("imagen");
            input.files = e.dataTransfer.files;
            previsualizarImagen(input);
        });

        function confirmarEliminar(id, nombre) {
            document.getElementById("modalId").value = id;
            document.getElementById("modalTexto").textContent =
                '¿Seguro que quieres eliminar "' + nombre + '"? Esta acción no se puede deshacer.';
            document.getElementById("modalEliminar").classList.add("active");
        }
        function cerrarModal() {
            document.getElementById("modalEliminar").classList.remove("active");
        }
        document.getElementById("modalEliminar").addEventListener("click", function(e) {
            if (e.target === this) cerrarModal();
        });

        // Scroll al formulario si hay edición activa
        <?php if ($producto_editar): ?>
        window.onload = () => document.querySelector('.form-panel').scrollIntoView({behavior:'smooth'});
        <?php endif; ?>
    </script>
</body>
</html>
