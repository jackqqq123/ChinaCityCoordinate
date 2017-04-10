## 中国行政区经纬度查找、距离计算
```
├─ChinaCity  类库目录
│ │  
│ ├─mapfile               数据文件存放目录
│ │  ├─city.json          中国行政区经纬度(无论市还是区都算成 省的children)
│ │  └─nation.json        中国民族数据(用于处理民族自治区)
│ │  
│ ├─coordinate.php        核心类库
│ │  
│ ├─tool.php              工具类函数(包括生成错误日志函数)
│ │  
│ ├─log.txt               日志记录，拷贝类库可以不带他走
│ │  
│ └─README.md             说明文档
│
└─mapfile  数据备份

```

### 一、类库文件安装
tp3.1

* 放到以下路径：`ThinkPHP/Extend/Vendor`
* 控制器引入类库  
```
<?php
// 本类由系统自动生成，仅供测试用途
Vendor('ChinaCity.coordinate');
class IndexAction extends Action {
  public function city(){
    $coor=new \coordinate();
    $coor->getTest();
  }
}
//如果成功，应该会看到-您已成功引用ChinaCity:coordinate.php
```

### 二、类库主要输出函数

#### getTest  检查是否引入
@ 输出：var_dump(您已成功引用ChinaCity:coordinate.php)
栗子：  
```
  $coor->getTest();
```
#### distance  根据输入地区计算这些地区的距离
@ 输入：地区数组[array]（支持两个及两个以上地点）  
@ 输出：累加距离[float] / false[boolean]  
@ 栗子：    
```
  //支持 忽略 省市区、X族自治区 等字眼
  $area=array("上海","湖北省-武汉市","佛山")
  var_dump( $coor->getTest($area) );

  //输出 --->  float(1529.42)
```
#### find  查询地区经纬度
@ 输入：地区名[string]  
@ 输出：地区信息[array] / false[boolean]  
@ 记录日志：ChinaCity/log.txt 找不到的地区会记录在记录日志  
@ 栗子：  
```
  //支持 忽略 省市区、X族自治区 等字眼
  var_dump( $coor->find("武汉") );

  //支持 ‘ - ’ 连接省市区
  var_dump( $coor->find("广东省-佛山市-顺德区") );

  //找不到的地区会 往上 去找 正确的行政区
  var_dump( $coor->find("广东省-佛山市-顺德错误示范") );

  /*输出 --->
  array(3) {
    ["name"]=>
    string(6) "武汉"
    ["log"]=>
    string(6) "114.31"
    ["lat"]=>
    string(5) "30.52"
  }
  array(3) {
    ["name"]=>
    string(6) "顺德"
    ["log"]=>
    string(6) "113.24"
    ["lat"]=>
    string(5) "22.84"
  }
  array(3) {
    ["name"]=>
    string(6) "佛山"
    ["log"]=>
    string(6) "113.11"
    ["lat"]=>
    string(5) "23.05"
  }
  */
```

### 三、日志文件、补充地区
日志文件记录了找不到经纬度的城市，定期查看，并从网上查找该地区经纬度补充进来，世界和平指日可待。  
#### 更新经纬度
* 在log.txt复制城市名称
* 登入[GPS经纬度](http://www.gpsspg.com/maps.htm)查找城市
* 复制百度地图坐标
* 打开`mapfile/city.json`找到 该城市所属省份 在其children下 添加如下信息
```
  //注意严格按照json格式   
  //log-经度(GPS经纬度中右边那个参数)  
  //lat-纬度(GPS经纬度中左边那个参数)  

 {"name":"民权", "log":"115.13", "lat":"34.65"},
```
