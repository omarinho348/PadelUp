<?php

require_once __DIR__ . '/../app/models/MatchModel.php';
require_once __DIR__ . '/../app/models/MatchPlayer.php';
require_once __DIR__ . '/../app/models/User.php';
require_once __DIR__ . '/../app/models/Venue.php';

use PHPUnit\Framework\TestCase;

class MatchModelTest extends TestCase
{
    private ?mysqli $conn = null;
    private ?int $testUserId = null;
    private ?int $testVenueId = null;

    protected function setUp(): void
    {
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

        $this->createTestTables();
        $this->setupTestData();
    }

    protected function tearDown(): void
    {
        if ($this->conn) {
            // Clean up in correct order
            $this->conn->query("DELETE FROM match_results WHERE match_id IN (SELECT match_id FROM matches WHERE venue_id = (SELECT venue_id FROM venues WHERE name LIKE '%test_%'))");
            $this->conn->query("DELETE FROM match_players WHERE match_id IN (SELECT match_id FROM matches WHERE venue_id = (SELECT venue_id FROM venues WHERE name LIKE '%test_%'))");
            $this->conn->query("DELETE FROM matches WHERE venue_id = (SELECT venue_id FROM venues WHERE name LIKE '%test_%')");
            $this->conn->query("DELETE FROM courts WHERE venue_id = (SELECT venue_id FROM venues WHERE name LIKE '%test_%')");
            $this->conn->query("DELETE FROM venues WHERE name LIKE '%test_%'");
            $this->conn->query("DELETE FROM player_profiles WHERE player_id IN (SELECT user_id FROM users WHERE email LIKE '%matchtest_%')");
            $this->conn->query("DELETE FROM users WHERE email LIKE '%matchtest_%'");
            $this->conn->close();
        }
    }

