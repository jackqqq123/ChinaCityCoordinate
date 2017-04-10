<?php
function tool(){
  var_dump('tool is required');
}
/**
*遍历对象找到 目标
*
*/
function array_find($array,$target,$loop=true){
  $NAME='name';
  $CHILD='children';
  $result="";
  $arr=$array;
  if(is_array($arr)){
    $result=false;
    foreach ($arr as $key => $value) {
      $name=$value[$NAME];
      $child=(isset ($value[$CHILD]))?$value[$CHILD]:"";
      if($name==$target){
        // unset($arr[$key][$CHILD]);
        $result=$arr[$key];
        break;
      }
      if($loop){
        $result=array_find($child,$target);
        if($result){break;}
      }
    }
  }
  return $result;
}
/**
*遍历对象找到 目标
*
*/
function array_find_array($array,$target_arr){
  $NAME='name';
  $CHILD='children';
  $result=false;
  $arr=$array;

  if(is_array($arr)){
    foreach ($arr as $key => $value) {

      $name=$value[$NAME];
      if(in_array($name,$target_arr)){

        // unset($arr[$key][$CHILD]);
        $arr_linshi=$arr[$key];
        unset($arr_linshi[$CHILD]);
        if(!$result){
          $result=array();
        }
        array_push($result,$arr_linshi);

      }

      $child=(isset ($value[$CHILD]))?$value[$CHILD]:"";
      if($child){
        $value=array_find_array($child,$target_arr);
        if($value){
          if(!$result){
            $result=array();
          }
          $result=array_merge($result,$value);
        }
      }

    }
  }
  return $result;
}
//函数:计时函数
//用法:Echo Runtime(1);
function Runtime($mode=0){
    Static $s=0;
    if($mode){
        $s=$mode;
    }
    $e=microtime();
    $s=explode(" ", $s);
    $e=explode(" ", $e);
    return "<br>".sprintf("%.2f ms",($e[1]+$e[0]-$s[1]-$s[0])*1000)."<br>";
}
//数组 键值转 unicode
//栗子：$str=urldecode(json_encode ( array_urlencode($nation_arr)) );
function array_urlencode($data){
	if(is_array($data)){
		foreach($data as $key=>$val){
			$data[$key] = _urlencode($val);
		}
		return $data;
	}else	{
		return urlencode($data);
	}
}
/**
* 转化为弧度(rad)
* */
function rad($d)
{
  return $d * pi() / 180.0;
}
/**
* log日志追加
*
*/
function logs($text,$type="log"){
  try{
    if(gettype($text)=="array"){
      $text=urldecode(json_encode (array_urlencode($text) ));
    }
    $text=(string)$text . " \r\n";

    $type="【 $type 】 ";

    if($text){
      $time=date("Y-m-d h:i:s",time());
      $data=$time . $type . $text;
      file_put_contents(__DIR__."\log.txt",$data,FILE_APPEND | LOCK_EX);
    }
  }catch(EXCEPTION $err){
    $err=serialize($err);
    if(!$err){$err="logs错误";}
    logs($err,"logs错误");
  }
}
