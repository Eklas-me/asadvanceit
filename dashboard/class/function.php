<?php

class AdvancedAdmin
{

    private $conn;

    public function __construct()
    {

        // Set the timezone
        date_default_timezone_set('Asia/Dhaka');
        // Local Database Credentials
        // $dbHost = 'localhost';
        // $dbUser = 'root';
        // $dbPass = '';
        // $dbName = 'asadvanc_advanced it';

        // Online Database Credentials
        $dbHost = 'localhost';
        $dbUser = 'asadvanc_eklas';
        $dbPass = '$kVNN@f1+FS';
        $dbName = 'asadvanc_advanced it';

        // Using MySQLi's object-oriented style
        $this->conn = mysqli_connect($dbHost, $dbUser, $dbPass, $dbName);
        if (!$this->conn) {
            die("Database Connection Error!");
        }

        // Set MySQL session timezone to +06:00 (Asia/Dhaka)
        $this->conn->query("SET time_zone = '+06:00'");
    }


    // Login Method (Admin/User)
    public function user_login($data)
    {
        $email = $data['email'];
        $password = md5($data['password']); // Ensure to use secure password hashing methods

        // Check if the user exists in the users table
        $userQuery = "SELECT * FROM users WHERE email = '$email' AND password = '$password'";
        $userResult = mysqli_query($this->conn, $userQuery);

        if (mysqli_num_rows($userResult) > 0) {
            $user_data = mysqli_fetch_assoc($userResult);
            session_start();
            $_SESSION['userId'] = $user_data['id'];
            $_SESSION['userName'] = $user_data['name'];
            $_SESSION['userEmail'] = $user_data['email'];
            $_SESSION['userRole'] = $user_data['role'];  // 'user'
            // $_SESSION['profile_pic'] = $user_data['profile_photo']; 

            header("location: user_dashboard.php"); // Redirect user to their dashboard
            exit();
        }

        // Check if the user exists in the admins table
        $adminQuery = "SELECT * FROM admin_info WHERE email = '$email' AND password = '$password'";
        $adminResult = mysqli_query($this->conn, $adminQuery);

        if (mysqli_num_rows($adminResult) > 0) {
            $admin_data = mysqli_fetch_assoc($adminResult);
            session_start();
            $_SESSION['userId'] = $admin_data['id'];
            $_SESSION['userName'] = $admin_data['name'];
            $_SESSION['userEmail'] = $admin_data['email'];
            $_SESSION['userRole'] = $admin_data['role'];  // 'admin'

            header("location: admin_dashboard.php"); // Redirect admin to their dashboard
            exit();
        }

        return "Invalid login credentials"; // If neither user nor admin found
    }


    // Logout Method (Admin/User)
    public function user_logout()
    {
        session_start();
        unset($_SESSION['userId']);
        unset($_SESSION['userName']);
        unset($_SESSION['userRole']);
        session_destroy();
        header("location:index.php");
        exit();
    }


    public function display_user()
    {
        $query = "SELECT * FROM users";
        $display_user = mysqli_query($this->conn, $query);
        return $display_user;
    }

    public function display_admin()
    {
        $query = "SELECT * FROM admin_info";
        $display_admin = mysqli_query($this->conn, $query);
        return $display_admin;
    }


    public function getProfilePicture($userId, $userRole)
    {
        $profilePhoto = "";

        // Query based on role
        if ($userRole == 'admin') {
            $query = "SELECT profile_photo FROM admin_info WHERE id = ?";
        } else {
            $query = "SELECT profile_photo FROM users WHERE id = ?";
        }

        // Prepare and execute the query
        $stmt = $this->conn->prepare($query); // Assuming $this->conn is your existing DB connection
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($profilePhoto);
        $stmt->fetch();
        $stmt->close();

        // Return the profile photo or a default avatar if none is available
        return !empty($profilePhoto) ? $profilePhoto : 'default-avatar.png';
    }

    // public function display_all_tokens($selected_date = null, $user_id = null) {
    //   $current_date = date('Y-m-d');
    //   $date_filter = $selected_date ? $selected_date : $current_date;

