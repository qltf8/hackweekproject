<!DOCTYPE html>
<html>
<head>
<meta charset='UTF-8' />
<link rel="stylesheet" type="text/css" href="bootstrap-3.3.5-dist/css/bootstrap-theme.min.css">
<link rel="stylesheet" type="text/css" href="bootstrap-3.3.5-dist/css/bootstrap.min.css">
<link rel="stylesheet" type="text/css" href="mystyle.css">
</head>
<body>	
<?php 
$colours = array('007AFF','FF7000','FF7000','15E25F','CFC700','CFC700','CF1100','CF00BE','F00');
$user_colour = array_rand($colours);
?>

<script type="text/javascript" src="http://ajax.googleapis.com/ajax/libs/jquery/1.8.3/jquery.min.js"></script>
<script type="text/javascript" src="./jquery.qqFace.js"></script> 

<script language="javascript" type="text/javascript">  
$(document).ready(function(){
	/*  websocket  part*/
	//create a new WebSocket object.
	var wsUri = "ws://melody-88f4zc63.cloudapp.net:1337/demo/server.php"; 	//please change this
	websocket = new WebSocket(wsUri); 
	websocket.onopen = function(ev) {
		// connection is open 
		$('.discussion').append("<li class='system_css'><div class=\"system_msg\">Connected!</div><li>"); //notify user
	}

	$('#send-btn').click(function(){
		//use clicks message send button	
		var mymessage = $('#saytext').val(); //get message text
		var myname = username; //get user name
		if(mymessage == ""){ //emtpy message?
			alert("Enter Some message Please!");
			return;
		}
		//prepare json data
		var msg = {
		message: mymessage,
		name: myname,
		color : '<?php echo $colours[$user_colour]; ?>',
		image: 0 //is not an image data
		};
		
		//convert and send data to server
		websocket.send(JSON.stringify(msg));
	});
	
	//#### Message received from server? 
	websocket.onmessage = function(ev) {
		var msg = JSON.parse(ev.data); //PHP sends Json data
		var type = msg.type; //message type
		var umsg = msg.message; //message text
		var uname = msg.name; //user name
		var ucolor = msg.color; //color
		var image=msg.image; //whether is a image data or not
		if(type == 'usermsg') 
		{
			if (image==0) {//text data
				umsg=replace_em(umsg);																									
				if(!$('.discussion').find('.other, .self').length){
					$('.discussion').append('<li class="other"><div class="avatar"><span class="label label-primary">'+uname+'</span></div> <div class="messages" style="color:#'+ucolor+'"><p>'+umsg+'</p></div></li>');
				}else{
					var class_name=$('.discussion').find('.other, .self').last().attr('class');
					if (class_name=='other') {
						$('.discussion').append('<li class="self"><div class="avatar"> <span class="label label-primary">'+uname+'</span></div> <div class="messages" style="color:#'+ucolor+'"><p>'+umsg+'</p></div></li>');
					}else{
						$('.discussion').append('<li class="other"><div class="avatar"> <span class="label label-primary">'+uname+'</span></div> <div class="messages" style="color:#'+ucolor+'"><p>'+umsg+'</p></div></li>');
					}
				}
			//$('#discussion').append("<div><span class=\"user_name\" style=\"color:#"+ucolor+"\">"+uname+"</span> : <span class=\"user_message\">"+umsg+"</span></div>");
			}else if(image==1){//image data
				
				if(!$('.discussion').find('.other, .self').length){
					$('.discussion').append('<li class="other"><div class="avatar"> <span class="label label-primary">'+uname+'</span></div> <div class="messages"><p><img class="show_image" src="'+umsg+'"></p></div></li>');
				}else{
					var class_name=$('.discussion').find('.other, .self').last().attr('class');
					if (class_name=='other') {
						$('.discussion').append('<li class="self"><div class="avatar"> <span class="label label-primary">'+uname+'</span></div> <div class="messages"><p><img class="show_image" src="'+umsg+'"></p></div></li>');
					}else{
						$('.discussion').append('<li class="other"><div class="avatar"> <span class="label label-primary">'+uname+'</span></div> <div class="messages"><p><img class="show_image" src="'+umsg+'"></p></div></li>');
					}
				}
			}
		}
		if(type == 'system')
		{
			$('.discussion').append("<li class='system_css'><div class=\"system_msg\">"+umsg+"</div></li>");
		}
		
		$('#saytext').val(''); //reset text
	};
	
	
	$('#imagefile').on('change', function(e){
		//Get the first (and only one) file element
		//that is included in the original event
		var file = e.originalEvent.target.files[0];
		if(file.size>20*1024){
			alert('upload size must be small than 20KB');
		}else{
			$('input#filename').val(file.name);
			reader = new FileReader();
			//When the file has been read...
			reader.onload = function(evt){
				var mymessage=evt.target.result;
					var myname = username;
				//$('.discussion').append('<li class="self"><div class="avatar"> <span class="label label-primary">'+myname+'</span></div> <div class="messages"><p><img class="show_image" src="'+mymessage+'"></p></div></li>');
				var msg = {
					message: mymessage,
					name: myname,
					color : '<?php echo $colours[$user_colour]; ?>',
					image : 1
				};
		
				websocket.send(JSON.stringify(msg));
			};
			//And now, read the image and base64
			reader.readAsDataURL(file);
		}
	});
	websocket.onerror	= function(ev){$('.discussion').append("<li class='system_css'><div class=\"system_error\">Error Occurred - "+ev.data+"   the server.php dose not runing. Please ask author to run this file or copy this project file to your own server and follow the document to run this project on your server</div><li>");}; 
	websocket.onclose 	= function(ev){$('.discussion').append("<li class='system_css'><div class=\"system_msg\">Connection Closed</div><li>");};
	
	
	//overlay window
	var overlay = document.getElementById('overlay');
   	overlay.style.display = "block";
   	overlay.style.opacity = .8;
    var ele=$("<div id='specialBox'></div>");
	ele.append("<div id='username_div'><p>Please input your username:</p><input type='text' id='username'></input></div>");
	ele.append('<div class="close"><button type="button" class="btn btn-danger" id="close">Finish</button></div>');
    $('body').prepend(ele);
	$('body').delegate('#close','click',function(){
		username=$('#username').val();
		console.log(username);
		if (username.length == 0) {
			alert('Please input a username');
		}else{
			$('#specialBox').remove();
			$('#overlay').css('display','none');
			$('#name_header').append("Hello , <span id='username_span'>"+username+"</span>");
		}
	});
	
	
	/*  face icon part*/
	
	$('.emotion').qqFace({
		id : 'facebox', //face box id
		assign:'saytext', 
		path:'face/'	//file path
	});
});


