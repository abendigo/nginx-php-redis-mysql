<?php
  require_once '/var/www/html/vendor/predis/predis/autoload.php';

  $redis = new Predis\Client(['host' => 'redis']);
  $redisStatus = redisConnect($redis);

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

  // associative array maps nicely to json
  $payload = array(
    "greeting" => " Hello Moz!",
    "redis" => $redisStatus
  );

  // send a repsonse to caller
  sendResponse(200, json_encode($payload));

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
?>
