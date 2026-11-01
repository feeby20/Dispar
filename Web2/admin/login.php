
<?php
session_start();
require_once __DIR__ . "/../config/database.php";

$error = "";

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $email = trim($_POST["email"] ?? "");
    $password = $_POST["password"] ?? "";

    $stmt = $pdo->prepare(
        "SELECT id, email, password_hash
         FROM admins
         WHERE email = ?"
    );
    $stmt->execute([$email]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if (
        $admin &&
        password_verify($password, $admin["password_hash"])
    ) {
        session_regenerate_id(true);
        $_SESSION["admin_id"] = $admin["id"];
        $_SESSION["admin_email"] = $admin["email"];

        header("Location: dashboard.php");
        exit;
    } else {
        $error = "Email atau password salah.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Login Admin Visit Ciamis</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            background: #f2f5f2;
            display: flex;
            justify-content: center;
            align-items: center;
            min-height: 100vh;
            margin: 0;
        }

        .login-box {
            background: white;
            padding: 30px;
            border-radius: 12px;
            width: 320px;
            box-shadow: 0 4px 18px #00000015;
        }

        h2 {
            text-align: center;
            color: #245c42;
        }

        label {
            display: block;
            margin-top: 15px;
        }

        input {
            box-sizing: border-box;
            width: 100%;
            padding: 11px;
            margin-top: 6px;
            border: 1px solid #ccc;
            border-radius: 6px;
        }

        button {
            width: 100%;
            padding: 12px;
            margin-top: 22px;
            background: #245c42;
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
        }

        .error {
            color: #c62828;
        }
    </style>
</head>
<body>
    <div class="login-box">
        <h2>Admin Visit Ciamis</h2>

        <?php if ($error !== ""): ?>
            <p class="error">
                <?= htmlspecialchars($error) ?>
            </p>
        <?php endif; ?>

        <form method="POST">
            <label for="email">Email</label>
            <input
                type="email"
                id="email"
                name="email"
                required
            >

            <label for="password">Password</label>
            <input
                type="password"
                id="password"
                name="password"
                required
            >

            <button type="submit">Login</button>
        </form>
    </div>
</body>
</html>

//email=admin@visitciamis.com
//pw=- AdminCiamis2026!