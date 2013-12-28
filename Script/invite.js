var flag = true;

$(function() {
  $('.error').hide();
  $('input.text-input').css({backgroundColor:"#FFFFFF"});
  $('input.text-input').focus(function(){
    $(this).css({backgroundColor:"#FFDDAA"});
  });
  $('input.text-input').blur(function(){
    $(this).css({backgroundColor:"#FFFFFF"});
  });
  
  $(".button").click(function() {
	 $('.error').hide();
	 $('div#general').html('');
	 NotifyUser();
	 
	  var name = $("input#name").val();
		if (name == "") {
      $("label#name_error").show();
      $("input#name").focus();
      StopNotify();
      return false;
    }
    
		var email = $("input#email").val();
		if (email != "") {
      var emailReg = /^([\w-\.]+@([\w-]+\.)+[\w-]{2,4})?$/;
      if(!emailReg.test(email)) {
      $("label#email_error").show();
      $("input#email").focus();
      StopNotify();
      return false; 
        }
    }
		var phone = $("textarea#phone").val();
		if (phone == "") {
      $("label#phone_error").show();
      $("textarea#phone").focus();
      StopNotify();
      return false;
    }
		if (flag == true){
		flag = false;
		var dataString = 'name='+ name + '&email=' + email + '&phone=' + phone;
		$.ajax({
      type: "POST",
      url: "/sendmail.php",
      data: dataString,
      error: function(){
      	$('div#general').html("<div id='general_error'>Ooops something went wrong.<br /> Please try again.</div>");
        	$("textarea#phone").focus();
        	flag = true;
        	StopNotify();
      	},
      success: function(msg) {
       var x = parseInt(msg);
      if(x == 0){
        $('#contact_form').html("<div id='message'></div>");
        $('#message').html("<h1>Cool you successfully sent your chat invitations</h1>")
        .append("<p>Click the 'x' above to close this panel.</p>")
        .hide()
        .fadeIn(1500, function() {
          $('#message').append("<img id='checkmark' src='/Images/check.png' />");
        });
        flag = true;
        StopNotify();
        }
        else if (x == 1)
        {
        	//something wrong
        	$('div#general').html("<div id='general_error'>Invalid email address.<br /> Separate multiple emails with coma.</div>");
        	$("textarea#phone").focus();
        	flag = true;
        	StopNotify();
        }
        else
        {
        	$('div#general').html("<div id='general_error'>Ooops something went wrong.<br /> Please try again.</div>");
        	$("textarea#phone").focus();
        	flag = true;
        	StopNotify();
        }
      }
     });
     }
    return false;
	});
});

function NotifyUser() {
        var lock = document.getElementById("skm_LockPane");
        if (lock)
            lock.className = "LockOn";
        lock.innerHTML = '<span style="font-size:16px">' + 'Processing............' + '</span>';
    }
    
    function StopNotify(){
    	var lock = document.getElementById("skm_LockPane");
        if (lock)
            lock.className = "LockOff";
            }
    
runOnLoad(function(){
  $("input#name").select().focus();
});
