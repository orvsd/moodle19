<!--
function audioplayer_DoFSCommand(command, args) {
	audioplayer_cmd(command, args); 
}

if (navigator.appName && navigator.appName.indexOf("Microsoft") != -1 && navigator.userAgent.indexOf("Windows") != -1 && navigator.userAgent.indexOf("Windows 3.1") == -1) {
	document.write('<SCRIPT LANGUAGE=VBScript\> \n');
	document.write('on error resume next \n');
	document.write('Sub audioplayer_FSCommand(ByVal command, ByVal args)\n');
	document.write('  call audioplayer_DoFSCommand(command, args)\n');
	document.write('end sub\n');
	document.write('</SCRIPT\> \n');
}
var stopTimeout;
var TimeOver = 0;
function audioplayer_cmd(command, args){
	switch (command) {
     case "record":
     	//document.all.AR.PlayStatus=2;
		document.all.AR.RecordSound(args);
		break;
     case "play":
     	//document.all.AR.PlayStatus=1;
     	alert(args);
     	//document.all.MediaPlayer.Open("c:\\audioplayerdata\\data\\"+args);
     	if (document.all.MediaPlayer) {
     		document.all.MediaPlayer.FileName="c:\\audioplayerdata\\data\\"+args;
     		document.all.MediaPlayer.Play();
     	}else {
     		alert('Cannot load Windows Media Player!');
     	}
		//document.all.AR.PlaySound(args);
		break;
	 case "rename":
	    oldname=args.substr(0,args.indexOf("|"));
	    newname=args.substr(args.indexOf("|")+1,args.length);
	    //alert(oldname+newname);
	    if (oldname==newname) {
	    	alert('You can not renaming a file to the same name as an existing one!');
	    }else if(newname==""){
	    	alert('You can not use empty file name!');
	    }else {
	    	document.all.AR.RenameSound(oldname,newname);
	    }
	    break;
     case "stop":
        //alert("stop:"+args);
		document.all.AR.StopSound(args);
		break;
     case "select":
		//if (document.all.AR.PlayStatus == 0)
		//{
			parent.html_audioplayer.document.all.uploadform.<?php echo $filemanager->get_form_fileupload();?>.click();
    		stopTimeout = window.setTimeout("CheckPlayer()",500);
		//}
		break;
     case "submit":
		//if (document.all.AR.PlayStatus == 0)
		//{
			parent.html_audioplayer.document.all.uploadform.<?php echo $filemanager->get_form_rename();?>.value = args;
			//parent.html_audioplayer.document.all.uploadform.<?php echo $filemanager->get_form_fileupload();?>.value = args;
			parent.html_audioplayer.document.all.uploadform.submit();
			//alert(parent.html_audioplayer.document.all.uploadform.<?php echo $filemanager->get_form_rename();?>.value);
		//}
		break;
    }
}

function stopTime(){
  window.clearTimeout(stopTimeout);
}
function CheckPlayer(){
 try{
    var Info;
    TimeOver += 1;
	var movieobj = parent.InternetExplorer ? window.audioplayer : window.document.audioplayer; 
	//var filename = parent.html_audioplayer.document.all.uploadform.<?php echo $filemanager->get_form_fileupload();?>.value;
	if (movieobj != null ) {
		if (filename != "" || filename != null)
		{
			//alert(movieobj);
    		movieobj.setVariable("uploadfile",filename);
    		stopTime();
    	}
	}
	else{
    	stopTimeout = window.setTimeout("CheckPlayer()",500);
    }
 }catch(exception){
     stopTimeout = window.setTimeout("CheckPlayer()",500);
  }
}
CheckPlayer();
//-->