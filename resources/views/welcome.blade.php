<!DOCTYPE html>

<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Welcome</title>
    <meta name="viewport" content="width=device-width, initial-scale=1.0">

```
<style>
    body {
        margin: 0;
        padding: 0;
        font-family: Arial, sans-serif;

        /* Background gradient */
        background: linear-gradient(135deg, #4facfe, #00f2fe);

        height: 100vh;
        display: flex;
        align-items: center;
        justify-content: center;
    }

    .container {
        background: #ffffff;
        padding: 40px 60px;
        border-radius: 12px;
        text-align: center;
        box-shadow: 0 10px 25px rgba(0,0,0,0.2);
    }

    h1 {
        margin: 0;
        font-size: 36px;
        color: #333;
    }

    p {
        margin-top: 10px;
        color: #666;
        font-size: 16px;
    }

    .btn {
        margin-top: 20px;
        padding: 10px 20px;
        border: none;
        background: #4facfe;
        color: #fff;
        border-radius: 6px;
        cursor: pointer;
        font-size: 14px;
    }

    .btn:hover {
        background: #3a8ee6;
    }
</style>
```

</head>
<body>

```
<div class="container">
    <h1>Welcome 🎉</h1>
    <p>Your Laravel app is working successfully.</p>

    <button class="btn" onclick="alert('It works!')">
        Test Button
    </button>
</div>
```

</body>
</html>
