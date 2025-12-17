<?php  
session_start();
include("../settings/connect_datebase.php");  


if (!isset($_SESSION['user']) || !isset($_SESSION['IdSession'])) {
    echo json_encode(['status' => 'error', 'message' => 'Сессия не найдена']);
    exit;
}

$IdUser = $_SESSION['user'];
$Message = trim($_POST["Message"]);
$IdPost = (int)$_POST["IdPost"];
$IdSession = $_SESSION['IdSession'];


$SqlComment = "INSERT INTO `comments` (`IdUser`, `IdPost`, `Messages`) VALUES (?, ?, ?)";
$stmt = $mysqli->prepare($SqlComment);
$stmt->bind_param("iis", $IdUser, $IdPost, $Message);
$stmt->execute();


$Sql = "SELECT `session`.*, `users`.`login`
        FROM `session` 
        JOIN `users` ON `users`.`id` = `session`.`IdUser`
        WHERE `session`.`Id` = ?";
        
$stmt = $mysqli->prepare($Sql);
$stmt->bind_param("i", $IdSession);
$stmt->execute();
$Read = $stmt->get_result()->fetch_assoc();

if (!$Read) {
    echo json_encode(['status' => 'error', 'message' => 'Сессия не найдена']);
    exit;
}

$TimeStart = strtotime($Read["DateStart"]);
$TimeNow = time();
$TimeDelta = gmdate("H:i:s", $TimeNow - $TimeStart); 
$Date = date("Y-m-d H:i:s");
$Login = $Read["login"];
$Ip = $Read["Ip"];

$SqlLog = "INSERT INTO `logs` (`Ip`, `IdUser`, `Date`, `TimeOnline`, `Event`) 
           VALUES (?, ?, ?, ?, ?)";
$stmtLog = $mysqli->prepare($SqlLog);
$event = "Пользователь {$Login} оставил комментарий к записи [Id: {$IdPost}]: {$Message}";
$stmtLog->bind_param("sisss", $Ip, $IdUser, $Date, $TimeDelta, $event);
$logResult = $stmtLog->execute();

if ($logResult) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Ошибка логирования']);
}
?>
