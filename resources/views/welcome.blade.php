<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ config('app.name', 'Talyn Gold Trading Platform') }}</title>
            <style>
            body {
                font-family: 'Arial', sans-serif;
                background-color: #f8f9fa;
                color: #333;
                margin: 0;
                padding: 0;
                display: flex;
                justify-content: center;
                align-items: center;
                min-height: 100vh;
                text-align: center;
            }
            .container {
                max-width: 800px;
                padding: 20px;
            }
            h1 {
                color: #f53003;
                font-size: 2.5rem;
                margin-bottom: 20px;
            }
            p {
                font-size: 1.2rem;
                line-height: 1.6;
                margin-bottom: 30px;
            }
            .links {
                margin-top: 30px;
            }
            .links a {
                display: inline-block;
                margin: 0 15px;
                color: #f53003;
                text-decoration: none;
                font-weight: bold;
            }
            .links a:hover {
                text-decoration: underline;
            }
            </style>
    </head>
    <body>
        <div class="container">
            <h1>Talyn Gold Trading Platform API</h1>
            <p>This is the backend API for the Talyn Gold Trading Platform.</p>

            <div class="links">
                <a href="{{ url('/api/doc') }}">API Documentation</a>
                </div>
        </div>
    </body>
</html>
