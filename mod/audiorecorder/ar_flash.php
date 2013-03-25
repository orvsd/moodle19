<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>e-China workspace AudioPlayer</title>
<style>
	.txtLoc {font-family: Arial, Helvetica, sans-serif}
</style>
<style type="text/css">
#flashcontent {
border: solid 0px #000;
float: center;
margin: 0px 0px;
}

</style>
<script language="JavaScript">

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

//read all data from AR local data directory.
function readInitData(){
	var flashobj = document.getElementById("audioplayer");
	if (flashobj==null) {
	    flashobj=parent.InternetExplorer ? window.audioplayer : window.document.audioplayer;
	}
	var arobj=document.getElementById("AR");
	if (arobj.SoundLocalPath=="" || arobj.SoundLocalPath=="/") {
	   flashobj.initAR();
	}else{
	   var fileList="";
	   flashobj.loadAR();
	   fileList=arobj.ReadDataDirectory(true);
	   var tmpFile="";
	   while (fileList.indexOf('|')>0) {
		   tmpFile=fileList.substr(0,fileList.indexOf('|'));
		   tmpFile=tmpFile.substr(0,tmpFile.indexOf('.'));
		   flashobj.addExistedItem(tmpFile);
		   fileList=fileList.substr(fileList.indexOf('|')+1);
	   }
	   var divText=document.getElementById("ARPath");
	   regExp = new RegExp( "\/", "g");
	   divText.innerHTML = arobj.SoundLocalPath.replace(regExp,"\\"); 
	}
}

function loadARData(){
	var fileList="";
	var flashobj = document.getElementById("audioplayer");
	if (flashobj==null) {
	    flashobj=parent.InternetExplorer ? window.audioplayer : window.document.audioplayer;
	}
	var arobj=document.getElementById("AR");
	if (arobj==null) {
	    arobj=parent.InternetExplorer ? window.AR : window.document.AR;
	}

	flashobj.loadAR();
	fileList=arobj.ReadDataDirectory(true);
	var tmpFile="";
	while (fileList.indexOf('|')>0) {
		tmpFile=fileList.substr(0,fileList.indexOf('|'));
		tmpFile=tmpFile.substr(0,tmpFile.indexOf('.'));
		flashobj.addExistedItem(tmpFile);
		fileList=fileList.substr(fileList.indexOf('|')+1);
	}
	var divText=document.getElementById("ARPath");
	re = new RegExp( "\/", "g");
	divText.innerHTML = arobj.SoundLocalPath.replace(re,"\\"); 
}

function initARData(strPath){
	var arobj=document.getElementById("AR");
	if (arobj==null) {
	    arobj=parent.InternetExplorer ? window.AR : window.document.AR;
	}
	re = new RegExp("\\\\", "g");
	strA=strPath.replace(re,"/");
	arobj.SetLocalPath(strPath);
	loadARData();
}

function callARBrowserFolder(){
	var arobj=document.getElementById("AR");
	if (arobj==null) {
	    arobj=parent.InternetExplorer ? window.AR : window.document.AR;
	}
	var strPath=arobj.GetBrowserPath();
	var flashobj = document.getElementById("audioplayer");
	if (flashobj==null) {
	    flashobj=parent.InternetExplorer ? window.audioplayer : window.document.audioplayer;
	}
	if ((strPath==null) || (strPath=="") ) {
		return;
	}else{
	   flashobj.setFolder(strPath);
	}
}

function audioplayer_cmd(command, args){
	var objAudio=document.getElementById("AR");
	if (objAudio==null) {
	    objAudio=parent.InternetExplorer ? window.AR : window.document.AR;
	}
	var objMediaPlayer = document.getElementById("MediaPlayer");
	if (objMediaPlayer==null) {
	    objMediaPlayer=parent.InternetExplorer ? window.MediaPlayer : window.document.MediaPlayer;
	}
	switch (command) {
     case "record" :
     		//document.all.AR.PlayStatus=2;
		  	objAudio.RecordSound(args);
				break;
     case "play":
     	if (objMediaPlayer) {
     		if (2==objMediaPlayer.playState) {
     			objMediaPlayer.controls.play();
     		}else {
     			objMediaPlayer.URL=objAudio.SoundLocalPath+"/"+args;
     			objMediaPlayer.controls.play();
     		}
     	}else {
     		alert("Can't loading Windows Media Player!");
     	}
			break;
	 case "pause":
	    objMediaPlayer.controls.pause();
	 		break;
	 case "rename":
	    oldname=args.substr(0,args.indexOf("|"));
	    newname=args.substr(args.indexOf("|")+1,args.length);
	    if (oldname==newname) {
	    	alert('You can not renaming a file to the same name as an existing one!');
	    }else if(newname==""){
	    	alert('You can not use empty file name!');
	    }else {
	    	objAudio.RenameSound(oldname,newname);
	    }
	    break;
     case "rewind":
     		objMediaPlayer.controls.stop();
				break;
     case "stop":
		    objAudio.StopSound(args);
				break;
     case "select":
            //Not support in Moodle
		    break;
     case "submit":
            //Not support in Moodle
			break;
	 case "delete":
		   objAudio.RemoveAudioFile(args);
		   break;
    }
}

