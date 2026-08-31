<?php
include 'basededatos.php';

if (!empty($_POST["btneditar"])) {

    $id              = (int)$_POST["id"];
    $ci              = trim($_POST["ci"]);
    $masa            = !empty($_POST["masa"])        ? (float)$_POST["masa"]        : null;
    $altura          = !empty($_POST["altura"])      ? (float)$_POST["altura"]      : null;

    $verificar = $pdo->prepare("SELECT COUNT(*) FROM jugadores WHERE ci = ? AND id != ?");
    $verificar->execute([$ci, $id]);
    if ($verificar->fetchColumn() > 0) {
        echo "<script>alert('La cédula $ci ya está registrada a otro jugador.');window.history.back();</script>";
        exit();
    }

    $stmt = $pdo->prepare("SELECT foto_url FROM jugadores WHERE id = ?");
    $stmt->execute([$id]);
    $actual = $stmt->fetch(PDO::FETCH_OBJ);
    $foto_ruta_db = $actual ? $actual->foto_url : null;

    if (isset($_FILES['foto_url']) && $_FILES['foto_url']['error'] === UPLOAD_ERR_OK) {
        $ext = strtolower(pathinfo($_FILES['foto_url']['name'], PATHINFO_EXTENSION));
        if (in_array($ext, ['jpg','jpeg','png'])) {
            if ($foto_ruta_db && file_exists("../" . $foto_ruta_db))
                unlink("../" . $foto_ruta_db);
            $dir = "../img/fotos/";
            if (!is_dir($dir)) mkdir($dir, 0777, true);
            $nuevo = "Foto_" . $ci . "." . $ext;
            if (move_uploaded_file($_FILES['foto_url']['tmp_name'], $dir . $nuevo))
                $foto_ruta_db = "img/fotos/" . $nuevo;
        }
    }

    $pdo->prepare("
        UPDATE jugadores SET
            nombre=?, apellido=?, ci=?, fecha_nacimiento=?,
            carnet_vencimiento=?, club_id=?, categoria_id=?,
            foto_url=?, masa=?, altura=?
        WHERE id=?
    ")->execute([
        $_POST["nombre"], $_POST["apellido"], $ci,
        $_POST["fecha_nacimiento"], $_POST["carnet_vencimiento"],
        $_POST["club_id"], $_POST["categoria_id"],
        $foto_ruta_db, $masa, $altura,
        $id
    ]);

    header("Location: ../paginas/jugadores.php");
    exit();
}