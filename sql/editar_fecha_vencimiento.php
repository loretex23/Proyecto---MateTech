<?php

require_once "basededatos.php";

if (!empty($_POST["btneditarfecha"])) {

    if (
        !empty($_POST["id"]) &&
        !empty($_POST["carnet_vencimiento"])
    ) {

        $id = $_POST["id"];
        $carnet_vencimiento = $_POST["carnet_vencimiento"];

        $stmt = $pdo->prepare("
            UPDATE jugadores
            SET carnet_vencimiento = ?
            WHERE id = ?
        ");

        $resultado = $stmt->execute([
            $carnet_vencimiento,
            $id
        ]);

        if ($resultado) {
            header("Location: ../paginas/jugadores.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>
                    Error al actualizar la fecha de vencimiento.
                  </div>";
        }

    } else {

        echo "<div class='alert alert-warning'>
                Falta seleccionar una fecha.
              </div>";
    }
}
?><?php

require_once "basededatos.php";

if (!empty($_POST["btneditarfecha"])) {

    if (
        !empty($_POST["id"]) &&
        !empty($_POST["carnet_vencimiento"])
    ) {

        $id = $_POST["id"];
        $carnet_vencimiento = $_POST["carnet_vencimiento"];

        $stmt = $pdo->prepare("
            UPDATE jugadores
            SET carnet_vencimiento = ?
            WHERE id = ?
        ");

        $resultado = $stmt->execute([
            $carnet_vencimiento,
            $id
        ]);

        if ($resultado) {
            header("Location: ../paginas/jugadores.php");
            exit();
        } else {
            echo "<div class='alert alert-danger'>
                    Error al actualizar la fecha de vencimiento.
                  </div>";
        }

    } else {

        echo "<div class='alert alert-warning'>
                Falta seleccionar una fecha.
              </div>";
    }
}
?>