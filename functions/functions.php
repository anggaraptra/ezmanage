<?php
require_once 'config.php';

// matikan error reporting
mysqli_report(MYSQLI_REPORT_OFF);

// koneksi ke database
if (!$mysqli = mysqli_connect(DBHOST, DBUSER, DBPASS, DBNAME)) {
    if (MODE == 'development') {
        // jika mode development
        exit('Gagal Koneksi: ' . mysqli_connect_error());
    } else {
        // show http 500 error
        http_response_code(500);
        // simpan error di log file
        exit();
    }
}

// fungsi untuk menjalankan query
function dbquery($sql)
{
    global $mysqli;
    // eksekusi query
    if (!$result = mysqli_query($mysqli, $sql)) {
        if (MODE == 'development') {
            // jika mode development
            exit('Gagal Query: ' . mysqli_error($mysqli));
        } else {
            // show http 500 error
            http_response_code(500);
            // simpan error di log file
            exit();
        }
    }

    // kembalikan nilai hasil query
    return $result;
}

// fungsi untuk menampilkan pesan flash
function setFlash($key, $message, $type = 'success')
{
    $_SESSION['flash'][$key] = [
        'message' => $message,
        'type' => $type
    ];
}

// fungsi untuk mengambil pesan flash
function getFlash($key)
{
    if (isset($_SESSION['flash'][$key])) {
        $flash = $_SESSION['flash'][$key];
        unset($_SESSION['flash'][$key]); // Hanya tampil sekali
        $alertClass = '';
        switch ($flash['type']) {
            case 'success':
                $alertClass = 'bg-green-100 border border-green-400 text-green-700';
                break;
            case 'info':
                $alertClass = 'bg-blue-100 border border-blue-400 text-blue-700';
                break;
            case 'error':
            default:
                $alertClass = 'bg-red-100 border border-red-400 text-red-700';
                break;
        }
        // button close
        $closeBtn = '<button type="button" onclick="this.parentElement.remove()" class="ml-4 text-xl font-bold focus:outline-none">&times;</button>';
        return "<div class=\"$alertClass px-4 py-3 rounded flex items-center justify-between\">" .
            "<span>" . htmlspecialchars($flash['message']) . "</span>" . $closeBtn .
            "</div>";
    }
    return '';
}

// Cek apakah user sudah login
function cek_session()
{
    // Timeout dalam detik (misal: 1800 detik = 30 menit)
    $timeout = 1800;

    if (!isset($_SESSION['login'])) {
        setFlash('auth', 'Silakan login terlebih dahulu!', 'info');
        header("Location: login_page.php");
        exit();
    }

    // Cek timeout session
    if (isset($_SESSION['LAST_ACTIVITY']) && (time() - $_SESSION['LAST_ACTIVITY'] > $timeout)) {
        // Set pesan flash sebelum logout
        setFlash('auth', 'Sesi Anda telah berakhir. Silakan login kembali.', 'info');
        // Simpan pesan flash ke variabel sementara
        $flash = $_SESSION['flash'];
        session_unset();
        session_destroy();
        // Mulai session baru untuk menampilkan pesan
        session_start();
        $_SESSION['flash'] = $flash;
        header("Location: login_page.php");
        exit();
    }
    // Update waktu aktivitas terakhir
    $_SESSION['LAST_ACTIVITY'] = time();
}

// Fungsi untuk mendapatkan user berdasarkan ID
function get_user_by_id($id)
{
    global $mysqli;
    $id = intval($id);
    $sql = "SELECT * FROM users WHERE id = $id";
    $result = dbquery($sql);
    return mysqli_fetch_assoc($result);
}

// Fungsi untuk menambah todo 
function tambah_todo($user_id, $title, $description, $priority, $due_date, $status = 'Belum')
{
    global $mysqli;
    $user_id = intval($user_id);
    $title = mysqli_real_escape_string($mysqli, $title);
    $description = mysqli_real_escape_string($mysqli, $description);
    $priority = mysqli_real_escape_string($mysqli, $priority);
    $due_date = mysqli_real_escape_string($mysqli, $due_date);
    $status = mysqli_real_escape_string($mysqli, $status);

    $sql = "INSERT INTO todos (user_id, title, description, priority, due_date, status) 
            VALUES ($user_id, '$title', '$description', '$priority', '$due_date', '$status')";
    return dbquery($sql);
}

