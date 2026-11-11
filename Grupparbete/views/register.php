
<?php require_once __DIR__ . '/partials/header.php'; ?>

<div class="auth-container">
  <div class="card auth-form">
    <h1>Registrera konto</h1>

    <form action="/Grupparbete/register_process.php" method="post">

      <div class="form-group">
        <label for="user_name">Användarnamn:</label>
        <input type="text" name="user_name" id="user_name" required>
      </div>

      <div class="form-group">
        <label for="email">E-post:</label>
        <input type="email" name="email" id="email" required>
      </div>

      <div class="form-group">
        <label for="password">Lösenord:</label>
        <input type="password" name="password" id="password" required minlength="6">
      </div>

      <button type="submit" class="button">Skapa konto</button>
    </form>

    <p style="text-align:center;margin-top:18px;">
      Har du redan ett konto? 
      <a href="/Grupparbete/views/login.php">Logga in här</a>.
    </p>

  </div>
</div>

<?php require_once __DIR__ . '/partials/footer.php'; ?>
