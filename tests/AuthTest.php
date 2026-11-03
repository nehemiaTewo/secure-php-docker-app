<?php
use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../www/auth.php';

final class AuthTest extends TestCase
{
    private PDO $pdo;

    // Runs before every test: a fresh in-memory database with one user of each role
    protected function setUp(): void
    {
        $this->pdo = new PDO('sqlite::memory:');
        $this->pdo->exec("CREATE TABLE users (userID INTEGER PRIMARY KEY, Email TEXT, Role TEXT NOT NULL DEFAULT 'user')");
        $this->pdo->exec("INSERT INTO users (userID, Email, Role) VALUES
            (1, 'admin@test.com',   'admin'),
            (2, 'user@test.com',    'user'),
            (3, 'pending@test.com', 'pending')");
    }

    /* ---------- Who can open admin.php ---------- */

    public function testGuestCannotAccessAdminPage(): void
    {
        $this->assertFalse(canAccessAdmin($this->pdo, null));
    }

    public function testNormalUserCannotAccessAdminPage(): void
    {
        $this->assertFalse(canAccessAdmin($this->pdo, 2));
    }

    public function testPendingAdminCannotAccessAdminPage(): void
    {
        $this->assertFalse(canAccessAdmin($this->pdo, 3));
    }

    public function testApprovedAdminCanAccessAdminPage(): void
    {
        $this->assertTrue(canAccessAdmin($this->pdo, 1));
    }

    public function testDeletedOrUnknownUserCannotAccessAdminPage(): void
    {
        $this->assertFalse(canAccessAdmin($this->pdo, 999));
    }

    public function testRevokedAdminLosesAccessImmediately(): void
    {
        $this->pdo->exec("UPDATE users SET Role = 'user' WHERE userID = 1");
        $this->assertFalse(canAccessAdmin($this->pdo, 1));
    }

    /* ---------- Approve / reject / revoke rules ---------- */

    public function testApproveMakesUserAdmin(): void
    {
        $this->assertSame('admin', newRoleFor('approve', 3, 1));
    }

    public function testRejectReturnsApplicantToUser(): void
    {
        $this->assertSame('user', newRoleFor('reject', 3, 1));
    }

    public function testRevokeRemovesAdmin(): void
    {
        $this->assertSame('user', newRoleFor('revoke', 2, 1));
    }

    public function testAdminCannotChangeOwnRole(): void
    {
        $this->assertNull(newRoleFor('revoke', 1, 1));
    }

    public function testUnknownActionIsRejected(): void
    {
        $this->assertNull(newRoleFor('make_superuser', 2, 1));
    }

    /* ---------- Database setup and sign-up safety ---------- */

    public function testInitSqlGivesEveryNewAccountUserRoleByDefault(): void
    {
        $sql = file_get_contents(__DIR__ . '/../db/init.sql');
        $this->assertStringContainsString("`Role` varchar(20) NOT NULL DEFAULT 'user'", $sql);
    }

    public function testAdminRegisterAlwaysCreatesPendingAndIgnoresFormRole(): void
    {
        $src = file_get_contents(__DIR__ . '/../www/adminRegister.php');
        $this->assertStringContainsString("NOW(), 'pending')", $src);
        $this->assertStringNotContainsString("\$_POST['role']", $src);
    }
}