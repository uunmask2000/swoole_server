<!DOCTYPE HTML>
<html>
   <head>
	
      <script type="text/javascript">
         function WebSocketTest()
         {
            if ("WebSocket" in window)
            {
               alert("WebSocket is supported by your Browser!");
               
               // Let us open a web socket
               //var ws = new WebSocket("ws://localhost:9998/echo");
				var ws = new WebSocket("ws://172.17.10.30:9521");
               ws.onopen = function()
               {
				   //alert("onopen...");
                  // Web Socket is connected, send data using send()
                  //ws.send("Message to send");
                  //alert("Message is sent...");
				// Web Socket 已连接上，使用 send() 方法发送数据
				var sendObj = {
				roomId: 2,
				player: 1,
				type: 0,
				event: 'initCard',
				data: ""
				};
				ws.send(JSON.stringify(sendObj));
				///ws.send("发送数据");
				alert("数据发送中...");
               };
				
               ws.onmessage = function (evt) 
               { 
                  var received_msg = evt.data;
                  alert("Message is received...");
               };
				
               ws.onclose = function()
               { 
                  // websocket is closed.
                  alert("Connection is closed..."); 
               };
            }
            
            else
            {
               // The browser doesn't support WebSocket
               alert("WebSocket NOT supported by your Browser!");
            }
         }
      </script>
		
   </head>
   <body>
   
      <div id="sse">
         <a href="javascript:WebSocketTest()">Run WebSocket</a>
      </div>
      
   </body>
</html>