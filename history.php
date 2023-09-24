<?php
session_start();

if (!$_SESSION['logged']) {
    header("refresh:1;url=index.php");
    die("Acesso restrito.");
}

function getImage($directory, $value)
{
    if (strcmp($value, 'On') == 0 || strcmp($value, 'Open') == 0 || strcmp($value, 'Yes') == 0)
        return $directory . '/images/2';

    if (strcmp($value, 'Off') == 0 || strcmp($value, 'Closed') == 0 || strcmp($value, 'No') == 0)
        return $directory . '/images/1';


    $range = file($directory . '/range.txt', FILE_IGNORE_NEW_LINES);
    $images = new FilesystemIterator($directory . '/images', FilesystemIterator::SKIP_DOTS);
    $imgs_num = iterator_count($images);

    for ($i = 1; $i <= $imgs_num; $i++) {
        if ((($range[1] - $range[0]) / $imgs_num) * $i + $range[0] >= $value)
            return $directory . '/images/' . $i;
    }
}

?>
<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-KK94CHFLLe+nY2dmCWGMq91rCGa5gtU4mk92HdvYe+M/SXH301p5ILy+dN9+nJOZ" crossorigin="anonymous">
    <link href="css/history.css" rel="stylesheet">
    <link href="css/navbar.css" rel="stylesheet">
    <link rel="icon" type="image/x-icon" href="src/favicon.svg">
    <title>Document</title>
</head>

