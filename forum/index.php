<?php
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);
session_start();
require_once('../conexion.php');

// Verificar usuario estar logueado
if (!isset($_SESSION['id'])) {
    header('Location: /inicio_sesion/index.html');
    exit;
}

$user_id = $_SESSION['id'];
$sql = "SELECT username FROM usuarios WHERE id = $user_id";
$result = mysqli_query($conn, $sql);
if ($result && mysqli_num_rows($result) > 0) {
    $user = mysqli_fetch_assoc($result);
    $_SESSION['username'] = $user['username'];
} else {
    header('Location: /inicio_sesion/index.html');
    exit;
}

// Crear nuevo tema
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['publicar_tema'])) {
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
    $query = "INSERT INTO temas (titulo, contenido, id_usuario, fecha) VALUES ('$titulo', '$contenido', '$user_id', NOW())";
    if (mysqli_query($conn, $query)) {
        header('Location: index.php');
        exit();
    } else {
        echo "Error al publicar el tema: " . mysqli_error($conn);
    }
}

// Editar tema
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_tema'])) {
    $id_tema = $_POST['id_tema'];
    $titulo = mysqli_real_escape_string($conn, $_POST['titulo']);
    $contenido = mysqli_real_escape_string($conn, $_POST['contenido']);
    $query = "UPDATE temas SET titulo='$titulo', contenido='$contenido' WHERE id=$id_tema AND id_usuario='$user_id'";
    mysqli_query($conn, $query);
}

// Eliminar un tema y sus comentarios
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_tema'])) {
    $id_tema = $_POST['id_tema'];
    mysqli_query($conn, "DELETE FROM comentarios WHERE id_tema=$id_tema");
    mysqli_query($conn, "DELETE FROM temas WHERE id=$id_tema AND id_usuario='$user_id'");
}

// Publicar un comentario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['publicar_comentario'])) {
    $id_tema = $_POST['id_tema'];
    $contenido = mysqli_real_escape_string($conn, $_POST['contenido_comentario']);
    $query = "INSERT INTO comentarios (contenido, id_usuario, id_tema, fecha) VALUES ('$contenido', '$user_id', '$id_tema', NOW())";
    if (mysqli_query($conn, $query)) {
        header("Location: index.php#tema-$id_tema"); // Redirigir para evitar reenvío de formulario
        exit();
    }
}

// Editar un comentario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['editar_comentario'])) {
    $id_comentario = $_POST['id_comentario'];
    $contenido = mysqli_real_escape_string($conn, $_POST['contenido_comentario']);
    $query = "UPDATE comentarios SET contenido='$contenido' WHERE id=$id_comentario AND id_usuario='$user_id'";
    mysqli_query($conn, $query);
}

// Eliminar un comentario
if ($_SERVER["REQUEST_METHOD"] == "POST" && isset($_POST['eliminar_comentario'])) {
    $id_comentario = $_POST['id_comentario'];
    mysqli_query($conn, "DELETE FROM comentarios WHERE id=$id_comentario AND id_usuario='$user_id'");
}

// Obtener los temas
$sql_temas = "
    SELECT temas.*, usuarios.username AS usuario 
    FROM temas 
    LEFT JOIN usuarios ON temas.id_usuario = usuarios.id 
    ORDER BY temas.fecha DESC";
$consulta_temas = mysqli_query($conn, $sql_temas);
?>

<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Foro MVMUP</title>
    <link rel="icon" href="/img/favicon-logo.ico">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        html, body {
            height: 100%;
            margin: 0;
            display: flex;
            flex-direction: column;
        }

        .container {
            flex: 1;
        }

        footer {
            flex-shrink: 0;
            background-color: #343a40;
            color: white;
            text-align: center;
            padding: 1rem 0;
        }

        .tema, .comentario {
            border: 1px solid #ddd;
            border-radius: 5px;
            padding: 15px;
            margin-bottom: 15px;
            background-color: #f9f9f9;
        }

        .tema-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .btn-sm {
            margin-left: 5px;
        }

        .comentario {
            margin-left: 20px;
        }

        .form-control {
            margin-bottom: 10px;
        }
    </style>