    //   // Query with optional filters for date and user
    //   $query = "SELECT user_name, live_token, insert_time FROM live_tokens WHERE DATE(insert_time) = ?";
    //   if ($user_id) {
    //       $query .= " AND user_id = ?";
    //   }

    //   // Prepare statement to prevent SQL injection
    //   $stmt = $this->conn->prepare($query);
    //   if ($user_id) {
    //       $stmt->bind_param('ss', $date_filter, $user_id);
    //   } else {
    //       $stmt->bind_param('s', $date_filter);
    //   }

    //   $stmt->execute();
    //   $result = $stmt->get_result();

    //   // Return the result set for further processing
    //   return $result;
    // }

    // public function display_all_tokens($selected_date = null, $user_id = null, $time_from = '00:00', $time_to = '23:59') {
    //         // Set timezone
    //     date_default_timezone_set('Asia/Dhaka');
    //     $current_date = date('Y-m-d');
    //     $date_filter = $selected_date ? $selected_date : $current_date;

    //     // Append seconds for datetime format
    //     $time_from_full = $time_from . ':00';
    //     $time_to_full = $time_to . ':00';

    //     $start_datetime = $date_filter . ' ' . $time_from_full;
    //     $end_datetime = $date_filter . ' ' . $time_to_full;

    //     $query = "SELECT user_name, live_token, insert_time FROM live_tokens WHERE insert_time BETWEEN ? AND ?";

    //     if ($user_id) {
    //         $query .= " AND user_id = ?";
    //     }

    //     $stmt = $this->conn->prepare($query);

    //     if ($user_id) {
    //         $stmt->bind_param('sss', $start_datetime, $end_datetime, $user_id);
    //     } else {
    //         $stmt->bind_param('ss', $start_datetime, $end_datetime);
    //     }

    //     $stmt->execute();
    //     $result = $stmt->get_result();

    //     return $result;
    // }

    // public function display_all_tokens($selected_date = null, $user_id = null, $time_from = '00:00', $time_to = '23:59') {
    //     // Set timezone
    //     date_default_timezone_set('Asia/Dhaka');
    //     $current_date = date('Y-m-d');
    //     $date_filter  = $selected_date ?: $current_date;

    //     // Append seconds for datetime format
    //     $time_from_full = $time_from . ':00';
    //     $time_to_full   = $time_to . ':00';

    //     // Check if time range crosses midnight
    //     if (strtotime($time_to_full) < strtotime($time_from_full)) {
    //         // Example: 19:00 → 07:00 (next day)
    //         $start_datetime = $date_filter . ' ' . $time_from_full;
    //         $end_datetime   = date('Y-m-d', strtotime($date_filter . ' +1 day')) . ' ' . $time_to_full;
    //     } else {
    //         // Normal same-day range
    //         $start_datetime = $date_filter . ' ' . $time_from_full;
    //         $end_datetime   = $date_filter . ' ' . $time_to_full;
    //     }

    //     // Prepare SQL
    //     $query = "SELECT user_name, live_token, insert_time 
    //               FROM live_tokens 
    //               WHERE insert_time BETWEEN ? AND ?";

    //     if ($user_id) {
    //         $query .= " AND user_id = ?";
    //     }

    //     $stmt = $this->conn->prepare($query);

    //     if ($user_id) {
    //         $stmt->bind_param('sss', $start_datetime, $end_datetime, $user_id);
    //     } else {
    //         $stmt->bind_param('ss', $start_datetime, $end_datetime);
    //     }

    //     $stmt->execute();
    //     return $stmt->get_result();
    // }

    public function display_all_tokens($user_id = null, $start_datetime = null, $end_datetime = null)
    {
        date_default_timezone_set('Asia/Dhaka');

        // Default range: today 7 AM → 7 AM tomorrow
        $start_datetime = $start_datetime ?: date('Y-m-d 07:00');
        $end_datetime   = $end_datetime ?: date('Y-m-d 07:00', strtotime('+1 day'));

        // Ensure seconds
        if (strlen($start_datetime) == 16) $start_datetime .= ':00';
        if (strlen($end_datetime) == 16) $end_datetime .= ':00';

        $query = "SELECT user_name, live_token, insert_time 
              FROM live_tokens 
              WHERE insert_time BETWEEN ? AND ?";

        if (!empty($user_id)) {
            $query .= " AND user_id = ?";
        }

        $stmt = $this->conn->prepare($query);

        if (!empty($user_id)) {
            $stmt->bind_param('sss', $start_datetime, $end_datetime, $user_id);
        } else {
            $stmt->bind_param('ss', $start_datetime, $end_datetime);
        }

        $stmt->execute();
        return $stmt->get_result();
    }

