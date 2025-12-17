<?php
    session_start();
    include("./settings/connect_datebase.php");
    
    if (isset($_SESSION['user'])) {
        if($_SESSION['user'] != -1) {
            $user_query = $mysqli->query("SELECT * FROM `users` WHERE `id` = ".$_SESSION['user']); 
            while($user_read = $user_query->fetch_row()) {
                if($user_read[3] == 0) header("Location: index.php");
            }
        } else header("Location: login.php");
    } else {
        header("Location: login.php");
        echo "Пользователя не существует";
    }

    include("./settings/session.php");
?>
<!DOCTYPE HTML>
<html>
    <head> 
        <script src="https://code.jquery.com/jquery-1.8.3.js"></script>
        <meta charset="utf-8">
        <title> Admin панель </title>
        
        <link rel="stylesheet" href="style.css">
        <style>
            table{
                width: 100%;
            }
            td{
                text-align: center;
                padding: 10px;
            }

            /* ===== Улучшенная панель фильтров ===== */
            .filters {
                margin: 20px 0;
                padding: 15px 20px;
                background: #ffffff;
                border-radius: 6px;
                border: 1px solid #e0e4ea;
                box-shadow: 0 2px 4px rgba(0,0,0,0.06);
                font-family: sans-serif;
            }

            .filters-row {
                display: flex;
                flex-wrap: wrap;
                gap: 10px 15px;
                align-items: flex-end;
            }

            .filter-group {
                display: flex;
                flex-direction: column;
                min-width: 150px;
            }

            .filter-group label {
                font-size: 11px;
                color: #6b778c;
                margin-bottom: 4px;
                text-transform: uppercase;
                letter-spacing: 0.05em;
            }

            .filter-input,
            .filters select {
                height: 32px;
                padding: 4px 8px;
                border-radius: 4px;
                border: 1px solid #c1c7d0;
                font-size: 13px;
                outline: none;
                transition: border-color 0.15s, box-shadow 0.15s, background-color 0.15s;
                box-sizing: border-box;
            }

            .filter-input:focus,
            .filters select:focus {
                border-color: #0052cc;
                box-shadow: 0 0 0 2px rgba(0, 82, 204, 0.15);
            }

            .filter-input::placeholder {
                color: #a0a4ad;
                font-size: 12px;
            }

            .filters-actions {
                display: flex;
                gap: 8px;
                margin-left: auto;
            }

            .btn-filter {
                padding: 7px 14px;
                font-size: 13px;
                border-radius: 4px;
                border: none;
                cursor: pointer;
                transition: background-color 0.15s, box-shadow 0.15s, transform 0.05s;
                display: inline-flex;
                align-items: center;
                gap: 6px;
            }

            .btn-filter-primary {
                background-color: #0052cc;
                color: #ffffff;
            }

            .btn-filter-primary:hover {
                background-color: #0747a6;
            }

            .btn-filter-secondary {
                background-color: #f4f5f7;
                color: #344563;
            }

            .btn-filter-secondary:hover {
                background-color: #e0e4ea;
            }

            .btn-filter:active {
                transform: scale(0.97);
            }

            .btn-filter-icon {
                font-size: 14px;
            }

            /* Немного выровняем таблицу под фильтры */
            .logs-table thead {
                background-color: #f4f5f7;
            }
        </style>
    </head>
    <body>
        <div class="top-menu">
            <a href=#><img src = "img/logo1.png"/></a>
            <div class="name">
                <a href="index.php">
                    <div class="subname">БЗОПАСНОСТЬ  ВЕБ-ПРИЛОЖЕНИЙ</div>
                    Пермский авиационный техникум им. А. Д. Швецова
                </a>
            </div>
        </div>
        <div class="space"> </div>
        <div class="main">
            <div class="content">
                <input type="button" class="button" value="Выйти" onclick="logout()"/>
                
                <div class="name">Журнал событий</div>


                <div class="filters">
                    <div class="filters-row">
                        <div class="filter-group">
                            <label for="dateFilter">Дата</label>
                            <input type="date" id="dateFilter" class="filter-input">
                        </div>

                        <div class="filter-group">
                            <label for="ipFilter">IP</label>
                            <input type="text" id="ipFilter" class="filter-input" placeholder="Например, 127.0.0.1">
                        </div>

                        <div class="filter-group">
                            <label for="userFilter">ID пользователя</label>
                            <input type="text" id="userFilter" class="filter-input" placeholder="ID пользователя">
                        </div>

                        <div class="filter-group">
                            <label for="statusFilter">Статус</label>
                            <select id="statusFilter" class="filter-input">
                                <option value="">Все статусы</option>
                                <option value="online">online</option>
                                <option value="offline">offline</option>
                            </select>
                        </div>

                        <div class="filter-group" style="flex: 1 1 200px; min-width: 180px;">
                            <label for="eventFilter">Событие</label>
                            <input type="text" id="eventFilter" class="filter-input" placeholder="Текст события">
                        </div>

                        <div class="filters-actions">
                            <button onclick="GetEvents()" class="btn-filter btn-filter-primary">
                                <span>Фильтрация</span>
                            </button>
                            <button onclick="clearFilters()" class="btn-filter btn-filter-secondary">
                                <span class="btn-filter-icon">✕</span>
                                <span>Очистить</span>
                            </button>
                        </div>
                    </div>
                </div>

                <table border="1" class="logs-table">
                    <thead>
                        <tr>
                            <td style="width: 165px;">Дата и время</td>
                            <td style="width: 165px;">IP пользователя</td>
                            <td style="width: 165px;">Время в сети</td>
                            <td style="width: 165px;">Статус</td>
                            <td style="width: 165px;">Произошедшее событие</td>
                        </tr>
                    </thead>
                    <tbody></tbody>
                </table>
            
                <div class="footer">
                    © КГАПОУ "Авиатехникум", 2020
                    <a href=#>Конфиденциальность</a>
                    <a href=#>Условия</a>
                </div>
            </div>
        </div>
        
        <script>
            function logout(){
                window.location.href = 'logout.php';
            }

            let $Table = $("table > tbody");

            function GetEvents() {
                let filters = {
                    date: $('#dateFilter').val(),
                    ip: $('#ipFilter').val(),
                    userId: $('#userFilter').val(),
                    status: $('#statusFilter').val(),
                    event: $('#eventFilter').val()
                };

                $.ajax({
                    url: 'ajax/events/get.php',
                    type: 'POST',
                    data: filters,
                    dataType: 'json',
                    success: GetEventAjax,
                    error: function(xhr) { 
                        console.log('AJAX ошибка:', xhr.responseText); 
                        $Table.html('<tr><td colspan="5">Ошибка загрузки данных</td></tr>');
                    }
                });
            }

            function GetEventAjax(events) {
                $Table.empty();
                
                if (!events.length) {
                    $Table.html('<tr><td colspan="5" style="text-align:center;">События не найдены</td></tr>');
                    return;
                }
                
                events.forEach(event => {
                    $Table.append(`
                        <tr>
                            <td>${event.Date || ''}</td>
                            <td>${event.Ip || ''}</td>
                            <td>${event.TimeOnline || ''}</td>
                            <td>${event.Status || ''}</td>
                            <td style="text-align: left;">${event.Event || ''}</td>
                        </tr>
                    `);
                });
            }

            function clearFilters() {
                $('#dateFilter').val('');
                $('#ipFilter').val('');
                $('#userFilter').val('');
                $('#statusFilter').val('');
                $('#eventFilter').val('');
                GetEvents();
            }

            // Автозагрузка при открытии страницы
            $(document).ready(function() {
                GetEvents();
            });
        </script>
    </body>
</html>