</head>
<body>
<nav class="navbar navbar-expand-md navbar-dark bg-dark fixed-top">
    <div class="container">
        <img src="/img/logo.png" alt="Logo MVMUP" class="rounded-circle" width="50" height="50">
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarSupportedContent" aria-controls="navbarSupportedContent" aria-expanded="false" aria-label="Toggle navigation">
            <span class="navbar-toggler-icon"></span>
        </button>
        <div class="collapse navbar-collapse" id="navbarSupportedContent">
            <ul class="navbar-nav ms-auto mb-2 mb-lg-0">
                <li class="nav-item"><a class="nav-link" href="/index.html">Inicio</a></li>
                <li class="nav-item"><a class="nav-link" href="/pagina_almacenamiento/index.html">Almacenamiento</a></li>
                <li class="nav-item"><a class="nav-link active" href="/forum/index.php">Forum</a></li>
                <li class="nav-item" id="auth-link">
                    <a class="nav-link" href="/inicio_sesion/index.html">Iniciar sesión/Registrarse</a>
                </li>
            </ul>
        </div>
    </div>
</nav>

<div class="container mt-5 pt-5">
    <h1 class="text-center mb-4">Bienvenido al Forum</h1>

    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h3>Publicar un nuevo tema</h3>
        </div>
        <div class="card-body">
            <form method="POST" action="index.php">
                <input type="text" name="titulo" class="form-control" placeholder="Título del tema" required>
                <textarea name="contenido" class="form-control" placeholder="Contenido del tema" required></textarea>
                <button type="submit" name="publicar_tema" class="btn btn-primary mt-2">Publicar Tema</button>
            </form>
        </div>
    </div>

    <h3 class="mb-4">Temas del Foro</h3>
    <?php while ($tema = mysqli_fetch_assoc($consulta_temas)): ?>
        <div class="tema">
            <div class="tema-header">
                <div>
                    <h4 id="tema-titulo-<?= $tema['id'] ?>"><?= htmlspecialchars($tema['titulo']) ?></h4>
                    <input type="text" id="input-titulo-<?= $tema['id'] ?>" value="<?= htmlspecialchars($tema['titulo']) ?>" class="form-control" style="display:none;">
                </div>
                <small class="text-muted">Publicado por <?= htmlspecialchars($tema['usuario'] ?? 'Usuario desconocido') ?> el <?= $tema['fecha'] ?></small>
            </div>
            <p id="tema-contenido-<?= $tema['id'] ?>"><?= nl2br(htmlspecialchars($tema['contenido'])) ?></p>
            <textarea id="input-contenido-<?= $tema['id'] ?>" class="form-control" style="display:none;"><?= htmlspecialchars($tema['contenido']) ?></textarea>

            <?php if ($tema['id_usuario'] == $user_id): ?>
                <div class="mt-2">
                    <button class="btn btn-warning btn-sm" onclick="editarTema(<?= $tema['id'] ?>)">Editar</button>
                    <form method="POST" style="display:inline;" onsubmit="return guardarTema(<?= $tema['id'] ?>)">
                        <input type="hidden" name="id_tema" value="<?= $tema['id'] ?>">
                        <input type="hidden" name="titulo" id="campo-titulo-<?= $tema['id'] ?>">
                        <input type="hidden" name="contenido" id="campo-contenido-<?= $tema['id'] ?>">
                        <button type="submit" name="editar_tema" class="btn btn-success btn-sm" id="guardar-tema-<?= $tema['id'] ?>" style="display:none;">Guardar</button>
                    </form>
                    <form method="POST" style="display:inline;">
                        <input type="hidden" name="id_tema" value="<?= $tema['id'] ?>">
                        <button type="submit" name="eliminar_tema" class="btn btn-danger btn-sm">Eliminar</button>
                    </form>
                </div>
            <?php endif; ?>

            <h5 class="mt-4">Comentarios:</h5>
            <?php
            $id_tema = $tema['id'];
            $consulta_comentarios = mysqli_query($conn, "SELECT comentarios.*, usuarios.username AS usuario FROM comentarios LEFT JOIN usuarios ON comentarios.id_usuario = usuarios.id WHERE id_tema = $id_tema ORDER BY fecha DESC");
            while ($comentario = mysqli_fetch_assoc($consulta_comentarios)): ?>
                <div class="comentario">
                    <p><strong><?= htmlspecialchars($comentario['usuario']) ?></strong> <small class="text-muted">- <?= $comentario['fecha'] ?></small></p>
                    <p id="comentario-texto-<?= $comentario['id'] ?>"><?= nl2br(htmlspecialchars($comentario['contenido'])) ?></p>
                    <textarea id="comentario-input-<?= $comentario['id'] ?>" class="form-control" style="display:none;"><?= htmlspecialchars($comentario['contenido']) ?></textarea>

                    <?php if ($comentario['id_usuario'] == $user_id): ?>
                        <div class="mt-1">
                            <button class="btn btn-warning btn-sm" onclick="editarComentario(<?= $comentario['id'] ?>)">Editar</button>
                            <form method="POST" style="display:inline;" onsubmit="return guardarComentario(<?= $comentario['id'] ?>)">
                                <input type="hidden" name="id_comentario" value="<?= $comentario['id'] ?>">
                                <input type="hidden" name="contenido_comentario" id="campo-comentario-<?= $comentario['id'] ?>">
                                <button type="submit" name="editar_comentario" class="btn btn-success btn-sm" id="guardar-comentario-<?= $comentario['id'] ?>" style="display:none;">Guardar</button>
                            </form>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="id_comentario" value="<?= $comentario['id'] ?>">
                                <button type="submit" name="eliminar_comentario" class="btn btn-danger btn-sm">Eliminar</button>
                            </form>
                        </div>
                    <?php endif; ?>
                </div>
            <?php endwhile; ?>

            <form method="POST" class="mt-3">
                <input type="hidden" name="id_tema" value="<?= $tema['id'] ?>">
                <textarea name="contenido_comentario" class="form-control" placeholder="Escribe tu comentario..." required></textarea>
                <button type="submit" name="publicar_comentario" class="btn btn-secondary mt-2">Comentar</button>
            </form>
        </div>
    <?php endwhile; ?>
