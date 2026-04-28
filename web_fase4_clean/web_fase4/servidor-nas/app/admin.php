<?php
require "includes/config.php";
require "includes/funciones.php";

if (!estaLogueado() || obtenerNombreUsuario() !== "admin") {
    header("Location: index.php");
    exit();
}

$mensaje = "";
$tipo_mensaje = "";
$tabla_activa = isset($_GET["tabla"]) ? $_GET["tabla"] : "clientes";
$tablas_permitidas = ["clientes", "pedidos", "proveedores", "categoria", "resenas", "linea_pedido", "tienda"];
if (!in_array($tabla_activa, $tablas_permitidas)) $tabla_activa = "clientes";

if (isset($_POST["action"]) && $_POST["action"] === "eliminar") {
    $tabla = $_POST["tabla"];
    $id    = intval($_POST["id"]);
    $col   = $_POST["col_id"];
    if (in_array($tabla, $tablas_permitidas)) {
        
        if ($tabla === "clientes") {
            $stmt_pr = mysqli_prepare($conn, "DELETE FROM `password_resets` WHERE `id_cliente` = ?");
            mysqli_stmt_bind_param($stmt_pr, "i", $id);
            mysqli_stmt_execute($stmt_pr);
        }
        $stmt = mysqli_prepare($conn, "DELETE FROM `$tabla` WHERE `$col` = ?");
        mysqli_stmt_bind_param($stmt, "i", $id);
        mysqli_stmt_execute($stmt)
            ? ($mensaje = "Registro eliminado.") && ($tipo_mensaje = "exito")
            : ($mensaje = "Error al eliminar: " . mysqli_error($conn)) && ($tipo_mensaje = "error");
    }
}

if (isset($_POST["action"]) && $_POST["action"] === "guardar_edicion") {
    $tabla  = $_POST["tabla"];
    $id     = intval($_POST["id"]);
    $col_id = $_POST["col_id"];
    if (in_array($tabla, $tablas_permitidas)) {
        $sets   = [];
        $valores = [];
        $tipos  = "";
        foreach ($_POST as $k => $v) {
            if (in_array($k, ["action","tabla","id","col_id"])) continue;
            $sets[]   = "`$k` = ?";
            $valores[] = $v;
            $tipos    .= "s";
        }
        if (!empty($sets)) {
            $sql  = "UPDATE `$tabla` SET " . implode(", ", $sets) . " WHERE `$col_id` = ?";
            $stmt = mysqli_prepare($conn, $sql);
            $valores[] = $id;
            $tipos    .= "i";
            mysqli_stmt_bind_param($stmt, $tipos, ...$valores);
            mysqli_stmt_execute($stmt)
                ? ($mensaje = "Registro actualizado correctamente.") && ($tipo_mensaje = "exito")
                : ($mensaje = "Error al actualizar: " . mysqli_error($conn)) && ($tipo_mensaje = "error");
        }
    }
}

function getColumnas($conn, $tabla) {
    $cols = [];
    $r = mysqli_query($conn, "SHOW COLUMNS FROM `$tabla`");
    while ($row = mysqli_fetch_assoc($r)) $cols[] = $row;
    return $cols;
}

function getDatos($conn, $tabla, $buscar = "", $col_buscar = "") {
    $sql = "SELECT * FROM `$tabla`";
    if ($buscar && $col_buscar) {
        $b    = "%" . mysqli_real_escape_string($conn, $buscar) . "%";
        $sql .= " WHERE `$col_buscar` LIKE '$b'";
    }
    $sql .= " ORDER BY 1 DESC LIMIT 500";
    return mysqli_query($conn, $sql);
}

$columnas    = getColumnas($conn, $tabla_activa);
$col_id      = $columnas[0]["Field"]; 
$buscar_q    = isset($_GET["buscar"]) ? $_GET["buscar"] : "";
$col_buscar  = isset($_GET["col_buscar"]) ? $_GET["col_buscar"] : ($columnas[1]["Field"] ?? $col_id);
$result      = getDatos($conn, $tabla_activa, $buscar_q, $col_buscar);
$total_rows  = mysqli_num_rows($result);