function stopTime(){
  window.clearTimeout(stopTimeout);
}

function flAlert(str){
  alert(str);
}

function resetARDirectory(){
	var arobj=document.getElementById("AR");
	if (arobj==null) {
	    arobj=parent.InternetExplorer ? window.AR : window.document.AR;
	}
	var strPath= arobj.GetBrowserPath();	
	ra = new RegExp( "\:\+\\\\", "g");
	if (strPath==null || strPath=="") {
		return;
	}else if (!(strPath.match(ra))) {
		alert("Please select a correct directory.\n e.g c:\\audioplayer");
		return;
	}
	re = new RegExp( "\\\\", "g");
	arobj.SetLocalPath(strPath.replace(re,"/"));
	loadARData();
}
</script>

</head>

<body bgcolor="#ffffff" onLoad="readInitData();">
<table border="0" align="center" width="100%">
	<tbody>
<tr>
	<td align="right" >
	<script type="text/javascript" src="swfobject.js"></script>
	<div id="flashcontent" align="right">
	  Requires Flash Player 7+. Please download from <a href="http://www.adobe.com/shockwave/download/download.cgi?P1_Prod_Version=ShockwaveFlash&promoid=BIOW">here</a>
	</div>
	
	<script type="text/javascript">
	   var so = new SWFObject("audioplayer.swf", "audioplayer", "200", "350", "8", "#FFFFFF");
	   so.addVariable("recTime","<?php echo $CFG->audiofile_maxtimes?>")
       so.addVariable("defaultPath","<?php echo $CFG->audiofile_localpath?>")
	   so.addVariable("urlPath", "http://disseminator.nottingham.ac.uk/ar/");
	   so.addVariable("windowSettings", "width=1000,height=600,left=0,top=0,toolbar=0,location=0,scrollbars=yes,status=0,resizable=yes,fullscreen=0");
	   so.write("flashcontent");
	</script>
<br/>
<font class="txtLoc">Your audio data stored in:<div id="ARPath" align="center"></div> <a href="#" onclick="resetARDirectory()">Change Directory</a></font>
</td>
<td align="left" valign="top" >
<object classid="clsid:D66F6E64-E742-4C6C-8DB8-4071EF3A9BE9" codebase="AudioRecorder.cab#version=1,1,0,1" name="AR" id="AR" width="0" height="0">
</object>
<!--
 You can upload your audio file here:<br/>
 -->
<?php
  //print upload form
  $arinstance->view();
?>
</td>
</tr>
</tbody>
</table>
<SCRIPT language="javascript">
	if (document.all.AR==null || document.all.AR==undefined) {
		document.write('We cannot find Audio Recorder Player ActiveX');
	}
</SCRIPT>
<OBJECT ID="MediaPlayer"  name="MediaPlayer" height="0" width="0"
  CLASSID="CLSID:6BF52A52-394A-11d3-B153-00C04F79FAA6">
</OBJECT>
<SCRIPT LANGUAGE = "JavaScript"  FOR =MediaPlayer EVENT =PlayStateChange(NewState)>

// Test for the player current state, display a message for each.
switch (NewState){
	  case 3:
				var movieobj = parent.InternetExplorer ? window.audioplayer : window.document.audioplayer;
				var duration=MediaPlayer.currentMedia.duration;
				if (duration>0) {
				   movieobj.onPlayStart(MediaPlayer.currentMedia.durationString);
				}
   			break;	  
    case 8:
				var movieobj = parent.InternetExplorer ? window.audioplayer : window.document.audioplayer;
				movieobj.onPlayEnd();
        break;
    // Other cases go here.
    default:
        break;
}
</SCRIPT>
</body>
</html>