<?php

require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/PlayerProfile.php';

use PHPUnit\Framework\TestCase;

class UserTest extends TestCase
{
    private ?mysqli $conn = null;

    protected function setUp(): void
    {
        // Use test database connection with XAMPP socket
        $this->conn = new mysqli(
            $_ENV['DB_HOST'] ?? 'localhost',
            $_ENV['DB_USER'] ?? 'root',
            $_ENV['DB_PASS'] ?? '',
            $_ENV['DB_NAME'] ?? 'padelup_test',
            0,
            $_ENV['DB_SOCKET'] ?? '/Applications/XAMPP/xamppfiles/var/mysql/mysql.sock'
        );

        if ($this->conn->connect_error) {
            $this->markTestSkipped('Database connection failed: ' . $this->conn->connect_error);
        }

        // Create test tables if needed
        $this->createTestTables();
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up test data
            $this->conn->query("DELETE FROM player_profiles WHERE player_id IN (SELECT user_id FROM users WHERE email LIKE '%test_%')");
            $this->conn->query("DELETE FROM users WHERE email LIKE '%test_%'");
            $this->conn->close();
        }
    }

    private function createTestTables(): void
    {
        // Create users table if not exists
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS users (
                user_id INT AUTO_INCREMENT PRIMARY KEY,
                name VARCHAR(255) NOT NULL,
                email VARCHAR(255) UNIQUE NOT NULL,
                password_hash VARCHAR(255) NOT NULL,
                role ENUM('player', 'coach', 'venue_admin') DEFAULT 'player',
                phone VARCHAR(20),
                created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
            )
        ");

        // Create player_profiles table if not exists
        $this->conn->query("
            CREATE TABLE IF NOT EXISTS player_profiles (
                player_id INT PRIMARY KEY,
                skill_level INT DEFAULT 0,
                gender VARCHAR(50),
                birth_date DATE,
                preferred_side VARCHAR(50),
                FOREIGN KEY (player_id) REFERENCES users(user_id) ON DELETE CASCADE
            )
        ");
    }

    /**
     * Test: findByEmail returns null when user doesn't exist
     */
    public function testFindByEmailReturnsNullForNonexistentUser(): void
    {
        $result = User::findByEmail($this->conn, 'nonexistent_' . time() . '@test.com');
        $this->assertNull($result);
    }

    /**
     * Test: createPlayerUser successfully creates player with profile
     */
    public function testCreatePlayerUserSuccessfullyCreatesPlayerAndProfile(): void
    {
        $userData = [
            'name' => 'Test Player',
            'email' => 'test_' . time() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'player',
            'phone' => '1234567890'
        ];

        $profileData = [
            'skill_level' => 3,
            'gender' => 'male',
            'birth_date' => '1990-01-01',
            'preferred_side' => 'right'
        ];

        $result = User::createPlayerUser($this->conn, $userData, $profileData);

        // Should return true on success
        $this->assertTrue($result === true, "Failed to create player user: " . ($result ?: 'unknown error'));

        // Verify user was created
        $user = User::findByEmail($this->conn, $userData['email']);
        $this->assertNotNull($user);
        $this->assertEquals($userData['name'], $user['name']);
        $this->assertEquals('player', $user['role']);

        // Verify profile was created
        if ($user) {
            $profile = PlayerProfile::findByUserId($this->conn, $user['user_id']);
            $this->assertNotNull($profile);
            $this->assertEquals($profileData['skill_level'], $profile['skill_level']);
            $this->assertEquals($profileData['gender'], $profile['gender']);
        }
    }

    /**
     * Test: findById returns user data by ID
     */
    public function testFindByIdReturnsUserData(): void
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test_findbyid_' . time() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'player',
            'phone' => '1234567890'
        ];

        $profileData = [
            'skill_level' => 2,
            'gender' => 'female',
            'birth_date' => '1995-05-15',
            'preferred_side' => 'left'
        ];

        User::createPlayerUser($this->conn, $userData, $profileData);
        $createdUser = User::findByEmail($this->conn, $userData['email']);

        // Now test findById
        $foundUser = User::findById($this->conn, $createdUser['user_id']);

        $this->assertNotNull($foundUser);
        $this->assertEquals($createdUser['user_id'], $foundUser['user_id']);
        $this->assertEquals($userData['name'], $foundUser['name']);
    }

    /**
     * Test: updateUser successfully updates user information
     */
    public function testUpdateUserSuccessfullyUpdatesData(): void
    {
        $userData = [
            'name' => 'Original Name',
            'email' => 'test_update_' . time() . '@example.com',
            'password_hash' => password_hash('password123', PASSWORD_DEFAULT),
            'role' => 'player',
            'phone' => '1111111111'
        ];

        $profileData = [
            'skill_level' => 1,
            'gender' => 'male',
            'birth_date' => '1992-03-20',
            'preferred_side' => 'right'
        ];

        User::createPlayerUser($this->conn, $userData, $profileData);
        $user = User::findByEmail($this->conn, $userData['email']);

        // Update user
        $updateData = [
            'name' => 'Updated Name',
            'phone' => '2222222222'
        ];

        $success = User::updateUser($this->conn, $user['user_id'], $updateData);
        $this->assertTrue($success);

        // Verify update
        $updatedUser = User::findById($this->conn, $user['user_id']);
        $this->assertEquals('Updated Name', $updatedUser['name']);
        $this->assertEquals('2222222222', $updatedUser['phone']);
    }
}
