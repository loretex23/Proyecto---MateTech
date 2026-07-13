<form class="login-caja" method="post" action="index.php">
  <div class="mb-3 mt-3">
    <label for="email" class="form-label">Email:</label>
    <input type="email" class="form-control" id="email" placeholder="Enter email" name="email">
  </div>
  <div class="mb-3">
    <label for="pwd" class="form-label">Password:</label>
    <input type="password" class="form-control" id="pwd" placeholder="Enter password" name="pswd">
  </div>
    <div class="form-check mb-3">
    <label class="form-check-label">
      <input class="form-check-input" type="checkbox" name="remember"> Remember me
    </label>
  </div>
  <button type="s  b bubmit" class="btn-submit">Submit</button>
   <?php if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = $_POST['uname'];
    $password = $_POST['pswd'];
    echo "<div class='mt-3'>Successful!";
  } elseif (isset($_GET['error'])) {
    echo "<div class='alert alert-danger mt-3' role='alert'>";
    echo htmlspecialchars($_GET['error']);
    echo "</div>";//
  } ?>
</form>