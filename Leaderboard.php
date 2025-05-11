<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Leaderboard - GREENTRACK</title>
    <link rel="stylesheet" href="Leaderboard.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
</head>
<body>
    <header>
        <div class="logo-container">
            <img src="LOGO.png" class="logo" />
            <h1><span>GREEN</span>TRACK</h1>
        </div>
        <div class="nav-links">
            <a href="Homepage.php" class="home-btn">Home</a>
        </div>
    </header>

    <div class="container">
        <div class="leaderboard-section">
            <h2>Leaderboard</h2>
            
            <div class="tabs">
                <button class="tab-btn active" data-tab="trees">
                    <i class="fas fa-tree"></i>
                    Trees Planted
                </button>
                <button class="tab-btn" data-tab="co2">
                    <i class="fas fa-leaf"></i>
                    CO₂ Reduction
                </button>
                <button class="tab-btn" data-tab="donations">
                    <i class="fas fa-hand-holding-heart"></i>
                    Donations
                </button>
            </div>

            <div class="tab-content">
                <!-- Trees Planted Leaderboard -->
                <div class="tab-pane active" id="trees">
                    <div class="leaderboard-list">
                        <?php
                        require_once "procedure.php";
                        $crud = new Crud();
                        $leaderboard = $crud->getLeaderboard();
                        
                        $rank = 1;
                        foreach ($leaderboard as $entry) {
                            $medal = '';
                            if ($rank == 1) $medal = '🥇';
                            elseif ($rank == 2) $medal = '🥈';
                            elseif ($rank == 3) $medal = '🥉';
                            
                            echo '<div class="leaderboard-item">';
                            echo '<div class="rank">' . $medal . ' ' . $rank . '</div>';
                            echo '<div class="user-info">';
                            echo '<div class="username">' . htmlspecialchars($entry['username']) . '</div>';
                            echo '<div class="group">' . (!empty($entry['group_members']) ? htmlspecialchars($entry['group_members']) : '—') . '</div>';
                            echo '</div>';
                            echo '<div class="score">' . number_format($entry['total_trees']) . ' trees</div>';
                            echo '</div>';
                            
                            $rank++;
                        }
                        ?>
                    </div>
                </div>

                <!-- CO2 Reduction Leaderboard -->
                <div class="tab-pane" id="co2">
                    <div class="leaderboard-list">
                        <?php
                        $rank = 1;
                        foreach ($leaderboard as $entry) {
                            $medal = '';
                            if ($rank == 1) $medal = '🥇';
                            elseif ($rank == 2) $medal = '🥈';
                            elseif ($rank == 3) $medal = '🥉';
                            
                            echo '<div class="leaderboard-item">';
                            echo '<div class="rank">' . $medal . ' ' . $rank . '</div>';
                            echo '<div class="user-info">';
                            echo '<div class="username">' . htmlspecialchars($entry['username']) . '</div>';
                            echo '<div class="group">' . (!empty($entry['group_members']) ? htmlspecialchars($entry['group_members']) : '—') . '</div>';
                            echo '</div>';
                            echo '<div class="score">' . number_format($entry['total_co2'], 2) . ' kg</div>';
                            echo '</div>';
                            
                            $rank++;
                        }
                        ?>
                    </div>
                </div>

                <!-- Donations Leaderboard -->
                <div class="tab-pane" id="donations">
                    <div class="leaderboard-list">
                        <div class="coming-soon">
                            <i class="fas fa-gift"></i>
                            <h3>Coming Soon</h3>
                            <p>Donation leaderboard will be available soon!</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabBtns = document.querySelectorAll('.tab-btn');
            const tabPanes = document.querySelectorAll('.tab-pane');

            tabBtns.forEach(btn => {
                btn.addEventListener('click', () => {
                    // Remove active class from all buttons and panes
                    tabBtns.forEach(b => b.classList.remove('active'));
                    tabPanes.forEach(p => p.classList.remove('active'));

                    // Add active class to clicked button and corresponding pane
                    btn.classList.add('active');
                    const tabId = btn.getAttribute('data-tab');
                    document.getElementById(tabId).classList.add('active');
                });
            });
        });
    </script>
</body>
</html> 