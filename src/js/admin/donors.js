jQuery(document).ready(function($){
	var availableTags = [
	      "ActionScript",
	      "AppleScript",
	      "Asp",
	      "BASIC",
	      "C",
	      "C++",
	      "Clojure",
	      "COBOL",
	      "ColdFusion",
	      "Erlang",
	      "Fortran",
	      "Groovy",
	      "Haskell",
	      "Java",
	      "JavaScript",
	      "Lisp",
	      "Perl",
	      "PHP",
	      "Python",
	      "Ruby",
	      "Scala",
	      "Scheme"
	    ];
	
	$('select[name=donor-type]').chosen();
	$('input[name=donor-name-email]').autocomplete({source: availableTags});
	$('input[name=first-donation-date]').datepicker();
	$('input[name=last-donation-date]').datepicker();
	$('select[name="campaigns[]"]').chosen();
	$('select[name=donation-status]').chosen();
	$('select[name="donors-tags[]"]').chosen();
	$('select[name="gateways[]"]').chosen();
});