<body>

    <!-- Background Image -->
    <div class="bg" alt="Blue and Purple Waves"></div>

    <!-- Desktop Navbar -->
    <div class="desktopnav d-xl-flex">
        <div>
            <a href="dashboard.php">
                <img class="navlogo" src="src/favicon.svg" alt="Company Logo">
                <h1>Warehouse</h1>
            </a>
            <a href="dashboard.php">
                <span><ion-icon class="icon" name="home-outline"></ion-icon></span>
                <span class="pagename">Home</span>
            </a>
            <a class="actualpage" href="history.php">
                <span><ion-icon class="icon" name="time-outline"></ion-icon></span>
                <span class="pagename">History</span>
            </a>
            <a href="logout.php">
                <span><ion-icon class="icon" name="exit-outline"></ion-icon></span>
                <span class="pagename">Exit</span>
            </a>
        </div>

        <div class="userdiv">
            <span><ion-icon class="icon" name="person-outline"></ion-icon></span>
            <div class="dropup-center dropup">
                <button class="btn btn-secondary dropdown-toggle dropupstyle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $_SESSION['username'] ?>
                </button>
                <div class="dropdown-menu" data-bs-theme="dark">
                    <a class="dropdown-item logoutbtn" href="logout.php">Log Out</a>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Mobile Navbar -->

    <div class="mobile-top-margin d-xl-none"></div>

    <div class="mobilenav d-xl-none">
        <div class="pagesdiv">
            <a href="dashboard.php">
                <img class="navlogo" src="src/favicon.svg" alt="Company Logo">
                <h1 class="d-md-flex">Warehouse</h1>
            </a>
            <a href="dashboard.php">
                <ion-icon class="icon" name="home-outline"></ion-icon>
                <span class="pagename d-sm-flex">Home</span>
            </a>
            <a class="actualpage" href="history.php">
                <ion-icon class="icon" name="time-outline"></ion-icon>
                <span class="pagename d-sm-flex">History</span>
            </a>
            <a href="logout.php">
                <ion-icon class="icon" name="exit-outline"></ion-icon>
                <span class="pagename d-sm-flex">Exit</span>
            </a>
        </div>

        <div class="userdiv">
            <ion-icon class="icon" name="person-outline"></ion-icon>
            <div class="dropdown-center dropdown">
                <button class="btn btn-secondary dropdown-toggle dropdownstyle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                    <?php echo $_SESSION['username'] ?>
                </button>
                <div class="dropdown-menu" data-bs-theme="dark">
                    <a class="dropdown-item logoutbtn" href="logout.php">Log Out</a>
                </div>
            </div>
        </div>
    </div>

    
    <!-- Main content -->
    <div class="container">
        <h1 class="title">History</h1>

        <?php
        //Code to generate tables to each sensors/actuators
        //If a get requeste are made, will only generate a table to this specific sensor/actuators
        if (isset($_GET['name']) && isset($_GET['type'])) {

            foreach (new DirectoryIterator('./api/files/') as $apifiles) {
                if ($apifiles->isDot() || $apifiles->isFile()) continue;
                if (strcmp(substr($apifiles, 1), strtolower($_GET['type'])) == 0) {
                    $_GET['type'] = $apifiles;
                    break;
                }
            }

            echo '  <div class="separator"></div>

                    <hr>
                    <h2 class="subtitle">- ' . substr($_GET['type'], 1) . '</h2>

                    <div class="row">
                        <div class="card">
                        <div class="card-body">
                        <table class="table">
                            <thead>
                                <tr>
                                    <th scope="col">Icon</th>
                                    <th scope="col">Name</th>
                                    <th scope="col">Value</th>
                                    <th scope="col">Update Date</th>
                                </tr>';
            $directory = "api/files/" . $_GET['type'] . "/" . strtolower($_GET['name']);
            $log = file($directory . "/log.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            $_GET['name'] = ucfirst($_GET['name']);
            for ($i = 0; $i < count($log); $i++) {
                $loginfo =  explode(";", $log[$i]);
                echo '<tr>
                                    <td scope="col"><img src="' . getImage($directory, trim($loginfo[1])) . '.svg" class="image"></td>
                                    <td scope="col">' . $_GET['name'] . '</td>
                                    <td scope="col">' . $loginfo[1] . $measurement . '</td>
                                    <td scope="col">' . $loginfo[0] . '</td>
                                    </tr>';
            }

            echo '          </thead>
                        </table>
                        </div>
                        </div>
                    </div>';
        } else {

            foreach (new DirectoryIterator('./api/files/') as $apifiles) {
                if ($apifiles->isDot() || $apifiles->isFile()) continue;

                echo '  <div class="separator"></div>
                    <hr>
                    <h2 class="subtitle">- ' . substr($apifiles, 1) . '</h2>
                    <div class="container">
                        <div class="card">
                            <div class="card-body">
                                <table class="table table-sm">
                                    <thead>
                                        <tr>
                                            <th scope="col">Icon</th>
                                            <th scope="col">Name</th>
                                            <th scope="col">Value</th>
                                            <th scope="col">Update Date</th>
                                        </tr>';
                foreach (new DirectoryIterator('./api/files/' . $apifiles) as $fileInfo) {

                    if ($fileInfo->isDot() || $fileInfo->isFile()) continue;
                    $directory = "api/files/" . $apifiles . "/" . $fileInfo;
                    $name = file_get_contents($directory . "/name.txt");
                    $time = file_get_contents($directory . "/time.txt");
                    $measurement = file_get_contents($directory . "/measurement.txt");
                    $log = file($directory . "/log.txt", FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);

                    for ($i = 0; $i < count($log); $i++) {
                        $loginfo =  explode(";", $log[$i]);
                        echo '<tr>
                                <td scope="col"><img src="' . getImage($directory, $loginfo[1]) . '.svg" class="image"></td>
                                <td scope="col">' . $name . '</td>
                                <td scope="col">' . $loginfo[1] . $measurement . '</td>
                            <td scope="col">' . $loginfo[0] . '</td>
                        </tr>';
                    }
                }
                echo '  </thead>
                    </table>
                    </div>
                    </div>
                    </div>';
            }
        }
        ?>
    </div>
    
    <!-- Scripts Import -->
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.7/dist/umd/popper.min.js" integrity="sha384-zYPOMqeu1DAVkHiLqWBUTcbYfZ8osu1Nd6Z89ify25QV9guujx43ITvfi12/QExE" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha3/dist/js/bootstrap.min.js" integrity="sha384-Y4oOpwW3duJdCWv5ly8SCFYWqFDsfob/3GkgExXKV4idmbt98QcxXYs9UoXAB7BZ" crossorigin="anonymous"></script>
    <script type="module" src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.esm.js"></script>
    <script nomodule src="https://unpkg.com/ionicons@7.1.0/dist/ionicons/ionicons.js"></script>
</body>

</html>