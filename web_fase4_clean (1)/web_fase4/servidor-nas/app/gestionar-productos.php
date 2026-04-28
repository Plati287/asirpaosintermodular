<?php
require "includes/config.php";
require "includes/funciones.php";

if (!estaLogueado() || obtenerNombreUsuario() !== "admin") {
    header("Location: index.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";

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

function subirImagen($archivo, $codigo) {
    if (!isset($archivo) || $archivo["error"] === UPLOAD_ERR_NO_FILE) {
        return ["ok" => true, "msg" => ""]; 
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

if (isset($_POST["action"]) && $_POST["action"] === "añadir") {
    $nombre      = limpiarEntrada($_POST["nombre"]);
    $codigo      = limpiarEntrada($_POST["codigo"]);
    $descripcion = limpiarEntrada($_POST["descripcion"]);
    $precio      = floatval($_POST["precio"]);
    $stock       = intval($_POST["stock"]);
    $id_cat      = intval($_POST["id_categoria"]);

    
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

if (isset($_POST["action"]) && $_POST["action"] === "editar") {
    $id          = intval($_POST["id"]);
    $nombre      = limpiarEntrada($_POST["nombre"]);
    $codigo      = limpiarEntrada($_POST["codigo"]);
    $descripcion = limpiarEntrada($_POST["descripcion"]);
    $precio      = floatval($_POST["precio"]);
    $stock       = intval($_POST["stock"]);
    $id_cat      = intval($_POST["id_categoria"]);

    
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

$producto_editar = null;
if (isset($_GET["editar"])) {
    $id = intval($_GET["editar"]);
    $stmt = mysqli_prepare($conn, "SELECT * FROM productos WHERE id = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $producto_editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

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
    <link rel="stylesheet" href="css/gestionar-productos.css">
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

        
        <div class="form-panel">
            <h2><?php echo $producto_editar ? " editar producto" : " añadir nuevo producto"; ?></h2>
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
                        <label for="precio">precio () *</label>
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
                                    <span style="font-size:36px;"></span>
                                    <span style="font-size:13px;">click para subir imagen</span>
                                </div>
                            <?php else: ?>
                                <img id="previewImg" style="display:none; max-height:120px; border-radius:4px; object-fit:contain;">
                                <div id="uploadPlaceholder" style="display:flex; flex-direction:column; align-items:center; gap:6px; color:#95a5a6;">
                                    <span style="font-size:36px;"></span>
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
                        <?php echo $producto_editar ? " guardar cambios" : " añadir producto"; ?>
                    </button>
                    <?php if ($producto_editar): ?>
                        <a href="gestionar-productos.php" class="btn btn-secondary">cancelar</a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        
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
                    <td><?php echo htmlspecialchars($p['categoria'] ?? ''); ?></td>
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
                               class="btn-editar"> editar</a>
                            <button class="btn-eliminar"
                                    onclick="confirmarEliminar(<?php echo $p['id']; ?>, '<?php echo addslashes($p['nombre_producto']); ?>')">
                                 eliminar
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

    
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-box">
            <h3> confirmar eliminación</h3>
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
                nombreArchivo.textContent = " " + input.files[0].name;
            }
        }

        
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

        
        <?php if ($producto_editar): ?>
        window.onload = () => document.querySelector('.form-panel').scrollIntoView({behavior:'smooth'});
        <?php endif; ?>
    </script>
</body>
</html>
