<?php 

if (!empty($_POST["btnregistrar"]))
    if (!empty($_POST["nombre"]) and !empty($_POST["apellido"]) and !empty($_POST["ci"]) and !empty($_POST["fecha_nacimiento"]) and !empty($_POST["carnet_vencimiento"]) and !empty($_POST["foto_url"])) {
    $nombre = $_POST["nombre"];
    $apellido = $_POST["apellido"];
    $ci = $_POST["ci"];
    $fecha_nacimiento = $_POST["fecha_nacimiento"];
    $carnet_vencimiento = $_POST["carnet_vencimiento"];
    $foto_url = $_POST["foto_url"];

    $foto_url = "";
        if (isset($_FILES["foto_url"]) && $_FILES["foto_url"]["error"] == 0) {
            $foto_url = "img/" . $_FILES["foto_url"]["name"];
            move_uploaded_file($_FILES["foto_url"]["tmp_name"], "../" . $foto_url);
        }

    $stmt = $pdo->prepare("INSERT INTO jugadores (nombre, apellido, ci, fecha_nacimiento, carnet_vencimiento, foto_url) VALUES (?, ?, ?, ?, ?, ?)");
    $resultado = $stmt->execute([$nombre, $apellido, $ci, $fecha_nacimiento, $carnet_vencimiento, $foto_url]);
        
    if ($resultado) {
        echo "<div class='alert alert-success' role='alert'>Jugador registrado correctamente</div>";
    } else {
        echo "<div class='alert alert-danger' role='alert'>Error al registrar jugador</div>";
    }
    
    } else {
        echo "Faltan datos por completar";
}

?>