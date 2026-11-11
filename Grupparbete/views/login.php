<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="auth-container">
  <div class="card auth-form">
    <h1>Logga in</h1>

    <form action="/Grupparbete/login_process.php" method="post">

      <div class="form-group">
        <label for="email">E-post:</label>
        <input type="email" id="email" name="email" required>
      </div>

      <div class="form-group">
        <label for="password">Lösenord:</label>
        <input type="password" name="password" id="password" required>
      </div>

      <button type="submit" class="button">Logga in</button>
    </form>

    <p style="text-align:center;margin-top:18px;">
      Inget konto?
      <a href="/Grupparbete/views/register.php">Registrera dig här</a>.
    </p>

  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
