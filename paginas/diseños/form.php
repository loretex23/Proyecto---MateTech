<form action="/web/validar_login.php" class="login-caja" method="POST">

    <div class="mb-3">
        <label>Email</label>

        <input
            type="email"
            class="form-control"
            name="email"
            required>
    </div>

    <div class="mb-3">
        <label>Contraseña</label>

        <input
            type="password"
            class="form-control"
            name="password"
            required>
    </div>

    <button type="submit" class="btn-submit">
        Iniciar sesión
    </button>

</form>

   <?php if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $password = $_POST['password'];
    echo "<div class='mt-3'>Successful!";
  } elseif (isset($_GET['error'])) {
    echo "<div class='alert alert-danger mt-3' role='alert'>";
    echo htmlspecialchars($_GET['error']);
    echo "</div>";//
  } ?>
