<?php
require_once __DIR__ . '/../core/dbh.inc.php';
require_once __DIR__ . '/../models/Venue.php';

class VenuesController
{
    public static function getAllVenues()
    {
        global $conn;
        return Venue::listAll($conn);
    }
    
    public static function searchVenues($searchTerm)
    {
        global $conn;
        $allVenues = Venue::listAll($conn);
        if (empty($searchTerm)) {
            return $allVenues;
        }
        return array_filter($allVenues, function($venue) use ($searchTerm) {
            return stripos($venue['name'], $searchTerm) !== false 
                || stripos($venue['city'], $searchTerm) !== false 
                || stripos($venue['address'], $searchTerm) !== false;
        });
    }

    public static function getVenue(int $venueId)
    {
        global $conn;
        return Venue::findById($conn, $venueId);
    }
}
?>
