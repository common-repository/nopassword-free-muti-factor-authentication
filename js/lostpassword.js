jQuery(document).ready(function($){
  $("#lostpasswordform #user_login").attr('value', $.cookie("NPemailReset")).attr('readonly', 'true');
});
