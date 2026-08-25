<?php
require_once "login/auth.php";
include "../sql/basededatos.php";

$rol = $_SESSION["Rol"] ?? "";

if ($rol === "Admin" && isset($_POST["btnpublicar"])) {
    $titulo   = trim($_POST["titulo"]);
    $contenido = trim($_POST["contenido"]);
    $tipo      = $_POST["tipo"] ?? "General";
    $pdf_ruta  = null;

    if (!empty($titulo) && !empty($contenido)) {
        if (isset($_FILES["pdf"]) && $_FILES["pdf"]["error"] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES["pdf"]["name"], PATHINFO_EXTENSION));
            if ($ext === "pdf") {
                $dir = "../img/comunicados/";
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $nombre = "comunicado_" . time() . ".pdf";
                if (move_uploaded_file($_FILES["pdf"]["tmp_name"], $dir . $nombre))
                    $pdf_ruta = "img/comunicados/" . $nombre;
            }
        }

        $pdo->prepare("INSERT INTO comunicados (titulo, contenido, tipo, pdf_url) VALUES (?, ?, ?, ?)")
            ->execute([$titulo, $contenido, $tipo, $pdf_ruta]);

        header("Location: noticias.php");
        exit();
    }
}

$comunicados = $pdo->query("SELECT * FROM comunicados ORDER BY fecha_publicacion DESC")->fetchAll(PDO::FETCH_OBJ);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow - Noticias</title>
</head>
<body class="fondo-body">

<?php include 'diseños/navbar.php'; ?>

<main>
    <div class="home-hero">
        <div>
            <h1>Noticias</h1>
            <p>Comunicados oficiales de la liga.</p>
        </div>
        <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
    </div>

    <div class="container my-4">
        <?php if ($rol === "Admin"): ?>
            <button type="button" class="btn btn-primary mb-4" data-bs-toggle="modal" data-bs-target="#modalNoticia">
                <i class="ti ti-plus"></i> Nuevo comunicado
            </button>
        <?php endif; ?>

        <?php if (empty($comunicados)): ?>
            <p class="text-muted">No hay comunicados publicados.</p>
        <?php endif; ?>

        <?php foreach ($comunicados as $c): ?>
            <article class="card mb-3 shadow-sm">
                <div class="card-body">
                    <span class="badge bg-primary"><?= htmlspecialchars($c->tipo) ?></span>
                    <h4 class="mt-2"><?= htmlspecialchars($c->titulo) ?></h4>
                    <small class="text-muted"><?= date("d/m/Y H:i", strtotime($c->fecha_publicacion)) ?></small>
                    <p class="mt-3 mb-2"><?= nl2br(htmlspecialchars($c->contenido)) ?></p>
                    <?php if (!empty($c->pdf_url)): ?>
                        <a href="../<?= htmlspecialchars($c->pdf_url) ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                            <i class="ti ti-file-type-pdf"></i> Ver PDF
                        </a>
                    <?php endif; ?>
                </div>
            </article>
        <?php endforeach; ?>
    </div>
</main>

<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<?php if ($rol === "Admin"): ?>
<div class="modal fade modal-comunicado" id="modalNoticia" tabindex="-1" aria-labelledby="modalNoticiaLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content">
            <form method="POST" enctype="multipart/form-data">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalNoticiaLabel">
                        <i class="ti ti-news"></i> Nuevo comunicado
                    </h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cerrar"></button>
                </div>

                <div class="modal-body">
                    <div class="mb-3">
                        <label for="titulo" class="form-label">Título</label>
                        <input type="text" id="titulo" name="titulo" class="form-control" placeholder="Título del comunicado" required>
                    </div>

                    <div class="mb-3">
                        <label for="tipo" class="form-label">Tipo de comunicado</label>
                        <select id="tipo" name="tipo" class="form-select" required>
                            <?php foreach (["General","Suspensión","Lesión","Partido","Sanción","Horario","Competencia","Importante"] as $t): ?>
                                <option value="<?= $t ?>"><?= $t ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <div class="mb-3">
                        <label for="contenido" class="form-label">Contenido</label>
                        <textarea id="contenido" name="contenido" class="form-control" rows="6" placeholder="Contenido del comunicado..." required></textarea>
                    </div>

                    <div class="mb-2">
                        <label for="pdf" class="form-label">Documento PDF</label>
                        <input type="file" id="pdf" name="pdf" class="form-control" accept=".pdf,application/pdf">
                    </div>
                    <small class="modal-ayuda"><i class="ti ti-info-circle"></i> El documento PDF es opcional.</small>
                </div>

                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" name="btnpublicar" value="1" class="btn btn-primary">
                        <i class="ti ti-send"></i> Publicar
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
<?php endif; ?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>