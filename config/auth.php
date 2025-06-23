<?php
// config/auth.php - Authentication System for C2 Panel
require_once 'config.php';

// Start session if not already started (like getting your school ID ready)
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

/**
 * Check if user has valid session (like checking if student is in class)
 * Returns true if user is properly logged in
 */
function isLoggedIn() {
    return isset($_SESSION['user_logged_in']) && 
           $_SESSION['user_logged_in'] === true &&
           isset($_SESSION['user_id']) &&
           isset($_SESSION['session_db_id']);
}

/**
 * Protect pages that need login (like teachers-only areas)
 * Kicks out anyone without proper credentials
 */
function requireLogin($redirectTo = 'index.php') {
    if (!isLoggedIn()) {
       // No valid session? Go back to login!
        header("Location: $redirectTo");
        exit;
    }
    
    // Also check if session is still valid in database
    if (!isSessionValid()) {
        logoutUser('session_expired');
        header("Location: $redirectTo?message=session_expired");
        exit;
    }
}

/**
 * Log user in (like giving someone a school ID card)
 * Creates both PHP session and database session record
 */
function loginUser($userId, $email, $username, $role) {
    try {
        $pdo = getDBConnection();
        
        // Create session record in database (like writing in visitor log)
        $sessionId = session_id();
        $expiresAt = date('Y-m-d H:i:s', time() + (24 * 60 * 60)); // 24 hours
        
        $stmt = $pdo->prepare("
            INSERT INTO user_sessions (user_id, session_id, ip_address, user_agent, expires_at) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        $stmt->execute([
            $userId,
            $sessionId,
            $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0',
            $_SERVER['HTTP_USER_AGENT'] ?? 'Unknown',
            $expiresAt
        ]);
        
        $sessionDbId = $pdo->lastInsertId();
        
        // Set PHP session variables (like putting info in your school ID)
        $_SESSION['user_logged_in'] = true;
        $_SESSION['user_id'] = $userId;
        $_SESSION['user_email'] = $email;
        $_SESSION['username'] = $username;
        $_SESSION['user_role'] = $role;
        $_SESSION['login_time'] = time();
        $_SESSION['session_db_id'] = $sessionDbId;
        
        // Regenerate session ID for security (like getting a new ID number)
        session_regenerate_id(true);
        
        // Update database with new session ID
        $newSessionId = session_id();
        $updateStmt = $pdo->prepare("UPDATE user_sessions SET session_id = ? WHERE id = ?");
        $updateStmt->execute([$newSessionId, $sessionDbId]);
        
        return true;
        
    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        return false;
    }
}

/**
 * Log user out (like returning school ID card)
 * Cleans up both PHP session and database records
 */
function logoutUser() {
    try {
        // Mark database session as inactive
        if (isset($_SESSION['session_db_id'])) {
            $pdo = getDBConnection();
            $stmt = $pdo->prepare("UPDATE user_sessions SET active = 0 WHERE id = ?");
            $stmt->execute([$_SESSION['session_db_id']]);
        }
    } catch (Exception $e) {
        error_log("Logout database error: " . $e->getMessage());
    }
    
    // Destroy PHP session (throw away the ID card)
    session_unset();
    session_destroy();

        // Start new session for flash message
    session_start();
    $_SESSION['logout_message'] = $message;
}

/**
 * Verify user credentials against your database
 * Like checking if someone's name is on the class list
 */
function verifyCredentials($email, $password) {
    try {
        $pdo = getDBConnection();
        
        // Look for user in your users table
        $stmt = $pdo->prepare("
            SELECT id, email, password, username, role 
            FROM users 
            WHERE email = ? 
            LIMIT 1
        ");
        
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        
        // Check if user exists
        if (!$user) {
            return false;
        }
        
        // Check if password is hashed or plain text
        $isHashed = (strlen($user['password']) >= 60 && 
                    (substr($user['password'], 0, 4) === '$2y$' || 
                     substr($user['password'], 0, 4) === '$2a$'));
        
        $passwordMatch = false;
        
        if ($isHashed) {
            // For hashed passwords, use password_verify
            $passwordMatch = password_verify($password, $user['password']);
        } else {
            // For plain text passwords - direct comparison
            $passwordMatch = (trim($password) == trim($user['password']));
        }
        
        if ($passwordMatch) {
            // Password correct! Return user info (without password for security)
            return [
                'id' => $user['id'],
                'email' => $user['email'],
                'username' => $user['username'],
                'role' => $user['role']
            ];
        }
        
        return false;
        
    } catch (Exception $e) {
        error_log("Credential verification error: " . $e->getMessage());
        return false;
    }
}

/**
 * Check if current session is still valid in database
 * Like checking if your school ID hasn't expired
 */
function isSessionValid() {
    if (!isset($_SESSION['session_db_id'])) {
        return false;
    }
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            SELECT active, expires_at 
            FROM user_sessions 
            WHERE id = ? AND active = 1
        ");
        
        $stmt->execute([$_SESSION['session_db_id']]);
        $session = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$session) {
            return false;
        }
        
        // Check if session hasn't expired
        if ($session['expires_at'] && strtotime($session['expires_at']) < time()) {
            return false;
        }
        
        return true;
        
    } catch (Exception $e) {
        error_log("Session validation error: " . $e->getMessage());
        return false;
    }
}

