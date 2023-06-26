<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="UTF-8">
    <title>KANZO-FORUM</title>
</head>
<body>

<?php
// DB接続設定(非表示にしています)
$dsn = 'XXX';
$user = 'XXX';
$password = 'XXX';
// DBエラー検出設定
$pdo = new PDO($dsn, $user, $password, array(PDO::ATTR_ERRMODE => PDO::ERRMODE_WARNING));

// ロギング初期設定
$filename = "XXX";
$fp = fopen($filename,"a");
$br = PHP_EOL;
$hr = "---------------------------------------------------";

// 実行ごとにタイトルと日付を記録する関数を設定
// 0-1.ロギングスタート関数
function Logging_start($log_title){
    global $fp, $br, $hr; // グローバル変数の呼び出し
    fwrite($fp,$hr.$br.$log_title.$br
    .date("Y/m/d H:i:s").$br.$hr.$br);
}

// データベースの実行結果を記録する関数を設定
// 0-2.ロギング関数
function Logging($result,$log){
    if($result){
        global $fp, $br; // グローバル変数の呼び出し
        fwrite($fp,"[".$log."]に成功しました".$br);
    }else{
        global $fp, $br;
        fwrite($fp,"[".$log."]に失敗しました".$br);
    }
}

// 実行の終了を記録する関数を設定
// 0-3.ロギングストップ関数
function Logging_stop(){
    global $fp, $br; // グローバル変数の呼び出し
    fwrite($fp,"【終了】".$br);
    fclose($fp); // fcloseは一度しか設定できないのでここで。
}

// [テスト用]テーブルを削除する関数を設定
function DROP_TABLE($pdo){
    $sql = 'DROP TABLE KANZO_TABLE';
    $result = $pdo->query($sql);

    $log = "テーブルの削除";
    Logging($result,$log);
}

// [テスト用]テーブルを作成する関数を設定
function CREATE_TABLE($pdo){
    $sql = "CREATE TABLE IF NOT EXISTS KANZO_TABLE"
    ."("
    // カラム名 データ型 (オプション/MAX文字数) で指定
    . "id INT AUTO_INCREMENT PRIMARY KEY,"
    . "name CHAR(32),"
    . "comment TEXT,"
    . "time DATETIME,"
    . "password CHAR(32)"
    .");";
    
    $log = "テーブルの作成";
    $result = $pdo->query($sql);
    Logging($result,$log);
}
function SHOW_TABLE($pdo){
    $sql = 'SHOW CREATE TABLE KANZO_TABLE';
    $result = $pdo -> query($sql);
    foreach ($result as $row){
        echo $row[1]."<br>"; 
    }
    $log = "テーブルの確認";
    Logging($result,$log);
}

// [テスト用]
// DROP_TABLE($pdo);
// CREATE_TABLE($pdo);
// SHOW_TABLE($pdo);

