<?php
session_start();
require_once("../settings/connect_datebase.php"); 

if (!isset($_SESSION["user"]) || !isset($_SESSION["IdSession"])) {
    session_destroy();
    exit("Сессия не найдена");
}

$IdUser = $_SESSION["user"];
$IdSession = $_SESSION["IdSession"];

// Ищем ТЕКУЩУЮ сессию пользователя
$Sql = "SELECT `session`.*, `users`.`login`
        FROM `session` 
        JOIN `users` ON `users`.`id` = `session`.`IdUser`
        WHERE `session`.`Id` = ? AND `session`.`IdUser` = ?";
        
$stmt = $mysqli->prepare($Sql);
$stmt->bind_param("ii", $IdSession, $IdUser);
$stmt->execute();
$Read = $stmt->get_result()->fetch_assoc();

if (!$Read) {
    session_destroy();
    exit("Сессия не найдена");
}

$TimeStart = strtotime($Read["DateStart"]);
$TimeNow = time();
$TimeDeltaSeconds = $TimeNow - $TimeStart;
$TimeDelta = gmdate("H:i:s", $TimeDeltaSeconds);
$Date = date("Y-m-d H:i:s");
$Login = $Read["login"];

// Логируем с подготовленным запросом
$SqlLog = "INSERT INTO `logs`(`Ip`, `IdUser`, `Date`, `TimeOnline`, `Event`)
           VALUES (?, ?, ?, ?, ?)";
$stmtLog = $mysqli->prepare($SqlLog);
$Ip = $Read["Ip"];
$event = "Пользователь {$Login} покинул данное бытие.";
$stmtLog->bind_param("sisss", $Ip, $IdUser, $Date, $TimeDelta, $event);
$stmtLog->execute();

// Уничтожаем сессию
$_SESSION = array();  // Очищаем все данные сессии
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000, $params["path"], $params["domain"], $params["secure"], $params["httponly"]);
}
session_destroy();

echo "Выход выполнен успешно";
?>