    public function add_live_token($data)
    {
        $token     = trim($data['tinder_token']);
        $user_id   = $data['user_id'];
        $user_name = $data['user_name'];
        $user_role = $data['user_role'];

        if (empty($token)) return "No token provided.";

        // Check last 7 days for duplicate token
        $check_query = "SELECT COUNT(*) AS cnt 
                    FROM live_tokens 
                    WHERE user_id = ? 
                      AND live_token = ? 
                      AND insert_time >= (CURDATE() - INTERVAL 6 DAY + INTERVAL 7 HOUR)";
        $stmt = $this->conn->prepare($check_query);
        $stmt->bind_param('is', $user_id, $token);
        $stmt->execute();
        $res = $stmt->get_result();
        $row = $res->fetch_assoc();
        $stmt->close();

        if ($row['cnt'] > 0) {
            return "Duplicate token! This token was already added in the last 7 days.";
        }

        // Insert the token
        $insert_query = "INSERT INTO live_tokens (user_id, user_name, live_token, insert_time, user_type) 
                     VALUES (?, ?, ?, NOW(), ?)";
        $stmt = $this->conn->prepare($insert_query);
        $stmt->bind_param('ssss', $user_id, $user_name, $token, $user_role);
        if ($stmt->execute()) {
            $stmt->close();
            return "Token added successfully!";
        } else {
            $error = $stmt->error;
            $stmt->close();
            return "Error adding token: " . $error;
        }
    }



    // public function count_tokens_today() {
    //     $todayStart = date('Y-m-d 07:00:00'); // আজ সকাল 7টা
    //     $tomorrowStart = date('Y-m-d 07:00:00', strtotime('+1 day')); // কাল সকাল 7টা

    //     $query = "SELECT COUNT(*) AS total_tokens 
    //               FROM live_tokens 
    //               WHERE insert_time >= '$todayStart' AND insert_time < '$tomorrowStart'";

    //     $result = mysqli_query($this->conn, $query);
    //     if (!$result) {
    //         die("Query Error: " . mysqli_error($this->conn));
    //     }

    //     $row = mysqli_fetch_assoc($result);
    //     return $row['total_tokens'];
    // }

    // public function count_workers_today() {
    //     $todayStart = date('Y-m-d 07:00:00');
    //     $tomorrowStart = date('Y-m-d 07:00:00', strtotime('+1 day'));

    //     $query = "SELECT COUNT(DISTINCT user_id) AS total_workers 
    //               FROM live_tokens 
    //               WHERE insert_time >= '$todayStart' AND insert_time < '$tomorrowStart'";

    //     $result = mysqli_query($this->conn, $query);
    //     if (!$result) {
    //         die("Query Error: " . mysqli_error($this->conn));
    //     }

    //     $row = mysqli_fetch_assoc($result);
    //     return $row['total_workers'];
    // }

    // public function count_account_today() {
    //     $todayStart = date('Y-m-d 07:00:00');
    //     $tomorrowStart = date('Y-m-d 07:00:00', strtotime('+1 day'));

    //     $query = "SELECT COUNT(*) AS total_account 
    //               FROM live_tokens 
    //               WHERE insert_time >= '$todayStart' AND insert_time < '$tomorrowStart'";

    //     $result = mysqli_query($this->conn, $query);
    //     if (!$result) {
    //         die("Query Error: " . mysqli_error($this->conn));
    //     }

    //     $row = mysqli_fetch_assoc($result);
    //     return $row['total_account'];
    // }


    // =========================
    // Shift-safe functions
    // =========================

