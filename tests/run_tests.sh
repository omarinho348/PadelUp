#!/bin/bash
# Quick test runner for PadelUp

# Stay in tests directory (where vendor and phpunit are)
SCRIPT_DIR="$(dirname "$0")"
cd "$SCRIPT_DIR"

echo "========================================="
echo "PadelUp Unit Test Runner"
echo "========================================="
echo ""

# Ensure test database exists
echo "Setting up test database..."
/Applications/XAMPP/xamppfiles/bin/mysql -u root \
  -S /Applications/XAMPP/xamppfiles/var/mysql/mysql.sock \
  -e "CREATE DATABASE IF NOT EXISTS padelup_test;" 2>/dev/null

echo "Running tests..."
echo ""


echo "========================================="
echo "Test Results Summary"
echo "========================================="

# Run all tests from tests directory
php vendor/bin/phpunit


