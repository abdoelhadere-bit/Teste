<?php
require 'config.php';
session_start();
$errors = $_SESSION['errors'] ?? [];
$old = $_SESSION['old'] ?? [];

unset($_SESSION['errors'], $_SESSION['old']);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <script src="https://cdn.tailwindcss.com"></script>
     <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/parsley.js/2.9.2/parsley.min.js"></script>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <style>

        input.parsley-error {
            background-color: #a34f4fff !important;
            border-color: #ae3d3dff !important;
        }
        
        input.parsley-success {
            background-color: #4c9269ff !important;
            border-color: #30b588ff !important;
        }
        
        .parsley-errors-list {
            background-color: #ce6c6cff;
            color: white;
            font-size: .75rem;
            margin-top: .30rem;
            padding: 0.5rem;
            border-radius: 0.25rem;
            list-style: none;
        }
        
        .parsley-errors-list li {
            font-weight: 500;
        }
    </style>
</head>
<body>
    <main>

        <div class="min-h-screen flex items-center justify-center bg-gray-100 dark:bg-gray-900">
            <div class="bg-white dark:bg-gray-800 p-8 rounded-lg shadow-md w-full max-w-md">
              <h2 class="text-2xl font-bold mb-6 text-center text-gray-900 dark:text-white">Register</h2>
              
              <form action="check_register.php" method="POST" data-parsley-validate>

                <div class="mb-4">
                  <label for="name" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Name</label>
                  <input type="text" name="name" id="name" value="<?= $old['name'] ?? "" ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600" placeholder="Your Name" required>
                </div>

                <div class="mb-4">
                  <label for="email" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Email</label>
                  <input type="email" name="email" id="email" value="<?= $old['email'] ?? "" ?>" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600" placeholder="Your Email" required
                  data-parsley-required-message="L'email est requis"
                  data-parsley-pattern="^[a-zA-Z0-9._]+@[a-zA-Z]+\.(com|ma)$"
                  data-parsley-trigg
                  er="change">
                </div>

                <div class="mb-6">
                  <label for="password" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Password</label>
                  <input type="password" name="password" id="password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600" placeholder="Password" required
                    data-parsley-required-message="Le mot de passe est requis"
                    data-parsley-minlength="8"
                    data-parsley-pattern="^(?=.*[A-Za-z])(?=.*\d)[A-Za-z\d@$!%*?&]{8,}$"
                    data-parsley-pattern-message="Le mot de passe doit contenir au moins 8 caractères, des lettres et des chiffres">
                </div>

                <div class="mb-6">
                  <label for="confirm_password" class="block text-gray-700 dark:text-gray-300 text-sm font-bold mb-2">Password</label>
                  <input type="password" name="confirm_password" id="confirm_password" class="shadow appearance-none border rounded w-full py-2 px-3 text-gray-700 dark:text-gray-200 leading-tight focus:outline-none focus:shadow-outline bg-gray-50 dark:bg-gray-700 border-gray-300 dark:border-gray-600" placeholder="Confirm your Password" required
                         data-parsley-equalto='#password'  
                         data-parsley-equalto-message="Les mots de passe ne correspondent pas">
                </div>

                <div class="flex items-center justify-between">
                  <button type="submit" class="bg-blue-500 hover:bg-blue-700 text-white font-bold py-2 px-4 rounded focus:outline-none focus:shadow-outline">
                    Register
                  </button>
                </div>
              </form>

              <p class="mt-3 text-center text-gray-600 dark:text-gray-400">
                  Already have an account ? 
                  <a href="login.php" class="text-blue-500 hover:text-blue-700 font-semibold hover:underline">
                      Sign in
                  </a>
              </p>
              
              <?php if(!empty($errors)) :?>
                <div class="mb-4 p-3 bg-red-100 border border-red-400 text-red-700 rounded">
                    <ul class="list-disc pl-5">

                        <?php foreach($errors as $error) : ?>
                            <li><?= htmlspecialchars($error) ?></li>
                        <?php endforeach; ?>
                        
                    </ul>
                </div>
                <?php endif ;?>

            </div>
        </div>
    </main>
</body>
</html>