    private function getShiftBoundaries()
    {
        $now = new DateTime();

        // আজকের 7 AM
        $shiftStart = new DateTime($now->format('Y-m-d') . ' 07:00:00');

        // যদি এখন shiftStart-এর আগে (রাত 12 AM–7 AM), তখন shiftStart হবে গতকের 7 AM
        if ($now < $shiftStart) {
            $shiftStart->modify('-1 day');
        }

        // shiftEnd = পরবর্তী 7 AM
        $shiftEnd = clone $shiftStart;
        $shiftEnd->modify('+1 day');

        return [$shiftStart->format('Y-m-d H:i:s'), $shiftEnd->format('Y-m-d H:i:s')];
    }

    // =========================
    // Count total tokens today (7 AM–7 AM shift)
    // =========================
    public function count_tokens_today()
    {
        list($shiftStart, $shiftEnd) = $this->getShiftBoundaries();

        $query = "SELECT COUNT(*) AS total_tokens 
              FROM live_tokens 
              WHERE insert_time >= '$shiftStart' AND insert_time < '$shiftEnd'";

        $result = mysqli_query($this->conn, $query);
        if (!$result) die("Query Error: " . mysqli_error($this->conn));

        $row = mysqli_fetch_assoc($result);
        return $row['total_tokens'];
    }

    // =========================
    // Count distinct workers today (7 AM–7 AM shift)
    // =========================
    public function count_workers_today()
    {
        list($shiftStart, $shiftEnd) = $this->getShiftBoundaries();

        $query = "SELECT COUNT(DISTINCT user_id) AS total_workers 
              FROM live_tokens 
              WHERE insert_time >= '$shiftStart' AND insert_time < '$shiftEnd'";

        $result = mysqli_query($this->conn, $query);
        if (!$result) die("Query Error: " . mysqli_error($this->conn));

        $row = mysqli_fetch_assoc($result);
        return $row['total_workers'];
    }

    // =========================
    // Count total accounts today (7 AM–7 AM shift)
    // =========================
    public function count_account_today()
    {
        list($shiftStart, $shiftEnd) = $this->getShiftBoundaries();

        $query = "SELECT COUNT(*) AS total_account 
              FROM live_tokens 
              WHERE insert_time >= '$shiftStart' AND insert_time < '$shiftEnd'";

        $result = mysqli_query($this->conn, $query);
        if (!$result) die("Query Error: " . mysqli_error($this->conn));

        $row = mysqli_fetch_assoc($result);
        return $row['total_account'];
    }



    // Count today's tokens (07:00 AM today → 07:00 AM tomorrow)
    public function my_token_count_today()
    {
        $user_id = $_SESSION['userId'];

        // =========================
        // Night-shift safe boundaries
        // =========================
        $now = new DateTime();
        $shiftStart = new DateTime($now->format('Y-m-d') . ' 07:00:00');

        if ($now < $shiftStart) {
            // রাত 12 AM–7 AM: shiftStart হবে গতকের 7 AM
            $shiftStart->modify('-1 day');
        }

        $shiftEnd = clone $shiftStart;
        $shiftEnd->modify('+1 day'); // পরবর্তী 7 AM

        $shiftStartStr = $shiftStart->format('Y-m-d H:i:s');
        $shiftEndStr = $shiftEnd->format('Y-m-d H:i:s');

        // =========================
        // Prepare query
        // =========================
        $query = "SELECT COUNT(*) AS total_tokens 
              FROM live_tokens 
              WHERE user_id = ? 
              AND insert_time >= ? 
              AND insert_time < ?";

        if ($stmt = mysqli_prepare($this->conn, $query)) {
            mysqli_stmt_bind_param($stmt, "iss", $user_id, $shiftStartStr, $shiftEndStr);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row['total_tokens'];
        } else {
            die("Query Error: " . mysqli_error($this->conn));
        }
    }