function replace_em(str){ //convert text into face icon
	str = str.replace(/\</g,'&lt;');
	str = str.replace(/\>/g,'&gt;'); 
	str = str.replace(/\n/g,'<br/>');
	str = str.replace(/\[em_([0-9]*)\]/g,'<img src="face/$1.gif" border="0" />');
	return str;
}
</script>

<div id="overlay"></div>

<div class="container">

<section class="module">
	<header class="top-bar">
    
    <div class="left">
      <span class="icon typicons-message"></span>
      <h1 id="name_header"></h1>
    </div>
    
    <div class="right">
      <span class="icon typicons-minus"></span>
      <span class="icon typicons-times"></span>
    </div>
	</header>
	<ol class="discussion">
		
	</ol>
</section>



<div class="panel">
<textarea class="input" id="saytext" name="saytext" placeholder="Input what you want to say to others. Please note you can select face icon by clicking face icon button. And click Send Button to share with others"></textarea>
<p class='floatright'><span class="emotion">face icon</span></p>

<div class="input-group floatright" id='file_container'>
                <span class="input-group-btn">
                    <span class="btn btn-primary btn-file">
                        Browse&hellip; <input type="file" name='file' id='imagefile' accept="image/*">
                    </span>
                </span>
                <input type="text" class="form-control" id='filename' placeholder="Share image with friends. Image will automatically upload without clicking Send Button" readonly>
</div>

 <button type="button" id="send-btn" class="btn btn-success floatright">Send</button>
</div>

</div>

</body>
</html>