/*
$sql = 'ALTER TABLE KANZO_TABLE CHANGE pass password VARCHAR(32)';
$pdo->exec($sql); */

        // 投稿用プログラム
        if(!empty($_POST["user_name"]) && !empty($_POST["comment"])
                                        // ※[edit_number = null] で新規投稿を判別
        && !empty($_POST["password"]) && empty($_POST["edit_number"])){

            // ロギングの開始(タイトル設定)
            $log_title = "投稿";
            Logging_start($log_title);
            
            // (新規投稿の場合、パスワードを変数に保存する)
            // 「投稿番号(id)/名前(name)/コメント(comment)/投稿日時(time)/パスワード(password)」の順で掲載
            $sql = $pdo -> prepare("INSERT INTO KANZO_TABLE (name,comment,time,password)
                VALUES (:name,:comment,:time,:password)");

            // 受信した変数をデータベース変数に代入
            $name = $_POST["user_name"];
            $comment = $_POST["comment"];
            $time = date("Y/m/d H:i:s");
            $password = $_POST["password"];

            // プレースホルダーに値をバインド
            $sql -> bindParam(':name',$name,PDO::PARAM_STR);
            $sql -> bindParam(':comment',$comment,PDO::PARAM_STR);
            $sql -> bindParam(':time',$time,PDO::PARAM_STR);
            $sql -> bindParam(':password',$password,PDO::PARAM_STR);

            // INSERT文を実行
            $result = $sql -> execute();

            // ロギング設定
            $log = "データの入力";
            Logging($result,$log);
        }
        
        // 削除用プログラム
        if(!empty($_POST["delete"]) && (!empty($_POST["password"]))){

            // ロギングの開始(タイトル設定)
            $log_title = "削除";
            Logging_start($log_title);

            // 指定した行のパスワードを取得
            $id = $_POST["delete"];
            $sql = 'SELECT password FROM KANZO_TABLE WHERE id = :id';
            $stmt = $pdo->prepare($sql); // WHERE句を用いる場合、prepareが必須
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            $true_pass = $stmt->fetchColumn();

            // ロギング設定
            $log = "本来のパスワードの取得";
            Logging($result,$log);
            
            // データを削除 (取得したパスワードと受信したパスワードが正しい場合)
            if($true_pass === $_POST["password"]){ // [===] ... データ型まで比較できる厳密演算子
                $sql = 'DELETE from KANZO_TABLE WHERE id=:id';
                $stmt = $pdo->prepare($sql); // WHERE句を用いる場合、prepareが必須
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $result = $stmt->execute();

                // ロギング設定
                $log = "データの削除";
                Logging($result,$log);

                 // レコード削除後、にid番号を再設定する (ブラウザ表示されるためロギング不要)
                $sql = 'SET @id = 0';
                $pdo->exec($sql); // idの初期化

                $sql = 'UPDATE KANZO_TABLE SET id = (@id := @id + 1)'; 
                $pdo->exec($sql); // idを連番に振りなおす

                $sql = 'ALTER TABLE KANZO_TABLE AUTO_INCREMENT = 1';
                $pdo->exec($sql); // idの自動増分値を１にリセット

            }else{ // 例外処理とロギング
                $result = false;
                $log = "データの削除";
                Logging($result,$log);
                echo $log."に失敗しました<br>パスワードが違います";
            }
        }
        
        // 編集用プログラム (受信)
        if(!empty($_POST["edit"]) && (!empty($_POST["password"]))){

            // ロギングの開始(タイトル設定)
            $log_title = "編集(受信)";
            Logging_start($log_title);

            // 指定した行のパスワードを取得
            $id = $_POST["edit"];
            $sql = 'SELECT password FROM KANZO_TABLE WHERE id = :id';
            $stmt = $pdo->prepare($sql); // WHERE句を用いる場合、prepareが必須
            $stmt->bindParam(':id', $id, PDO::PARAM_INT);
            $result = $stmt->execute();
            $true_pass = $stmt->fetchColumn();

            // ロギング設定
            $log = "本来のパスワードの取得";
            Logging($result,$log);

            // 編集したいデータを受信 (取得したパスワードと受信したパスワードが正しい場合)
            if($true_pass === $_POST["password"]){ // [===] ... データ型まで比較できる厳密演算子
                // 指定した行のデータを抽出し、配列に格納
                $sql = 'SELECT * FROM KANZO_TABLE WHERE id = :id';
                $stmt = $pdo->prepare($sql); // WHERE句を用いる場合、prepareが必須
                $stmt->bindParam(':id', $id, PDO::PARAM_INT);
                $result = $stmt->execute();
                $results = $stmt->fetchAll();

                // ロギング設定 (ブラウザ上にも表示されるが、重要動作のため)
                $log = "編集データの受信";
                Logging($result,$log);

                foreach ($results as $row){
                    //フォーム(HTML)の変数に代入する (配列の要素はテーブルのカラム名で指定する)
                    $edit_name = $row['name'];
                    $edit_comment = $row['comment'];
                    $edit_pass = $row['password'];
                    $edit_ID = $id; // フォーム出力時のために別変数へ代入
                }

            }else{ // 例外処理とロギング
                $result = false;
                $log = "編集データの受信";
                Logging($result,$log);
                echo $log."に失敗しました<br>パスワードが違います";
            }
        }

        // 編集用プログラム (送信)          ※edit_numberで編集投稿を判別
        if(!empty($_POST["comment"]) && !empty($_POST["user_name"])
        && !empty($_POST["password"])&& !empty($_POST["edit_number"])){

            // ロギングの開始(タイトル設定)
            $log_title = "編集(送信)";
            Logging_start($log_title);

            // 「投稿番号(id)/名前(name)/コメント(comment)/投稿日時(time)/パスワード(password)」の順で編集
            $id = $_POST["edit_number"];
            $sql = $pdo -> prepare('UPDATE KANZO_TABLE SET name=:name,comment=:comment,time=:time,password=:password WHERE id=:id');

            // 受信した変数をデータベース変数に代入
            $name = $_POST["user_name"];
            $comment = $_POST["comment"];
            $time = date("Y/m/d H:i:s");
            $password = $_POST["password"];

            // プレースホルダーに値をバインド
            $sql -> bindParam(':name', $name, PDO::PARAM_STR);
            $sql -> bindParam(':comment', $comment, PDO::PARAM_STR);
            $sql -> bindParam(':time', $time, PDO::PARAM_STR);
            $sql -> bindParam(':password', $password, PDO::PARAM_STR);
            $sql -> bindParam(':id', $id, PDO::PARAM_INT);

            // UPDATE文を実行
            $result = $sql -> execute();

            // ロギング設定
            $log = "編集データの送信";
            Logging($result,$log);
        }
    ?>

<hr>
【入力フォーム】
<hr>
        <!-- 投稿フォーム ＆ 編集機能 (投稿フォームへ編集前データが送られる) -->
    <form action="" method="post">
        <input type="text" name="user_name" placeholder="名前"
        value="<?php echo isset($edit_name) ? $edit_name : ''; ?>">
        <input type="text" name="comment" placeholder="コメント" 
        value="<?php echo isset($edit_comment) ? $edit_comment : ''; ?>">

        <!--パスワード / [tel & maxlength] ⇒ パスワードの桁数を制限-->
        <input type="tel" maxlength="4" name="password" placeholder="パスワード(半角4桁)"
        value="<?php echo isset($edit_pass) ? $edit_pass : ''; ?>">
        <input type="submit" name="submit">

        <!-- 編集機能で投稿フォームへデータを表示した際に、隠し表示で編集番号を呼び出す(これを用いて、新規投稿か編集投稿か判別する)-->
        <input type="hidden" name="edit_number"
        value="<?php echo isset($edit_ID) ? $edit_ID : ''; ?>">
        <br>
    </form>
    
        <!-- 削除フォーム -->
    <form action="" method="post">
        <input type="number" min="1" name="delete" placeholder="削除対象番号">

        <!--パスワード / [tel & maxlength] ⇒ パスワードの桁数を制限-->
        <input type="tel" maxlength="4" name="password" placeholder="パスワード(半角4桁)">
        <input type="submit" name="submit" value="削除">
    </form>
    
        <!-- 編集フォーム -->
    <form action="" method="post">
        <input type="number" min="1" name="edit" placeholder="編集対象番号">

        <!--パスワード / [tel & maxlength] ⇒ パスワードの桁数を制限-->
        <input type="tel" maxlength="4" name="password" placeholder="パスワード(半角4桁)">
        <input type="submit" name="submit" value="編集">
    </form>

<hr>
【投稿フォーム】
<hr>

    <?php

        // 表示
        $sql = 'SELECT * FROM KANZO_TABLE';
        $result = $pdo->query($sql);
        $results = $result->fetchAll();
        foreach ($results as $row){
           //$rowの中にはテーブルのカラム名が入る
            echo $row['id'].' / ';
            echo $row['name'].' / ';
            echo $row['comment'].' / ';
            echo $row['time'];
            echo "<br>";
        }
        
        // ロギング設定
        $log = "データの表示";
        Logging($result,$log);

        // ロギングの終了
        Logging_stop();
    ?>
</body>
</html>