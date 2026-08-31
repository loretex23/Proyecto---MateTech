<?php
include 'basededatos.php';

if (!empty($_POST["btnregistrar"])) {

    if (
        !empty($_POST["nombre"]) && !empty($_POST["apellido"]) &&
        !empty($_POST["ci"]) && !empty($_POST["fecha_nacimiento"]) &&
        !empty($_POST["carnet_vencimiento"]) &&
        !empty($_POST["club_id"]) && !empty($_POST["categoria_id"])
    ) {
        $ci = trim($_POST["ci"]);

        $verificar = $pdo->prepare("SELECT COUNT(*) FROM jugadores WHERE ci = ?");
        $verificar->execute([$ci]);
        if ($verificar->fetchColumn() > 0) {
            echo "<script>alert('La cédula $ci ya está registrada.');window.history.back();</script>";
            exit();
        }

        $foto_ruta_db = null;
        if (isset($_FILES['foto_url']) && $_FILES['foto_url']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['foto_url']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png'])) {
                $dir = "../img/fotos/";
                if (!is_dir($dir)) mkdir($dir, 0777, true);
                $nuevo = "Foto_" . $ci . "." . $ext;
                if (move_uploaded_file($_FILES['foto_url']['tmp_name'], $dir . $nuevo))
                    $foto_ruta_db = "img/fotos/" . $nuevo;
            }
        }

        $masa        = !empty($_POST["masa"])        ? (float)$_POST["masa"]        : null;
        $altura      = !empty($_POST["altura"])      ? (float)$_POST["altura"]      : null;

        $sql = $pdo->prepare("
            INSERT INTO jugadores
              (nombre, apellido, ci, fecha_nacimiento, carnet_vencimiento,
               club_id, categoria_id, foto_url, masa, altura)
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");

        if ($sql->execute([
            $_POST["nombre"], $_POST["apellido"], $ci,
            $_POST["fecha_nacimiento"], $_POST["carnet_vencimiento"],
            $_POST["club_id"], $_POST["categoria_id"],
            $foto_ruta_db, $masa, $altura
        ])) {
            header("Location: ../paginas/jugadores.php");
            exit();
        }

        echo "<div class='alert alert-danger'>Error al registrar el jugador.</div>";
    } else {
        echo "<div class='alert alert-warning'>Complete todos los campos requeridos.</div>";
    }
}