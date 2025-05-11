<?php
session_start();
require_once 'config.php';

// Check if admin is logged in
if (!isset($_SESSION['admin_id'])) {
    header('Location: login.php');
    exit;
}

// Fetch counts from database
function getCount($dbconn, $query) {
    $result = pg_query($dbconn, $query);
    if ($result && pg_num_rows($result) > 0) {
        $row = pg_fetch_row($result);
        return $row[0];
    }
    return 0;
}

$num_learners = getCount($dbconn, "SELECT COUNT(*) FROM learners");
$num_teachers = getCount($dbconn, "SELECT COUNT(*) FROM teachers");
$num_books = getCount($dbconn, "SELECT COUNT(*) FROM books");
$num_borrowed_books = getCount($dbconn, "SELECT COUNT(*) FROM borrow_records WHERE status = 'borrowed'");
$num_returned_books = getCount($dbconn, "SELECT COUNT(*) FROM borrow_records WHERE status = 'returned'");

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1" />
    <title>Admin Dashboard - TinySteps</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f8f9fa;
            margin: 0;
        }
        .sidebar {
            width: 260px;
            background-color: #343a40;
            color: white;
            min-height: 100vh;
            padding: 1.5rem 1rem;
            display: flex;
            flex-direction: column;
        }
        .sidebar h3 {
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: 2px;
        }
        .sidebar a {
            color: white;
            text-decoration: none;
            display: flex;
            align-items: center;
            padding: 0.75rem 1rem;
            border-radius: 6px;
            margin-bottom: 0.5rem;
            font-size: 1.1rem;
            transition: background-color 0.3s ease;
        }
        .sidebar a i {
            margin-right: 12px;
            font-size: 1.2rem;
        }
        .sidebar a:hover, .sidebar a.active {
            background-color: #495057;
            text-decoration: none;
        }
        .content {
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: auto;
        }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
        }
        .header h2 {
            font-weight: 700;
            color: #212529;
        }
        .user-info {
            font-size: 1rem;
            color: #6c757d;
        }
        .card-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
        }
        .card {
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            transition: transform 0.3s ease, box-shadow 0.3s ease;
        }
        .card:hover {
            transform: translateY(-6px);
            box-shadow: 0 8px 20px rgba(0,0,0,0.15);
        }
        .card .card-body {
            padding: 1.8rem 2rem;
        }
        .card .card-title {
            font-weight: 600;
            font-size: 1.25rem;
            margin-bottom: 0.75rem;
            color: #495057;
        }
        .card .card-text {
            font-size: 2.5rem;
            font-weight: 700;
            color: #212529;
        }
        .bg-primary { background-color:rgb(163, 166, 171) !important; color: white !important; }
        .bg-success { background-color:rgb(131, 174, 154) !important; color: white !important; }
        .bg-info { background-color:rgb(145, 154, 156) !important; color: white !important; }
        .bg-warning { background-color:rgb(191, 188, 180) !important; color: #212529 !important; }
        .bg-secondary { background-color:rgb(138, 141, 143) !important; color: white !important; }
        .bg-dark { background-color:rgb(174, 182, 190) !important; color: white !important; }
        .bg-danger { background-color:rgb(204, 193, 194) !important; color: white !important; }
        .toggle-btns {
            margin-bottom: 1.5rem;
        }
        .toggle-btns button {
            min-width: 120px;
            font-weight: 600;
            border-radius: 30px;
            padding: 0.5rem 1.5rem;
            transition: background-color 0.3s ease;
        }
        .toggle-btns button.btn-primary {
            background-color: #0d6efd;
            border: none;
        }
        .toggle-btns button.btn-primary:hover {
            background-color: #0b5ed7;
        }
        .toggle-btns button.btn-secondary {
            background-color: #6c757d;
            border: none;
        }
        .toggle-btns button.btn-secondary:hover {
            background-color: #5c636a;
        }
        #analyticsView {
            padding: 2rem;
            background-color: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            color: #495057;
        }
        @media (max-width: 768px) {
            body {
                flex-direction: column;
            }
            .sidebar {
                width: 100%;
                min-height: auto;
                flex-direction: row;
                overflow-x: auto;
                padding: 0.5rem 1rem;
            }
            .sidebar h3 {
                display: none;
            }
            .sidebar a {
                flex: 1 0 auto;
                margin: 0 0.25rem;
                text-align: center;
                padding: 0.75rem 0.5rem;
                font-size: 0.9rem;
            }
            .sidebar a i {
                margin: 0 0 4px 0;
                font-size: 1.1rem;
            }
            .content {
                padding: 1rem 1.5rem;
            }
            .card-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1rem;
            }
            .card .card-text {
                font-size: 2rem;
            }
        }
        #sidebarToggleSidebar {
            align-self: flex-end;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
    <nav class="sidebar">
        <button id="sidebarToggleSidebar" class="btn btn-outline-light mb-3 d-md-none" aria-label="Toggle sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <h3>TinySteps Admin</h3>
        <a href="admin_dashboard.php" class="active"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_learners.php"><i class="fas fa-user-graduate"></i> Manage Learners</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php"><i class="fas fa-book"></i> Manage Books</a>
        <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="settings.php"><i class="fas fa-chart-line"></i> settings</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="header d-flex align-items-center">
            <button id="sidebarToggle" class="btn btn-outline-secondary me-3 d-md-none" aria-label="Toggle sidebar">
                <i class="fas fa-bars"></i>
            </button>
            <?php if (!empty($_SESSION['profile_image']) && file_exists($_SESSION['profile_image'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="Profile Image" style="width: 50px; height: 50px; border-radius: 50%; object-fit: cover; margin-right: 15px;">
            <?php endif; ?>
            <h2>Dashboard</h2>
            <div class="user-info ms-auto">Welcome, <?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        </div>
        <section id="cardsView" class="card-grid">
            <div class="card bg-primary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Number of Learners</h5>
                    <p class="card-text fs-3"><?php echo $num_learners; ?></p>
                </div>
            </div>
            <div class="card bg-success mb-3">
                <div class="card-body">
                    <h5 class="card-title">Number of Teachers</h5>
                    <p class="card-text fs-3"><?php echo $num_teachers; ?></p>
                </div>
            </div>
            <div class="card bg-info mb-3">
                <div class="card-body">
                    <h5 class="card-title">Number of Books</h5>
                    <p class="card-text fs-3"><?php echo $num_books; ?></p>
                </div>
            </div>
            <div class="card bg-warning mb-3">
                <div class="card-body">
                    <h5 class="card-title">Number of Borrowed Books</h5>
                    <p class="card-text fs-3"><?php echo $num_borrowed_books; ?></p>
                </div>
            </div>
            <div class="card bg-secondary mb-3">
                <div class="card-body">
                    <h5 class="card-title">Number of Returned Books</h5>
                    <p class="card-text fs-3"><?php echo $num_returned_books; ?></p>
                </div>
            </div>
        </section>
        <section id="analyticsView" style="display:none;">
            <div class="chart-grid">
                <canvas id="barChart" width="400" height="300"></canvas>
                <canvas id="lineChart" width="400" height="300"></canvas>
                <canvas id="pieChart" width="400" height="300"></canvas>
                <canvas id="doughnutChart" width="400" height="300"></canvas>
            </div>
        </section>
    </main>
    <style>
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap: 2rem;
            padding: 1rem 0;
        }
        canvas {
            background: white;
            border-radius: 12px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.1);
            width: 100% !important;
            height: 300px !important;
        }
    </style>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        const sidebar = document.querySelector('.sidebar');
        const toggleBtn = document.getElementById('sidebarToggle');
        const toggleSidebarBtn = document.getElementById('sidebarToggleSidebar');

        toggleBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        toggleSidebarBtn.addEventListener('click', () => {
            sidebar.classList.toggle('collapsed');
        });

        // Get dashboard view preference from PHP session
        const dashboardView = '<?php echo $_SESSION["dashboard_view"] ?? "cards"; ?>';

        const cardsView = document.getElementById('cardsView');
        const analyticsView = document.getElementById('analyticsView');

        if (dashboardView === 'charts') {
            cardsView.style.display = 'none';
            analyticsView.style.display = 'grid';

            // Data for charts
            const labels = ['Learners', 'Teachers', 'Books', 'Borrowed Books', 'Returned Books'];
            const dataValues = [
                <?php echo $num_learners; ?>,
                <?php echo $num_teachers; ?>,
                <?php echo $num_books; ?>,
                <?php echo $num_borrowed_books; ?>,
                <?php echo $num_returned_books; ?>
            ];
            const backgroundColors = [
                'rgba(13, 110, 253, 0.7)',
                'rgba(25, 135, 84, 0.7)',
                'rgba(13, 202, 240, 0.7)',
                'rgba(255, 193, 7, 0.7)',
                'rgba(108, 117, 125, 0.7)'
            ];
            const borderColors = [
                'rgba(13, 110, 253, 1)',
                'rgba(25, 135, 84, 1)',
                'rgba(13, 202, 240, 1)',
                'rgba(255, 193, 7, 1)',
                'rgba(108, 117, 125, 1)'
            ];

            // Common data object for charts
            const commonData = {
                labels: labels,
                datasets: [{
                    label: 'Counts',
                    data: dataValues,
                    backgroundColor: backgroundColors,
                    borderColor: borderColors,
                    borderWidth: 1
                }]
            };

            // Bar Chart
            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: commonData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Bar Chart - Preschool Data Overview'
                        }
                    }
                }
            });

            // Line Chart
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: commonData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Line Chart - Preschool Data Overview'
                        }
                    }
                }
            });

            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'pie',
                data: commonData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: 'Pie Chart - Preschool Data Overview'
                        }
                    }
                }
            });

            // Doughnut Chart
            const doughnutCtx = document.getElementById('doughnutChart').getContext('2d');
            new Chart(doughnutCtx, {
                type: 'doughnut',
                data: commonData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: 'Doughnut Chart - Preschool Data Overview'
                        }
                    }
                }
            });

            // Radar Chart
            const radarCtx = document.getElementById('radarChart').getContext('2d');
            new Chart(radarCtx, {
                type: 'radar',
                data: commonData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Radar Chart - Preschool Data Overview'
                        }
                    },
                    scales: {
                        r: {
                            angleLines: { display: true },
                            suggestedMin: 0,
                            suggestedMax: Math.max(...dataValues) + 10
                        }
                    }
                }
            });

            // Polar Area Chart
            const polarAreaCtx = document.getElementById('polarAreaChart').getContext('2d');
            new Chart(polarAreaCtx, {
                type: 'polarArea',
                data: commonData,
                options: {
                    responsive: true,
                    plugins: {
                        legend: { position: 'top' },
                        title: {
                            display: true,
                            text: 'Polar Area Chart - Preschool Data Overview'
                        }
                    }
                }
            });

        } else {
            cardsView.style.display = 'grid';
            analyticsView.style.display = 'none';
        }
    </script>
</body>
</html>
