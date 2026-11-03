<?php
// RBAC helper functions, kept separate so they can be unit tested.

// Only a logged-in user whose Role in the database is 'admin' may open admin.php.
function canAccessAdmin(PDO $pdo, $userId): bool
{
    if (empty($userId)) {
        return false; // guest: not logged in
    }
    $stmt = $pdo->prepare("SELECT Role FROM users WHERE userID = ?");
    $stmt->execute([$userId]);
    return $stmt->fetchColumn() === 'admin';
}

// Returns the new role for an admin action, or null if the action is not allowed.
function newRoleFor(string $action, int $targetId, int $actorId): ?string
{
    if ($targetId === $actorId) {
        return null; // admins can't change their own role
    }
    $roles = ['approve' => 'admin', 'reject' => 'user', 'revoke' => 'user'];
    return $roles[$action] ?? null;
}