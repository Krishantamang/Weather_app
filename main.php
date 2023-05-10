<?php
    // Database connection
    $servername = "localhost";
    $username = "root";
    $password = "";
    $dbname = "weather";

    $conn = mysqli_connect($servername, $username, $password, $dbname);
    

    if (!$conn) {
        die("Connection failed: " . $conn->connect_error);
    }

    if (isset($_POST['input'])){
        $name = $_POST['input'];
        $cityname = $_POST['input'];
    } else {
        $name = "Valdez";
        $cityname = "Valdez";
    }
    $ID = getID($cityname);
    $WeatherInfo = PastFetch($ID, $conn, $cityname);
    $WeatherInfo = DefaultFetch($name, $conn);

    //Getting ID of the city 
    function getID($cityname){
        $url = "http://api.openweathermap.org/data/2.5/weather?q=" . urlencode($cityname) . "&appid=a80dfe911bd3bcc798043ec30902ccab";
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $ID = $data["id"];
        return $ID;
    }

    //Fetching data 
    function DefaultFetch($name, $conn){
        $url = ("https://api.openweathermap.org/data/2.5/weather?units=metric&q=".$name."&appid=a80dfe911bd3bcc798043ec30902ccab");
        $response = file_get_contents($url);
        $data = json_decode($response, true);
          if ((mysqli_query($conn, "SELECT * FROM $name")) === false){
            $create = "CREATE TABLE $name(Date varchar(20), Temperature varchar(20), Status varchar(20), Pressure varchar(20), Wind varchar(20), Humidity varchar(20), Weather varchar(20))";
            mysqli_query($conn, $create);
        } else {
            echo '';
        }
        $temp = $data['main']['temp'];
        $location = $data['name'];
        $Status = $data['weather'][0]['description'];
        $pressure = $data['main']['pressure'];
        $wind = $data['wind']['speed'];
        $humidity = $data['main']['humidity'];
        $weather = $data['weather'][0]['main'];
        $date = $data['dt'];
        $finaldate = date("Y-m-d",$date);
        echo "<script> console.log('".$Status."'); </script>";
        
        $sql = "INSERT IGNORE INTO Valdez (Date, Temperature, Status, Pressure, Wind, Humidity, Weather)
        VALUES ('$finaldate', '$temp', '$Status', '$pressure', '$wind', '$humidity', '$weather')";
        if ($conn->query($sql) === TRUE) {
            echo "";
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        } 

        $result = array(
            "temp" => $temp,
            "status" => $Status,
            "pressure" => $pressure,
            "wind" => $wind,
            "humidity" => $humidity,
            "name" => $location,
            "icon" => $weather,
            "date" => $finaldate
        );
        return $result;
    }      

    //Fetching the past datas
    function PastFetch($ID, $conn, $cityname, $unit_system = 'imperial'){
        $time = time();
        $start = date(strtotime('-7days'),$time);

        $url = ("https://history.openweathermap.org/data/2.5/history/city?id=".$ID."&type=hour&start=$start&end=$time&appid=a80dfe911bd3bcc798043ec30902ccab&units=metric");
        $response = file_get_contents($url);
        $data = json_decode($response, true);
        $name = $cityname;
        for($i = 0; $i<166; $i++){         
                $temp = $data['list'][$i]['main']['temp'];
                $Status = $data['list'][$i]['weather'][0]['description'];
                $wind = $data['list'][$i]['wind']['speed'];
                $pressure = $data['list'][$i]['main']['pressure'];
                $humidity = $data['list'][$i]['main']['humidity'];
                $weather = $data['list'][$i]['weather'][0]['main'];
                $date = $data['list'][$i]['dt'];
                $finaldate = date("Y-m-d",$date);
                try {
                    $check = mysqli_query($conn, "SELECT * FROM $name");
                    if ($check === false){
                        throw new Exception("Table doesn't exist");
                    }
                } catch (Exception $e){
                    $create = "CREATE TABLE $name(Date varchar(20), Temperature varchar(20), Status varchar(20), Pressure varchar(20), Wind varchar(20), Humidity varchar(20), Weather varchar(20), PRIMARY KEY(Date))";
                    mysqli_query($conn, $create);
                }
                $sql = "INSERT IGNORE INTO $name(Date, Temperature, Status, Pressure, Wind, Humidity, Weather)
                VALUES ('$finaldate', '$temp', '$Status', '$pressure', '$wind', '$humidity', '$weather')";
                if ($conn->query($sql) === TRUE) {
                    echo "";
                } else {
                    echo "Error: " . $sql . "<br>" . $conn->error;
                }
            }
    }

    //switch case for printing icon according to the case
    switch ($WeatherInfo['icon']) {
        case "Clear":
            $icon = "https://openweathermap.org/img/wn/02d@2x.png";
            break;
        case "Clouds":
            $icon = "https://openweathermap.org/img/wn/02d@2x.png";
            break;
        case "Drizzle":
            $icon = "https://openweathermap.org/img/wn/09d@2x.png";
            break;
        case "Thunderstorm":
            $icon = "https://openweathermap.org/img/wn/11d@2x.png";
            break;
        case "Rain":
            $icon = "https://openweathermap.org/img/wn/09d@2x.png";
            break;
        case "Snow":
            $icon = "https://openweathermap.org/img/wn/13d@2x.png";
            break;
        case "Mist":
            $icon = "https://openweathermap.org/img/wn/50d@2x.png";
            break;
    }
    
