$(function() {
	$('abbr.timeago').timeago();
});

$(function(){
	if(answer_to= document.location.hash.replace(/\#comment-form-(.+)/,"$1")){
		try{
			comment_form('comment-form-'+answer_to);
		}
		catch(e) {

		}
	}
});