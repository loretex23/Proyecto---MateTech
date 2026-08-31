<?php
require_once "login/auth.php";
include '../sql/basededatos.php';

$rol = $_SESSION["Rol"] ?? "";

// 1. Obtener lista de categorías para el filtro
$categorias = $pdo->query("SELECT id, nombre FROM categorias ORDER BY nombre ASC")->fetchAll(PDO::FETCH_OBJ);

// 2. Determinar categoría seleccionada (por defecto toma la primera)
$categoria_id = isset($_GET['categoria_id']) ? (int)$_GET['categoria_id'] : ($categorias[0]->id ?? 0);

$posiciones = [];

if ($categoria_id > 0) {
    /*
     * Explicación de la consulta SQL:
     * Unimos la tabla 'club' con los partidos jugados en la categoría seleccionada en donde el club haya actuado como Local o Visitante.
     * Evaluamos victorias (3 pts), empates (1 pt) y derrotas (0 pts) según los goles de cada equipo.
     */
    $sql = "
        SELECT 
            c.id AS club_id,
            c.nombre AS club_nombre,
            COUNT(p.id) AS PJ,
            SUM(CASE 
                WHEN (p.club_local_id = c.id AND p.goles_local > p.goles_visitante) OR 
                     (p.club_visitante_id = c.id AND p.goles_visitante > p.goles_local) THEN 1 
                ELSE 0 
            END) AS PG,
            SUM(CASE 
                WHEN p.goles_local = p.goles_visitante THEN 1 
                ELSE 0 
            END) AS PE,
            SUM(CASE 
                WHEN (p.club_local_id = c.id AND p.goles_local < p.goles_visitante) OR 
                     (p.club_visitante_id = c.id AND p.goles_visitante < p.goles_local) THEN 1 
                ELSE 0 
            END) AS PP,
            SUM(CASE 
                WHEN p.club_local_id = c.id THEN p.goles_local 
                ELSE p.goles_visitante 
            END) AS GF,
            SUM(CASE 
                WHEN p.club_local_id = c.id THEN p.goles_visitante 
                ELSE p.goles_local 
            END) AS GC,
            SUM(CASE 
                WHEN p.club_local_id = c.id THEN (p.goles_local - p.goles_visitante)
                ELSE (p.goles_visitante - p.goles_local)
            END) AS DG,
            SUM(CASE 
                WHEN (p.club_local_id = c.id AND p.goles_local > p.goles_visitante) OR 
                     (p.club_visitante_id = c.id AND p.goles_visitante > p.goles_local) THEN 3
                WHEN p.goles_local = p.goles_visitante THEN 1
                ELSE 0 
            END) AS Pts
        FROM club c
        LEFT JOIN partidos p ON (c.id = p.club_local_id OR c.id = p.club_visitante_id)
            AND p.categoria_id = :categoria_id 
            AND p.estado = 'jugado'
        WHERE c.rol = 'Club'
        GROUP BY c.id, c.nombre
        ORDER BY Pts DESC, DG DESC, GF DESC, c.nombre ASC
    ";

    $stmt = $pdo->prepare($sql);
    $stmt->execute([':categoria_id' => $categoria_id]);
    $posiciones = $stmt->fetchAll(PDO::FETCH_OBJ);
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow - Tabla de Posiciones</title>
</head>
<body class="fondo-body">

<?php include 'diseños/navbar.php'; ?>

<main>
    <div class="home-hero">
        <div>
            <h1>Tabla de Posiciones</h1>
            <p>Clasificación general de los equipos según los resultados oficiales.</p>
        </div>
        <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
    </div>

    <div class="container mt-4">
        <!-- Selector de Categoría -->
        <div class="row mb-4 justify-content-center">
            <div class="col-md-5">
                <form method="GET" action="posiciones.php" class="d-flex align-items-center gap-2">
                    <label for="categoria_id" class="form-label mb-0 fw-bold text-white text-nowrap">Categoría:</label>
                    <select name="categoria_id" id="categoria_id" class="form-select" onchange="this.form.submit()">
                        <?php foreach ($categorias as $cat): ?>
                            <option value="<?= $cat->id ?>" <?= $cat->id == $categoria_id ? 'selected' : '' ?>>
                                <?= htmlspecialchars($cat->nombre) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </form>
            </div>
        </div>

        <!-- Tabla de Posiciones -->
        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-striped table-hover mb-0 text-center align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th style="width: 50px;">#</th>
                                <th class="text-start">Club</th>
                                <th>Pts</th>
                                <th>PJ</th>
                                <th>PG</th>
                                <th>PE</th>
                                <th>PP</th>
                                <th>GF</th>
                                <th>GC</th>
                                <th>DIF</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($posiciones)): ?>
                                <tr>
                                    <td colspan="10" class="text-muted py-4">No hay datos registrados para esta categoría.</td>
                                </tr>
                            <?php else: ?>
                                <?php $pos = 1; foreach ($posiciones as $p): ?>
                                    <tr class="<?= ($_SESSION['ClubID'] ?? null) == $p->club_id ? 'table-primary fw-bold' : '' ?>">
                                        <td><strong><?= $pos++ ?></strong></td>
                                        <td class="text-start"><?= htmlspecialchars($p->club_nombre) ?></td>
                                        <td class="fw-bold text-primary"><?= $p->Pts ?? 0 ?></td>
                                        <td><?= $p->PJ ?? 0 ?></td>
                                        <td><?= $p->PG ?? 0 ?></td>
                                        <td><?= $p->PE ?? 0 ?></td>
                                        <td><?= $p->PP ?? 0 ?></td>
                                        <td><?= $p->GF ?? 0 ?></td>
                                        <td><?= $p->GC ?? 0 ?></td>
                                        <td>
                                            <?php 
                                                $dg = $p->DG ?? 0;
                                                if ($dg > 0) echo "<span class='text-success'>+$dg</span>";
                                                elseif ($dg < 0) echo "<span class='text-danger'>$dg</span>";
                                                else echo "0";
                                            ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <!-- Leyenda de siglas -->
        <div class="d-flex flex-wrap gap-3 mt-3 text-white-50 small justify-content-center">
            <span><strong>Pts:</strong> Puntos</span>
            <span><strong>PJ:</strong> Partidos Jugados</span>
            <span><strong>PG:</strong> Ganados</span>
            <span><strong>PE:</strong> Empatados</span>
            <span><strong>PP:</strong> Perdidos</span>
            <span><strong>GF:</strong> Goles a Favor</span>
            <span><strong>GC:</strong> Goles en Contra</span>
            <span><strong>DIF:</strong> Diferencia de Goles</span>
        </div>
    </div>
</main>

<footer>
    &copy; 2026 MateTech. Todos los derechos reservados.
    <img class="foot" src="../img/logo.png" alt="Logo">
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>