</div>

  <!--Footer-->
  <footer class="bg-dark text-white py-3 mt-auto">
    <div class="container d-flex flex-wrap justify-content-between">
      <div class="footer-left">
        <p>Has iniciado sesión como: <span id="username"></span></p>
      </div>
      <div class="footer-center text-center flex-grow-1">
        <p>&copy; IES Manuel Vazquez Montalban. Todos los derechos reservados.</p>
      </div>
      <div class="footer-right text-end">
        <p><a href="https://agora.xtec.cat/iesmvm" class="text-white text-decoration-none">Moodle MVM: https://agora.xtec.cat/iesmvm/</a></p>
      </div>
    </div>
  </footer>

<script>
function editarTema(id) {
    document.getElementById('tema-titulo-' + id).style.display = 'none';
    document.getElementById('tema-contenido-' + id).style.display = 'none';
    document.getElementById('input-tiulo-' + id).style.display = 'block';
    document.getElementById('input-contenido-' + id).style.display = 'block';
    document.getElementById('guardar-tema-' + id).style.display = 'inline-block';
}

function guardarTema(id) {
    const titulo = document.getElementById('input-titulo-' + id).value;
    const contenido = document.getElementById('input-contenido-' + id).value;
    document.getElementById('campo-titulo-' + id).value = titulo;
    document.getElementById('campo-contenido-' + id).value = contenido;
    return true;
}

function editarComentario(id) {
    document.getElementById('comentario-texto-' + id).style.display = 'none';
    document.getElementById('comentario-input-' + id).style.display = 'block';
    document.getElementById('guardar-comentario-' + id).style.display = 'inline-block';
}

function guardarComentario(id) {
    const contenido = document.getElementById('comentario-input-' + id).value;
    document.getElementById('campo-comentario-' + id).value = contenido;
    return true;
}
</script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="/check_sesion.js"></script>

</body>
</html>