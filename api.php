<?php
require "config.php";
class api
{
  public static function listUpcomingEvents()
  {
    global $db;
    $query=$db->query("SELECT * FROM `Events` WHERE `end`>now();");
    return $query->fetchAll(PDO::FETCH_ASSOC);
  }
  public static function submitEvent($name, $place, $startdate, $starttime, $enddate, $endtime)
  {
    global $db;
    // Parse into datetimes
    // "2019-04-29","01:53"
    $start=DateTime::CreateFromFormat("Y-m-d H:i","$startdate $starttime");
    $end=DateTime::CreateFromFormat("Y-m-d H:i","$enddate $endtime");
    if(!$start)
    {
      return ["status"=>2,"data"=>"start"];
    }
    if(!$end)
    {
      return ["status"=>2,"data"=>"end"];
    }
    if($start>$end)
    {
      return ["status"=>2,"data"=>"end"];
    }
    // Check for conflict
    if($conflict=self::checkConflict($place,$start,$end))
    {
      return ["status"=>1,"data"=>$conflict];
    }
    $query=$db->prepare("INSERT INTO `Events` (`name`, `place`, `start`, `end`) VALUES (?,?,?,?);");
    $query->execute([$name,$place,$start->format("Y-m-d H:i:s"),$end->format("Y-m-d H:i:s")]);
    return ["status"=>0];
  }
  /**
   * Checks for conflicts when attempting to schedule an event
   * @return string|null Event name, if an event conflicts
   */
  private static function checkConflict($place,$start,$end):?string
  {
    global $db;
    // Select where start1<end2 and start2>end1
    $query=$db->prepare("SELECT `name` FROM `Events` WHERE `start`<? AND ?<`end` AND `place`=?");
    $query->execute([$end->format("Y-m-d H:i:s"),$start->format("Y-m-d H:i:s"),$place]);
    $fetch=$query->fetch();
    if($fetch) return $fetch[0];
    return null;
  }
}
if(isset($argv))
{
  register_shutdown_function(function(){
    echo PHP_EOL;
  });
}
if(isset($argv[1]))
{
  $method=$argv[1];
}
elseif(isset($_REQUEST["method"]))
{
  $method=$_REQUEST["method"];
}
else
{
    http_response_code(404);
    die("Method not found.");
}
if(isset($argv))
{
  foreach($argv as $arg)
  {
    if(!preg_match("/(.+)\=(.+)/", $arg,$matches))
    {
      continue;
    }
    $_REQUEST[$matches[1]]=$matches[2];
  }
}
if(method_exists("api",$method))
{
  $f=new ReflectionMethod("api",$method);
  if(!$f->isPublic())
  {
    http_response_code(404);
    die("Method not found.");
  }
  $args=[];
  foreach($f->getParameters() as $param)
  {
    if(isset($_REQUEST[$param->name]))
    {
      $args[]=$_REQUEST[$param->name];
    }
    elseif($param->isDefaultValueAvailable())
    {
      $args[]=$param->getDefaultValue();
    }
    else
    {
      http_response_code(400);
      die("Requires param ".$param->name);
    }
  }
  $data=api::$method(...$args);
  $d=json_encode($data);
  echo $d;
}
else
{
  http_response_code(404);
  die("Method not found.");
}