// Fungsi untuk mengedit todo
function edit_todo($id, $user_id, $title, $description, $priority, $due_date, $status)
{
    global $mysqli;
    $id = intval($id);
    $user_id = intval($user_id);
    $title = mysqli_real_escape_string($mysqli, $title);
    $description = mysqli_real_escape_string($mysqli, $description);
    $priority = mysqli_real_escape_string($mysqli, $priority);
    $due_date = mysqli_real_escape_string($mysqli, $due_date);
    $status = mysqli_real_escape_string($mysqli, $status);

    $sql = "UPDATE todos SET 
                            title = '$title', 
                            description = '$description', 
                            priority = '$priority',
                            due_date = '$due_date', 
                            status = '$status'
                        WHERE id = $id AND user_id = $user_id";
    return dbquery($sql);
}

// Fungsi untuk menangani submit form tambah dan edit todo
function handle_todo_form_submit()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $user_id = $_SESSION['user']['id'];
        // Tambah todo
        if (
            empty($_POST['id']) &&
            !empty($_POST['title']) &&
            !empty($_POST['description']) &&
            !empty($_POST['priority']) &&
            !empty($_POST['due_date'])
        ) {
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $priority = trim($_POST['priority']);
            $due_date = $_POST['due_date'];
            $status = isset($_POST['status']) ? $_POST['status'] : 'Belum';
            if (tambah_todo($user_id, $title, $description, $priority, $due_date, $status)) {
                setFlash('todo', "Todo $title berhasil ditambahkan!", 'success');
                echo "<script>window.location='todo.php';</script>";
                exit;
            } else {
                setFlash('todo', "Gagal menambah todo $title.", 'error');
                echo "<script>window.location='todo.php';</script>";
                exit;
            }
        }
        // Edit todo
        if (
            !empty($_POST['id']) &&
            !empty($_POST['title']) &&
            !empty($_POST['description']) &&
            !empty($_POST['priority']) &&
            !empty($_POST['due_date']) &&
            isset($_POST['status'])
        ) {
            $id = intval($_POST['id']);
            $title = trim($_POST['title']);
            $description = trim($_POST['description']);
            $priority = trim($_POST['priority']);
            $due_date = $_POST['due_date'];
            $status = $_POST['status'];
            if (edit_todo($id, $user_id, $title, $description, $priority, $due_date, $status)) {
                setFlash('todo', "Todo $title berhasil diedit!", 'success');
                echo "<script>window.location='todo.php';</script>";
                exit;
            } else {
                setFlash('todo', "Gagal mengedit todo $title.", 'error');
                echo "<script>window.location='todo.php';</script>";
                exit;
            }
        }
    }
}

// Fungsi untuk menghapus todo berdasarkan ID dan user_id
function hapus_todo($id, $user_id)
{
    $id = intval($id);
    $user_id = intval($user_id);
    $sql = "DELETE FROM todos WHERE id = $id AND user_id = $user_id";
    return dbquery($sql);
}

// Handler proses hapus todo 
function handle_hapus_todo()
{
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['delete_id'])) {
        $delete_id = intval($_POST['delete_id']);
        $user_id = $_SESSION['user']['id'];

        // Ambil judul todo sebelum dihapus
        $sql = "SELECT title FROM todos WHERE id = $delete_id AND user_id = $user_id";
        $result = dbquery($sql);
        $todo = mysqli_fetch_assoc($result);
        $title = $todo ? $todo['title'] : '';

        if (hapus_todo($delete_id, $user_id)) {
            setFlash('todo', "Todo $title berhasil dihapus!", 'success');
            echo "<script>window.location='todo.php';</script>";
            exit;
        } else {
            setFlash('todo', "Gagal menghapus todo $title.", 'error');
            echo "<script>window.location='todo.php';</script>";
            exit;
        }
    }
}