$sevendays = "";
for($i = 0;$i<7;$i++){
    $date = date('Y-m-d',strtotime('-'.$i.'days'));
    $select = "SELECT * FROM $name WHERE Date = '$date'";
    $result = mysqli_query($conn, $select);
    $row = mysqli_fetch_assoc($result);

    $temp = $row['Temperature'];
    $Status = $row['Status'];
    $pressure = $row['Pressure'];
    $wind = $row['Wind'];
    $humidity = $row['Humidity'];
    $weather = $row['Weather'];

     //switch case for printing icon according to the case
    switch ($weather) {
        case "Clear":
            $icon = "https://openweathermap.org/img/wn/02d@2x.png";
            break;
        case "Clouds":
            $icon = "https://openweathermap.org/img/wn/02d@2x.png";
            break;
        case "Drizzle":
            $icon = "https://openweathermap.org/img/wn/09d@2x.png";
            break;
        case "Thunderstorm":
            $icon = "https://openweathermap.org/img/wn/11d@2x.png";
            break;
        case "Rain":
            $icon = "https://openweathermap.org/img/wn/09d@2x.png";
            break;
        case "Snow":
            $icon = "https://openweathermap.org/img/wn/13d@2x.png";
            break;
        case "Mist":
            $icon = "https://openweathermap.org/img/wn/50d@2x.png";
            break;
    }

    $sevendays .= '
    <div class="sevendays">
        <div class="box">
            <p class="date">'.date('M d', strtotime('-'.$i.'days')).'</p>
            <p class="icon"><img src="'.$icon.'"></p>
            <p class="temp">'.$temp.'°C</p>
            <p class="Status">'.$Status.' </p>
            <p class="pressure">'.$pressure.' hPa</p>
            <p class="wind">'.$wind.' m/s</p>
            <p class="humidity">'.$humidity.'%</p>
        </div>
    </div>
';
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" integrity="sha512-iecdLmaskl7CVkqkXNQ/ZH/XLlvWZOJyj7Yy7tcenmpD1ypASozpmT/E0iPtmFIB46ZmdtAc9eNBvH0H/ZpiBw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
    <title>Weather App</title>
</head>
<body>
 
    <div class="main">
        <a class="heading">WEATHER APP</a><br>
        <div id="info">
            <div class="left">
                <a class="city"><?php echo $WeatherInfo['name']?></a><img id="iconhead" src="<?php echo $icon?>"><br>
                <a class="statushead"><?php echo $WeatherInfo['status']?></a><br>
                <a class="date"></a><br>
                <a class="time"></a><br>
            </div>
            <form action="" method="post">
                <div class="middle">
                <input type="text" placeholder="Enter city" class="input" name="input"></input><br>
                <div class="buttons"><button id="show" type="submit">Show Weather</button><br></div>
            </div>
            </form>
            <div class="right">
                <a class="top1">Tempetrature</a><br>
                <a class="temper"><?php echo $WeatherInfo['temp']."°C"?></a><br>
                <a class="top2">Pressure</a><br>
                <a class="pressure"><?php echo "Pressure : " .$WeatherInfo['pressure']. " hPa"?></a><br>
                <a class="top3">Wind Speed</a><br>
                <a class="wind"><?php echo "Wind : " .$WeatherInfo['wind']. " m/s"?></a><br>
                <a class="top4">Humidity</a><br>
                <a class="humid"><?php echo "Humidity : " .$WeatherInfo['humidity']. "%"?></a>
            </div>
            </div>
        
        <div class="bottom">
            <?php echo $sevendays; ?>
        </div>
    </div>

    <script>
    async function fetchData(){
        const response = await fetch("https://history.openweathermap.org/data/2.5/history/city?id=2643743&type=hour&start=1682442000&end=1688230800&appid=a80dfe911bd3bcc798043ec30902ccab");
        const data=await response.json();
        console.log(data);
    }
    fetchData();

        const date=document.querySelector(".date");
        const months = [
            "Jan",
            "Feb",
            "Mar",
            "Apr",
            "May",
            "Jun",
            "Jul",
            "Aug",
            "Sep",
            "Oct",
            "Nov",
            "Dec",
            ];

        function update_time() {
            var now = new Date();
            var hours = now.getHours();
            var minutes = now.getMinutes();
            var seconds = now.getSeconds();
            var timeString = hours.toString().padStart(2, '0') + ' : ' +
                            minutes.toString().padStart(2, '0') + ' : ' +
                            seconds.toString().padStart(2, '0');
    
            document.querySelector('.time').textContent = timeString;
    }
    
    setInterval(update_time, 1000);

    fulldate=new Date();
    date.innerHTML=months[fulldate.getMonth()]+" "+fulldate.getDate()+", "+fulldate.getFullYear();

    </script>
    </body>
</html>