    // Count yesterday's tokens (07:00 AM yesterday → 07:00 AM today)
    public function my_token_count_yesterday()
    {
        $user_id = $_SESSION['userId'];

        // =========================
        // Night-shift safe boundaries
        // =========================
        $now = new DateTime();

        // আজকের 7 AM
        $today_7am = new DateTime($now->format('Y-m-d') . ' 07:00:00');

        if ($now < $today_7am) {
            // রাত 12 AM–7 AM: আজকের 7 AM মানে আসলে রাত shift-এর শেষে, তাই shift start = গতকের 7 AM
            $today_7am->modify('-1 day');
        }

        $yesterday_7am = clone $today_7am;
        $yesterday_7am->modify('-1 day');

        $yesterdayStr = $yesterday_7am->format('Y-m-d H:i:s');
        $todayStr = $today_7am->format('Y-m-d H:i:s');

        // =========================
        // Query
        // =========================
        $query = "SELECT COUNT(*) AS total_tokens 
              FROM live_tokens 
              WHERE user_id = ? 
              AND insert_time >= ? 
              AND insert_time < ?";

        if ($stmt = mysqli_prepare($this->conn, $query)) {
            mysqli_stmt_bind_param($stmt, "sss", $user_id, $yesterdayStr, $todayStr);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row['total_tokens'];
        } else {
            die("Query Error: " . mysqli_error($this->conn));
        }
    }


