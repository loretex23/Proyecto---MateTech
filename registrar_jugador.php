<?php

if (!empty ($_POST['btnregistrar'])) {
    if (!empty ($_POST['nombre']) && !empty($_POST['apellido']) && !empty($_POST['ci']) && !empty($_POST['fecha_nacimiento']) && !empty($_POST['carnet_vencimiento'])) {
    include '../sql/basededatos.php';

    $nombre = $_POST['nombre'];
    $apellido = $_POST['apellido'];
    $ci = $_POST['ci'];
    $fecha_nacimiento = $_POST['fecha_nacimiento'];
    $carnet_vencimiento = $_POST['carnet_vencimiento'];

    $sql = "INSERT INTO jugadores (nombre, apellido, ci, fecha_nacimiento, carnet_vencimiento) VALUES (:nombre, :apellido, :ci, :fecha_nacimiento, :carnet_vencimiento)";
    $stmt = $pdo->prepare($sql);
    $stmt->bindParam(':nombre', $nombre);
    $stmt->bindParam(':apellido', $apellido);
    $stmt->bindParam(':ci', $ci);
    $stmt->bindParam(':fecha_nacimiento', $fecha_nacimiento);
    $stmt->bindParam(':carnet_vencimiento', $carnet_vencimiento);

    if ($stmt->execute()) {
        echo "Jugador registrado exitosamente.";
        header("Location: jugadores.php");
        exit();
    } else {
        echo "Error al registrar el jugador.";
    }
} else {
    echo "Por favor complete todos los campos.";
}

}

?>