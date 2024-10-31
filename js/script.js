validateEmail = function(email){
  var re = /^(([^<>()\[\]\\.,;:\s@"]+(\.[^<>()\[\]\\.,;:\s@"]+)*)|(".+"))@((\[[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\.[0-9]{1,3}\])|(([a-zA-Z\-0-9]+\.)+[a-zA-Z]{2,}))$/;
  return re.test(String(email).toLowerCase());
}

validateName = function(name){
  var re = /^[a-zA-Z0-9]{1,50}$/;
  return re.test(String(name).toLowerCase());
}

var currentEmail;
var wrongPasswordCounter = 0;

jQuery(document).ready(function($){

  $('#nav, #loginform, body script').remove();

  scene = new Scene(jQuery);
  popup = new Popup(jQuery);
  scene.init();

  $('#npMail').keyup(function(e){
    if(e.keyCode == 13){
      if ($('#npPassword').length) passwordlogin();
      else loginclick();
    }
  });

  $('#closePopup').click(function(){
    popup.toggle();
  });


  loginclick = function(){
    var userEmailEl = $("#npMail");
    var userEmailVal = userEmailEl.val();

    if(!validateEmail(userEmailVal)){
      scene.showError('Please enter a valid Email address');
      scene.wrongEmail();
    }
    else{
      scene.correctEmail();
      currentEmail = userEmailVal;
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        dataType: "text",
        cache: false,
        data: {
          action: 'nopass_auth',
          email :userEmailVal.trim()
        },
        beforeSend: function() {
            scene.startCountdown();
        },
        success: function(response){
          console.log('RESPONE: '+response);

          try {
            response = JSON.parse(response);
          }
          catch (err) {
            scene.showError('Unknown error');
            scene.showDefaultScreen();
            return true;
          }

          if(response.Status == 'Success'){
            $.cookie("NPuserLastEmail", userEmailVal.trim(), {
              expires: 356,
              path: "/",
              domain: document.domain,
              secure: false
            });

            if (response.NewUser){
              window.location.replace('./wp-admin/profile.php');
              return true;
            }
            else{
              window.location.replace('./wp-admin/');
              return true;
            }
          }
          else if(response.Status == 'Fail' && response.Method != 'DefaultLogin'){
            scene.showError(response.Message);
            scene.wrongEmail();
            scene.showDefaultScreen();
          }
          else{
            scene.showPasswordScreen();
            scene.showAccountCreateLink();
            $.cookie("NPemailReset", userEmailVal, {
              expires: 1,
              path: "/",
              domain: document.domain,
              secure: false
            });
            popup.init(userEmailVal);
            if(response.Value == 'CheckEmail') popup.checkEmail = true;
            else popup.checkEmail = false;
          }
        },
        fail: function(){
          scene.showError('Unknown error');
          scene.showDefaultScreen();
        }
      });
    }
  }

  passwordlogin = function(){
    var userPassEl = $("#npPassword");
    var userPassVal = userPassEl.val();

    if(userPassVal == ''){
      scene.showError('Please enter your password');
      scene.wrongPassword();
    }
    else{
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        dataType: "text",
        cache: false,
        data: {
          action : 'nopass_password_login',
          email : currentEmail,
          password : userPassVal
        },
        success: function(response){
          console.log(response);
          try {
            response = JSON.parse(response);
          }
          catch (err) {
            scene.showError('Unknown error');
            scene.showDefaultScreen();
            return true;
          }

          if(response.Status == 'Success'){
            $.cookie("NPuserLastEmail", currentEmail, {
              expires: 356,
              path: "/",
              domain: document.domain,
              secure: false
            });
            window.location.replace('./wp-admin/');
            return true;
          }
          else{
            wrongPasswordCounter++;

            if(wrongPasswordCounter == 3){
              wrongPasswordCounter = 0;
              scene.showError("Please eneter your E-mail address again!");
              scene.showDefaultScreen();
              return false;
            }
            else{
              scene.showError(response.Message,'password')
              scene.wrongPassword();
            }
          }
        }
      });
    }
  }

  createuser = function(){
    var email = currentEmail;
    var validForm = true;

    if(validateEmail(email)){
      $.ajax({
        type: "POST",
        url: ajax_object.ajax_url,
        dataType: "text",
        cache: false,
        data: {
          action : 'nopass_create_user',
          email : email
        },
        beforeSend: function(){
          popup.correctData();
          popup.loading();
        },
        success: function(response){
          console.log(response);
          try {
            response = JSON.parse(response);
          }
          catch (err) {
            scene.showError('Unknown error');
            scene.showDefaultScreen();
            return true;
          }

          if(response.Status == 'Success'){
            popup.success();
          }
          else{
            popup.showError();
          }
        }
      });
    }
  }

});