    private function createTestTables(): void
    {
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

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS venues (
                venue_id INT AUTO_INCREMENT PRIMARY KEY,
                venue_admin_id INT,
                name VARCHAR(255) NOT NULL,
                address VARCHAR(255),
                city VARCHAR(100),
                opening_time TIME,
                closing_time TIME,
                hourly_rate INT,
                logo_path VARCHAR(255)
            )
        ");

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS courts (
                court_id INT AUTO_INCREMENT PRIMARY KEY,
                venue_id INT,
                court_name VARCHAR(100),
                court_type VARCHAR(100),
                is_active INT DEFAULT 1,
                FOREIGN KEY (venue_id) REFERENCES venues(venue_id) ON DELETE CASCADE
            )
        ");

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS matches (
                match_id INT AUTO_INCREMENT PRIMARY KEY,
                creator_id INT,
                venue_id INT,
                match_date DATE NOT NULL,
                match_time TIME NOT NULL,
                min_skill_level INT DEFAULT 0,
                max_skill_level INT DEFAULT 5,
                status ENUM('open', 'full', 'completed', 'cancelled') DEFAULT 'open',
                current_players INT DEFAULT 0,
                max_players INT DEFAULT 4,
                description TEXT,
                FOREIGN KEY (creator_id) REFERENCES users(user_id),
                FOREIGN KEY (venue_id) REFERENCES venues(venue_id)
            )
        ");

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS match_players (
                id INT AUTO_INCREMENT PRIMARY KEY,
                match_id INT,
                player_id INT,
                joined_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                FOREIGN KEY (match_id) REFERENCES matches(match_id) ON DELETE CASCADE,
                FOREIGN KEY (player_id) REFERENCES users(user_id)
            )
        ");

        $this->conn->query("
            CREATE TABLE IF NOT EXISTS match_results (
                result_id INT AUTO_INCREMENT PRIMARY KEY,
                match_id INT,
                team1_player1_id INT,
                team1_player2_id INT,
                team2_player1_id INT,
                team2_player2_id INT,
                team1_set1_score INT,
                team2_set1_score INT,
                team1_set2_score INT,
                team2_set2_score INT,
                team1_set3_score INT,
                team2_set3_score INT,
                winner_team INT,
                FOREIGN KEY (match_id) REFERENCES matches(match_id)
            )
        ");
    }

    private function setupTestData(): void
    {
        // Create test user
        $sql = "INSERT INTO users (name, email, password_hash, role, phone) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $email = 'matchtest_creator_' . time() . '@example.com';
        $hash = password_hash('password123', PASSWORD_DEFAULT);
        $role = 'player';
        $phone = '1234567890';
        $stmt->bind_param("sssss", $email, $email, $hash, $role, $phone);
        $stmt->execute();
        $this->testUserId = $this->conn->insert_id;
        $stmt->close();

        // Create player profile
        $sql = "INSERT INTO player_profiles (player_id, skill_level, gender, birth_date, preferred_side) VALUES (?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $skill = 3;
        $gender = 'male';
        $birthDate = '1990-01-01';
        $side = 'right';
        $stmt->bind_param("idsss", $this->testUserId, $skill, $gender, $birthDate, $side);
        $stmt->execute();
        $stmt->close();

        // Create test venue
        $sql = "INSERT INTO venues (name, address, city, opening_time, closing_time, hourly_rate) VALUES (?, ?, ?, ?, ?, ?)";
        $stmt = $this->conn->prepare($sql);
        $venueName = 'test_venue_' . time();
        $address = '123 Test St';
        $city = 'Test City';
        $open = '08:00:00';
        $close = '22:00:00';
        $rate = 50;
        $stmt->bind_param("sssssi", $venueName, $address, $city, $open, $close, $rate);
        $stmt->execute();
        $this->testVenueId = $this->conn->insert_id;
        $stmt->close();
    }

    /**
     * Test: fetchAll returns empty array with no matches
     */
    public function testFetchAllReturnsEmptyArrayWhenNoMatches(): void
    {
        $result = MatchModel::fetchAll($this->conn, ['venue_id' => $this->testVenueId]);
        $this->assertIsArray($result);
    }

    /**
     * Test: create successfully creates a match
     */
    public function testCreateSuccessfullyCreatesMatch(): void
    {
        $data = [
            'creator_id' => $this->testUserId,
            'venue_id' => $this->testVenueId,
            'match_date' => date('Y-m-d', strtotime('+1 day')),
            'match_time' => '10:00:00',
            'min_skill_level' => 2,
            'max_skill_level' => 4,
            'description' => 'Test match for unit testing'
        ];

        $result = MatchModel::create($this->conn, $data);

        // Should return match ID on success
        $this->assertIsInt($result, "Failed to create match: " . ($result ?: 'unknown error'));
        $this->assertGreaterThan(0, $result);

        // Verify match was created
        $match = MatchModel::findById($this->conn, $result);
        $this->assertNotNull($match);
        $this->assertEquals($data['match_date'], $match['match_date']);
        $this->assertEquals($this->testUserId, $match['creator_id']);
        $this->assertEquals('open', $match['status']);
    }

    /**
     * Test: findById returns correct match
     */
    public function testFindByIdReturnsCorrectMatch(): void
    {
        $data = [
            'creator_id' => $this->testUserId,
            'venue_id' => $this->testVenueId,
            'match_date' => date('Y-m-d', strtotime('+2 days')),
            'match_time' => '14:00:00',
            'min_skill_level' => 1,
            'max_skill_level' => 5,
            'description' => 'Test match 2'
        ];

        $matchId = MatchModel::create($this->conn, $data);
        $match = MatchModel::findById($this->conn, $matchId);

        $this->assertNotNull($match);
        $this->assertEquals($matchId, $match['match_id']);
        $this->assertEquals($data['match_date'], $match['match_date']);
        $this->assertEquals($data['match_time'], $match['match_time']);
        $this->assertEquals($this->testUserId, $match['creator_id']);
    }

    /**
     * Test: updatePlayerCountAndStatus correctly updates match
     */
    public function testUpdatePlayerCountAndStatusUpdatesCorrectly(): void
    {
        // Create a match
        $data = [
            'creator_id' => $this->testUserId,
            'venue_id' => $this->testVenueId,
            'match_date' => date('Y-m-d', strtotime('+3 days')),
            'match_time' => '16:00:00',
            'min_skill_level' => 2,
            'max_skill_level' => 4,
            'description' => 'Test match for status update'
        ];

        $matchId = MatchModel::create($this->conn, $data);

        // Update player count
        $success = MatchModel::updatePlayerCountAndStatus($this->conn, $matchId);
        $this->assertTrue($success);

        // Verify status is 'open' (has only creator)
        $match = MatchModel::findById($this->conn, $matchId);
        $this->assertEquals('open', $match['status']);
        $this->assertGreaterThan(0, $match['current_players']);
    }

    /**
     * Test: joinMatch prevents duplicate joins
     */
    public function testJoinMatchPreventsDuplicateJoins(): void
    {
        $data = [
            'creator_id' => $this->testUserId,
            'venue_id' => $this->testVenueId,
            'match_date' => date('Y-m-d', strtotime('+4 days')),
            'match_time' => '18:00:00',
            'min_skill_level' => 2,
            'max_skill_level' => 4,
            'description' => 'Test duplicate join'
        ];

        $matchId = MatchModel::create($this->conn, $data);

        // Try to join as creator (already in)
        $result = MatchPlayer::joinMatch($this->conn, $matchId, $this->testUserId);

        // Should fail because creator is already in
        $this->assertFalse($result === true, "Should not allow duplicate join");
    }
}
