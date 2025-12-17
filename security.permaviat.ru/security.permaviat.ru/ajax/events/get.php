<?php
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

require_once("../../settings/connect_datebase.php");

$Events = [];
try {
    // Фильтры из POST
    $date = $_POST['date'] ?? '';
    $ip = $_POST['ip'] ?? '';
    $userId = $_POST['userId'] ?? '';
    $statusFilter = $_POST['status'] ?? '';
    $event = $_POST['event'] ?? '';

    // Базовый SQL с фильтрами
    $sql = "SELECT * FROM `logs` WHERE 1=1";
    $params = [];
    $types = "";

    if ($date) {
        $sql .= " AND DATE(`Date`) = ?";
        $params[] = $date;
        $types .= "s";
    }
    if ($ip) {
        $sql .= " AND `Ip` LIKE ?";
        $params[] = "%$ip%";
        $types .= "s";
    }
    if ($userId !== '') {
        $sql .= " AND `IdUser` = ?";
        $params[] = (int)$userId;
        $types .= "i";
    }
    if ($event) {
        $sql .= " AND `Event` LIKE ?";
        $params[] = "%$event%";
        $types .= "s";
    }

    $sql .= " ORDER BY `Date` DESC LIMIT 50";

    // Подготовленный запрос
    $stmt = $mysqli->prepare($sql);
    if (!empty($params)) {
        $stmt->bind_param($types, ...$params);
    }
    $stmt->execute();
    $Query = $stmt->get_result();

    if ($Query && $Query->num_rows > 0) { 
        while($Read = $Query->fetch_assoc()) {
            // Вычисление статуса (ваша логика)
            $Status = $Read["Status"] ?? "Неизвестно"; 
            
            $SqlSession = "SELECT * FROM `session` WHERE `IdUser` = " . (int)$Read["IdUser"] . " ORDER BY `DateStart` DESC LIMIT 1"; 
            $QuerySession = $mysqli->query($SqlSession); 
            
            if($QuerySession && $QuerySession->num_rows > 0){
                $ReadSession = $QuerySession->fetch_assoc(); 
                $TimeEnd = strtotime($ReadSession["DateNow"]) + 5*60;  
                $TimeNow = time();
                
                if($TimeEnd > $TimeNow) {
                    $Status = "online";
                } else {
                    $TimeEnd = strtotime($ReadSession["DateNow"]);
                    $TimeDelta = round(($TimeNow - $TimeEnd)/60);  
                    $Status = "Был в сети: {$TimeDelta} мин. назад";
                }
            }
            
            // Фильтр по статусу (после вычисления)
            if ($statusFilter && stripos($Status, $statusFilter) === false) {
                continue; // Пропускаем не подходящие по статусу
            }
            
            $Event = [
                "Id" => (int)$Read["Id"],
                "Ip" => $Read["Ip"],
                "Date" => $Read["Date"],
                "TimeOnline" => $Read["TimeOnline"],
                "Status" => $Status,
                "Event" => htmlspecialchars($Read["Event"] ?? "")
            ];
            
            $Events[] = $Event; 
        }
    }
    
} catch (Exception $e) {
    http_response_code(500);
    $Events = [['error' => 'Ошибка БД: ' . $e->getMessage()]];
}

echo json_encode($Events, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
?>
