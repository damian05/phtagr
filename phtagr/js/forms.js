function reset_text(nodeid, text)
{
  var e=document.getElementById(nodeid);
  // decode node content from b64
  e.innerHTML=atob(text);
}

function add_form_tags(id, tags)
{
  var node=id+"-tag";
  var e=document.getElementById(node);
  var i=id+"-edit";
  var text=e.innerHTML;

  // Does a form already exists?
  if (text.substr(0,5)=="<form")
    return;

  // encode node content to b64 to catch all special characters
  text=btoa(text); 
  e.innerHTML="<form>" +
    "<input type=\"hidden\" name=\"section\" value=\"edit\"/>"+
    "<input type=\"hidden\" name=\"action\" value=\"edit\"/>"+
    "<input type=\"hidden\" name=\"images[]\" value=\""+id+"\"/>"+
    "<input type=\"text\" id=\"" + i + "\" name=\"tags\" value=\"" + tags + "\" size=\"35\"/>"+
    "<br/>"+
    "<input class=\"submit\" type=\"submit\" value=\"Send\"/> or "+
    "<input class=\"reset\" type=\"reset\" onclick=\"reset_text('"+node+"','"+text+"')\"/>"+
  "</form>";
  document.getElementById(i).focus();
}

