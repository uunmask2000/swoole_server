<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Title</title>
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
    <script type="text/javascript" src="/swoole_server/libs/js/websocket.js"></script>
    <script>
        //手牌
        var CardName = function (str) {
            var newStr = '';

            str = str.toString();
            switch (str[0]){
                case '1':
                    newStr = str[1] + "萬";
                    break;
                case '2':
                    newStr = str[1] + "筒";
                    break;
                case '3':
                    newStr = str[1] + "索";
                    break;
                case '4':
                    //echo "i equals 2";
                    switch (str[1]){
                        case '1':
                            newStr = "東";
                            break;
                        case '2':
                            newStr = "南";
                            break;
                        case '3':
                            newStr = "西";
                            break;
                        case '4':
                            newStr = "北";
                            break;
                        case '5':
                            newStr = "中";
                            break;
                        case '6':
                            newStr = "發";
                            break;
                        case '7':
                            newStr = "白";
                            break;
                    }
            }
            return newStr;

        }
    function Player_setting() {



	}

	$(function () {
		window.Room_setting   = <?=$_GET['roomId']?>;
        window.Player_setting = <?=$_GET['playerId']?>;
        if (window.Player_setting != null)
        {
         //alert(Player_setting);
		   document.getElementById("Player_n").innerHTML= window.Player_setting ;
		   document.getElementById("Room_setting").innerHTML= window.Room_setting ;
        }

        var msg = document.getElementById('msg');
        var usr1 = document.getElementById('usr1');
        var usr2 = document.getElementById('usr2');
        var usr3 = document.getElementById('usr3');
        var usr4 = document.getElementById('usr4');
        var round = document.getElementById('round');
        var ws = new WebsocketClass(msg, usr1, usr2, usr3, usr4, window.Room_setting, window.Player_setting);
	    //var room_key = 1 ;


		$(document).on("click", "button", function () {
			// 判斷牌合
			var RO= ($('#msg').children('#button0').length  );
			//alert(RO);

			if ( RO == 84 ) {
				//alert("開局");
			} else if( RO != 0  ) {
				//alert(RO);
			} else {
				//alert(RO);
			}

			if($(this).attr('id') == 'getCard'){
				alert('A');
				ws.send(window.Room_setting, window.Player_setting, 1, "getCard", 0);
			} else if($(this).attr('id') == 'openRound') {
				ws.send(window.Room_setting, window.Player_setting, 1, "getCard", 0);
			} else {
				//alert($(this).attr('id'));
				//alert('B');
				//var cardId = $(this).val();
				//ws.send(1, Player_setting , 2, "outCard", cardId);
				switch ($(this).attr('id')) {

					case "button1":
						if (window.Player_setting == 1) {
							var cardId = $(this).val();
							ws.send(window.Room_setting, window.Player_setting , 2, "outCard", cardId);
						}
						break;
					case "button2":
						//alert("hello");
						if (Player_setting == 2) {
							var cardId = $(this).val();
							ws.send(window.Room_setting, window.Player_setting , 2, "outCard", cardId);
						}
						break;
					case "button3":
						if(window.Player_setting == 3){
							var cardId = $(this).val();
							ws.send(window.Room_setting, window.Player_setting , 2, "outCard", cardId);
						}
						break;
					case "button4":
						if(window.Player_setting == 4){
							var cardId = $(this).val();
							ws.send(window.Room_setting, window.Player_setting , 2, "outCard", cardId);
						}
						break;
				}
			}
		});
	});

    </script>
</head>
<body>
Room :　<span id="Room_setting">_</span>
<br>
Player :　<span id="Player_n">_</span>
<br>
CardBox:<div id="msg"></div>
Player1:<div id="usr1"></div>
Player2:<div id="usr2"></div>
Player3:<div id="usr3"></div>
Player4:<div id="usr4"></div>
<!--
<button id="getCard">拿牌</button>
-->
<button id="openRound">開局</button>
<!-- <button id="getcard">getCard</button> -->
<!-- <input type="text" id="text">
<input type="button" value="dong" id="text" onclick="ws.send(this.value)">
<input type="button" value="nan"  id="text" onclick="ws.send(this.value)">
<input type="button" value="xi"   id="text" onclick="ws.send(this.value)">
<input type="button" value="bei"  id="text" onclick="ws.send(this.value)">
<input type="submit" value="发送数据" onclick="song()"> -->
</body>

</html>
