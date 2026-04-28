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
    <link rel="stylesheet" href="css/admin.css">
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
