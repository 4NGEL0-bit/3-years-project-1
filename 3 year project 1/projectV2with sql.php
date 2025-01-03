<?php

session_start();


// Database configuration

$host = 'localhost';

$username = 'root';

$password = '';



// First connect without database to create it if needed

try {

    $pdo = new PDO("mysql:host=$host", $username, $password);

    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);


    // Create database if it doesn't exist

    $pdo->exec("CREATE DATABASE IF NOT EXISTS login_db");

    $pdo->exec("USE login_db");


    // Create users table if it doesn't exist

    $pdo->exec("CREATE TABLE IF NOT EXISTS users (

        id INT AUTO_INCREMENT PRIMARY KEY,

        username VARCHAR(50) UNIQUE NOT NULL,

        password VARCHAR(255) NOT NULL,

        email VARCHAR(100),

        full_name VARCHAR(100),

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    )");


    // Create user_data table if it doesn't exist

    $pdo->exec("CREATE TABLE IF NOT EXISTS user_data (

        id INT AUTO_INCREMENT PRIMARY KEY,

        name VARCHAR(100) NOT NULL,

        email VARCHAR(100) NOT NULL,

        student_id VARCHAR(20) NOT NULL,

        school VARCHAR(100) NOT NULL,

        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP

    )");


    // Insert default admin user if table is empty

    $stmt = $pdo->query("SELECT COUNT(*) FROM users");

    if ($stmt->fetchColumn() == 0) {

        $defaultPassword = password_hash("admin123", PASSWORD_DEFAULT);

        $stmt = $pdo->prepare("INSERT INTO users (username, password, email, full_name) VALUES (?, ?, ?, ?)");

        $stmt->execute(['admin', $defaultPassword, 'admin@example.com', 'Administrator']);

    }


    // Handle AJAX requests

    if ($_SERVER['REQUEST_METHOD'] === 'POST') {

        header('Content-Type: application/json');


        // Handle login

        if (isset($_POST['login'])) {

            $username = trim($_POST['username']);

            $password = $_POST['password'];


            $stmt = $pdo->prepare("SELECT * FROM users WHERE username = ?");

            $stmt->execute([$username]);

            $user = $stmt->fetch();


            if ($user && password_verify($password, $user['password'])) {

                $_SESSION['user_id'] = $user['id'];

                $_SESSION['username'] = $user['username'];

                echo json_encode(['success' => true]);

            } else {

                echo json_encode(['error' => 'Invalid username or password']);

            }

            exit;

        }


        // Handle data management actions (requires login)

        if (isset($_POST['action']) && isset($_SESSION['user_id'])) {

            switch ($_POST['action']) {

                case 'add':

                    $name = $_POST['name'];

                    $email = $_POST['email'];

                    $student_id = $_POST['student_id'];

                    $school = $_POST['school'];


                    // $stmt = $pdo->prepare("INSERT INTO user_data (name, email, student_id, school) VALUES (?, ?, ?, ?)");

                    // $stmt->execute([$name, $email, $student_id, $school]);

                    // echo json_encode(['success' => true, 'message' => 'Data added successfully']);

                    $stmt = $pdo->prepare("CALL AddUserData1(?, ?, ?, ?)");

                    $stmt->execute([$name, $email, $student_id, $school]);

                    echo json_encode(['success' => true, 'message' => 'Data added successfully']);


                    break;


                case 'modify':

                    $id = $_POST['id'];

                    $name = $_POST['name'];

                    $email = $_POST['email'];

                    $student_id = $_POST['student_id'];

                    $school = $_POST['school'];


                    // $stmt = $pdo->prepare("UPDATE user_data SET name=?, email=?, student_id=?, school=? WHERE id=?");

                    $stmt = $pdo->prepare("CALL ModifyUserData1(?, ?, ?, ?, ?)");

                    $stmt->execute([$id, $name, $email, $student_id, $school]);

                    echo json_encode(['success' => true, 'message' => 'Data modified successfully']);

                    break;


                case 'delete':

                    $id = $_POST['id'];

                    // $stmt = $pdo->prepare("DELETE FROM user_data WHERE id = ?");

                    // $stmt->execute([$id]);

                    // echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);

                    $stmt = $pdo->prepare("CALL DeleteUserData1(?)");

                    $stmt->execute([$id]);

                    echo json_encode(['success' => true, 'message' => 'Data deleted successfully']);


                    break;


                case 'get':

                    // $stmt = $pdo->prepare("SELECT * FROM user_data ORDER BY id DESC");

                    // $stmt->execute();

                    // echo json_encode($stmt->fetchAll(PDO::FETCH_ASSOC));

                    $stmt = $pdo->prepare("CALL GetAllUserData1()");

                    $stmt->execute();

                    $data = $stmt->fetchAll(PDO::FETCH_ASSOC);

                    echo json_encode($data);


                    break;


                default:

                    echo json_encode(['error' => 'Invalid action']);

            }

            exit;

        }

    }

} catch(PDOException $e) {

    if (isset($_POST['action']) || isset($_POST['login'])) {

        header('Content-Type: application/json');

        echo json_encode(['error' => 'Database error: ' . $e->getMessage()]);

        exit;

    }

    die("Database error: " . $e->getMessage());

}


