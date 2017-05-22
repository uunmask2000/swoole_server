<!DOCTYPE HTML>
<html>
   <head>
	<script src="http://ajax.googleapis.com/ajax/libs/jquery/1.9.1/jquery.min.js" type="text/javascript"></script>
      <script type="text/javascript">
         function WebSocketTest()
         {
            if ("WebSocket" in window)
            {
               alert("WebSocket is supported by your Browser!");
               
               // Let us open a web socket
                var ws = new WebSocket("ws://172.17.10.30:9511");				
				//var ws = new WebSocket("ws://172.17.10.30/majong_websocket");
               ws.onopen = function()
               {
                  // Web Socket is connected, send data using send()
                  //ws.send("Message to send");
                  //alert("Message is sent...");
               };
				
               ws.onmessage = function (evt) 
               { 
                  var received_msg = evt.data;
				  obj = JSON.parse(received_msg);
				  document.getElementById("demo3").innerHTML =received_msg;
						var player_hand1 = JSON.parse("[" + obj.cardData.player1 + "]");
						var player_hand2 = JSON.parse("[" + obj.cardData.player2 + "]");
						var player_hand3 = JSON.parse("[" + obj.cardData.player3 + "]");
						var player_hand4 = JSON.parse("[" + obj.cardData.player4 + "]");
						var endCard = JSON.parse("[" + obj.cardData.endCard + "]");
						var Round = JSON.parse("[" + obj.Round + "]");
						var X = JSON.parse("[" + obj.rounds_number + "]");
						//alert(X);
						document.getElementById("endCard").innerHTML =endCard;
						document.getElementById("player_hand1").innerHTML =player_hand1;
						document.getElementById("player_hand2").innerHTML =player_hand2;
						document.getElementById("player_hand3").innerHTML =player_hand3;
						document.getElementById("player_hand4").innerHTML =player_hand4;
						document.getElementById("Round").innerHTML =Round;
						document.getElementById("X").innerHTML =X;
                  //alert("Message is received...");
				  
					var data = {
								fn: "記錄檔",
								endCard: endCard,
								player_hand1: player_hand1,
								player_hand2: player_hand2,
								player_hand3: player_hand3,
								player_hand4: player_hand4,
								Round : Round ,
								rounds_number : X 
					};

					$.post("log.php", data);
				  
               };
				
               ws.onclose = function()
               { 
                  // websocket is closed.
                  alert("Connection is closed..."); 
               };
			   
				function OnSocketError(ev)
				{
				output("Socket error: " + ev.data);
				}
            }
            
            else
            {
               // The browser doesn't support WebSocket
               alert("WebSocket NOT supported by your Browser!");
            }
         }
      </script>
		
   </head>
   <body onload="javascript:WebSocketTest()">
   <!--
      <div id="sse">
         <a href="javascript:WebSocketTest()">Run WebSocket</a>
      </div>
	--->  
	  <p id="demo3"></p>
		<H4 id="endCard"></H4>
		<H4 id="player_hand1"></H4>
		<H4 id="player_hand2"></H4>
		<H4 id="player_hand3"></H4>
		<H4 id="player_hand4"></H4>
		<H4 id="Round"></H4>
		<H4 id="X"></H4>
      
   </body>
</html>