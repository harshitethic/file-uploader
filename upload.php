<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['username']) && isset($_POST['password'])) {
        if ($_POST['username'] == 'admin' && $_POST['password'] == 'harshitethic') {
            $_SESSION['loggedin'] = true;
            header("Location: upload.php");
            exit;
        } else {
            $login_error = "Invalid username or password.";
        }
    } elseif (isset($_POST['action']) && $_POST['action'] == 'upload' && isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true) {
        $target_dir = "uploads/";

        if (isset($_POST['url']) && !empty($_POST['url'])) {
            $url = $_POST['url'];
            $filename = basename(parse_url($url, PHP_URL_PATH));
            $target_file = $target_dir . $filename;

            if (copy($url, $target_file)) {
                echo json_encode(['success' => 'File uploaded successfully.']);
            } else {
                echo json_encode(['error' => 'Sorry, there was an error uploading your file.']);
            }
        } elseif (isset($_FILES['file'])) {
            $target_file = $target_dir . basename($_FILES["file"]["name"]);

            if (move_uploaded_file($_FILES["file"]["tmp_name"], $target_file)) {
                echo json_encode(['success' => 'File uploaded successfully.']);
            } else {
                echo json_encode(['error' => 'Sorry, there was an error uploading your file.']);
            }
        } else {
            echo json_encode(['error' => 'No file or URL was provided.']);
        }
        exit;
    }
}
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>File Uploader</title>
    <style>
        body {
            background-color: #f6f6f6;
            font-family: Arial, sans-serif;
            color: #333;
        }

        /* Add your provided progress bar styles */
        #progressbar {
            height: 26px;
            position: absolute;
            left: 50%;
            top: 20%;
            width: 200px;
            background: rgba(159, 159, 159, 0.5);
            border-radius: 10px;
            margin: -20px 0 0 -100px;
            padding: 2px;
        }

        #loading {
            transition: all 500ms ease;
            height: 20px;
            width: calc(100% - 10px);
            border-radius: 8px;
            background: #474747;
            position: absolute;
            margin: 3px;
            display: inline-block;
            animation: load 15s ease infinite;
        }

        #load {
            font-family: Arial;
            font-weight: bold;
            text-align: center;
            margin-top: -30px;
        }

        @keyframes load {
            0% {
                width: 2%;
            }
            10% {
                width: 10%;
            }
        }

        /* Add your login form styles */
        #admin-login {
            padding-top: 80px;
        }

        .container {
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
        }

                .row {
            display: flex;
            flex-direction: column;
            width: 100%;
            max-width: 450px;
        }

        .card {
            background-color: white;
            padding: 30px;
            border-radius: 4px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
        }

        .input-field {
            position: relative;
            margin-top: 2rem;
            margin-bottom: 2rem;
        }

        label {
            position: absolute;
            top: 0.75rem;
            left: 0.75rem;
            font-size: 1rem;
            color: #9e9e9e;
            transition: all 0.2s ease-in-out;
        }

        input {
            display: block;
            width: 100%;
            height: 2rem;
            padding: 0.5rem;
            background: none;
            border: none;
            border-bottom: 1px solid #9e9e9e;
            outline: none;
            color: #424242;
            font-size: 1.3rem;
        }

        input:focus,
        input:valid {
            border-bottom: 1px solid #26a69a;
            box-shadow: 0 1px 0 0 #26a69a;
        }

        input:focus + label,
        input:valid + label {
            color: #26a69a;
            font-size: 0.8rem;
            transform: translateY(-1.5rem);
        }

        button {
            display: inline-block;
            height: 36px;
            line-height: 36px;
            padding: 0 2rem;
            text-transform: uppercase;
            font-weight: bold;
            color: white;
            background-color: #26a69a;
            letter-spacing: .5px;
            border: none;
            border-radius: 2px;
            cursor: pointer;
            outline: none;
            transition: all .2s ease-in-out;
        }

        button:hover {
            background-color: #2bbbad;
        }
      
      
       /* Header and footer styles */
        header {
            background-color: #1DA1F2;
            padding: 5px 20px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            color: #fff;
            font-weight: bold;
            font-size: 14px;
        }

        header a {
            color: #fff;
            text-decoration: none;
        }

        footer {
            background-color: #1DA1F2;
            padding: 10px;
            text-align: center;
            color: #fff;
            font-weight: bold;
        }

        /* Success pop-up styles */
        .success-popup {
            display: none;
            position: fixed;
            top: 20px;
            right: 20px;
            background-color: #4CAF50;
            color: white;
            padding: 15px;
            border-radius: 4px;
            box-shadow: 0px 4px 6px rgba(0, 0, 0, 0.1);
            z-index: 100;
            opacity: 0.9;
        }

    </style>
    <script>
    function uploadFile() {
    const fileInput = document.getElementById("file");
    const xhr = new XMLHttpRequest();
    const formData = new FormData();
    const progressBar = document.getElementById("progressbar");
    const loading = document.getElementById("loading");
    const loadText = document.getElementById("load");

    if (fileInput.files.length === 0 && !document.getElementById('url').value) {
        alert("Please choose a file or provide a URL to upload.");
        return;
    }

    if (document.getElementById('url').value) {
        formData.append("url", document.getElementById('url').value);
    } else {
        formData.append("file", fileInput.files[0]);
    }

    formData.append("action", "upload");

    xhr.upload.onprogress = function (e) {
        if (e.lengthComputable) {
            const percentage = (e.loaded / e.total) * 100;
            loading.style.width = percentage + "%";
            loadText.innerHTML = Math.round(percentage) + "%";
            if (percentage === 100) {
                loading.style.animation = "none";
            }
        }
    };

            xhr.onload = function () {
        const message = document.getElementById("message");
        const response = JSON.parse(xhr.responseText);

        progressBar.style.display = "none";

        if (response.error) {
            message.innerHTML = "Error: " + response.error;
        } else if (response.success) {
            message.innerHTML = "Success: " + response.success;
        } else {
            message.innerHTML = "An unknown error occurred.";
        }
    };

    progressBar.style.display = "block";
    xhr.open("POST", "upload.php", true);
    xhr.send(formData);
}

