<?php
include 'basededatos.php';

if (!empty($_POST["btneditar"])) {

    $id = $_POST["id"];
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $ci = trim($_POST["ci"]);
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $carnet_vencimiento = $_POST["carnet_vencimiento"];
    $club_id = $_POST["club_id"];
    $categoria_id = $_POST["categoria_id"];

    $verificar = $pdo->prepare("
        SELECT COUNT(*) 
        FROM jugadores 
        WHERE ci = ? AND id != ?
    ");

    $verificar->execute([$ci, $id]);

    if ($verificar->fetchColumn() > 0) {
        echo "
        <script>
            alert('No se puede editar el jugador. La cédula $ci ya está registrada a otro jugador.');
            window.history.back();
        </script>
        ";
        exit();
    }


    $stmt = $pdo->prepare("SELECT foto_url FROM jugadores WHERE id = ?");
    $stmt->execute([$id]);

    $jugadorActual = $stmt->fetch(PDO::FETCH_OBJ);

    $foto_ruta_db = $jugadorActual
        ? $jugadorActual->foto_url
        : null;


    if (
        isset($_FILES['foto_url']) &&
        $_FILES['foto_url']['error'] === UPLOAD_ERR_OK
    ) {

        $nombre_original = $_FILES['foto_url']['name'];
        $ext = strtolower(
            pathinfo($nombre_original, PATHINFO_EXTENSION)
        );

        $extensiones_permitidas = array(
            'jpg',
            'jpeg',
            'png'
        );

        if (in_array($ext, $extensiones_permitidas)) {

            if (
                $foto_ruta_db &&
                file_exists("../" . $foto_ruta_db)
            ) {
                unlink("../" . $foto_ruta_db);
            }

            $nuevo_nombre = "Foto_" . $ci . "." . $ext;

            $directorio_destino = "../img/fotos/";

            if (!file_exists($directorio_destino)) {
                mkdir($directorio_destino, 0777, true);
            }

            $ruta_completa = $directorio_destino . $nuevo_nombre;

            if (
                move_uploaded_file(
                    $_FILES['foto_url']['tmp_name'],
                    $ruta_completa
                )
            ) {
                $foto_ruta_db = "img/fotos/" . $nuevo_nombre;
            }
        }
    }
    $sql = $pdo->prepare("
        UPDATE jugadores SET
            nombre = ?,
            apellido = ?,
            ci = ?,
            fecha_nacimiento = ?,
            carnet_vencimiento = ?,
            club_id = ?,
            categoria_id = ?,
            foto_url = ?
        WHERE id = ?
    ");

    $sql->execute([
        $nombre,
        $apellido,
        $ci,
        $fecha_nacimiento,
        $carnet_vencimiento,
        $club_id,
        $categoria_id,
        $foto_ruta_db,
        $id
    ]);

    header("Location: ../paginas/jugadores.php");
    exit();
}
?>