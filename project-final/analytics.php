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
    <title>Ratang Bana Pre School</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet" />
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet" />
    <style>
        body {
            min-height: 100vh;
            display: flex;
            flex-direction: row;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: whitesmoke;
            margin: 0;
        }
        .sidebar {
            width: 358.5px;
            background:rgb(0, 98, 115);
            color: #aaa;
            min-height: 100vh;
            padding: 1.5rem 1rem;
            display: flex;
            border-top-right-radius: 8rem;
            border-bottom-right-radius: 8rem;
            flex-direction: column;
        }
        .sidebar h3 {
            background:linear-gradient(90deg,#aaa,#333,silver,rgb(70, 0, 73),rgb(70, 0, 73),rgb(1, 157, 189),rgb(0, 63, 73),rgb(0, 204, 204),rgb(0, 128, 36),rgb(92, 1, 117),transparent,transparent,transparent);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-size:400% 300%;
            font-weight: 700;
            font-size: 1.8rem;
            margin-bottom: 2rem;
            text-align: center;
            letter-spacing: 2px;
            
            animation: logo 10s infinite;
        }
        @keyframes logo{
            0%{
                background-position:0%;
            }
            50%{
                background-position:100%;
            }
            100%{
                background-position:0%;
            }
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
            background-color:rgb(0, 85, 111);
            text-decoration: none;
            color:white;
        }
        .content {
            flex-grow: 1;
            padding: 2.5rem 3rem;
            overflow-y: auto;
        }
        .rounded-circle{
            width: 60px; 
            height: 60px; 
            border-radius: 50%; 
            object-fit: cover; 
            margin-right: 15px;
            border:2px solid silver;
            box-shadow: 0 3px 10px rgba(0, 0, 0, 0.56);
            transition:all 1s ease-in-out;
         }
         .rounded-circle:hover{
            transform:scale(1.5) rotateZ(360deg);
             box-shadow: 0 3px 10px rgb(0, 0, 0);
         }
        .header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 2rem;
            padding-bottom:5px;
            border-bottom:3px solid rgb(0, 98, 115);
        }
        .header h2 {
            font-weight: 700;
            color:rgb(0, 98, 115);
        }
        .user-info {
            font-size: 1rem;
            color: #6c757d;
        }
        .chart-grid {
            display: grid;
            grid-template-columns: repeat(4, 1fr);
            gap:1rem;
            padding: 1rem;
            padding-bottom:9.8rem;
            border-bottom:3px solid rgb(0, 98, 115);
        }
        canvas {
            background: transparent;
            border-radius: 12px;
            width: 100% !important;
            height: 300px !important;
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
            .chart-grid {
                grid-template-columns: repeat(auto-fit, minmax(180px, 1fr));
                gap: 1rem;
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
        <h3>Manage</h3>
        <a href="analytics.php"><i class="fas fa-tachometer-alt"></i> Dashboard</a>
        <a href="manage_learners.php"><i class="fas fa-user-graduate"></i> Manage Learners</a>
        <a href="manage_teachers.php"><i class="fas fa-chalkboard-teacher"></i> Manage Teachers</a>
        <a href="manage_book.php"><i class="fas fa-book"></i> Manage Books</a>
        <a href="manage_borrow_records.php"><i class="fas fa-book-reader"></i> Borrow Records</a>
        <a href="settings.php"><i class="fas fa-gear"></i> settings</a>
        <a href="login.php?action=logout"><i class="fas fa-sign-out-alt"></i> Logout</a>
    </nav>
    <main class="content">
        <div class="header d-flex align-items-center">
            <?php if (!empty($_SESSION['profile_image']) && file_exists($_SESSION['profile_image'])): ?>
                <img src="<?= htmlspecialchars($_SESSION['profile_image']) ?>" alt="Profile Image" style="" class="rounded-circle" >
            <?php endif; ?>
            <h2>Analytics</h2>
            <div class="user-info ms-auto">Welcome <?php echo htmlspecialchars($_SESSION['full_name']); ?></div>
        </div>
        <section id="analyticsView" style="margin-top: 2rem;">
            <div class="chart-grid">
                <canvas id="barChart" width="400" height="300"></canvas>
                <canvas id="lineChart" width="400" height="300"></canvas>
                <canvas id="pieChart" width="400" height="300"></canvas>
                <canvas id="doughnutChart" width="400" height="300"></canvas>
            </div>
        </section>
         <small style="color:#aaa;"> Last update: <?php echo date('y/m/d')?>  <a href="admin_dashboard.php" style="text-decoration:none;color:purple">Cards <i class="fa-solid fa-arrow-right"></i></a></small>
    </main>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script>
        if (typeof Chart === 'undefined') {
            console.error('Chart.js library is not loaded.');
        } else {
            console.log('Chart.js library loaded successfully.');
        }

        // Data for charts
        const labels = ['Learners', 'Teachers', 'Books', 'Borrowed Books'];
        const dataValues = [
            <?php echo $num_learners; ?>,
            <?php echo $num_teachers; ?>,
            <?php echo $num_books; ?>,
            <?php echo $num_borrowed_books; ?>
           
        ];
        // Colors for Bar Chart
        const barBackgroundColors = [
            'rgb(22, 2, 94)',
            'rgb(101, 112, 2)',
            'rgb(240, 0, 44)',
            'rgb(0, 154, 154)'
        ];
        const barBorderColors = [
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)'
          
        ];

        // Colors for Line Chart
        const lineBackgroundColors = [
            'rgb(255, 0, 0)',
            'rgb(255, 242, 0)',
            'rgb(30, 255, 0)',
            'rgb(0, 255, 255)'
           
        ];
        const lineBorderColors = [
            'rgba(255, 159, 64, 1)',
            'rgba(54, 162, 235, 1)',
            'rgba(255, 205, 86, 1)',
            'rgba(201, 203, 207, 1)'
           
        ];

        // Colors for Pie Chart
        const pieBackgroundColors = [
            'rgb(116, 0, 120)',
            'rgb(5, 18, 114)',
            'rgb(251, 180, 0)',
            'rgb(255, 0, 98)'
           
        ];
        const pieBorderColors = [
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)'
           
        ];

        // Colors for Doughnut Chart
        const doughnutBackgroundColors = [
            'rgb(0, 4, 133)',
            'rgb(255, 128, 0)',
            'rgb(255, 0, 55)',
            'rgb(30, 255, 0)'
           
        ];
        const doughnutBorderColors = [
           'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)',
            'rgb(255, 255, 255)'
           
        ];

        try {
            // Bar Chart
            const barCtx = document.getElementById('barChart').getContext('2d');
            new Chart(barCtx, {
                type: 'bar',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Counts',
                        data: dataValues,
                        backgroundColor: barBackgroundColors,
                        borderColor: barBorderColors,
                        borderWidth: 1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Bar Chart - Preschool Data Overview'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Line Chart
            const lineCtx = document.getElementById('lineChart').getContext('2d');
            new Chart(lineCtx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Counts',
                        data: dataValues,
                        backgroundColor: lineBackgroundColors,
                        borderColor: lineBorderColors,
                        borderWidth: 1,
                        fill: false,
                        tension: 0.1
                    }]
                },
                options: {
                    responsive: true,
                    plugins: {
                        legend: { display: false },
                        title: {
                            display: true,
                            text: 'Line Chart - Preschool Data Overview'
                        }
                    },
                    scales: {
                        y: {
                            beginAtZero: true
                        }
                    }
                }
            });

            // Pie Chart
            const pieCtx = document.getElementById('pieChart').getContext('2d');
            new Chart(pieCtx, {
                type: 'pie',
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Counts',
                        data: dataValues,
                        backgroundColor: pieBackgroundColors,
                        borderColor: pieBorderColors,
                        borderWidth: 1
                    }]
                },
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
                data: {
                    labels: labels,
                    datasets: [{
                        label: 'Counts',
                        data: dataValues,
                        backgroundColor: doughnutBackgroundColors,
                        borderColor: doughnutBorderColors,
                        borderWidth: 1
                    }]
                },
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
        } catch (error) {
            console.error('Error initializing charts:', error);
        }
    </script>
</body>
</html>
