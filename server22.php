

    <?php
        function connect_to_database(){
            $link = @mysqli_connect( 
                'localhost',  // MySQL主機名稱 
                'test',       // 使用者名稱 
                'danny',  // 密碼 
                'test7');  // 預設使用的資料庫名稱 
            if ( !$link ) {
            echo "MySQL資料庫連接錯誤!<br/>";
            exit();
            }
            else {
            echo "MySQL資料庫test連接成功!<br/>";
            }
            return $link;
        }
        function insert($link,$out){
            // sql語法存在變數中
            echo "in data";
            $sql = "INSERT INTO `mytest` (`name`, `num1`, `num2`) VALUES ('$out[0]', '$out[1]', '$out[2]');";

            // 用mysqli_query方法執行(sql語法)將結果存在變數中
            $result = mysqli_query($link,$sql);

            // 如果有異動到資料庫數量(更新資料庫)
            if (mysqli_affected_rows($link)>0) {
            // 如果有一筆以上代表有更新
            // mysqli_insert_id可以抓到第一筆的id
            $new_id= mysqli_insert_id ($link);
            echo "新增後的id為 {$new_id} ";
            }
            elseif(mysqli_affected_rows($link)==0) {
                echo "無資料新增";
            }
            else {
                echo "{$sql} 語法執行失敗，錯誤訊息: " . mysqli_error($link);
            }
            //mysqli_close($link); 
            //return $result;

        }
        function close($link){
            mysqli_close($link);
        }
        

        $link = connect_to_database();

        $host = "172.20.10.2";
        $port = 2391;

        //socket_close($sock);

        // No Timeout 
        set_time_limit(0);

        //Create Socket
        $sock = socket_create(AF_INET, SOCK_STREAM, 0) or die("Could not create socket\n");

        //Bind the socket to port and host
        $result = socket_bind($sock, $host, $port) or die("Could not bind to socket\n");

        while(true) {
 
            //Start listening to the port
            $result = socket_listen($sock, 3) or die("Could not set up socket listener\n");

            //Make it to accept incoming connection
            $spawn = socket_accept($sock) or die("Could not accept incoming connection\n");

            //Read the message from the client socket
        
                $input = socket_read($spawn, 4194304) or die("Could not read input\n");
                echo "<br/>";
                


                if(strlen($input)>200){
                    //這是把string轉成mp3檔
                    $file = fopen("temp.mp3","wb"); 
                    fwrite($file,$input); 
                    fclose($file); 


                    $size_of = filesize("temp.mp3");
                    echo $size_of;

                }
                else{
                    //這個是當我指令為確認時，他的塞進資料庫之動作
                    $output = "Need";
                    $input = substr($input,1,-1);
                    $input = str_replace('"',"",$input);


                    echo $input;
                    
                    
                    $str=explode(",",$input);
                    print_r($str);
                    insert($link,$str);

                }
            
            //此為處理語音辨識的部分
            if($output != "Need"){

                $output="wait";

                if(file_exists('temp.wav')){
                    unlink('temp.wav');
                }
                
                exec("C:\PATH_Programs\bin\\ffmpeg -y  -i temp.mp3  temp.wav");              

               

                

                header("Content-type: text/html; charset=utf-8");
               

                $command2 = escapeshellcmd('C:\Users\Danny\AppData\Local\Programs\Python\Python310\python.exe try.py');
                $out = exec($command2, $output, $ret_code);
                
               
                print_r($out);
                
                
                $out = trim($out);



                if ($out != ""){
                    echo "HERE";
                    print_r($out);
                    
                    if($out=="離開"){
                        //因server會一直監聽導致port被占用，故此會close port
                        break;
                    }
                   
                    else{

                        $output = json_encode($out);
                        socket_write($spawn, $output, strlen ($output)) or die("Could not write output\n");
                        continue;
          

                    }
                    

                }
            }
                

            
        }
        close($link);
        socket_close($sock);




        
        
    ?>
