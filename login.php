<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - WANTED</title>
    <script src="https://cdn.tailwindcss.com"></script>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.2/css/all.min.css">
</head>
<body class="bg-gray-900 min-h-screen flex items-center justify-center px-4">
    <div class="w-full max-w-md">
        <div class="bg-gray-800 rounded-2xl shadow-2xl p-8">
            <img src="wanted_new.png" alt="WANTED" class="w-64 h-auto mx-auto mb-6">
            <h4 class="text-center text-white text-xl font-bold">Login WANTED</h4>
            <p class="text-center text-gray-400 text-sm mb-6">Temukan yang hilang!</p>

            <form action="login_process.php" method="POST">
                <div class="mb-4">
                    <label for="username" class="block text-sm font-medium text-gray-300 mb-1">Username</label>
                    <input type="text" id="username" name="username" required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <div class="mb-6">
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                    <input type="password" id="password" name="password" required
                        class="w-full px-3 py-2 rounded-lg bg-gray-700 border border-gray-600 text-white placeholder-gray-400 focus:ring-2 focus:ring-red-500 outline-none">
                </div>
                <button type="submit"
                    class="w-full bg-red-600 hover:bg-red-700 text-white font-semibold py-2 rounded-lg transition">
                    Login
                </button>
            </form>

            <p class="text-center text-gray-400 text-sm mt-6">
                Lupa password? <a href="forgot_password.php" class="text-yellow-400 hover:underline">Klik di sini</a>
            </p>
        </div>
    </div>
</body>
</html>
