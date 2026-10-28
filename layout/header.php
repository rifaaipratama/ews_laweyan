<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SIAGA LAWEYAN - Command Center</title>

    <!-- Bootstrap 5 & Icons -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.5/font/bootstrap-icons.css">

    <!-- SCRIPT TEMA GLOBAL (Harus di atas agar tidak kedip saat ganti halaman) -->
    <script>
        const savedTheme = localStorage.getItem('theme') || 'dark';
        document.documentElement.setAttribute('data-theme', savedTheme);
    </script>

    <style>
        /* =========================================
           TEMA DARK (DEFAULT)
        ========================================= */
        :root, [data-theme="dark"] {
            --bg-body: #0f172a;       
            --bg-panel: #1e293b;      
            --text-main: #f8fafc;
            --text-muted: #94a3b8;
            --accent-blue: #2563eb;
            --accent-red: #ef4444; 
            --border-color: rgba(255,255,255,0.05);
            --shadow-color: rgba(0,0,0,0.2);
            --hover-bg: rgba(255,255,255,0.05);
        }

        /* =========================================
           TEMA LIGHT
        ========================================= */
        [data-theme="light"] {
            --bg-body: #f8fafc;       
            --bg-panel: #ffffff;      
            --text-main: #0f172a;
            --text-muted: #64748b;
            --accent-blue: #2563eb;
            --accent-red: #ef4444; 
            --border-color: rgba(0,0,0,0.08);
            --shadow-color: rgba(0,0,0,0.05);
            --hover-bg: rgba(0,0,0,0.03);
        }

        /* --- GLOBAL CSS --- */
        body {
            background-color: var(--bg-body);
            color: var(--text-main);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            overflow-x: hidden;
            margin: 0;
            transition: background-color 0.3s, color 0.3s;
        }

        .text-dynamic { color: var(--text-main) !important; }
        
        /* --- SIDEBAR --- */
        #sidebar {
            width: 270px;
            background-color: var(--bg-panel);
            height: 100vh; 
            position: fixed;
            left: 0;
            top: 0;
            border-right: 1px solid var(--border-color);
            display: flex;
            flex-direction: column;
            z-index: 1000;
            transition: background-color 0.3s, border-color 0.3s;
        }

        .sidebar-brand {
            padding: 25px 20px;
            display: flex;
            align-items: center;
            border-bottom: 1px solid var(--border-color);
        }

        .brand-icon {
            color: var(--accent-red);
            font-size: 2.2rem;
            filter: drop-shadow(0 0 8px rgba(239, 68, 68, 0.4));
        }

        .sidebar-scroll {
            flex-grow: 1; 
            overflow-y: auto;
            overflow-x: hidden;
            scroll-behavior: smooth;
        }

        .sidebar-scroll::-webkit-scrollbar { width: 5px; }
        .sidebar-scroll::-webkit-scrollbar-track { background: transparent; }
        .sidebar-scroll::-webkit-scrollbar-thumb { background: var(--border-color); border-radius: 10px; }

        .menu-title {
            color: var(--text-muted);
            font-size: 0.75rem;
            font-weight: bold;
            text-transform: uppercase;
            letter-spacing: 1px;
            padding: 10px 20px;
            margin-top: 10px;
        }

        .nav-link {
            color: var(--text-muted);
            padding: 12px 20px;
            border-radius: 8px;
            margin: 4px 15px;
            transition: all 0.3s;
            font-weight: 500;
        }

        .nav-link:hover {
            color: var(--text-main);
            background-color: var(--hover-bg);
            transform: translateX(5px);
        }

        .nav-link.active {
            background-color: rgba(37, 99, 235, 0.15) !important;
            color: var(--accent-blue) !important;
            border-left: 4px solid var(--accent-blue);
            border-radius: 0 8px 8px 0;
            margin-left: 0;
            padding-left: 31px;
        }

        .profile-card {
            background-color: var(--bg-body);
            border: 1px solid var(--border-color);
            border-radius: 12px;
            padding: 15px;
            text-align: center;
            margin-bottom: 12px;
            transition: background-color 0.3s, border-color 0.3s;
        }

        #main-content {
            margin-left: 270px;
            padding: 25px;
            min-height: 100vh;
        }
    </style>
</head>
<body>