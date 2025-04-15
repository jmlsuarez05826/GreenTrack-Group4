<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Greentrack</title>

  <style>
    * {
      margin: 0;
      padding: 0;
      box-sizing: border-box;
    }

    body {
      font-family: 'Segoe UI', sans-serif;
      background-image: url('images/forest.jpg');
      background-size: cover;
      background-position: center;
      height: 100vh;
      color: #333;
    }

    header {
      display: flex;
      justify-content: space-between;
      align-items: center;
      padding: 20px 40px;
      background-color: rgba(0, 0, 0, 0.3);
      color: white;
    }

    .logo {
      font-size: 28px;
      font-weight: bold;
      letter-spacing: 1px;
    }

    nav a {
      color: white;
      margin: 0 15px;
      text-decoration: none;
      font-weight: 500;
    }

    .top-login {
      padding: 6px 16px;
      background-color: transparent;
      border: 1px solid white;
      color: white;
      border-radius: 10px;
      cursor: pointer;
    }

    .form-container {
      max-width: 300px;
      margin: 100px auto;
      background-color: rgba(255, 255, 255, 0.85);
      padding: 25px 30px;
      border-radius: 15px;
      box-shadow: 0 5px 15px rgba(0,0,0,0.2);
    }

    .tabs {
      display: flex;
      justify-content: space-between;
      margin-bottom: 20px;
      background: linear-gradient(to right, #556b2f 50%, #ddd 50%);
      border-radius: 25px;
      overflow: hidden;
    }

    .tabs button {
      flex: 1;
      padding: 10px 0;
      border: none;
      background-color: transparent;
      font-weight: bold;
      cursor: pointer;
    }

    .tabs .active {
      background-color: #2e4d1d;
      color: white;
    }

    .login-form label {
      font-size: 14px;
      margin: 10px 0 4px;
      display: block;
    }

    .input-group {
      display: flex;
      align-items: center;
      border: 1px solid #ccc;
      border-radius: 10px;
      padding: 8px 12px;
      margin-bottom: 10px;
      background-color: white;
    }

    .input-group input {
      border: none;
      outline: none;
      flex: 1;
      font-size: 14px;
    }

    .icon {
      margin-left: 8px;
    }

    .options {
      display: flex;
      justify-content: space-between;
      font-size: 12px;
      margin: 10px 0;
    }

    .options a {
      text-decoration: none;
      color: #333;
    }

    .submit {
      width: 100%;
      padding: 10px;
      background-color: black;
      color: white;
      border: none;
      border-radius: 6px;
      font-weight: bold;
      cursor: pointer;
      margin-top: 10px;
    }
  </style>
</head>
<body>

  <header>
    <div class="logo">GREENTRACK</div>
    <nav>
      <a href="#">Home</a>
      <a href="#">About</a>
      <a href="#">How It Works</a>
      <a href="#">Contact</a>
      <button class="top-login">Login</button>
    </nav>
  </header>

  <div class="form-container">
    <div class="tabs">
      <button class="active">Log In</button>
      <button>Register</button>
    </div>
    <form class="login-form">
      <label>Username</label>
      <div class="input-group">
        <input type="text" placeholder="Username" required />
        <span class="icon">👤</span>
      </div>

      <label>Password</label>
      <div class="input-group">
        <input type="password" placeholder="Password" required />
        <span class="icon">🔒</span>
      </div>

      <div class="options">
        <label><input type="checkbox" /> Remember me</label>
        <a href="#">Forgot Password?</a>
      </div>

      <button class="submit">Login</button>
    </form>
  </div>

</body>
</html>
