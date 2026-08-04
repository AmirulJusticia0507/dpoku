<!-- forgot_password.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <title>Lupa Password</title>
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h3 class="text-center">Lupa Password</h3>
    <form action="forgot_password_process.php" method="POST">
        <div class="form-group">
            <label>Masukkan Username Anda</label>
            <input type="text" name="username" class="form-control" required>
        </div>
        <button type="submit" class="btn btn-primary btn-block">Reset Password</button>
    </form>
</div>
</body>
</html>
