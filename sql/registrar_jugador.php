<?php
include 'basededatos.php';

if (!empty($_POST["btnregistrar"])) {

    if (
        !empty($_POST["nombre"]) &&
        !empty($_POST["apellido"]) &&
        !empty($_POST["ci"]) &&
        !empty($_POST["fecha_nacimiento"]) &&
        !empty($_POST["carnet_vencimiento"]) &&
        !empty($_POST["club_id"]) &&
        !empty($_POST["categoria_id"])
    ) {

        $nombre = $_POST["nombre"];
        $apellido = $_POST["apellido"];
        $ci = trim($_POST["ci"]);
        $fecha_nacimiento = $_POST["fecha_nacimiento"];
        $carnet_vencimiento = $_POST["carnet_vencimiento"];
        $club_id = $_POST["club_id"];
        $categoria_id = $_POST["categoria_id"];

        $verificar = $pdo->prepare("SELECT COUNT(*) FROM jugadores WHERE ci = ?");
        $verificar->execute([$ci]);

        if ($verificar->fetchColumn() > 0) {
            echo "
            <script>
                alert('No se puede registrar el jugador. La cédula $ci ya está registrada.');
                window.history.back();
            </script>
            ";
            exit();
        }

        $foto_ruta_db = null;
        if (
            isset($_FILES['foto_url']) &&
            $_FILES['foto_url']['error'] === UPLOAD_ERR_OK
        ) {

            $nombre_original = $_FILES['foto_url']['name'];
            $ext = strtolower(pathinfo($nombre_original, PATHINFO_EXTENSION));

            $extensiones_permitidas = array('jpg', 'jpeg', 'png');

            if (in_array($ext, $extensiones_permitidas)) {

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
            INSERT INTO jugadores
            (
                nombre,
                apellido,
                ci,
                fecha_nacimiento,
                carnet_vencimiento,
                club_id,
                categoria_id,
                foto_url
            )
            VALUES (?, ?, ?, ?, ?, ?, ?, ?)
        ");

        $resultado = $sql->execute([
            $nombre,
            $apellido,
            $ci,
            $fecha_nacimiento,
            $carnet_vencimiento,
            $club_id,
            $categoria_id,
            $foto_ruta_db
        ]);

        if ($resultado) {
            header("Location: ../paginas/jugadores.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>Error al registrar el jugador.</div>";
        }

    } else {

        echo "<div class='alert alert-warning'>Por favor, complete todos los campos requeridos.</div>";
    }
}
?>