    public function my_token_count_this_month()
    {
        // Get the current user's ID from session
        $user_id = $_SESSION['userId'];

        // Get start (this month 1st day at 07:00 AM) and end (next month 1st day at 07:00 AM)
        $start = date("Y-m-01 07:00:00");
        $end   = date("Y-m-01 07:00:00", strtotime("+1 month"));

        // Query for this month token count
        $query = "SELECT COUNT(*) AS total_tokens 
              FROM live_tokens 
              WHERE insert_time >= ? 
              AND insert_time < ? 
              AND user_id = ?";

        if ($stmt = mysqli_prepare($this->conn, $query)) {
            mysqli_stmt_bind_param($stmt, "ssi", $start, $end, $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row['total_tokens'];
        } else {
            die("Query Error: " . mysqli_error($this->conn));
        }
    }


    // Count lifetime tokens (no date restriction, all-time)
    public function my_token_count_lifetime()
    {
        $user_id = $_SESSION['userId'];
        $query = "SELECT COUNT(*) AS total_tokens 
              FROM live_tokens 
              WHERE user_id = ?";

        if ($stmt = mysqli_prepare($this->conn, $query)) {
            mysqli_stmt_bind_param($stmt, "i", $user_id);
            mysqli_stmt_execute($stmt);
            $result = mysqli_stmt_get_result($stmt);
            $row = mysqli_fetch_assoc($result);
            return $row['total_tokens'];
        } else {
            die("Query Error: " . mysqli_error($this->conn));
        }
    }

    // public function display_my_tokens($current_user_id, $selected_date = null) {
    //   // If a specific date is provided, filter by user_id and date
    //   $query = "SELECT * FROM live_tokens WHERE user_id = '$current_user_id'";
    //   if ($selected_date) {
    //     $query .= " AND DATE(`insert_time`) = '$selected_date'"; // token_date should be the date column in live_tokens table
    //   }
    //   $live_token = mysqli_query($this->conn, $query);
    //   return $live_token;
    // }

    public function display_my_tokens($current_user_id, $selected_date)
    {
        date_default_timezone_set('Asia/Dhaka');

        // Default range: selected date 07:00 → next day 07:00
        $start_datetime = date('Y-m-d 07:00:00', strtotime($selected_date));
        $end_datetime   = date('Y-m-d 07:00:00', strtotime($selected_date . ' +1 day'));

        $query = "SELECT * FROM live_tokens 
              WHERE user_id = '$current_user_id' 
              AND insert_time >= '$start_datetime' 
              AND insert_time < '$end_datetime'";

        return mysqli_query($this->conn, $query);
    }

    public function delete_my_token($current_user_id, $token_id)
    {
        $query = "DELETE FROM live_tokens 
              WHERE id = '$token_id' AND user_id = '$current_user_id'";
        return mysqli_query($this->conn, $query);
    }



    // public function export_tokens_to_text($date, $user_id = null) {
    //     $fileName = "tokens_" . date("Y-m-d") . ".txt";
    //     $filePath = "./tokens/" . $fileName;

    //     $query = "
    //         SELECT live_token, user_name 
    //         FROM live_tokens 
    //         WHERE DATE(insert_time) = '$date'
    //     ";

    //     if ($user_id) {
    //         $query .= " AND user_id = $user_id";
    //     }

    //     $query .= " ORDER BY user_name";

    //     $result = mysqli_query($this->conn, $query);

    //     $file = fopen($filePath, 'w');

    //     if ($file && $result) {
    //         fwrite($file, "Tokens for Date: $date\n\n");

    //         $currentUser = '';
    //         while ($row = mysqli_fetch_assoc($result)) {
    //             $userName = $row['user_name'];
    //             $token = $row['live_token'];

    //             if ($currentUser !== $userName) {
    //                 fwrite($file, "\nUser: $userName\n");
    //                 $currentUser = $userName;
    //             }

    //             // Write the entire token string as is
    //             fwrite($file, $token . "\n");
    //         }

    //         fclose($file);
    //         return $filePath;
    //     } else {
    //         return false;
    //     }
    // }

    // public function export_tokens_to_text($date, $user_id = null, $time_from = '00:00', $time_to = '23:59') {
    //     $fileName = "tokens_" . date("Y-m-d_H-i-s") . ".txt";  
    //     $filePath = "./tokens/" . $fileName;

    //     // Time range formatting
    //     $start_datetime = $date . ' ' . $time_from . ':00';
    //     $end_datetime   = $date . ' ' . $time_to . ':00';

    //     // Base query without username
    //     $query = "
    //         SELECT live_token 
    //         FROM live_tokens 
    //         WHERE insert_time BETWEEN ? AND ?
    //     ";

    //     // Filter by specific user_id if given
    //     if ($user_id) {
    //         $query .= " AND user_id = ?";
    //     }

    //     $stmt = $this->conn->prepare($query);

    //     if ($user_id) {
    //         $stmt->bind_param('sss', $start_datetime, $end_datetime, $user_id);
    //     } else {
    //         $stmt->bind_param('ss', $start_datetime, $end_datetime);
    //     }

    //     $stmt->execute();
    //     $result = $stmt->get_result();

    //     $file = fopen($filePath, 'w');

    //     if ($file && $result) {
    //         // Header info
    //         fwrite($file, "Tokens for Date: $date, Time Range: $time_from - $time_to\n\n");

    //         // Write each token in a new line
    //         while ($row = $result->fetch_assoc()) {
    //             fwrite($file, $row['live_token'] . "\n");
    //         }

    //         fclose($file);
    //         return $filePath;
    //     } else {
    //         return false;
    //     }
    // }

    public function export_tokens_to_text($user_id = null, $start_datetime = null, $end_datetime = null)
    {
        date_default_timezone_set('Asia/Dhaka');

        // Default range: yesterday 7 AM → today 7 AM
        $start_datetime = $start_datetime ?: date('Y-m-d 07:00', strtotime('-1 day'));
        $end_datetime   = $end_datetime   ?: date('Y-m-d 07:00');

        // Ensure seconds
        if (strlen($start_datetime) == 16) $start_datetime .= ':00';
        if (strlen($end_datetime) == 16) $end_datetime .= ':00';

        $fileName = "tokens_" . date("Y-m-d_H-i-s") . ".txt";
        $filePath = "./tokens/" . $fileName;

        // SQL query
        $query = "SELECT live_token FROM live_tokens WHERE insert_time BETWEEN ? AND ?";
        if (!empty($user_id)) {
            $query .= " AND user_id = ?";
        }

        $stmt = $this->conn->prepare($query);
        if (!empty($user_id)) {
            $stmt->bind_param('sss', $start_datetime, $end_datetime, $user_id);
        } else {
            $stmt->bind_param('ss', $start_datetime, $end_datetime);
        }

        $stmt->execute();
        $result = $stmt->get_result();

        $file = fopen($filePath, 'w');
        if ($file && $result) {
            while ($row = $result->fetch_assoc()) {
                fwrite($file, $row['live_token'] . "\n");
            }
            fclose($file);
            return $filePath;
        } else {
            return false;
        }
    }





    public function add_workers($data)
    {
        $name = $data['name'];
        $email = $data['email'];
        $phone = $data['phone'];
        $password = md5($data['password']);
        $profile_photo = $_FILES['profile_photo']['name'];
        $temp_pp = $_FILES['profile_photo']['tmp_name'];

        // Upload profile photo if provided
        if (!empty($profile_photo)) {
            $target_dir = "uploads/";
            $target_file = $target_dir . basename($profile_photo);
            move_uploaded_file($temp_pp, $target_file);
        } else {
            $profile_photo = ""; // Set to empty if no photo uploaded
        }

        $role = $data['role'];

        // Insert into the appropriate table based on the role
        if ($role === 'admin') {
            $query = "INSERT INTO admin_info (name, email, phone, password, profile_photo) VALUES ('$name', '$email', '$phone', '$password', '$profile_photo')";
        } else { // Default to 'user'
            $query = "INSERT INTO users (name, email, phone, password, profile_photo) VALUES ('$name', '$email', '$phone', '$password', '$profile_photo')";
        }

        // Execute the query
        if (mysqli_query($this->conn, $query)) {
            return "User added successfully!";
        } else {
            return "Error: " . mysqli_error($this->conn);
        }
    }


    public function display_workers()
    {
        $query = "
        SELECT id, name FROM admin_info
        UNION
        SELECT id, name FROM users
        ORDER BY name ASC
    ";
        $result = mysqli_query($this->conn, $query);

        if (!$result) {
            die("Query failed: " . mysqli_error($this->conn));
        }

        return $result;
    }


    public function display_worker_by_id($id)
    {
        // Sanitize the id to prevent SQL injection
        $id = mysqli_real_escape_string($this->conn, $id);

        // Query to fetch user details by ID from both admin_info and users tables
        $query = "SELECT id, name, email, phone, profile_photo, role FROM users WHERE id = '$id' ";

        $result = mysqli_query($this->conn, $query);

        // Check if a result is found
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result); // Fetch as associative array
        } else {
            return null; // Return null if no user is found with the given id
        }
    }


    public function display_workers_id($id)
    {
        // Prepare the SQL query with LEFT JOIN
        $query = "
        SELECT u.id, u.name, u.email, u.phone, u.profile_photo, 'user' AS role
        FROM users u
        WHERE u.id = ?
        UNION ALL
        SELECT a.id, a.name, a.email, a.phone, a.profile_photo, 'admin' AS role
        FROM admin_info a
        WHERE a.id = ?
    ";

        // Prepare the statement
        $stmt = mysqli_prepare($this->conn, $query);

        // Bind the parameter for both queries
        mysqli_stmt_bind_param($stmt, "ii", $id, $id);

        // Execute the statement
        mysqli_stmt_execute($stmt);

        // Get the result
        $result = mysqli_stmt_get_result($stmt);

        // Fetch the user data
        if (mysqli_num_rows($result) > 0) {
            return mysqli_fetch_assoc($result); // Return the first match
        } else {
            return null; // Return null if no match is found
        }
    }




    public function update_person_by_id($id, $role, $name, $email, $phone, $profile_photo_name)
    {
        // Determine table based on role
        if ($role === 'user') {
            $table = 'users';
        } elseif ($role === 'admin') {
            $table = 'admin_info';
        } else {
            return false; // Invalid role
        }

        $query = "UPDATE $table SET name = ?, email = ?, phone = ?, profile_photo = ? WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        if (!$stmt) {
            echo "DB Prepare Error: " . $this->conn->error;
            return false;
        }

        $stmt->bind_param("ssssi", $name, $email, $phone, $profile_photo_name, $id);

        if ($stmt->execute()) {
            return true;
        } else {
            echo "DB Execute Error: " . $stmt->error;
            return false;
        }
    }





    public function display_all_users()
    {
        $query = " SELECT * FROM users";
        $result = mysqli_query($this->conn, $query);
        return $result;
    }


    public function add_notification($data)
    {
        $title = $data["notification_title"];
        $message = $data['message'];

        // Set the time to Asia/Dhaka
        date_default_timezone_set('Asia/Dhaka');
        $current_time = date('Y-m-d H:i:s');

        // Prepare the query with placeholders to avoid SQL injection
        $query = "INSERT INTO notifications (title, message, status, created_at) VALUES (?, ?, 'unread', ?)";

        // Use prepared statements
        if ($stmt = mysqli_prepare($this->conn, $query)) {
            // Bind the parameters
            mysqli_stmt_bind_param($stmt, "sss", $title, $message, $current_time);

            // Execute the statement
            if (mysqli_stmt_execute($stmt)) {
                return "Notification added successfully!";
            } else {
                return "Error: " . mysqli_stmt_error($stmt);
            }

            // Close the prepared statement
            mysqli_stmt_close($stmt);
        } else {
            return "Error preparing query: " . mysqli_error($this->conn);
        }
    }


    public function get_notification()
    {
        $query = "SELECT * FROM notifications ORDER BY created_at DESC";
        $result = mysqli_query($this->conn, $query);
        return $result;
    }

    public function getUnreadNotificationCount()
    {
        $query = "SELECT COUNT(*) as count FROM notifications WHERE status = 'unread'";
        $result = mysqli_query($this->conn, $query);
        $data = mysqli_fetch_assoc($result);
        return $data['count'];
    }


    public function markNotificationAsRead($notificationId)
    {
        // Example of SQL query to update the notification
        $query = "UPDATE notifications SET status = 'read' WHERE id = ?";
        $stmt = $this->conn->prepare($query);
        $stmt->bind_param('i', $notificationId);
        if ($stmt->execute()) {
            echo "Notification updated successfully";
        } else {
            echo "Error updating notification";
        }
    }
    public function timeAgo($timestamp)
    {
        $datetime1 = new DateTime($timestamp, new DateTimeZone('Asia/Dhaka'));
        $datetime2 = new DateTime("now", new DateTimeZone('Asia/Dhaka'));

        $interval = $datetime1->diff($datetime2);

        if ($interval->y == 0 && $interval->m == 0 && $interval->d == 0 && $interval->h == 0 && $interval->i == 0) {
            return "just now";
        }
        if ($interval->y > 0) {
            return $interval->y . ' year' . ($interval->y > 1 ? 's' : '') . ' ago';
        } elseif ($interval->m > 0) {
            return $interval->m . ' month' . ($interval->m > 1 ? 's' : '') . ' ago';
        } elseif ($interval->d > 0) {
            return $interval->d . ' day' . ($interval->d > 1 ? 's' : '') . ' ago';
        } elseif ($interval->h > 0) {
            return $interval->h . ' hour' . ($interval->h > 1 ? 's' : '') . ' ago';
        } elseif ($interval->i > 0) {
            return $interval->i . ' minute' . ($interval->i > 1 ? 's' : '') . ' ago';
        } else {
            return $interval->s . ' second' . ($interval->s > 1 ? 's' : '') . ' ago';
        }
    }




    public function delete_worker($id)
    {
        $query = "DELETE FROM users WHERE id = '$id'";
        if (mysqli_query($this->conn, $query)) {
            return "Worker deleted successfully!";
        } else {
            return "Error: " . mysqli_error($this->conn);
        }
    }
    
        public function get_workers_report($start_date, $end_date)
    {
        $query = "
        SELECT u.id as user_id,
               u.name as worker_name,
               u.profile_photo,
               COUNT(DISTINCT lt.live_token) as total_tokens,
               MAX(lt.insert_time) as last_update
        FROM live_tokens lt
        JOIN users u ON lt.user_id = u.id
        WHERE lt.insert_time BETWEEN ? AND ?
        GROUP BY u.id, u.name, u.profile_photo
        ORDER BY total_tokens DESC, u.name ASC
    ";

        $stmt = $this->conn->prepare($query);
        $stmt->bind_param("ss", $start_date, $end_date);
        $stmt->execute();
        $res = $stmt->get_result();
        $data = [];
        while ($row = $res->fetch_assoc()) {
            $data[] = $row;
        }
        $stmt->close();
        return $data;
    }
    
    public function count_active_last30min()
{
    $query = "
        SELECT COUNT(DISTINCT user_id) AS active_count
        FROM live_tokens
        WHERE insert_time >= (NOW() - INTERVAL 30 MINUTE)
    ";
    $result = $this->conn->query($query);
    $row = $result->fetch_assoc();
    return $row['active_count'] ?? 0;
}


 
    
}
