# PadelUp Unit Tests

Automated unit tests for core PadelUp functionality.

## Setup

### 1. Install PHPUnit

```bash
cd /Applications/XAMPP/xamppfiles/htdocs/PadelUp
composer require --dev phpunit/phpunit
```

Or install globally:
```bash
composer global require phpunit/phpunit
```

### 2. Create Test Database

```sql
CREATE DATABASE padelup_test;
```

### 3. Configure Test Environment

Edit `.env.test` to match your test database credentials:
```
DB_HOST=localhost
DB_USER=root
DB_PASS=your_password
DB_NAME=padelup_test
```

## Running Tests

### Run all tests:
```bash
phpunit
```

### Run specific test file:
```bash
phpunit tests/UserTest.php
```

### Run with verbose output:
```bash
phpunit --verbose
```

### Generate coverage report:
```bash
phpunit --coverage-html=coverage/
```

## Test Suites

### 1. UserTest.php
Tests for User model functionality:
- `testFindByEmailReturnsNullForNonexistentUser` - Verifies null return for missing user
- `testCreatePlayerUserSuccessfullyCreatesPlayerAndProfile` - Verifies user + profile creation with transaction
- `testFindByIdReturnsUserData` - Verifies user lookup by ID
- `testUpdateUserSuccessfullyUpdatesData` - Verifies user data updates

**Coverage:**
- User::findByEmail()
- User::findById()
- User::createPlayerUser()
- User::updateUser()
- PlayerProfile::findByUserId()

### 2. MatchModelTest.php
Tests for Match model functionality:
- `testFetchAllReturnsEmptyArrayWhenNoMatches` - Verifies empty array handling
- `testCreateSuccessfullyCreatesMatch` - Verifies match creation
- `testFindByIdReturnsCorrectMatch` - Verifies match lookup
- `testUpdatePlayerCountAndStatusUpdatesCorrectly` - Verifies status updates
- `testJoinMatchPreventsDuplicateJoins` - Verifies duplicate join prevention

**Coverage:**
- MatchModel::create()
- MatchModel::findById()
- MatchModel::fetchAll()
- MatchModel::updatePlayerCountAndStatus()
- MatchPlayer::joinMatch()

## Test Database Cleanup

Tests automatically:
1. Create required tables on setup
2. Insert test data
3. Delete test data on teardown

No manual cleanup required between test runs.

## Adding New Tests

1. Create new test file in `tests/` directory
2. Extend `PHPUnit\Framework\TestCase`
3. Implement `setUp()` and `tearDown()` methods
4. Add test methods starting with `test`
5. Add file path to `phpunit.xml`

Example:
```php
<?php

use PHPUnit\Framework\TestCase;

class MyTest extends TestCase
{
    protected function setUp(): void
    {
        // Initialize test resources
    }

    protected function tearDown(): void
    {
        // Clean up test resources
    }

    public function testSomething(): void
    {
        $this->assertTrue(true);
    }
}
```

## Continuous Integration

Configure CI/CD pipeline to run:
```bash
phpunit --coverage-clover=coverage.xml
```

## Notes

- Tests use isolated test database (`padelup_test`)
- Each test creates and cleans up its own data
- Tests can run in any order
- No external API calls or dependencies
