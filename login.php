<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WANTED</title>
    <!-- Bootstrap CSS CDN -->
    <link href="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body {
            background-color: #343a40;
            color: #ffffff;
        }
        .login-container {
            margin-top: 100px;
        }
        .login-card {
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.5);
            background-color: #495057;
        }
        .login-image {
            width: 300px;
            height: auto;
            display: block;
            margin: 0 auto 20px auto;
        }
        .btn-primary {
            background-color: #dc3545;
            border: none;
        }
        .btn-primary:hover {
            background-color: #c82333;
        }
        .footer-link {
            color: #ffc107;
        }
        .footer-link:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>

<div class="container login-container">
    <div class="row justify-content-center">
        <div class="col-md-6">
            <div class="card login-card">
                <img src="wanted_new.png" alt="WANTED" class="login-image">
                <div class="card-header text-center">
                    <h4>Login WANTED</h4>
                    <p>Temukan yang hilang!</p>
                </div>
                <div class="card-body">
                    <form action="login_process.php" method="POST">
                        <div class="form-group">
                            <label for="username">Username</label>
                            <input type="text" class="form-control" id="username" name="username" required>
                        </div>
                        <div class="form-group">
                            <label for="password">Password</label>
                            <input type="password" class="form-control" id="password" name="password" required>
                        </div>
                        <button type="submit" class="btn btn-primary btn-block">Login</button>
                    </form>
                </div>
                <div class="card-footer text-center">
                    <small>Lupa password? <a href="forgot_password.php" class="footer-link">Klik di sini</a></small>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Bootstrap JS and dependencies -->
<script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.9.2/dist/umd/popper.min.js"></script>
<script src="https://stackpath.bootstrapcdn.com/bootstrap/4.5.2/js/bootstrap.min.js"></script>
</body>
</html>