<?php
  require_once '/var/www/html/vendor/predis/predis/autoload.php';

  $redis = new Predis\Client(['host' => 'redis']);
  $redisStatus = redisConnect($redis);

  $result = mysqlConnect();
  $mysqlStatus = $result[0];
  $mysql = $result[1];

  // check redis I/O
  if ($redisStatus == "OK") {
      $ok = $redis->set("testKey", "testValue");
      if ($ok == "OK") {
          $okBool = $redis->get("testKey");
          if ($okBool) {
              $redis->del("testKey");
              $redisStatus = "Yessir. Readin' and ritin' to redis";
          } else {
              $redisStatus = "Failed reading redis testKey";
          }
      } else {
          $redisStatus = "Failed writing redis testKey";
      }
  }

  // check mysql I/O
  if ($mysqlStatus == "OK") {
      $data = [];
      $sql = "SELECT variable FROM sys_config;";
      $db = $mysql->query($sql);
      if ($db) {
          while($row = $db->fetch_assoc()) {
              $data[] = $row;
          }
          if (sizeof($data) < 1) {
              $mysqlStatus = "No data found";
          } else {
              $mysqlStatus = "All good. Found data," . sizeof($data) . " items";
          }
          $db->free();
      } else {
          $mysqlStatus = "Mysql query failed";
      }
  }

  // associative array maps nicely to json
  $payload = array(
    "greeting" => " Hello Moz!",
    "redis" => $redisStatus,
    "mysql" => $mysqlStatus
  );

  // send a repsonse to caller
  sendResponse(200, json_encode($payload));

  // close db
  $mysql->close();

  // bye -- no close for redis
  exit(1);



  function sendResponse($status = 200, $body = '', $content_type = 'application/json')
  {
    // old-school http response
    $status_header = 'HTTP/1.1 ' . $status . ' OK';
    header($status_header);
    header('Content-type: ' . $content_type);
    header('Access-Control-Allow-Origin: *');
    header('Access-Control-Allow-Methods: GET, POST, OPTIONS, HEAD');
    header('Access-Control-Allow-Headers: apikey, Authorization, Origin, X-Requested-With, Content-Type, Accept');
    echo $body;
  }

  function redisConnect($mem) {
    try {
        $mem->connect();
        $mem->select(0);
        $status = "OK";
    }

    catch (Exception $exception) {
        $status = "Redis failed to connect";
    }
    return $status;
  }


  function mysqlConnect() {
    mysqli_report(MYSQLI_REPORT_STRICT);
    try {
        $db = new mysqli(
            "mysql",
            "root",
            "root",
            "sys"
        );
        $status = "OK";
    } catch (Exception $e ) {
        echo $e;
        $status = "MySQL failed to connect";
        $db = false;
    }
    return [$status, $db];
  }
?>
