<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Offline - CISMART 2</title>
    <style>
        body {
            font-family: 'Inter', system-ui, -apple-system, sans-serif;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            height: 100vh;
            margin: 0;
            background-color: #f8fafc;
            color: #334155;
            text-align: center;
            padding: 20px;
        }
        .icon {
            font-size: 4rem;
            margin-bottom: 1rem;
            color: #94a3b8;
        }
        h1 {
            font-size: 1.5rem;
            margin-bottom: 0.5rem;
            color: #0f172a;
        }
        p {
            font-size: 1rem;
            max-width: 400px;
            margin-bottom: 2rem;
            line-height: 1.5;
        }
        button {
            background-color: #f97316;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        button:hover {
            background-color: #ea580c;
        }
    </style>
</head>
<body>
    <div class="icon">🔌</div>
    <h1>Anda Sedang Offline</h1>
    <p>Sepertinya koneksi internet Anda terputus. Silakan periksa jaringan Wi-Fi atau data seluler Anda untuk kembali menggunakan CISMART.</p>
    <button onclick="window.location.reload()">Coba Lagi</button>
</body>
</html>
