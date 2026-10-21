<?php
require_once "login/auth.php";
include '../sql/basededatos.php';
$id_club_actual = $_SESSION['club_id'] ?? null;

$jugadores_transferibles = [];

if ($id_club_actual) {
    try {
        // Consulta SQL utilizando la tabla 'jugadores' y 'club' de tu base de datos matetech
        $sql = "SELECT j.id, j.ci, j.nombre, j.apellido, j.fecha_nacimiento, j.carnet_vencimiento, j.masa, j.altura, c.nombre AS club_actual 
                FROM jugadores j
                INNER JOIN club c ON j.club_id = c.id
                WHERE j.club_id != :id_club_actual 
                  AND j.carnet_vencimiento < CURDATE()";

        $stmt = $pdo->prepare($sql);
        $stmt->execute(['id_club_actual' => $id_club_actual]);
        $jugadores_transferibles = $stmt->fetchAll(PDO::FETCH_ASSOC);

    } catch (PDOException $e) {
        $error_db = "Error al cargar los jugadores: " . $e->getMessage();
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="icon" href="../img/logo.png" type="image/png">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons-webfont@latest/dist/tabler-icons.min.css">
    <link href="../estilos.css" rel="stylesheet">
    <title>LeagueFlow - Transferencias</title>
</head>
<body class="fondo-body">

    <?php include 'diseños/navbar.php'; ?>

    <main class="container my-4">
        <!-- Sección Hero existente -->
        <div class="home-hero mb-5">
            <div>
                <h1>Transferencias</h1>
                <p>Jugadores de otros clubes con el carnet de salud vencido disponibles para fichaje.</p>
            </div>
            <img src="../img/Login-foto0.png" alt="LeagueFlow" class="home-hero-logo">
        </div>

        <!-- Sección de Alertas o Errores -->
        <?php if (isset($error_db)): ?>
            <div class="alert alert-danger" role="alert">
                <?= $error_db ?>
            </div>
        <?php endif; ?>

        <?php if (!$id_club_actual): ?>
            <div class="alert alert-warning" role="alert">
                Debes iniciar sesión con una cuenta de club para ver los jugadores disponibles para transferencias.
            </div>
        <?php else: ?>
            <!-- Tabla de Jugadores Disponibles -->
            <div class="card shadow-sm border-0 bg-dark text-white p-3">
                <div class="card-body">
                    <h3 class="card-title mb-4">Mercado de Pases (Carnets Vencidos)</h3>
                    
                    <div class="table-responsive">
                        <table class="table table-dark table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Cédula (CI)</th>
                                    <th>Jugador</th>
                                    <th>Club Actual</th>
                                    <th>Vencimiento de Carnet</th>
                                    <th>Acción</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($jugadores_transferibles)): ?>
                                    <?php foreach ($jugadores_transferibles as $jugador): ?>
                                        <tr>
                                            <td><?= htmlspecialchars($jugador['ci']) ?></td>
                                            <td><?= htmlspecialchars($jugador['nombre'] . ' ' . $jugador['apellido']) ?></td>
                                            <td><span class="badge bg-secondary"><?= htmlspecialchars($jugador['club_actual']) ?></span></td>
                                            <td class="text-danger fw-bold"><?= htmlspecialchars($jugador['carnet_vencimiento']) ?></td>
                                            <td>
                                                <form action="procesar_transferencia.php" method="POST">
                                                    <input type="hidden" name="jugador_id" value="<?= $jugador['id'] ?>">
                                                    <button type="submit" class="btn btn-success btn-sm">
                                                        <i class="ti ti-user-plus"></i> Solicitar Fichaje
                                                    </button>
                                                </form>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="5" class="text-center text-muted py-4">
                                            No hay jugadores de otros clubes con el carnet vencido en este momento.
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </main>

    <footer>
        &copy; 2026 MateTech. Todos los derechos reservados.
        <img class="foot" src="../img/logo.png" alt="Logo">
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>