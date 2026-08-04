<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>500 - Server Error | {{ config('app.name') }}</title>
    <link href="{{ asset('assets/styles/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F5C518;
            --primary-dark: #D4A90A;
            --dark-color: #1a1a1a;
            --danger-color: #dc3545;
        }
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, Oxygen, Ubuntu, sans-serif;
            background: var(--dark-color);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
        }
        .error-container {
            text-align: center;
            padding: 2rem;
            max-width: 600px;
        }
        .error-code {
            font-size: 10rem;
            font-weight: 800;
            color: var(--danger-color);
            line-height: 1;
            text-shadow: 4px 4px 0 #a71d2a;
            margin-bottom: 1rem;
        }
        .error-icon {
            font-size: 5rem;
            color: var(--danger-color);
            margin-bottom: 1.5rem;
            animation: pulse 2s ease-in-out infinite;
        }
        @keyframes pulse {
            0%, 100% { transform: scale(1); opacity: 1; }
            50% { transform: scale(1.05); opacity: 0.8; }
        }
        .error-title {
            font-size: 2rem;
            font-weight: 600;
            margin-bottom: 1rem;
            color: #fff;
        }
        .error-message {
            font-size: 1.1rem;
            color: #a0a0a0;
            margin-bottom: 2rem;
            line-height: 1.6;
        }
        .btn-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }
        .btn-home {
            display: inline-flex;
            align-items: center;
            gap: 0.5rem;
            padding: 0.875rem 2rem;
            background: var(--primary-color);
            color: var(--dark-color);
            text-decoration: none;
            font-weight: 600;
            font-size: 1rem;
            border-radius: 0.5rem;
            transition: all 0.3s ease;
        }
        .btn-home:hover {
            background: var(--primary-dark);
            transform: translateY(-2px);
            box-shadow: 0 4px 20px rgba(245, 197, 24, 0.3);
        }
        .btn-home i {
            font-size: 1.2rem;
        }
        .decoration {
            position: fixed;
            opacity: 0.03;
            font-size: 40rem;
            font-weight: 900;
            color: #fff;
            z-index: -1;
            user-select: none;
        }
        .decoration.top-left {
            top: -10rem;
            left: -5rem;
        }
        .decoration.bottom-right {
            bottom: -10rem;
            right: -5rem;
        }
        .gears {
            display: flex;
            justify-content: center;
            margin-bottom: 1.5rem;
        }
        .gear {
            font-size: 3rem;
            color: var(--danger-color);
        }
        .gear:nth-child(1) {
            animation: spin 3s linear infinite;
        }
        .gear:nth-child(2) {
            animation: spin-reverse 3s linear infinite;
            margin-left: -0.5rem;
            font-size: 2rem;
            margin-top: 1rem;
        }
        @keyframes spin {
            from { transform: rotate(0deg); }
            to { transform: rotate(360deg); }
        }
        @keyframes spin-reverse {
            from { transform: rotate(0deg); }
            to { transform: rotate(-360deg); }
        }
    </style>
</head>
<body>
    <div class="decoration top-left">5</div>
    <div class="decoration bottom-right">0</div>

    <div class="error-container">
        <div class="gears">
            <i class="bi bi-gear-fill gear"></i>
            <i class="bi bi-gear-fill gear"></i>
        </div>
        <div class="error-code">500</div>
        <h1 class="error-title">Server Error</h1>
        <p class="error-message">
            Something went wrong on our end. Our team has been notified and is working on fixing the issue.
        </p>
        <div class="btn-group">
            <a href="{{ url('/') }}" class="btn-home">
                <i class="bi bi-house-door-fill"></i>
                Back to Home
            </a>
        </div>
    </div>
</body>
</html>
