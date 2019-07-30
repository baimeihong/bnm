<?php
$page = isset($_GET['page']) ? $_GET['page'] : 1;
$search = isset($_GET['title']) ? $_GET['title'] : '';
$redis = new Redis();
$redis->connect('127.0.0.1', 6379);
if ($search == '') {
    if ($redis->hExists("data", "data_$page")) {
        $res = $redis->hget('data', "data_$page");
        $arr = array('code' => 1, 'msg' => '查询成功', 'data' => json_decode("$res", true));

        echo json_encode($arr);die;
    }
} else {
    if( $redis->hexists('data',"data_$page&$search")){
        $data=json_decode($redis->hGet('data',"data_$page&$search"));
        $reslut=['code'=>1,'msg'=>'查询成功','data'=>$data];
        echo json_encode($reslut);
        die();
    }
}
$dbh = new PDO('mysql:host=127.0.0.1;dbname=hhh', 'root', 'root');

$size = 3;
$where="1=1";
if(!empty($search)){
    $where.=" and title  like '%$search%'";
}
$sql = "select * from title";

$count = $dbh->query($sql);

$rowcount = $count->rowCount();

//     print_r($rowcount);die;

$last = ceil($rowcount / $size);

$prev = $page <= 1 ? 1 : $page - 1;
$next = $page >= $last ? $last : $page + 1;

$limit = ($page - 1) * $size;

$sql1 = "select  *  from  title  where  $where  order by  'id' limit $limit,$size";

$res = $dbh->query($sql1);

$data = $res->fetchAll(2);
$result = [
    'page' => $page,
    'count' => $rowcount,
    'prev' => $prev,
    'next' => $next,
    'data' => $data
];
if($search==""){
    $redis->hSet("data","data_$page",json_encode($result));
}else{
    $redis->hSet("data","data_$page&$search",json_encode($result));
}
//$redis->hSet("data", "data_$page", json_encode($result));
//$arr = array('code' => 1, 'msg' => '查询成功', 'data' => $result);
if(count($result)==0){
    $reslut=['code'=>'1','msg'=>'没有数据','data'=>$result];
}else{
    $reslut=['code'=>'0','msg'=>'查询成功','data'=>$result];
}
echo json_encode($reslut);

?>