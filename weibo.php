<?php
    //创建数据库
    $db =@ mysql_connect('localhost','root','123');
    //设置数据库默认字符为utf8
    mysql_query("set names 'utf-8'");
    mysql_query("CREATE DATABASE weibo");

    mysql_select_db('weibo');

$sql = <<< END
CREATE TABLE  `weibo`.`message` (
`ID` INT NOT NULL AUTO_INCREMENT PRIMARY KEY ,
`content` TEXT NOT NULL ,
`time` INT NOT NULL ,
`acc` INT NOT NULL ,
`ref` INT NOT NULL
) CHARACTER SET utf8 COLLATE utf8_general_ci
END;
//COLLATE utf8_general_ci :数据库校对规则，三部分分别是数据库字符集、解析不明白、区分大小写

    mysql_query($sql);

    //开始

    $act = $_GET['act'];
    $PAGE_SIZE = 6;

    switch ($act) {
      case 'add':
        //将url中的中文字符转化为utf8字符编码
        $content = urlencode($_GET['content']);
        $time = time();
        //将$content中的换行符号/n替换为空
        $content = str_replace("/n","",$content);

        $sql = "INSERT INTO message (ID,content,time,acc,ref) VALUES (0,'{$content}',{$time},0,0)";

        mysql_query($sql);

        $res = mysql_query("SELECT LAST_INSERT_ID()");

        $rows = mysql_fetch_row($res);

        $id = (int)$rows[0];

        echo "{\"error\":0,\"id\":{$id},\"time\":{$time}}";
        break;
      case 'get':
        $page = (int)$_GET['page'];
        if($page<1){
          $page = 1;
        }

        $s = ($page - 1)*$PAGE_SIZE;

        $sql = "SELECT ID,content,time,acc,ref FROM message ORDER BY time DESC LIMIT {$s},{$PAGE_SIZE}";

        $res = mysql_query($sql);
        //创建数组
        $aResult = array();
        while($row = mysql_fetch_array($res)){
          //创建数组
          $arr = array();
          array_push($arr,'"id":'.$row[0]);
          array_push($arr,'"content":"'.$row[1].'"');
          array_push($arr,'"time":'.$row[2]);
          array_push($arr,'"acc":'.$row[3]);
          array_push($arr,'"ref":'.$row[4]);
          array_push($aResult,implode(',',$arr));
        }
        if(count($aResult)>0){
          echo '[{'.implode('},{',$aResult).'}]';
        }else{
          echo '[]';
        }
        break;
      case 'acc':
        $id = (int)$_GET['id'];
        $res = mysql_query("SELECT acc FROM message WHERE ID={$id}");
        $row = mysql_fetch_array($res);
        $old = (int)$row[0]+1;
        $sql = "UPDATE message SET acc={$old} WHERE ID={$id}";
        mysql_query($sql);
        echo "{\"error\":0,\"acc\":{$old}}";
        break;
       case 'ref':
          $id = (int)$_GET['id'];
          $res = mysql_query("SELECT ref FROM message WHERE ID={$id}");
          $row = mysql_fetch_array($res);
          $old = (int)$row[0]+1;
          $sql = "UPDATE message SET ref={$old} WHERE ID={$id}";
          mysql_query($sql);
          echo "{\"error\":0,\"ref\":{$old}}";
          break;
       case 'del':
          $id = (int)$_GET['id'];
          $sql="DELETE FROM message WHERE ID={$id}";
          mysql_query($sql);
          echo '{"error":0}';
          break;
      default:
        # code...
        break;
    }
