<?php
	    header('Content-Type: text/html; charset=utf-8' );
		$host = 'localhost';
		$database = 'k908243k_db';
		$name_user = "k908243k_db";
		$password_user = "Aa1234";
		
		$conn =  new PDO("mysql:host=localhost;dbname=k908243k_db",$name_user,$password_user);
		$link = mysqli_connect("localhost",$name_user,$password_user,"k908243k_db") or die("Error ".mysqli_error($link));
        
        $query = "SELECT * FROM pes_houses";
        $result = mysqli_query($link,$query) or die(mysqli_error($link));
        for($data = [];$row=mysqli_fetch_assoc(
            $result);$data[]=$row);
           
		$result = '<!DOCTYPE HTML>
<html>
	<head>
	    <script src="https://api-maps.yandex.ru/2.1?apikey=7240d620-d65b-44d8-baa4-a9272e7779bb&lang=ru_RU" type="text/javascript"></script>
	    <meta charset="utf-8">
		<link rel="stylesheet" href="bootstrap.css">
		<link rel="stylesheet" href="mainpagestyle_1.css">
        <link rel="shortcut icon" href="/img/home.ico" type="image/x-icon">
		<link rel="stylesheet" type="text/css" href="https://yastatic.net/bootstrap/3.3.4/css/bootstrap.min.css">
		<script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
		<script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
		<script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
        <script type="text/javascript">
            ymaps.ready(function () {
                var myMap = new ymaps.Map("YMapsID", {
                    center: [58.0050, 56.1456],
                    zoom: 10
                });';

            foreach ($data as $elem){
                $pic = "one1.png";
                $rate = 0;
                
                //Расчет среднего возраста счетчика
                $dbh = mysql_connect($host,$name_user,$password_user) or die("Не могу соединиться с MySQL");
                mysql_select_db($database) or die("Не могу подключиться к базе");
                $query1 = "SELECT avg(datediff(CURDATE(),manufacture_date)) as avg_age FROM pes_account_meters WHERE meter_placement_id IN(SELECT apartment_id FROM pes_geo_apartments WHERE house_id =".$elem['house_id'].")";
                $age = null;
                $res = mysql_query($query1);
                while($row1=mysql_fetch_array($res)){
                    if($row1['avg_age']<365*3 && $row1['avg_age']!=null)
                    $rate = $rate + 1;
                    
                }
                
                //Расчет количества квартир с долгами
                $dbh = mysql_connect($host,$name_user,$password_user) or die("Не могу соединиться с MySQL");
                mysql_select_db($database) or die("Не могу подключиться к базе");
                $query1 = "SELECT count(apartment_id) as count_kv FROM pes_geo_apartments WHERE house_id=".$elem['house_id']." AND apartment_id IN(SELECT apartment_id FROM pes_client WHERE client_id IN(SELECT client_id FROM pes_account WHERE account_id IN(SELECT account_id FROM pes_ps_payments WHERE payment_id NOT IN (SELECT payment_id FROM pes_pv_distrib_pays))))";
                $res = mysql_query($query1);
                $count_kv_with = 0;
                while($row1=mysql_fetch_array($res)){
                    $count_kv_with=$row1['count_kv'];
                }

                //Расчет общего количества квартир в доме
                $dbh = mysql_connect($host,$name_user,$password_user) or die("Не могу соединиться с MySQL");
                mysql_select_db($database) or die("Не могу подключиться к базе");
                $query1 = "SELECT count(apartment_id) as count_kv FROM pes_geo_apartments WHERE house_id=".$elem['house_id'];
                $res = mysql_query($query1);
                $count_all_kv = null;
                while($row1=mysql_fetch_array($res)){
                    $count_all_kv=$row1['count_kv'];
                }
                    
                if($count_all_kv*0.5 > $count_kv_with && $count_all_kv!=0)
                {
                    $rate = $rate+1;
                }
                
                //Расчет количества обслуживаний
                $dbh = mysql_connect($host,$name_user,$password_user) or die("Не могу соединиться с MySQL");
                mysql_select_db($database) or die("Не могу подключиться к базе");
                $query1 = "SELECT count(request_id) as count_req FROM pes_pr_proc_requests WHERE datediff(CURDATE(),request_start)<30 AND attr_entity_id IN(SELECT attr_entity_id FROM pes_account WHERE client_id IN(SELECT client_id FROM pes_client WHERE apartment_id IN(SELECT apartment_id FROM pes_geo_apartments WHERE house_id =".$elem['house_id'].")))";
                $res = mysql_query($query1);
                while($row1=mysql_fetch_array($res)){
                    if($row1['count_req']<4 && $row1['count_req']!=null && $count_all_kv!=0)
                    $rate = $rate + 1;
                    
                }
                
                //Расчет суммы долгов
                $dbh = mysql_connect($host,$name_user,$password_user) or die("Не могу соединиться с MySQL");
                mysql_select_db($database) or die("Не могу подключиться к базе");
                $query1 = "SELECT sum(payment_sum) as sum_pay FROM pes_ps_payments WHERE payment_id NOT IN (SELECT payment_id FROM pes_pv_distrib_pays) AND account_id IN(SELECT account_id FROM pes_account WHERE client_id IN(SELECT client_id FROM pes_client WHERE apartment_id IN(SELECT apartment_id FROM pes_geo_apartments WHERE house_id =".$elem['house_id'].")))";
                $res = mysql_query($query1);
                $sum = null;
                while($row1=mysql_fetch_array($res)){
                    $sum = $row1['sum_pay'];
                    if($sum<1000000 && $count_all_kv!=0){
                        $rate = $rate + 1;
                    }
                }
                
                
                if($rate == 0)
                    $pic = "one1.png";
                    
                if($rate == 1)
                    $pic = "two2.png";
                
                if($rate == 2)
                    $pic = "three3.png";
                    
                if($rate == 3)
                    $pic = "four4.png";
                    
                if($rate == 4)
                    $pic = "five5.png";
                
                $result.='var placemark1 = new ymaps.Placemark(['.$elem['house_coord_lat'].','.$elem['house_coord_long'].'],{
				
				},
				{
				    iconLayout:\'default#image\',
				    iconImageHref:\'img/'.$pic.'\',
				    iconImageSize:[40,40]
				});
				// Размещение геообъекта на карте.
				myMap.geoObjects.add(placemark1);';
            }
				 
				
            $result.='});
            
        </script>
		<title>Рейтинг домов</title>
	</head>
	<body>
	    <!-- Шапка -->
		<div class="parent">
		    <div class="child">
				Рейтинг домов
			</div>
		</div>
		
		<!-- Оснновная часть сайта. Карта -->
		<div class="box">
			<div class="row">
				<div id="YMapsID" style="width: 100%; height: 40vw;"></div>
			</div>
		</div>
		
		<!-- Подвал -->
	    <div class="bottom">
            <image src="img\Screenshot_pes3.png"></image>
		</div>
	</body>
</html>';
		echo $result;

?>