<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>404 - Page Not Found | {{ config('app.name') }}</title>
    <link href="{{ asset('assets/styles/bootstrap-icons.min.css') }}" rel="stylesheet">
    <style>
        :root {
            --primary-color: #F5C518;
            --primary-dark: #D4A90A;
            --dark-color: #1a1a1a;
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
            color: var(--primary-color);
            line-height: 1;
            text-shadow: 4px 4px 0 var(--primary-dark);
            margin-bottom: 1rem;
        }
        .error-icon {
            font-size: 5rem;
            color: var(--primary-color);
            margin-bottom: 1.5rem;
            animation: float 3s ease-in-out infinite;
        }
        @keyframes float {
            0%, 100% { transform: translateY(0); }
            50% { transform: translateY(-10px); }
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
    </style>
</head>
<body>
    <div class="decoration top-left">4</div>
    <div class="decoration bottom-right">4</div>

    <div class="error-container">
        <div class="error-icon">
            <i class="bi bi-search"></i>
        </div>
        <div class="error-code">404</div>
        <h1 class="error-title">Page Not Found</h1>
        <p class="error-message">
            Oops! The page you're looking for doesn't exist or has been moved.
            Let's get you back on track.
        </p>
        <a href="{{ url('/') }}" class="btn-home">
            <i class="bi bi-house-door-fill"></i>
            Back to Home
        </a>
    </div>
</body>
</html>
