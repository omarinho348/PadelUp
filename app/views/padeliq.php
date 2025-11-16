<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PadelIQ - AI-Powered Skill Ratings</title>
    <link rel="stylesheet" href="../../public/styling/styles.css">
    <link rel="stylesheet" href="../../public/styling/padeliq.css">
</head>
<body>
    <?php include __DIR__ . '/partials/navbar.php'; ?>
    <div class="container">
        <div class="main-heading padeliq-header">
            <div class="header-content">
                <h1>PadelIQ <span class="accent">Skill Ratings</span></h1>
                <p>Your AI-powered performance analysis and skill level tracker.</p>
            </div>
        </div>

        <div class="padeliq-grid">
            <!-- Left Column: Rating & Progression -->
            <aside class="padeliq-sidebar">
                <div class="padeliq-card rating-card">
                    <h3>Current PadelIQ Rating</h3>
                    <div class="rating-level">Intermediate</div>
                    <div class="rating-score">78<span class="rating-score-of">/100</span></div>
                    <p class="rating-description">Based on recent match results, opponent skill levels, and positive feedback.</p>
                </div>

                <div class="padeliq-card progression-card">
                    <h3>Rating Progression</h3>
                    <div class="progression-chart">
                        <div class="chart-bar" style="height: 60%;">
                            <span class="bar-value">60</span>
                            <span class="bar-label">Jul</span>
                        </div>
                        <div class="chart-bar" style="height: 70%;">
                            <span class="bar-value">70</span>
                            <span class="bar-label">Aug</span>
                        </div>
                        <div class="chart-bar" style="height: 65%;">
                            <span class="bar-value">65</span>
                            <span class="bar-label">Sep</span>
                        </div>
                        <div class="chart-bar" style="height: 78%;">
                            <span class="bar-value">78</span>
                            <span class="bar-label">Oct</span>
                        </div>
                    </div>
                </div>
            </aside>

            <!-- Right Column: Match History & Feedback -->
            <main class="padeliq-main">
                <div class="padeliq-card">
                    <h3>Recent Match History</h3>
                    <table class="match-history-table">
                        <thead>
                            <tr>
                                <th>Date</th>
                                <th>Opponent</th>
                                <th>Score</th>
                                <th>Result</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>2025-10-15</td>
                                <td>Omar Akram</td>
                                <td>6-4, 6-3</td>
                                <td class="match-outcome-win">Win</td>
                            </tr>
                            <tr>
                                <td>2025-10-12</td>
                                <td>Seif Makled</td>
                                <td>3-6, 7-5, 4-6</td>
                                <td class="match-outcome-loss">Loss</td>
                            </tr>
                            <tr>
                                <td>2025-10-08</td>
                                <td>Yehia Shorim</td>
                                <td>7-6, 6-2</td>
                                <td class="match-outcome-win">Win</td>
                            </tr>
                             <tr>
                                <td>2025-10-05</td>
                                <td>Omar Alaa</td>
                                <td>6-0, 6-1</td>
                                <td class="match-outcome-win">Win</td>
                            </tr>
                        </tbody>
                    </table>
                </div>

                <div class="padeliq-card">
                    <h3>Performance Stats</h3>
                    <div class="stats-grid">
                        <div class="stat-item">
                            <div class="label">Win Rate</div>
                            <div class="value win">75%</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Loss Rate</div>
                            <div class="value loss">25%</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Tournaments Won</div>
                            <div class="value">2</div>
                        </div>
                        <div class="stat-item">
                            <div class="label">Matches Played</div>
                            <div class="value">16</div>
                        </div>
                    </div>
                </div>

                <div class="padeliq-card">
                    <h3>Recent Tournament Results</h3>
                    <ul class="tournament-results-list">
                        <li><span class="tournament-name">PadelUp Open 2023</span> - 1st Place</li>
                        <li><span class="tournament-name">MIU Cup</span> - Quarter-Finals</li>
                        <li><span class="tournament-name">Cairo Championship</span> - 2nd Place</li>
                    </ul>
                </div>
            </main>
        </div>

    </div>
<?php include 'Includes/footer.php'; ?>