/**
 * Get current user information
 * Like reading your school ID card
 */
function getCurrentUser() {
    if (!isLoggedIn()) {
        return null;
    }
    
    return [
        'id' => $_SESSION['user_id'],
        'email' => $_SESSION['user_email'],
        'username' => $_SESSION['username'],
        'role' => $_SESSION['user_role'],
        'login_time' => $_SESSION['login_time']
    ];
}

/**
 * Check if user has specific role
 * Like checking if someone is a teacher or student
 */
function hasRole($requiredRole) {
    $user = getCurrentUser();
    if (!$user) return false;
    
    $roleHierarchy = ['viewer' => 1, 'operator' => 2, 'admin' => 3];
    $userLevel = $roleHierarchy[$user['role']] ?? 0;
    $requiredLevel = $roleHierarchy[$requiredRole] ?? 999;
    
    return $userLevel >= $requiredLevel;
}

/**
 * Require specific role (like teachers-only room)
 */
function requireRole($requiredRole, $redirectTo = 'index.php') {
    requireLogin($redirectTo);
    
    if (!hasRole($requiredRole)) {
         header("Location: $redirectTo?message=unauthorized");
        exit;
    }
}

/**
 * Auto-logout user after inactivity
 */
function checkInactivityTimeout($timeoutMinutes = 30) {
    if (!isLoggedIn()) {
        return;
    }
    
    $lastActivity = $_SESSION['last_activity'] ?? time();
    $timeout = $timeoutMinutes * 60;
    
    if (time() - $lastActivity > $timeout) {
        logoutUser('session_expired');
        header("Location: index.php?message=session_expired");
        exit;
    }
    
    // Update last activity
    $_SESSION['last_activity'] = time();
}

/**
 * Log command execution (like keeping track of computer usage)
 */
function logCommand($computerId, $command, $response = null, $status = 'pending') {
    $user = getCurrentUser();
    if (!$user) return false;
    
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            INSERT INTO command_logs (user_id, computer_id, command, response, status) 
            VALUES (?, ?, ?, ?, ?)
        ");
        
        return $stmt->execute([
            $user['id'],
            $computerId,
            $command,
            $response,
            $status
        ]);
        
    } catch (Exception $e) {
        error_log("Command logging error: " . $e->getMessage());
        return false;
    }
}

/**
 * Clean up expired sessions (like cleaning old visitor logs)
 * Run this periodically or on login
 */
function cleanupExpiredSessions() {
    try {
        $pdo = getDBConnection();
        $stmt = $pdo->prepare("
            UPDATE user_sessions 
            SET active = 0 
            WHERE expires_at < NOW() AND active = 1
        ");
        $stmt->execute();
        
    } catch (Exception $e) {
        error_log("Session cleanup error: " . $e->getMessage());
    }
}

/**
 * Get and clear logout message
 */
function getLogoutMessage() {
    if (isset($_SESSION['logout_message'])) {
        $message = $_SESSION['logout_message'];
        unset($_SESSION['logout_message']);
        return $message;
    }
    return null;
}
?>