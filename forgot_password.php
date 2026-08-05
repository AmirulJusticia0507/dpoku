<!-- forgot_password.php -->
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lupa Password</title>
    <script src="https://cdn.tailwindcss.com"></script>
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 rounded-2xl shadow-2xl p-8">
            <h3 class="text-center text-white text-xl font-bold mb-6">Lupa Password</h3>
            <form action="forgot_password_process.php" method="POST">
                <div class="mb-6">
                    <label class="block text-sm font-medium text-gray-300 mb-1">Masukkan Username Anda</label>
                    <input type="text" name="username" required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:ring-2 focus:ring-yellow-400 outline-none">
                </div>
                <button type="submit"
                    class="w-full bg-yellow-500 hover:bg-yellow-600 text-white font-semibold py-2 rounded-lg transition">
                    Reset Password
                </button>
            </form>
            <p class="text-center mt-5">
                <a href="login.php" class="text-gray-400 hover:text-white text-sm">Kembali ke Login</a>
            </p>
        </div>
    </div>
</body>
</html>
