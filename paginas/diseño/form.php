<form action="login.php" method="post" class="login-caja">
  <div class="loginheader">
    <p>Por favor, ingresa tus credenciales para iniciar sesion.</p>
  </div>

  <?php if (!empty($error)) : ?>
    <div class="alert alert-danger" role="alert">
      <?php echo htmlspecialchars($error, ENT_QUOTES, 'UTF-8'); ?>
    </div>
  <?php endif; ?>

  <div class="mb-3 mt-3">
    <label for="usuario" class="form-label">Nombre o email:</label>
    <input type="text" class="form-control" id="usuario" placeholder="Nombre o usuario@gmail.com" name="usuario" value="<?php echo htmlspecialchars($usuario_o_email ?? '', ENT_QUOTES, 'UTF-8'); ?>">
  </div>
  <div class="mb-3">
    <label for="pwd" class="form-label">Contraseña:</label>
    <input type="password" class="form-control" id="pwd" placeholder="********" name="pswd">
  </div>
  <div class="form-check mb-3">
    <label class="form-check-label">
      <input class="form-check-input" type="checkbox" name="remember"> Recordarme en este dispositivo
    </label>
  </div>


  



  <button type="submit" class="btn">Entrar</button>
</form>
