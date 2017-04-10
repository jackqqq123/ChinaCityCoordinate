<?php
define('CITY_PATH',(__DIR__) . '\mapfile\city.json');
define('NATION_PATH',(__DIR__) . '\mapfile\nation.json');
require 'tool.php';
/**
*中国城市经纬度查询
*
* @author janing
*/

class coordinate{
  /**
  *实例化对象
  *
  */
  protected static $_instance;
  /**
  *获取实例化对象函数
  *
  */
  public static function getInstance(){
    if(! (self::$_instance instanceof self) ){
      self::$_instance = new self();
    }
    return self::$_instance;
  }
  /**
  *坐标json路径
  *
  */
  protected $city_path= CITY_PATH;
  /**
  *地图坐标对象
  *
  */
  protected $city_map=false;
  /**
  *民族json路径
  *
  */
  protected $nation_path= NATION_PATH;
  /**
  *民族数组
  *
  */
  protected $nation_map=false;
  /**
  *省 免过滤字段
  *
  */
  protected $white_key=array("广西壮族自治区","新疆维吾尔自治区","内蒙古自治区","西藏自治区","宁夏回族自治区");
  /**
  *初始化函数
  *
  */
  public function __construct(){

  }
  /**
  *测试函数
  *
  */
  public function getTest(){
    var_dump("您已成功引用ChinaCity:coordinate.php");
  }
  /**
  *获取地图坐标实例
  *
  */
  public function getCityMap(){

    if(! $this->city_map){
      //读取地图文件
      $map_str = file_get_contents($this->city_path);
      if(!$map_str){logs("地图文件位置错误");}
      $map_str = str_replace(PHP_EOL,'',$map_str);
      $map_json = json_decode($map_str,true);
      if(!$map_json){logs("地图文件格式错误，请注意json中的，] }是否正确使用");}
      $this->city_map = ($map_json)?$map_json:false;
    }
    return $this->city_map;
  }
  /**
  *获取民族文件
  *
  */
  public function getNationMap(){

    if(! $this->nation_map){
      //读取地图文件
      $map_str = file_get_contents($this->nation_path);
      if(!$map_str){logs("民族文件位置错误");}
      $map_str = str_replace(PHP_EOL,'',$map_str);
      $map_json = json_decode($map_str,true);
      if(!$map_json){logs("民族文件格式错误，请注意json中的，] }是否正确使用");}
      $this->nation_map = ($map_json)?$map_json:false;
    }
    return $this->nation_map;
  }
  /**
  *名称过滤
  *
  */
  public function filter($city_name){

    $filtered=$city_name;
    //判断是否免过滤字符串
    if(in_array($city_name,$this->white_key)){
      return $filtered;
    }
    //判断长度是否小于2
    if(strlen($city_name) <= 2){
      return $filtered;
    }
    //判断是否民族自治地区
    if(preg_match('[族]',$city_name)){
      //民族自治区/县等清除障碍
      $nation_arr=$this->getNationMap();
      foreach ($nation_arr as $key => $value) {
        if(preg_match("[$value]",$city_name)){
          $name_split=explode($value,$city_name);
          $filtered=$name_split[0];
        }
      }
    }
    //清除 市、区、县 字样
    $last=mb_substr($city_name, -1,null,'UTF8');
    if(in_array($last,array("区","市","县"))){
      $filtered=mb_substr($city_name, 0, -1,'UTF8');
    }
    return $filtered;
  }
  /**
  *获取任何城市的坐标（速度慢）- 暂时未使用
  *@return array/false ;
  */
  public function MapFind($city_name){
    if(! $city_name ){ return false;}
    $city_name=$this->filter($city_name);
    $map = $this->getCityMap();
    $result = array_find($map,$city_name);
    if($result){
      unset($result['children']);
    }
    return $result;
  }
  /**
  *分级查询城市
  *@return
  */
  public function AreaFind($name){
    if(! $name ){ return false;}
    $map = $this->getCityMap();

    $name_arr=explode("-",$name);

    foreach ($name_arr as $key => $value) {
      $name_arr[$key]=$this->filter($value);
    }
    if(!$name_arr){return $name_arr;}
    $result_arr=array_find_array($map,$name_arr);
    if(!$result_arr){
      $name_arr[0].="省";
      $result_arr=array_find_array($map,$name_arr);
    }
    return $result_arr[count($result_arr)-1];
  }
  /**
  *综合搜索城市
  *
  */
  public function find($name){
    $area_result=$this->AreaFind($name);

    if(!$area_result){
      $area_result=false;
      logs(" $name - 无法查找");
    }
    return $area_result;
  }

  /**
     * 基于余弦定理求两经纬度距离
     * @param log1 第一点的经度
     * @param lat1 第一点的纬度
     * @param log2 第二点的经度
     * @param lat3 第二点的纬度
     * @return 返回的距离，单位km
     * */
  public function distance($name_arr){

    if(!$name_arr|| !$name_arr[1] ){return false;}
    (double) $EARTH_RADIUS = 6378137;//赤道半径(单位m)
    $sum=0;
    for($i = 1 , $len= count($name_arr) ;$i < $len;$i ++ ){

      $place_s = $this->find($name_arr[$i-1]);
      $place_e = $this->find($name_arr[$i]);
      if(!$place_s || !$place_e){return false;};
      // var_dump($place_s,$place_e);
      $log1 = (double)$place_s['log'];
      $log2 = (double)$place_e['log'];
      $lat1 = (double)$place_s['lat'];
      $lat2 = (double)$place_e['lat'];

       $radlog1 = rad($log1);
       $radlog2 = rad($log2);
       $radlat1 = rad($lat1);
       $radlat2 = rad($lat2);

      if ($radlat1 < 0)
          {$radlat1 = pi() / 2 + abs($radlat1);}// south
      if ($radlat1 > 0)
          {$radlat1 = pi() / 2 - abs($radlat1);}// north
      if ($radlog1 < 0)
          {$radlog1 = pi() * 2 - abs($radlog1);}// west
      if ($radlat2 < 0)
          {$radlat2 = pi() / 2 + abs($radlat2);}// south
      if ($radlat2 > 0)
          {$radlat2 = pi() / 2 - abs($radlat2);}// north
      if ($radlog2 < 0)
          {$radlog2 = pi() * 2 - abs($radlog2);}// west

      (double) $x1 =$EARTH_RADIUS * cos($radlog1) * sin($radlat1);
      (double) $y1 =$EARTH_RADIUS * sin($radlog1) * sin($radlat1);
      (double) $z1 =$EARTH_RADIUS * cos($radlat1);

      (double) $x2 =$EARTH_RADIUS * cos($radlog2) * sin($radlat2);
      (double) $y2 =$EARTH_RADIUS * sin($radlog2) * sin($radlat2);
      (double) $z2 =$EARTH_RADIUS * cos($radlat2);

      (double) $d = sqrt(($x1 - $x2) * ($x1 - $x2) + ($y1 - $y2) * ($y1 - $y2)+ ($z1 - $z2) * ($z1 - $z2));

      //余弦定理求夹角
      (double) $theta = acos(($EARTH_RADIUS *$EARTH_RADIUS +$EARTH_RADIUS *$EARTH_RADIUS - $d * $d) / (2 *$EARTH_RADIUS *$EARTH_RADIUS));
      (double) $dist = $theta *$EARTH_RADIUS / 1000;//米 转 千米

      if($dist){
        $sum+=$dist;
      }
    }
    return round($sum,2);
  }
}
