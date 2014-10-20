// A utility function that returns true if a string 
// contains only whitespace characters.
function isblank(s)
{
  for(var i = 0; i < s.length; i++)
  {
     var c = s.charAt(i);
     if ((c != ' ') &&
         (c != '\n') &&
         (c != '\t'))
        return false;
  }
  return true;
}

function strChinese(str){  
  var   pattern   =   /[\u4E00-\u9FA5]/;    
  return   pattern.test(str);  
  }  

var thumbExtArray = new Array(".gif", ".jpg", ".jpeg");
var videoExtArray = new Array(".flv");

// This function used to check the textarea's characters count
function textcount(message,total,remain)
{
   var max = total;
   if (message.value.length > max) {
       message.value = message.value.substring(0,max);
       remain.value = 0;
       alert("Your text can not excess " + total + " characters!");
      }
   else {
        remain.value = max - message.value.length;
      }
}



// This is the function that performs <form> validation.  
// It will be invoked from the onSubmit(  ) event handler.
// The handler should return whatever value this function
// returns.
function verify(f)
{
  var msg;
  var empty_fields = "";
  var errors = "";
  var newPwdIsSet = 0;
  var newPwd = "";
  var pwd = "";
  var forUpload = 0;
  var uncheckedSNA = 0;

  // Loop through the elements of the form, looking for all
  // text and textarea elements that don't have an
  //  "optional" property defined.  Then, check for fields
  // that are empty and make a list of them.
  // Also, if any of these elements have a "min" or a "max"
  // property defined, then verify that they are numbers 
  // and that they are in the right range.
  // Put together error messages for fields that are wrong.
  for(var i = 0; i < f.length; i++)
  {
     var e = f.elements[i];	 	
	 
	 if (e.type == "checkbox" && (e.name == "twitter" || e.name == "facebook" || e.name == "buzz" || e.name == "weibo"))
        {
		   if (!e.checked)
		   {
			 //alert("I am here!\n" + e.name);
			 ++uncheckedSNA;
             continue;
		   }
	    }

	 if (((e.type == "text") ||
         (e.type == "textarea") ||
		 (e.type == "password")) &&
         !e.optional)
     {
        if (e.value == "User name" && e.name == "username")
		   {
		     empty_fields += "\n        " +
                           e.name;
            continue;
		    }
		
		if (e.name == "password")
		{
		 if ((e.value == null) ||
            (e.value == "") || (e.value == "Password") || 
            isblank(e.value))
		    {
		     empty_fields += "\n        " +
                           e.name;
            continue;
		    }
		 else
		   {
			if (e.value.length >= 5)
			   {
		       pwd = e.value;
			   }
			else
			   {
				errors += "- Password length too short!\n";
			   }
			continue;
		   }
			
		}
		else if (e.name == "retype_password")
		{
		if ((e.value == null) ||
            (e.value == "") ||
            isblank(e.value))
          {
           empty_fields += "\n        " +
                           e.name;
           continue;
		  }
		else if (e.value != pwd)
		  {
		  errors += "- Passwords does not match!\n"; 
		  continue;
		  }			
		}
		else if (e.name == "newpwd")
		{
		 if ((e.value == null) ||
            (e.value == "") ||
            isblank(e.value))
		    {
		     newPwdIsSet = 0;
		    }
		 else
		   {
			if (e.value.length >= 5)
			   {
		       newPwdIsSet = 1;
			   newPwd = e.value;
			   }
			else
			   {
				errors += "- New Password length too short!\n"; 
		        continue;
			   }
		   }
		 continue;
	    }
		else if (e.name == "retypenewpwd" && newPwdIsSet == 1)
		{
		if ((e.value == null) ||
            (e.value == "") ||
            isblank(e.value))
          {
           empty_fields += "\n        " +
                           e.name;
           continue;
		  }
		else if (e.value != newPwd)
		  {
		  errors += "- New Passwords does not match!\n"; 
		  continue;
		  }
		}
		else if (e.name == "retypenewpwd")
		{
		if ((e.value == null) ||
            (e.value == "") ||
            isblank(e.value))
          {
           continue;
		  }
		else
		  {
		  errors += "- Retype New Password field is not empty! You didn't specify a new Password!\n"; 
		  continue;
		  }
		}
		
		if (e.name == "newpwd" || e.name == "retypenewpwd")
		    continue;
		
		// first check if the field is empty
        if (e.type != "checkbox" && ((e.value == null) ||
            (e.value == "") ||
            isblank(e.value)))
        {
           empty_fields += "\n        " +
                           e.name;
           continue;
		}
		else if (e.name == "description")
		{
		   if (e.value == "Enter your video description here... (max. 300 characters)")
		   {
			 empty_fields += "\n        " +
                           e.name;
             continue;
		   }
	    }
		else if (e.name == "happening")
		{
		   if (e.value == "What is happening..." || isblank(e.value))
		   {
			 empty_fields += "\n        " +
                           e.name;
             continue;
		   }
	    }
		else if (e.name == "comment")
		{
		   if (e.value == "(max. 180 characters)")
		   {
			 empty_fields += "\n        " +
                           e.name;
             continue;
		   }
	    }

        // Now check for fields that are supposed 
        // to be numeric.
        if (e.numeric ||
           (e.min != null) ||
           (e.max != null))
        {
           var v = parseFloat(e.value);
           if (isNaN(v) ||
              ((e.min != null) && (v < e.min)) ||
              ((e.max != null) && (v > e.max)))
           {
              errors += "\n- The field " +
                        e.name +
                        " must be a number";
              if (e.min != null)
                 errors += " that is greater than " +
                           e.min;

              if (e.max != null &&
                  e.min != null)
                 errors += " and less than " +
                           e.max;

              else if (e.max != null)
                 errors += " that is less than " +
                           e.max;

              errors += ".\n";
           }
        }

        // Now check for fields that are supposed 
        // to be emails.
        // Not exactly as described in RFC 2822, but 
        // a rough attempt
        // of the form "local-bit@domain-bit"
        if (e.name == "email" && !isblank(e.value))
        {
           var seenAt = false;
           var append = "";
           for(var j = 0; j < e.value.length; j++)
           {
              var c = e.value.charAt(j);
              if ((c == ' ') ||
                  (c == '\n') ||
                  (c == '\t'))
                 append += 
     "\n           - not contain white-space";
              if ((c == '@') && (seenAt == true))
                 append += 
     "\n           - contain only one @";
              if ((c == '@'))
                 seenAt = true;
           }

           if (seenAt == false)
              append += 
     "\n           - contain exactly one @";
           if (append)
              errors += "\n- The field " +
                        e.name +
                        " must: " + append;
        }

        // Now check for fields that are supposed 
        // to be DOBs.
        if (e.name == "birthday" && !isblank(e.value))
        {
           var slashCount = 0;
           var append = "";
           var addedError1 = false;
           var addedError2 = false;
		   var m1 = 0;
		   var m2 = 0;
		   var d1 = 0;
		   var d2 = 0;
		   var month = 0;
		   var day = 0;

           for(var j = 0; j < e.value.length; j++)
           {
              var c = e.value.charAt(j);

              if ((c == '-'))
                 slashCount++;

              if (c != '-' &&
                 (c < '0' || c > '9') &&
                 addedError1 == false)
              {
                 addedError1 = true;
                 append += 
     "\n           - must contain only numbers " +
     "and '-'";
              }
			  
			  if (c >= '0' && c <= '9')
			     {
				 if (j == 5)
			        m1 = parseInt(c);
			     else if (j == 6)
			        m2 = parseInt(c);
			     else if (j == 8)
			        d1 = parseInt(c);
			     else if (j == 9)
			        d2 = parseInt(c);	 
				 }
			  
			  
           }

           if (j != 10 || slashCount != 2)
              append += 
     "\n           - must have the format YYYY-MM-DD";
           if (slashCount != 2)
              append += 
     "\n           - must contain two slashes";
	      
		   // Calculate Month and Day to verify
		   month = m1 * 10 + m2;
	       if (month > 12 || month == 0)
	          append += 
     "\n           - month must between 01 - 12";
	       day = d1 * 10 + d2;
		   switch(month)
		      {
			  case 2:
			     if (day > 29 || day == 0)
				   append += 
     "\n           - day must between 01 - 29";
	             break;
			  case 1:
			  case 3:
			  case 5:
			  case 7:
			  case 8:
			  case 10:
			  case 12:
			    if (day > 31 || day == 0)
				   append += 
     "\n           - day must between 01 - 31";
	            break;
			  case 4:
			  case 6:
			  case 9:
			  case 11:
				 if (day > 30 || day == 0)
				    append += 
     "\n           - day must between 01 - 30";
	             break;
			  default:
                 break;
			  }
			  
           if (append)
              errors +=  "\n- The field " + 
                         e.name + append;
                        
        }

        // Now check for fields that are supposed 
        // not to have spaces
        if (e.nospaces)
        {
           var seenAt = false;
           var append = "";

           for(var j = 0; j < e.value.length; j++)
           {
              var c = e.value.charAt(j);

              if ((c == ' ') ||
                  (c == '\n') ||
                  (c == '\t'))
                 errors += "- The field " + e.name +
                           " must not contains white-space";
           }
        }

     } // if (type is text or textarea) and !optional
	 
	 if (e.type == "file")
	 {
		if (e.name == "uploaded") 
		   forUpload = 1;
		
		if (e.name == "vthumb" && f.name == "uploadform" && (e.value == null || e.value == "" || isblank(e.value)))
		   {
		   empty_fields += "\n        " +
                           "video thumb";
           continue;	  
		   }
		   
	    if (e.name == "uploaded" && (e.value == null || e.value == "" || isblank(e.value)) && f.name == "uploadform")
		{
		   empty_fields += "\n        " +
                           "video";
           continue;	
	    }
		else if (e.name == "uploaded" && f.name == "uploadform")
		{		   
		   if (e.value.indexOf('&') != -1)
		      errors += "- The .flv video file name must not contain symbol \'&\'!\n";
			
		   var rxVideo = new RegExp("[^\.]\."+"flv"+"\s*$", "i");
		   if (!rxVideo.test(e.value.toLowerCase()))
		   {
			  errors += "You should only upload .flv video file!\n";
			  continue;
		   }
	    }
		else if (e.name == "vthumb" && f.name == "uploadform")
		{
		   if (e.value == null || e.value == "" || isblank(e.value)) continue;
		   
		   var rxthumb1 = new RegExp("[^\.]\."+"gif"+"\s*$", "i");
		   var rxthumb2 = new RegExp("[^\.]\."+"jpg"+"\s*$", "i");
		   if (!(rxthumb1.test(e.value.toLowerCase()) || rxthumb2.test(e.value.toLowerCase())))
		   {
			  errors += "You should only upload .gif/.jpg video thumb file!\n";
			  continue;
		   }
	    }
	 } // if e.type == "file"	 
	 
  } // for each character in field
  
  // For friendstream
  if (uncheckedSNA == 4)
     {
	 errors += "____________________________________________________  _  _\n\n"
     errors += "Error: You have NOT selected any SNA network yet!\n"
     errors += "Please select at least one SNA network!\n";
     errors += "____________________________________________________  _  _\n\n"
     }

  // Now, if there were any errors, then display the
  // messages, and return true to prevent the form from
  // being submitted.  Otherwise return false
  if (!empty_fields && !errors)
  {
     if (forUpload)
	     loadingPanelUpload.show();
	 return true;
  }
  
  msg  = "____________________________________________________  _  _\n\n"
  msg += "The form was not submitted because of the " +
         "following error(s).\n";
  msg += "Please correct these error(s) and re-submit.\n";
  msg += "____________________________________________________  _  _\n\n"

  if (empty_fields)
  {
     msg += "- The following required field(s) are empty:"
           + empty_fields + "\n";
     if (errors)
        msg += "\n";
  }
  msg += errors;
  alert(msg);
  return false;
}

