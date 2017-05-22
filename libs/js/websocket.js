    var WebsocketClass = function (msg, usr1, usr2, usr3, usr4, Room_setting, Player_setting) {

        var wsServer = 'ws://172.17.10.30:9511';
	//var wsServer = 'ws://172.17.10.30/majong_websocket';
        //调用websocket对象建立连接：
        //参数：ws/wss(加密)：//ip:port （字符串）
        var websocket = new WebSocket(wsServer);
        this.websocket = websocket;

        this.msg  = msg;
        this.usr1 = usr1;
        this.usr2 = usr2;
        this.usr3 = usr3;
        this.usr4 = usr4;
        
        var self = this;

        //onopen openConnect
        websocket.onopen = function(evt) {
            //websocket.readyState 属性：
            /*
            CONNECTING    0    The connection is not yet open.
            OPEN    1    The connection is open and ready to communicate.
            CLOSING    2    The connection is in the process of closing.
            CLOSED    3    The connection is closed or couldn't be opened.
            */
            console.log(websocket.readyState);
            var sendObj = {
                roomId: Room_setting,
                player: Player_setting,
                type: 7,
                event: 'JoinRoom',
                data: ""
            };

            websocket.send(JSON.stringify(sendObj));

            var sendObj = {
                roomId: Room_setting,
                player: Player_setting,
                type: 0,
                event: 'initCard',
                data: ""
            };
            websocket.send(JSON.stringify(sendObj));

            
        };
        //onmessage 监听服务器数据推送
        websocket.onmessage = function(evt) {
            console.log(evt);
            console.log(JSON.parse(evt.data));
            var data = JSON.parse(evt.data);
         
            Event(msg, usr1, usr2, usr3, usr4, data.userData.event, data);
            
            return evt;
            //msg.innerHTML += evt.data +'<br>';
            //console.log('Retrieved data from server: ' + evt.data);
        };
        //监听连接错误信息
        websocket.onerror = function(evt, e) {
            console.log('Error occured: ' + evt.data);
            return evt;
        };
    };

    WebsocketClass.prototype.send = function(roomId, playerId, type, event, data) {
        //console.log(value);
        var sendObj = {            
            roomId: roomId,
            player: playerId,
            type: type,
            event: event,            
            data: data
        };
        //向服务器发送数据
        this.websocket.send(JSON.stringify(sendObj));
    }

    

    var Event = function (msg, usr1, usr2, usr3, usr4, event, data) {
        switch(event){
            case 'initCard':
                render(msg, usr1, usr2, usr3, usr4, data);
                break;

            case 'getCard':
                render(msg, usr1, usr2, usr3, usr4, data);
                break;

            case 'outCard':
                render(msg, usr1, usr2, usr3, usr4, data);
                break;
			case 'RoundEnd':
                render(msg, usr1, usr2, usr3, usr4, data);
                break;	
        }


    }

    var render = function (msg, usr1, usr2, usr3, usr4, data) {
        var endCard = data.cardData.endCard;
        var newEndCard = [];
        while (msg.firstChild) {
            msg.removeChild(msg.firstChild);
        }
        while (usr1.firstChild) {
            usr1.removeChild(usr1.firstChild);
        }

        while (usr2.firstChild) {
            usr2.removeChild(usr2.firstChild);
        }
        while (usr3.firstChild) {
            usr3.removeChild(usr3.firstChild);
        }
        while (usr4.firstChild) {
            usr4.removeChild(usr4.firstChild);
        }

        endCard.forEach(function (entry){            
            var node = document.createElement("button");
            node.setAttribute("id", "button0");
            node.setAttribute("value", entry);
            node.innerHTML = entry;
            msg.appendChild(node);
        });

        var player1 = data.cardData.player1;
        
        player1.forEach(function (entry){            
            var node = document.createElement("button");
            node.setAttribute("id", "button1");
            node.setAttribute("value", entry);
            node.innerHTML = entry;
            usr1.appendChild(node);
        });

        var player2 = data.cardData.player2;
        player2.forEach(function (entry){            
            var node = document.createElement("button");
            node.setAttribute("id", "button2");
            node.setAttribute("value", entry);
            node.innerHTML = entry;
            usr2.appendChild(node);
        });

        var player3 = data.cardData.player3;
        player3.forEach(function (entry){            
            var node = document.createElement("button");
            node.setAttribute("id", "button3");
            node.setAttribute("value", entry);
            node.innerHTML = entry;
            usr3.appendChild(node);
        });

        var player4 = data.cardData.player4;
        player4.forEach(function (entry){            
            var node = document.createElement("button");
            node.setAttribute("id", "button4");
            node.setAttribute("value", entry);
            node.innerHTML = entry;
            usr4.appendChild(node);
        });
    }