function submitForm(e) {
    e.preventDefault();
    uploadFile();
}
                 function showSuccessPopup() {
            const popup = document.getElementById('success-popup');
            popup.style.display = 'block';
            setTimeout(function() {
                popup.style.display = 'none';
            }, 3000);
        }

        function logout() {
            window.location.href = "upload.php?logout=1";
        }

    </script>
</head>
<body>
<header>
        <h1 style="font-size: 18px;">File Uploader</h1>
        <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
            <a href="javascript:void(0);" onclick="logout();">Logout</a>
        <?php endif; ?>
    </header>
  <?php
    if (isset($_GET['logout']) && $_GET['logout'] == '1') {
        session_destroy();
        header("Location: upload.php");
        exit;
    }
    ?>
  
    <?php if (isset($_SESSION['loggedin']) && $_SESSION['loggedin'] === true): ?>
        <div class="container">
            <div class="row">
                <form onsubmit="submitForm(event);">
                    <div class="input-field">
                        <input type="file" name="file" id="file">
                    </div>
                    <div class="input-field">
                        <input type="url" name="url" id="url">
                        <label for="url">File URL (optional)</label>
                    </div>
                    <div id="progressbar" style="display: none;">
                        <span id="loading"></span>
                        <div id="load">loading</div>
                    </div>
                    <div id="message"></div>
                    <button type="submit">Upload File</button>
                </form>
            </div>
        </div>
        <div id="success-popup" class="success-popup">Success: File uploaded successfully.</div>
        <script>
            xhr.onload = function () {
                const message = document.getElementById("message");
                const response = JSON.parse(xhr.responseText);

                progressBar.style.display = "none";

                if (response.error) {
                    message.innerHTML = "Error: " + response.error;
                } else if (response.success) {
                    message.innerHTML = "";
                    showSuccessPopup();
                } else {
                    message.innerHTML = "An unknown error occurred.";
                }
            };
        </script>
    <?php else: ?>
        <div class="container">
            <div class="row" id="admin-login">
                <form action="upload.php" method="post" class="card">
                    <?php if (isset($login_error)): ?>
                    <p class="error"><?php echo $login_error; ?></p>
                    <?php endif; ?>
                    <div class="input-field">
                        <input type="text" name="username" id="username" required>
                        <label for="username">Enter Username</label>
                    </div>
                    <div class="input-field">
                        <input type="password" name="password" id="password" required>
                        <label for="password">Enter Password</label>
                    </div>
                    <button type="submit">Login Please</button>
                </form>
            </div>
        </div>
    <?php endif; ?>

    <footer>
        <p>© 2023 File Uploader. All rights reserved. | Website: harshitethic.in | Created by Harshit Sharma</p>
    </footer>
</body>
</html>
