<?php
// public/dashboard.php - Main Dashboard
require_once '../config/config.php';
require_once '../config/auth.php';

// Require login to access dashboard
requireLogin();

// Get current user info
$user = getCurrentUser();

// Get computers from database (instead of hardcoded list)
$computers = getComputers();

// If no computers in database, show sample data
if (empty($computers)) {
    $computers = [
        [
            'computer_id' => 'PC01',
            'computer_name' => 'PC01',
            'owner_name' => 'Jan Kowalski',
            'ip_address' => '192.168.1.100',
            'status' => 'online'
        ],
        [
            'computer_id' => 'PC02',
            'computer_name' => 'PC02', 
            'owner_name' => 'Anna Nowak',
            'ip_address' => '192.168.1.101',
            'status' => 'offline'
        ],
        [
            'computer_id' => 'PC03',
            'computer_name' => 'PC03',
            'owner_name' => 'Serwer testowy',
            'ip_address' => '192.168.1.102',
            'status' => 'online'
        ]
    ];
}

// Get role-specific welcome message
function getRoleWelcomeMessage($role) {
    switch ($role) {
        case 'admin':
            return 'Witamy Administratora! Masz pełny dostęp do systemu.';
        case 'operator':
            return 'Witamy Operatora! Możesz zarządzać komputerami.';
        case 'viewer':
            return 'Witamy! Masz dostęp tylko do odczytu.';
        default:
            return 'Witamy w systemie!';
    }
}

// Get role color class
function getRoleColorClass($role) {
    switch ($role) {
        case 'admin': return 'text-danger';
        case 'operator': return 'text-success';
        default: return 'text-secondary';
    }
}

// Count computers by status
function getComputerStats($computers) {
    $stats = ['online' => 0, 'offline' => 0, 'total' => count($computers)];
    foreach ($computers as $computer) {
        $status = isset($computer['status']) ? $computer['status'] : 'offline';
        if ($status === 'online') {
            $stats['online']++;
        } else {
            $stats['offline']++;
        }
    }
    return $stats;
}

$stats = getComputerStats($computers);
?>
<!DOCTYPE html>
<html lang="pl">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= APP_NAME ?> - Dashboard</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <style>
        body {
            background-color: #f8f9fa;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .navbar-custom {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .card {
            border: none;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }
        
        .card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }
        
        .computer-card {
            cursor: pointer;
            text-decoration: none;
            color: inherit;
        }
        
        .computer-card:hover {
            color: inherit;
            text-decoration: none;
        }
        
        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
        }
        
        .user-info {
            background: rgba(255,255,255,0.2);
            backdrop-filter: blur(10px);
            border-radius: 10px;
            padding: 15px;
            color: white;
        }
        
        .stats-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
        }
        
        .welcome-section {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
        }
        
        .status-online {
            background-color: #28a745;
        }
        
        .status-offline {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar navbar-expand-lg navbar-custom">
        <div class="container">
            <a class="navbar-brand text-white fw-bold" href="#">
                <i class="bi bi-shield-check"></i>
                <?= APP_NAME ?>
            </a>
            
            <div class="user-info">
                <div class="d-flex align-items-center">
                    <div class="me-3">
                        <i class="bi bi-person-circle fs-4"></i>
                    </div>
                    <div>
                        <div class="fw-bold"><?= htmlspecialchars($user['username']) ?></div>
                        <small class="opacity-75">
                            <span class="<?= getRoleColorClass($user['role']) ?>">
                                <?= ucfirst($user['role']) ?>
                            </span>
                        </small>
                    </div>
                    <div class="ms-3">
                        <a href="logout.php" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-box-arrow-right"></i> Wyloguj
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </nav>

    <div class="container mt-4">
        <!-- Welcome Section -->
        <div class="welcome-section">
            <h1 class="h3 mb-2">
                <i class="bi bi-house-door"></i>
                Dashboard
            </h1>
            <p class="mb-0"><?= getRoleWelcomeMessage($user['role']) ?></p>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-4">
                <div class="card stats-card">
                    <div class="card-body text-center">
                        <i class="bi bi-pc-display fs-1 mb-2"></i>
                        <h3><?= $stats['total'] ?></h3>
                        <p class="mb-0">Wszystkie komputery</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-success text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-check-circle fs-1 mb-2"></i>
                        <h3><?= $stats['online'] ?></h3>
                        <p class="mb-0">Online</p>
                    </div>
                </div>
            </div>
            <div class="col-md-4">
                <div class="card bg-danger text-white">
                    <div class="card-body text-center">
                        <i class="bi bi-x-circle fs-1 mb-2"></i>
                        <h3><?= $stats['offline'] ?></h3>
                        <p class="mb-0">Offline</p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Computers List -->
        <div class="row">
            <div class="col-12">
                <div class="card">
                    <div class="card-header bg-light">
                        <h5 class="mb-0">
                            <i class="bi bi-pc-display"></i>
                            Lista komputerów
                        </h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($computers as $computer): ?>
                                <div class="col-md-6 col-lg-4 mb-3">
                                    <a href="computer.php?id=<?= urlencode($computer['computer_id']) ?>" class="computer-card">
                                        <div class="card h-100 position-relative">
                                            <!-- Status Badge -->
                                            <span class="status-badge badge <?= isset($computer['status']) && $computer['status'] === 'online' ? 'status-online' : 'status-offline' ?>">
                                                <?= isset($computer['status']) && $computer['status'] === 'online' ? 'Online' : 'Offline' ?>
                                            </span>
                                            
                                            <div class="card-body">
                                                <div class="text-center mb-3">
                                                    <i class="bi bi-pc-display fs-1 text-primary"></i>
                                                </div>
                                                
                                                <h6 class="card-title text-center">
                                                    <?= htmlspecialchars($computer['computer_name']) ?>
                                                </h6>
                                                
                                                <div class="small text-muted">
                                                    <div class="mb-1">
                                                        <i class="bi bi-person"></i>
                                                        <?= htmlspecialchars($computer['owner_name']) ?>
                                                    </div>
                                                    <div>
                                                        <i class="bi bi-router"></i>
                                                        <?= htmlspecialchars($computer['ip_address']) ?>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if (empty($computers)): ?>
                            <div class="text-center py-5">
                                <i class="bi bi-inbox fs-1 text-muted"></i>
                                <h5 class="text-muted mt-3">Brak komputerów</h5>
                                <p class="text-muted">Nie znaleziono żadnych komputerów w systemie.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>