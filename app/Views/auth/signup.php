<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>BMS - Customer Signup</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600&display=swap');
        
        * { margin: 0; padding: 0; box-sizing: border-box; font-family: 'Inter', sans-serif; }
        
        body {
            background: linear-gradient(135deg, #1e1e2f, #2a2a40);
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            color: #fff;
        }

        .signup-container {
            background: rgba(255, 255, 255, 0.05);
            backdrop-filter: blur(15px);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 20px;
            padding: 40px;
            width: 100%;
            max-width: 400px;
            box-shadow: 0 15px 35px rgba(0,0,0,0.2);
            transform: translateY(20px);
            opacity: 0;
            animation: slideUp 0.6s ease-out forwards;
        }

        @keyframes slideUp {
            to { transform: translateY(0); opacity: 1; }
        }

        .signup-container h2 { margin-bottom: 25px; text-align: center; font-weight: 600; font-size: 24px; color: #4facfe; }

        .input-group { margin-bottom: 20px; }
        
        .input-group label { display: block; margin-bottom: 8px; font-size: 14px; color: #b3b3c5; }
        
        .input-group input, .input-group select {
            width: 100%;
            padding: 12px 15px;
            background: rgba(0, 0, 0, 0.2);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: 8px;
            color: #fff;
            outline: none;
            transition: all 0.3s ease;
        }
        
        .input-group select option {
            color: #333;
        }
        
        .input-group input:focus, .input-group select:focus { border-color: #4facfe; box-shadow: 0 0 10px rgba(79, 172, 254, 0.3); }

        .btn-submit {
            width: 100%;
            padding: 12px;
            background: linear-gradient(90deg, #00f2fe, #4facfe);
            border: none;
            border-radius: 8px;
            color: white;
            font-size: 16px;
            font-weight: 600;
            cursor: pointer;
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }

        .btn-submit:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(79, 172, 254, 0.4);
        }

        .links { margin-top: 20px; text-align: center; font-size: 14px; }
        .links a { color: #4facfe; text-decoration: none; transition: color 0.3s ease; }
        .links a:hover { color: #00f2fe; }

        .alert {
            padding: 10px;
            border-radius: 5px;
            margin-bottom: 20px;
            font-size: 14px;
            text-align: left;
        }
        .alert-error { background: rgba(255, 77, 79, 0.2); border: 1px solid #ff4d4f; color: #ff4d4f; }
    </style>
</head>
<body>

    <div class="signup-container">
        <h2>Customer Signup</h2>

        <?php if(session()->getFlashdata('errors')): ?>
            <div class="alert alert-error">
                <ul style="padding-left: 20px;">
                <?php foreach(session()->getFlashdata('errors') as $error): ?>
                    <li><?= esc($error) ?></li>
                <?php endforeach ?>
                </ul>
            </div>
        <?php endif; ?>

        <form action="<?= base_url('signup') ?>" method="post">
            <?= csrf_field() ?>
            <div class="input-group">
                <label for="username">Enter Username</label>
                <input type="text" id="username" name="username" required autocomplete="off">
            </div>
            
            <div class="input-group">
                <label for="password">Create Password</label>
                <input type="password" id="password" name="password" required>
            </div>

            <div class="input-group">
                <label for="branch_id">Home Branch</label>
                <select id="branch_id" name="branch_id" required>
                    <option value="">-- Select a Branch --</option>
                    <?php foreach ($branches as $branch): ?>
                        <option value="<?= esc($branch['id']) ?>"><?= esc($branch['name']) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>



            <button type="submit" class="btn-submit">Register</button>
        </form>

        <div class="links">
            <p>Already have an account? <a href="<?= base_url('login') ?>">Log in here</a></p>
        </div>
    </div>

</body>
</html>