$registro_editar = null;
if (isset($_GET["editar"])) {
    $id   = intval($_GET["editar"]);
    $stmt = mysqli_prepare($conn, "SELECT * FROM `$tabla_activa` WHERE `$col_id` = ?");
    mysqli_stmt_bind_param($stmt, "i", $id);
    mysqli_stmt_execute($stmt);
    $registro_editar = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));
}

$counts = [];
foreach ($tablas_permitidas as $t) {
    $r = mysqli_query($conn, "SELECT COUNT(*) as n FROM `$t`");
    $counts[$t] = mysqli_fetch_assoc($r)["n"];
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Panel de Administración - TechStore</title>
    <link rel="stylesheet" href="css/index.css">
    <style>
        /*  Layout  */
        .admin-layout { display: flex; gap: 0; min-height: calc(100vh - 70px); }
        .sidebar {
            width: 220px;
            min-width: 220px;
            background: #2C3E50;
            padding: 20px 0;
        }
        .sidebar h3 {
            color: #95a5a6;
            font-size: 11px;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 0 20px 10px;
            margin: 0;
        }
        .sidebar a {
            display: flex;
            align-items: center;
            gap: 10px;
            padding: 12px 20px;
            color: #bdc3c7;
            text-decoration: none;
            font-size: 14px;
            border-left: 3px solid transparent;
            transition: all 0.15s;
        }
        .sidebar a:hover { background: #34495e; color: white; text-decoration: none; }
        .sidebar a.activo { background: #34495e; color: white; border-left-color: #3498db; }
        .sidebar .badge {
            margin-left: auto;
            background: #3498db;
            color: white;
            font-size: 11px;
            padding: 2px 7px;
            border-radius: 10px;
        }
        .sidebar .sep { height: 1px; background: #34495e; margin: 10px 20px; }
        .main-content { flex: 1; padding: 25px; overflow-x: auto; background: #f4f4f4; }

        /*  Tarjetas resumen  */
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 25px;
        }
        .stat-card {
            background: white;
            border-radius: 8px;
            padding: 15px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            text-align: center;
        }
        .stat-card .num { font-size: 28px; font-weight: bold; color: #2C3E50; }
        .stat-card .lbl { font-size: 12px; color: #7f8c8d; margin-top: 4px; }

        /*  Tabla  */
        .panel { background: white; border-radius: 8px; box-shadow: 0 2px 5px rgba(0,0,0,0.08); overflow: hidden; }
        .panel-header {
            background: #2C3E50;
            color: white;
            padding: 14px 20px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            flex-wrap: wrap;
        }
        .panel-header h2 { margin: 0; font-size: 16px; }
        .panel-header form { display: flex; gap: 8px; align-items: center; }
        .panel-header input, .panel-header select {
            padding: 6px 10px;
            border: none;
            border-radius: 4px;
            font-size: 13px;
        }
        .tabla-admin { width: 100%; border-collapse: collapse; font-size: 13px; }
        .tabla-admin th {
            background: #ecf0f1;
            padding: 10px 12px;
            text-align: left;
            color: #555;
            font-size: 12px;
            text-transform: uppercase;
            white-space: nowrap;
        }
        .tabla-admin td {
            padding: 9px 12px;
            border-bottom: 1px solid #f0f0f0;
            max-width: 200px;
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
            vertical-align: middle;
        }
        .tabla-admin tr:last-child td { border-bottom: none; }
        .tabla-admin tr:hover td { background: #fafafa; }
        .tabla-footer { padding: 10px 15px; color: #999; font-size: 12px; border-top: 1px solid #eee; }

        /*  Botones  */
        .btn-sm {
            padding: 5px 10px;
            font-size: 12px;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            text-decoration: none;
            display: inline-block;
        }
        .btn-edit  { background: #f39c12; color: white; }
        .btn-edit:hover  { background: #e67e22; text-decoration: none; }
        .btn-del   { background: #e74c3c; color: white; }
        .btn-del:hover   { background: #c0392b; }
        .btn-save  { background: #27ae60; color: white; }
        .btn-save:hover  { background: #219a52; }
        .btn-cancel { background: #95a5a6; color: white; }
        .btn-cancel:hover { background: #7f8c8d; text-decoration: none; }

        /*  Formulario edición  */
        .edit-panel {
            background: white;
            border-radius: 8px;
            padding: 22px;
            box-shadow: 0 2px 5px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border-left: 4px solid #f39c12;
        }
        .edit-panel h3 { color: #2C3E50; margin: 0 0 18px; font-size: 15px; }
        .edit-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(220px, 1fr)); gap: 12px; }
        .edit-group { display: flex; flex-direction: column; gap: 4px; }
        .edit-group label { font-size: 12px; color: #666; font-weight: bold; }
        .edit-group input, .edit-group textarea {
            padding: 7px 10px;
            border: 1px solid #ddd;
            border-radius: 4px;
            font-size: 13px;
            font-family: Arial, sans-serif;
        }
        .edit-group input:focus, .edit-group textarea:focus { outline: none; border-color: #f39c12; }
        .edit-group input[readonly] { background: #f8f8f8; color: #999; }
        .edit-actions { display: flex; gap: 10px; margin-top: 15px; }

        /*  Mensajes  */
        .mensaje { padding: 11px 16px; border-radius: 6px; margin-bottom: 18px; font-weight: bold; font-size: 14px; }
        .mensaje.exito { background: #d5f5e3; color: #1e8449; border: 1px solid #a9dfbf; }
        .mensaje.error { background: #fadbd8; color: #922b21; border: 1px solid #f1948a; }

        /*  Modal  */
        .modal-overlay { display: none; position: fixed; inset: 0; background: rgba(0,0,0,0.5); z-index: 1000; align-items: center; justify-content: center; }
        .modal-overlay.active { display: flex; }
        .modal-box { background: white; border-radius: 8px; padding: 28px; max-width: 380px; width: 90%; text-align: center; box-shadow: 0 10px 30px rgba(0,0,0,0.3); }
        .modal-box h3 { color: #2C3E50; margin-bottom: 8px; }
        .modal-box p  { color: #666; margin-bottom: 20px; font-size: 14px; }
        .modal-btns { display: flex; gap: 10px; justify-content: center; }

        /*  Iconos de tabla  */
        .tabla-icon { font-size: 18px; }

        @media (max-width: 768px) {
            .admin-layout { flex-direction: column; }
            .sidebar { width: 100%; min-width: unset; display: flex; flex-wrap: wrap; padding: 10px; gap: 5px; }
            .sidebar h3, .sidebar .sep { display: none; }
            .sidebar a { border-left: none; border-bottom: 2px solid transparent; padding: 8px 12px; border-radius: 4px; }
            .sidebar a.activo { border-bottom-color: #3498db; }
            .stats-grid { grid-template-columns: repeat(3, 1fr); }
        }
    </style>
</head>
<body>
    <header>
        <div class="header-container">
            <div class="logo"><h1>TechStore</h1></div>
            <?php require_once "includes/nav_helper.php"; generarNav(); generarUserInfo(); ?>
        </div>
    </header>

    <div class="admin-layout">
        
        <aside class="sidebar">
            <h3>base de datos</h3>
            <?php
            $iconos = [
                "clientes"     => ["", "Clientes"],
                "pedidos"      => ["", "Pedidos"],
                "linea_pedido" => ["", "Líneas Pedido"],
                "proveedores"  => ["", "Proveedores"],
                "categoria"    => ["", "Categorías"],
                "resenas"      => ["", "Reseñas"],
                "tienda"       => ["", "Tienda"],
            ];
            foreach ($tablas_permitidas as $t):
                $activo = $tabla_activa === $t ? "activo" : "";
                [$icono, $nombre] = $iconos[$t];
            ?>
                <a href="admin.php?tabla=<?php echo $t; ?>" class="<?php echo $activo; ?>">
                    <span class="tabla-icon"><?php echo $icono; ?></span>
                    <?php echo $nombre; ?>
                    <span class="badge"><?php echo $counts[$t]; ?></span>
                </a>
            <?php endforeach; ?>
            <div class="sep"></div>
            <a href="gestionar-productos.php"> Productos</a>
            <a href="gestionar-envios.php"> Envíos</a>
        </aside>

        
        <div class="main-content">

            
            <div class="stats-grid">
                <?php foreach ($iconos as $t => [$icono, $nombre]): ?>
                <div class="stat-card">
                    <div style="font-size:22px"><?php echo $icono; ?></div>
                    <div class="num"><?php echo $counts[$t]; ?></div>
                    <div class="lbl"><?php echo $nombre; ?></div>
                </div>
                <?php endforeach; ?>
            </div>

            <?php if ($mensaje): ?>
                <div class="mensaje <?php echo $tipo_mensaje; ?>"><?php echo $mensaje; ?></div>
            <?php endif; ?>

            
            <?php if ($registro_editar): ?>
            <div class="edit-panel">
                <h3> editando registro en <strong><?php echo $tabla_activa; ?></strong>  ID <?php echo $registro_editar[$col_id]; ?></h3>
                <form method="POST" action="admin.php?tabla=<?php echo $tabla_activa; ?>">
                    <input type="hidden" name="action" value="guardar_edicion">
                    <input type="hidden" name="tabla" value="<?php echo $tabla_activa; ?>">
                    <input type="hidden" name="col_id" value="<?php echo $col_id; ?>">
                    <input type="hidden" name="id" value="<?php echo $registro_editar[$col_id]; ?>">
                    <div class="edit-grid">
                        <?php foreach ($columnas as $col):
                            $field = $col["Field"];
                            $val   = $registro_editar[$field] ?? "";
                            $is_pk = $col["Key"] === "PRI";
                        ?>
                        <div class="edit-group">
                            <label><?php echo $field; ?><?php echo $is_pk ? " " : ""; ?></label>
                            <?php if ($is_pk): ?>
                                <input type="text" value="<?php echo htmlspecialchars($val); ?>" readonly>
                            <?php elseif (strlen($val) > 80): ?>
                                <textarea name="<?php echo $field; ?>" rows="3"><?php echo htmlspecialchars($val); ?></textarea>
                            <?php else: ?>
                                <input type="text" name="<?php echo $field; ?>" value="<?php echo htmlspecialchars($val); ?>">
                            <?php endif; ?>
                        </div>
                        <?php endforeach; ?>
                    </div>
                    <div class="edit-actions">
                        <button type="submit" class="btn btn-save btn-sm"> guardar cambios</button>
                        <a href="admin.php?tabla=<?php echo $tabla_activa; ?>" class="btn btn-cancel btn-sm">cancelar</a>
                    </div>
                </form>
            </div>
            <?php endif; ?>

            
            <div class="panel">
                <div class="panel-header">
                    <h2><?php echo $iconos[$tabla_activa][0]; ?> <?php echo $iconos[$tabla_activa][1]; ?></h2>
                    <form method="GET" action="admin.php">
                        <input type="hidden" name="tabla" value="<?php echo $tabla_activa; ?>">
                        <select name="col_buscar">
                            <?php foreach ($columnas as $col): ?>
                                <option value="<?php echo $col['Field']; ?>"
                                    <?php echo $col_buscar === $col['Field'] ? 'selected' : ''; ?>>
                                    <?php echo $col['Field']; ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <input type="text" name="buscar" placeholder="buscar..."
                               value="<?php echo htmlspecialchars($buscar_q); ?>">
                        <button type="submit" class="btn btn-sm" style="background:#3498db;color:white;">buscar</button>
                        <?php if ($buscar_q): ?>
                            <a href="admin.php?tabla=<?php echo $tabla_activa; ?>"
                               class="btn btn-sm" style="background:#95a5a6;color:white;"></a>
                        <?php endif; ?>
                    </form>
                </div>

                <div style="overflow-x:auto;">
                    <table class="tabla-admin">
                        <thead>
                            <tr>
                                <?php foreach ($columnas as $col): ?>
                                    <th><?php echo $col["Field"]; ?></th>
                                <?php endforeach; ?>
                                <th>acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php
                            $result = getDatos($conn, $tabla_activa, $buscar_q, $col_buscar);
                            while ($row = mysqli_fetch_assoc($result)):
                            ?>
                            <tr>
                                <?php foreach ($columnas as $col):
                                    $val = $row[$col["Field"]] ?? "";
                                    
                                    if (stripos($col["Field"], "contrasena") !== false || stripos($col["Field"], "password") !== false) {
                                        $val = "";
                                    }
                                ?>
                                    <td title="<?php echo htmlspecialchars($row[$col["Field"]] ?? ""); ?>">
                                        <?php echo htmlspecialchars(mb_strimwidth($val, 0, 40, "")); ?>
                                    </td>
                                <?php endforeach; ?>
                                <td>
                                    <div style="display:flex;gap:5px;">
                                        <a href="admin.php?tabla=<?php echo $tabla_activa; ?>&editar=<?php echo $row[$col_id]; ?>"
                                           class="btn-sm btn-edit"></a>
                                        <button class="btn-sm btn-del"
                                                onclick="confirmarEliminar(<?php echo $row[$col_id]; ?>, '<?php echo $tabla_activa; ?>', '<?php echo $col_id; ?>')">
                                            
                                        </button>
                                    </div>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
                <div class="tabla-footer"><?php echo $total_rows; ?> registro(s) encontrado(s)</div>
            </div>
        </div>
    </div>

    
    <div class="modal-overlay" id="modalEliminar">
        <div class="modal-box">
            <h3> confirmar eliminación</h3>
            <p id="modalTexto">¿Seguro que quieres eliminar este registro? Esta acción no se puede deshacer.</p>
            <div class="modal-btns">
                <form method="POST" action="admin.php?tabla=<?php echo $tabla_activa; ?>" id="formEliminar">
                    <input type="hidden" name="action" value="eliminar">
                    <input type="hidden" name="tabla" id="modalTabla">
                    <input type="hidden" name="col_id" id="modalColId">
                    <input type="hidden" name="id" id="modalId">
                    <div style="display:flex;gap:10px;">
                        <button type="submit" class="btn btn-sm btn-del" style="padding:10px 20px;">sí, eliminar</button>
                        <button type="button" class="btn btn-sm btn-cancel" style="padding:10px 20px;"
                                onclick="cerrarModal()">cancelar</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function confirmarEliminar(id, tabla, colId) {
            document.getElementById("modalId").value    = id;
            document.getElementById("modalTabla").value = tabla;
            document.getElementById("modalColId").value = colId;
            document.getElementById("modalTexto").textContent =
                `¿Seguro que quieres eliminar el registro ID ${id} de la tabla "${tabla}"? Esta acción no se puede deshacer.`;
            document.getElementById("modalEliminar").classList.add("active");
        }
        function cerrarModal() {
            document.getElementById("modalEliminar").classList.remove("active");
        }
        document.getElementById("modalEliminar").addEventListener("click", function(e) {
            if (e.target === this) cerrarModal();
        });
        <?php if ($registro_editar): ?>
        window.onload = () => document.querySelector('.edit-panel').scrollIntoView({behavior:'smooth'});
        <?php endif; ?>
    </script>
</body>
</html>
