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
        return Venue::search($conn, $searchTerm);
    }

    public static function getVenue(int $venueId)
    {
        global $conn;
        return Venue::findById($conn, $venueId);
    }
}
?>