// Check if user is logged in

$isLoggedIn = isset($_SESSION['user_id']);

?>

<!DOCTYPE html>

<html lang="en">

<head>

    <meta charset="UTF-8">

    <meta name="viewport" content="width=device-width, initial-scale=1.0">

    <title>Data Management System</title>

    <style>

        * {

            margin: 0;

            padding: 0;

            box-sizing: border-box;

            font-family: 'Segoe UI', sans-serif;

        }


        body {

            display: flex;

            justify-content: center;

            align-items: center;

            min-height: 100vh;

            background: #000;

            color: #fff;

            overflow-x: hidden;

            position: relative;

        }


        /* Matrix Rain Effect */

        .matrix-bg {

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: #000;

            z-index: 0;

        }


        .matrix-bg::before {

            content: '';

            position: absolute;

            top: -50%;

            left: -50%;

            width: 200%;

            height: 200%;

            background: radial-gradient(circle, transparent 40%, #000 70%),

            repeating-linear-gradient(transparent 0px, transparent 2px, #0f0 3px);

            background-size: 100% 3px;

            animation: matrix 20s linear infinite;

            opacity: 0.3;

        }


        @keyframes matrix {

            0% { transform: translateY(0); }

            100% { transform: translateY(50%); }

        }


        /* Matrix Rain Animation */

        .container {

            position: absolute;

            inset: 0;

        }


        .container::before {

            content: "";

            position: absolute;

            inset: -145%;

            rotate: -45deg;

            background: #000;

            background-image: radial-gradient(4px 100px at 0px 235px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 235px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 117.5px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 252px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 252px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 126px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 150px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 150px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 75px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 253px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 253px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 126.5px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 204px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 204px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 102px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 134px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 134px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 67px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 179px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 179px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 89.5px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 299px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 299px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 149.5px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 215px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 215px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 107.5px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 281px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 281px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 140.5px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 158px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 158px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 79px, #0f0 100%, #0000 150%),

                radial-gradient(4px 100px at 0px 210px, #0f0, #0000),

                radial-gradient(4px 100px at 300px 210px, #0f0, #0000),

                radial-gradient(1.5px 1.5px at 150px 105px, #0f0 100%, #0000 150%);

            background-size: 300px 235px, 300px 235px, 300px 235px, 300px 252px, 300px 252px, 300px 252px, 300px 150px, 300px 150px, 300px 150px, 300px 253px, 300px 253px, 300px 253px, 300px 204px, 300px 204px, 300px 204px, 300px 134px, 300px 134px, 300px 134px,
300px 179px, 300px 179px, 300px 179px, 300px 299px, 300px 299px, 300px 299px, 300px 215px, 300px 215px, 300px 215px, 300px 281px, 300px 281px, 300px 281px, 300px 158px, 300px 158px, 300px 158px, 300px 210px, 300px 210px, 300px 210px;

            animation: hi 150s linear infinite;

        }


        @keyframes hi {

            0% {

                background-position: 0px 220px, 3px 220px, 151.5px 337.5px, 25px 24px, 28px 24px, 176.5px 150px, 50px 16px, 53px 16px, 201.5px 91px, 75px 224px, 78px 224px, 226.5px 350.5px, 100px 19px, 103px 19px, 251.5px 121px, 125px 120px, 128px 120px, 276.5px
187px, 150px 31px, 153px 31px, 301.5px 120.5px, 175px 235px, 178px 235px, 326.5px 384.5px, 200px 121px, 203px 121px, 351.5px 228.5px, 225px 224px, 228px 224px, 376.5px 364.5px, 250px 26px, 253px 26px, 401.5px 105px, 275px 75px, 278px 75px, 426.5px 180px;

            }

            to {

                background-position: 0px 6800px, 3px 6800px, 151.5px 6917.5px, 25px 13632px, 28px 13632px, 176.5px 13758px, 50px 5416px, 53px 5416px, 201.5px 5491px, 75px 17175px, 78px 17175px, 226.5px 17301.5px, 100px 5119px, 103px 5119px, 251.5px 5221px,
125px 8428px, 128px 8428px, 276.5px 8495px, 150px 9876px, 153px 9876px, 301.5px 9965.5px, 175px 13391px, 178px 13391px, 326.5px 13540.5px, 200px 14741px, 203px 14741px, 351.5px 14848.5px, 225px 18770px, 228px 18770px, 376.5px 18910.5px, 250px 5082px, 253px
5082px, 401.5px 5161px, 275px 6375px, 278px 6375px, 426.5px 6480px;

            }

        }


        /* Login Form */

        .login-form {

            position: relative;

            width: 400px;

            padding: 40px;

            background: rgba(0, 0, 0, 0.8);

            border-radius: 10px;

            border: 1px solid rgba(0, 255, 0, 0.1);

            backdrop-filter: blur(15px);

            z-index: 1;

            animation: formAppear 1s ease-out;

        }


        @keyframes formAppear {

            0% {

                opacity: 0;

                transform: translateY(-20px);

            }

            100% {

                opacity: 1;

                transform: translateY(0);

            }

        }


        .login-form h2 {

            color: #0f0;

            text-align: center;

            margin-bottom: 30px;

            font-size: 2em;

            text-transform: uppercase;

            letter-spacing: 3px;

            animation: glitch 3s infinite;

        }


        @keyframes glitch {

            0% { text-shadow: none; }

            20% { text-shadow: 2px 0 #ff0, -2px 0 #0ff; }

            21% { text-shadow: none; }

            50% { text-shadow: -2px 0 #ff0, 2px 0 #0ff; }

            51% { text-shadow: none; }

            100% { text-shadow: none; }

        }


        .input-box {

            position: relative;

            width: 100%;

            margin-bottom: 25px;

        }


        .input-box input {

            width: 100%;

            padding: 15px 20px;

            background: rgba(0, 255, 0, 0.1);

            border: none;

            outline: none;

            border-radius: 5px;

            color: #fff;

            font-size: 1em;

            transition: all 0.3s ease;

        }


        .input-box input:focus {

            background: rgba(0, 255, 0, 0.2);

            box-shadow: 0 0 15px rgba(0, 255, 0, 0.3);

        }


        .login-btn {

            width: 100%;

            padding: 15px;

            background: #0f0;

            border: none;

            outline: none;

            border-radius: 5px;

            color: #000;

            font-size: 1.2em;

            font-weight: bold;

            cursor: pointer;

            transition: all 0.3s ease;

            text-transform: uppercase;

            letter-spacing: 2px;

        }


        .login-btn:hover {

            background: #00ff00;

            box-shadow: 0 0 20px rgba(0, 255, 0, 0.5);

            transform: translateY(-2px);

        }


        /* Dashboard */

        .dashboard {

            display: none;

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: #000;

            z-index: 2;

            overflow-y: auto;

            padding: 20px;

            animation: dashboardAppear 1s ease-out;

        }


        @keyframes dashboardAppear {

            0% {

                opacity: 0;

                transform: scale(0.95);

            }

            100% {

                opacity: 1;

                transform: scale(1);

            }

        }


        .feature-container {

            display: flex;

            justify-content: center;

            align-items: center;

            gap: 40px;

            padding: 50px;

            flex-wrap: wrap;

            perspective: 1000px;

        }


        .feature-card {

            width: 300px;

            height: 200px;

            background: rgba(0, 255, 0, 0.1);

            border-radius: 15px;

            display: flex;

            align-items: center;

            justify-content: center;

            cursor: pointer;

            transition: all 0.5s cubic-bezier(0.175, 0.885, 0.32, 1.275);

            position: relative;

            overflow: hidden;

            transform-style: preserve-3d;

            box-shadow: 0 5px 15px rgba(0, 255, 0, 0.2);

        }


        .feature-card::before {

            content: '';

            position: absolute;

            top: -50%;

            left: -50%;

            width: 200%;

            height: 200%;

            background: linear-gradient(

                    45deg,

                    transparent,

                    rgba(0, 255, 0, 0.2),

                    transparent

            );

            transform: rotate(45deg);

            animation: shine 3s infinite;

        }


        .feature-card:hover {

            transform: translateY(-20px) rotateX(10deg) rotateY(10deg);

            background: rgba(0, 255, 0, 0.2);

            box-shadow:

                    0 15px 30px rgba(0, 255, 0, 0.3),

                    0 0 15px rgba(0, 255, 0, 0.5) inset;

        }


        .feature-card:hover::after {

            content: '';

            position: absolute;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: radial-gradient(circle at center, rgba(0, 255, 0, 0.2) 0%, transparent 70%);

            animation: pulseGlow 2s infinite;

        }


        @keyframes pulseGlow {

            0% { opacity: 0.5; transform: scale(1); }

            50% { opacity: 0.8; transform: scale(1.1); }

            100% { opacity: 0.5; transform: scale(1); }

        }


        .feature-card h3 {

            color: #0f0;

            font-size: 1.8em;

            text-transform: uppercase;

            letter-spacing: 3px;

            position: relative;

            z-index: 1;

            text-shadow: 0 0 10px rgba(0, 255, 0, 0.5);

            transition: all 0.3s ease;

        }


        .feature-card:hover h3 {

            transform: scale(1.1);

            text-shadow: 0 0 20px rgba(0, 255, 0, 0.8);

        }


        @keyframes shine {

            0% { transform: translateX(-100%) rotate(45deg); }

            100% { transform: translateX(100%) rotate(45deg); }

        }


        /* Action Forms */

        .action-form {

            display: none;

            position: fixed;

            top: 50%;

            left: 50%;

            transform: translate(-50%, -50%);

            width: 400px;

            background: rgba(0, 0, 0, 0.95);

            padding: 30px;

            border-radius: 10px;

            border: 1px solid #0f0;

            z-index: 3;

            animation: formSlideIn 0.3s ease-out;

        }


        @keyframes formSlideIn {

            0% {

                opacity: 0;

                transform: translate(-50%, -60%);

            }

            100% {

                opacity: 1;

                transform: translate(-50%, -50%);

            }

        }


        .action-form h3 {

            color: #0f0;

            text-align: center;

            margin-bottom: 20px;

            text-transform: uppercase;

            letter-spacing: 2px;

        }


        .action-form input {

            width: 100%;

            padding: 10px;

            margin: 10px 0;

            background: rgba(0, 255, 0, 0.1);

            border: 1px solid #0f0;

            border-radius: 5px;

            color: #fff;

            transition: all 0.3s ease;

        }


        .action-form input:focus {

            background: rgba(0, 255, 0, 0.2);

            box-shadow: 0 0 10px rgba(0, 255, 0, 0.3);

        }


        .action-form button {

            width: 100%;

            padding: 10px;

            margin-top: 20px;

            background: #0f0;

            border: none;

            border-radius: 5px;

            color: #000;

            font-weight: bold;

            cursor: pointer;

            text-transform: uppercase;

            letter-spacing: 2px;

            transition: all 0.3s ease;

        }


        .action-form button:hover {

            background: #00ff00;

            box-shadow: 0 0 15px rgba(0, 255, 0, 0.5);

            transform: translateY(-2px);

        }


        .close-form {

            position: absolute;

            top: 10px;

            right: 15px;

            color: #0f0;

            cursor: pointer;

            font-size: 20px;

            transition: all 0.3s ease;

        }


        .close-form:hover {

            transform: rotate(90deg);

            color: #00ff00;

        }


        /* School Selector Styles */

        .school-selector {

            max-height: 150px;

            overflow-y: auto;

            border: 1px solid #0f0;

            border-radius: 5px;

            margin: 10px 0;

            background: rgba(0, 255, 0, 0.1);

            scrollbar-width: thin;

            scrollbar-color: #0f0 transparent;

        }


        .school-selector::-webkit-scrollbar {

            width: 6px;

        }


        .school-selector::-webkit-scrollbar-track {

            background: transparent;

        }


        .school-selector::-webkit-scrollbar-thumb {

            background-color: #0f0;

            border-radius: 3px;

        }


        .school-option {

            padding: 10px;

            cursor: pointer;

            transition: all 0.3s ease;

            color: #fff;

        }


        .school-option:hover {

            background: rgba(0, 255, 0, 0.2);

            color: #0f0;

        }


        .school-option.selected {

            background: rgba(0, 255, 0, 0.3);

            color: #0f0;

        }


        /* Data Table */

        #dataTableContainer {

            margin: 20px;

            background: rgba(0, 0, 0, 0.8);

            padding: 20px;

            border-radius: 10px;

            border: 1px solid #0f0;

            animation: tableAppear 0.5s ease-out;

        }


        @keyframes tableAppear {

            0% {

                opacity: 0;

                transform: translateY(20px);

            }

            100% {

                opacity: 1;

                transform: translateY(0);

            }

        }


        table {

            width: 100%;

            border-collapse: collapse;

            color: #fff;

        }


        th, td {

            padding: 12px;

            text-align: left;

            border-bottom: 1px solid rgba(0, 255, 0, 0.2);

            transition: all 0.3s ease;

        }


        th {

            color: #0f0;

            text-transform: uppercase;

            letter-spacing: 1px;

            background: rgba(0, 255, 0, 0.1);

        }


        tr:hover td {

            background: rgba(0, 255, 0, 0.05);

            color: #0f0;

        }


        /* Data Cards */

        .data-cards-container {

            display: grid;

            grid-template-columns: repeat(auto-fill, minmax(300px, 1fr));

            gap: 20px;

            padding: 20px;

        }


        .data-card {

            background: rgba(0, 0, 0, 0.8);

            border: 1px solid #0f0;

            border-radius: 10px;

            padding: 20px;

            position: relative;

            transition: all 0.3s ease;

        }


        .data-card:hover {

            transform: translateY(-5px);

            box-shadow: 0 5px 15px rgba(0, 255, 0, 0.3);

        }


        .card-content h4 {

            color: #0f0;

            font-size: 1.5em;

            margin-bottom: 15px;

        }


        .card-content p {

            color: #fff;

            margin: 10px 0;

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .card-content i {

            color: #0f0;

            width: 20px;

        }


        .card-actions {

            margin-top: 15px;

            display: flex;

            gap: 10px;

        }


        .modify-btn, .delete-btn {

            padding: 8px 15px;

            border: none;

            border-radius: 5px;

            cursor: pointer;

            font-weight: bold;

            text-transform: uppercase;

            transition: all 0.3s ease;

        }


        .modify-btn {

            background: #0f0;

            color: #000;

        }


        .delete-btn {

            background: #ff3333;

            color: #fff;

        }


        .modify-btn:hover, .delete-btn:hover {

            transform: translateY(-2px);

            box-shadow: 0 3px 10px rgba(0, 255, 0, 0.3);

        }


        /* Loading Animation */

        .loading {

            display: none;

            position: fixed;

            top: 0;

            left: 0;

            width: 100%;

            height: 100%;

            background: rgba(0, 0, 0, 0.9);

            z-index: 1000;

            justify-content: center;

            align-items: center;

        }


        .terminal-loader {

            background: #000;

            padding: 20px;

            border-radius: 5px;

            border: 1px solid #0f0;

            animation: pulse 2s infinite;

        }


        @keyframes pulse {

            0% { box-shadow: 0 0 0 0 rgba(0, 255, 0, 0.4); }

            70% { box-shadow: 0 0 0 20px rgba(0, 255, 0, 0); }

            100% { box-shadow: 0 0 0 0 rgba(0, 255, 0, 0); }

        }


        .terminal-header {

            color: #0f0;

            margin-bottom: 10px;

            font-size: 1.2em;

            text-transform: uppercase;

            letter-spacing: 2px;

        }


        .terminal-text {

            color: #0f0;

            position: relative;

        }


        .terminal-text::after {

            content: '_';

            animation: blink 1s infinite;

        }


        @keyframes blink {

            0%, 100% { opacity: 0; }

            50% { opacity: 1; }

        }


        /* Success Message */

        .success-message {

            position: fixed;

            top: 20px;

            right: 20px;

            background: rgba(0, 255, 0, 0.2);

            border: 1px solid #0f0;

            border-radius: 10px;

            padding: 15px 25px;

            color: #0f0;

            z-index: 1000;

            transition: opacity 0.5s ease;

            animation: slideIn 0.5s ease;

        }


        .success-content {

            display: flex;

            align-items: center;

            gap: 10px;

        }


        .success-content i {

            font-size: 1.5em;

        }


        @keyframes slideIn {

            from {

                transform: translateX(100%);

                opacity: 0;

            }

            to {

                transform: translateX(0);

                opacity: 1;

            }

        }

    </style>

</head>

<body>

<div class="matrix-bg"></div>

<div class="container"></div>

<form class="login-form" id="loginForm">

    <h2>System Access</h2>

    <div class="input-box">

        <input type="text" name="username" placeholder="Username" required>

    </div>

    <div class="input-box">

        <input type="password" name="password" placeholder="Password" required>

    </div>

    <button type="submit" class="login-btn">Initialize</button>

</form>


<div class="loading" id="loading">

    <div class="terminal-loader">

        <div class="terminal-header">System Authentication</div>

        <div class="terminal-text">Initializing secure connection...</div>

    </div>

</div>


<div class="dashboard" id="dashboard">

    <div class="feature-container">

        <div class="feature-card" onclick="showForm('add')">

            <h3>Add Data</h3>

        </div>

        <div class="feature-card" onclick="showDataCards('modify')">

            <h3>Modify Data</h3>

        </div>

        <div class="feature-card" onclick="showDataCards('delete')">

            <h3>Delete Data</h3>

        </div>

        <div class="feature-card" onclick="toggleDataVisual()">

            <h3>Show Data</h3>

        </div>

    </div>


    <div class="action-form" id="addForm">

        <span class="close-form" onclick="closeForm('addForm')">&times;</span>

        <h3>Add New Data</h3>

        <form onsubmit="handleSubmit(event, 'add')">

            <input type="text" name="name" placeholder="Name" required>

            <input type="email" name="email" placeholder="Email" required>

            <input type="text" name="student_id" placeholder="Student ID" required>

            <input type="hidden" name="school" id="selectedSchool" required>

            <div class="school-selector" id="schoolSelector">

                <div class="school-option" data-value="School of Engineering">School of Engineering</div>

                <div class="school-option" data-value="School of Business">School of Business</div>

                <div class="school-option" data-value="School of Arts">School of Arts</div>

                <div class="school-option" data-value="School of Science">School of Science</div>

                <div class="school-option" data-value="School of Medicine">School of Medicine</div>

                <div class="school-option" data-value="School of Law">School of Law</div>

                <div class="school-option" data-value="School of Education">School of Education</div>

            </div>

            <button type="submit">Add Data</button>

        </form>

    </div>


    <div id="dataVisualContainer" style="display: none;">

        <div class="data-cards-container"></div>

    </div>


    <div id="dataTableContainer" style="display: none;">

        <table>

            <thead>

            <tr>

                <th>Number</th>

                <th>Name</th>

                <th>Email</th>

                <th>Student ID</th>

                <th>School</th>

                <th>Actions</th>

            </tr>

            </thead>

            <tbody id="dataTableBody"></tbody>

        </table>

    </div>

</div>


<script>

    let currentMode = null;

    let currentData = [];


    // Login form handling

    document.getElementById('loginForm').addEventListener('submit', function(e) {

        e.preventDefault();

        const formData = new FormData(this);

        formData.append('login', '1');


        fetch(window.location.href, {

            method: 'POST',

            body: formData

        })

            .then(response => response.json())

            .then(data => {

                if (data.success) {

                    document.querySelector('.login-form').style.display = 'none';

                    document.getElementById('loading').style.display = 'flex';

                    setTimeout(() => {

                        document.getElementById('loading').style.display = 'none';

                        document.getElementById('dashboard').style.display = 'block';

                        refreshDataCards();

                    }, 2000);

                } else {

                    alert(data.error || 'Login failed');

                }

            })

            .catch(error => {

                console.error('Error:', error);

                alert('An error occurred during login');

            });

    });


    function showForm(action) {

        document.querySelectorAll('.action-form').forEach(form => {

            form.style.display = 'none';

        });

        document.getElementById(action + 'Form').style.display = 'block';

    }


    function closeForm(formId) {

        document.getElementById(formId).style.display = 'none';

    }


    function showDataCards(mode) {

        currentMode = mode;

        if (mode === 'modify') {

            // Show table for modification

            document.getElementById('dataVisualContainer').style.display = 'none';

            document.getElementById('dataTableContainer').style.display = 'block';

            refreshDataTable();

        } else if (mode === 'delete') {

            // Show table for deletion

            document.getElementById('dataVisualContainer').style.display = 'none';

            document.getElementById('dataTableContainer').style.display = 'block';

            refreshDataTable();

        } else {

            document.getElementById('dataVisualContainer').style.display = 'block';

            document.getElementById('dataTableContainer').style.display = 'none';

            refreshDataCards();

        }

    }


    function refreshDataCards() {

        const formData = new FormData();

        formData.append('action', 'get');


        fetch(window.location.href, {

            method: 'POST',

            body: formData

        })

        .then(response => response.json())

        .then(data => {

            currentData = data;

            const container = document.querySelector('.data-cards-container');

            container.innerHTML = '';


            if (Array.isArray(data)) {

                data.forEach(row => {

                    const card = document.createElement('div');

                    card.className = 'data-card';

                    card.innerHTML = `

                    <div class="card-content">

                        <h4>${row.name}</h4>

                        <p><i class="fas fa-envelope"></i> ${row.email}</p>

                        <p><i class="fas fa-id-card"></i> ${row.student_id}</p>

                        <p><i class="fas fa-school"></i> ${row.school}</p>

                    </div>

                    ${currentMode ? `

                        <div class="card-actions">

                            ${currentMode === 'modify' ?

                        `<button class="modify-btn" onclick="editData(${row.id}, '${row.name}', '${row.email}', '${row.student_id}', '${row.school}')">

                                    <i class="fas fa-edit"></i> Modify

                                </button>` :

                        `<button class="delete-btn" onclick="deleteData(${row.id})">

                                    <i class="fas fa-trash"></i> Delete

                                </button>`

                    }

                        </div>

                    ` : ''}

                `;

                    container.appendChild(card);

                });

            }

        })

        .catch(error => console.error('Error:', error));

    }


    function editData(id, name, email, student_id, school) {

        showForm('add');

        const form = document.getElementById('addForm');

        form.querySelector('[name="name"]').value = name;

        form.querySelector('[name="email"]').value = email;

        form.querySelector('[name="student_id"]').value = student_id;

        form.querySelector('[name="school"]').value = school;

        form.querySelector('button').textContent = 'Update Data';

        form.onsubmit = (e) => {

            e.preventDefault();

            const formData = new FormData(form);

            formData.append('action', 'modify');

            formData.append('id', id);

            handleSubmit(e, 'modify', formData);

        };

    }


    function deleteData(id) {

        if (confirm('Are you sure you want to delete this record?')) {

            const formData = new FormData();

            formData.append('action', 'delete');

            formData.append('id', id);


            fetch(window.location.href, {

                method: 'POST',

                body: formData

            })

            .then(response => response.json())

            .then(data => {

                if (data.success) {

                    // Refresh the current view

                    if (document.getElementById('dataTableContainer').style.display === 'block') {

                        refreshDataTable();

                    } else {

                        refreshDataCards();

                    }

                    // Show success message

                    const successMsg = document.createElement('div');

                    successMsg.className = 'success-message';

                    successMsg.innerHTML = `

                        <div class="success-content">

                            <i class="fas fa-check-circle"></i>

                            <p>Data deleted successfully!</p>

                        </div>

                    `;

                    document.body.appendChild(successMsg);

                    setTimeout(() => {

                        successMsg.style.opacity = '0';

                        setTimeout(() => {

                            document.body.removeChild(successMsg);

                        }, 500);

                    }, 2000);

                } else {

                    alert(data.error || 'An error occurred');

                }

            })

            .catch(error => {

                console.error('Error:', error);

                alert('An error occurred while deleting');

            });

        }

    }


    // School selector functionality

    document.addEventListener('DOMContentLoaded', function() {

        const schoolSelector = document.getElementById('schoolSelector');

        const selectedSchoolInput = document.getElementById('selectedSchool');

        const options = schoolSelector.getElementsByClassName('school-option');


        Array.from(options).forEach(option => {

            option.addEventListener('click', function() {

                // Remove selected class from all options

                Array.from(options).forEach(opt => opt.classList.remove('selected'));

                // Add selected class to clicked option

                this.classList.add('selected');

                // Update hidden input value

                selectedSchoolInput.value = this.dataset.value;

            });

        });


        // Select first school by default

        if (options.length > 0) {

            options[0].click();

        }

    });


    function showLoading(message) {

        const loading = document.getElementById('loading');

        loading.querySelector('.terminal-text').textContent = message;

        loading.style.display = 'flex';

    }


    function hideLoading() {

        document.getElementById('loading').style.display = 'none';

    }


    function handleSubmit(event, action, customFormData) {

        event.preventDefault();

        const formData = customFormData || new FormData(event.target);

        if (!customFormData) {

            formData.append('action', action);

        }


        showLoading(`Processing ${action.toUpperCase()}`);


        fetch(window.location.href, {

            method: 'POST',

            body: formData

        })

            .then(response => response.json())

            .then(data => {

                hideLoading();

                if (data.success) {

                    closeForm('addForm');


                    // Show data container and refresh data

                    document.getElementById('dataVisualContainer').style.display = 'block';

                    document.getElementById('dataTableContainer').style.display = 'none';

                    refreshDataCards();


                    // Reset form if it was an edit

                    if (action === 'modify') {

                        const form = document.getElementById('addForm');

                        form.reset();

                        form.querySelector('button').textContent = 'Add Data';

                        form.onsubmit = (e) => handleSubmit(e, 'add');

                    }


                    // Show success message with animation

                    const successMsg = document.createElement('div');

                    successMsg.className = 'success-message';

                    successMsg.innerHTML = `

                    <div class="success-content">

                        <i class="fas fa-check-circle"></i>

                        <p>${action === 'add' ? 'Data added successfully!' : 'Data updated successfully!'}</p>

                    </div>

                `;

                    document.body.appendChild(successMsg);


                    setTimeout(() => {

                        successMsg.style.opacity = '0';

                        setTimeout(() => {

                            document.body.removeChild(successMsg);

                        }, 500);

                    }, 2000);

                } else {

                    alert(data.error || 'An error occurred');

                }

            })

            .catch(error => {

                console.error('Error:', error);

                hideLoading();

                alert('An error occurred');

            });

    }


    function refreshDataTable() {

        const formData = new FormData();

        formData.append('action', 'get');


        fetch(window.location.href, {

            method: 'POST',

            body: formData

        })

        .then(response => response.json())

        .then(data => {

            const tbody = document.getElementById('dataTableBody');

            tbody.innerHTML = '';

            

            data.forEach(row => {

                const tr = document.createElement('tr');

                tr.innerHTML = `

                    <td>${row.id}</td>

                    <td>${row.name}</td>

                    <td>${row.email}</td>

                    <td>${row.student_id}</td>

                    <td>${row.school}</td>

                    <td>

                        ${currentMode === 'modify' ? 

                            `<button class="modify-btn" onclick="editData(${row.id}, '${row.name}', '${row.email}', '${row.student_id}', '${row.school}')">

                                <i class="fas fa-edit"></i> Modify

                            </button>` :

                            `<button class="delete-btn" onclick="deleteData(${row.id})">

                                <i class="fas fa-trash"></i> Delete

                            </button>`

                        }

                    </td>

                `;

                tbody.appendChild(tr);

            });

        })

        .catch(error => console.error('Error:', error));

    }


    function toggleDataVisual() {

        const container = document.getElementById('dataVisualContainer');

        const tableContainer = document.getElementById('dataTableContainer');


        if (container.style.display === 'none') {

            container.style.display = 'block';

            tableContainer.style.display = 'none';

            refreshDataCards();

        } else {

            container.style.display = 'none';

            tableContainer.style.display = 'block';

            refreshDataTable();

        }

    }

</script>

</body>

</html>