<?php include 'proteger.php'; ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="estilos.css?v=<?php echo filemtime('estilos.css'); ?>" rel="stylesheet" type="text/css">
    <title>MateTech - Jugadores</title>
</head>
<body>

    <?php include __DIR__ . '/navbarclub.php'; ?>

    <div class="container mt-4">
        <h1>Jugadores</h1>
        <p>Este es el contenido principal de la página de jugadores.</p>
    </div>
    <div>
 <?php include __DIR__ . '/../diseño/footer.php'; ?>
 </div>
</body>
</html>
