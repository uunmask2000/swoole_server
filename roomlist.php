<?php

	

?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="/swoole_server/libs/js/websocket.js"></script>
    <script type="text/javascript">
    	$(function (){
            var wsServer = 'ws://172.17.10.30:9511';
            //var wsServer = 'ws://172.17.10.30/majong_websocket';
            //调用websocket对象建立连接：
            //参数：ws/wss(加密)：//ip:port （字符串）
            var websocket = new WebSocket(wsServer);
            //onopen openConnect
            websocket.onopen = function(evt) {
                //websocket.readyState 属性：
                /*
                CONNECTING    0    The connection is not yet open.
                OPEN    1    The connection is open and ready to communicate.
                CLOSING    2    The connection is in the process of closing.
                CLOSED    3    The connection is closed or couldn't be opened.
                */
                for(var i = 1; i <= 5; i++){
                   var sendObj = {
                        roomId: i,
                        player: 0,
                        type: 100,
                        event: 'getRoomPeople',
                        data: ""
                    };
                    websocket.send(JSON.stringify(sendObj)); 
                }
            };
            // setInterval(function (){
            //     for(var i = 1; i <= 5; i++){
            //        var sendObj = {
            //             roomId: i,
            //             player: 0,
            //             type: 0,
            //             event: 'getRoomPeople',
            //             data: ""
            //         };
            //         websocket.send(JSON.stringify(sendObj)); 
            //     }
            // }, 1000);
            //onmessage 监听服务器数据推送
            websocket.onmessage = function(evt) {
                console.log(evt);
                console.log(JSON.parse(evt.data));
                var data = JSON.parse(evt.data);
                var str = '#room' + data['userData'].roomId;
                $(str).text("游戏室人数: " + data.peopleNum + '人');
                
            };

    		$("button").click(function (){
    			//console.log($(this).attr("id"));
    			var roomId = $(this).attr("id");
    			var playerId = $("#username").val();
    			location.href = "./websoc_cli.php?roomId=" + roomId + "&playerId=" + playerId;
    			//var ws = new WebsocketClass(msg, usr1, usr2, usr3, usr4, roomId, playerId);
    		});
    	});
    	

    </script>
</head>
<body>
<br>
player<input type="text" id="username"><br>
<button id="1">遊戲室1</button><div id="room1"></div>
<button id="2">遊戲室2</button><div id="room2"></div>
<button id="3">遊戲室3</button><div id="room3"></div>
<button id="4">遊戲室4</button><div id="room4"></div>
<button id="5">遊戲室5</button><div id="room5"></div>

<!-- <button id="getcard">getCard</button> -->
<!-- <input type="text" id="text">
<input type="button" value="dong" id="text" onclick="ws.send(this.value)">
<input type="button" value="nan"  id="text" onclick="ws.send(this.value)">
<input type="button" value="xi"   id="text" onclick="ws.send(this.value)">
<input type="button" value="bei"  id="text" onclick="ws.send(this.value)">
<input type="submit" value="发送数据" onclick="song()"> -->
</body>

</html>
