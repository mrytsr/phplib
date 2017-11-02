// ajax初始化函数
function hj_ajaxChange(url, func, method, async, data){
    var xmlhttp = null;
    if(undefined == async){
        async = true;
    }

    if(window.XMLHttpRequest){
        xmlhttp = new XMLHttpRequest();
    }else if(window.ActiveXObject){
        xmlhttp = new ActiveXObject("Microsoft.XMLHTTP");
    }else{
        alert("bowser don't support xmlhttp");
        return null;
    }

    if(async){
        xmlhttp.onreadystatechange = function(){
            if(xmlhttp.readyState == 4 && xmlhttp.status == 200){
                var result = xmlhttp.responseText;
                func(result);
            }
        }
    }

    xmlhttp.open(method || "get", url, async);
    if(/^post$/i.test(method)){
        xmlhttp.setRequestHeader("Content-Type", "application/x-www-form-urlencoded;charset=UTF-8");
    }
    xmlhttp.send(data || null);
    if(!async){
        return xmlhttp.responseText;
    }
}

// 美化的遮罩层弹窗 
function hj_confirm(func, str_info){
    hj_maskdiv(func, str_info, false, true);
}

function hj_prompt(func, str_info){
    hj_maskdiv(func, str_info, true, true);
}

function hj_alert(str_info){
    if(!(str_info && str_info.length)){
        str_info = "[null]";
    }
    hj_maskdiv(null, str_info, false, false);
}

function hj_maskdiv(func, str_info, input, cancel){
    // 建立遮罩层
    var mask = document.createElement("div");
    mask.id = "mask_id";
    mask.setAttribute("style", "display:block; border:1px solid black; top:0; left:0; z-index:999; opacity:0.5; background:#333;position:absolute; margin:0; padding:0; width:100%; height:800px;");
    document.body.appendChild(mask);

    // 建立提示框
    var message = document.createElement("div");
    message.id = "message_id";
    message.setAttribute("style", "padding:10px; border:none; color:black; background:white; margin:0 500px; top:200px; z-index:1000; position:fixed; text-align:center; width:200px;");
    var cancel_html = "";
    if(cancel){
        cancel_html = "&nbsp;&nbsp;<span id=msg_cancel onclick='msg_cancel_click();'>取消</span>";
    }

    var input_html = "";
    if(input){
        input_html = "<input style='margin:10px;' type='text' name='name' id=msg_input />";
    }
    message.innerHTML = "<h2 id=msg_info style='margin:10px;'>" + str_info + "</h2>" + input_html + "<div style='border:none; margin:10px;'><span id=msg_ok onclick='msg_ok_click();'>确定</span>" + cancel_html + "</div>";

    var style = document.createElement("style");
    style.innerHTML = "span#msg_cancel,span#msg_ok{margin:0 20px; font-size:14px; padding:5px; }span#msg_cancel:hover,span#msg_ok:hover{background:#ccc; cursor:pointer;}";
    message.appendChild(style);
    document.body.appendChild(message);
    document.body.old_overflow = document.body.style.overflow;
    document.body.style.overflow = "hidden";
    if(func){
        talert_func = func;
    }else{
        talert_func = function (){}
    }
}


// 关闭方法
function close_talert(){
    message_id.parentNode.removeChild(message_id);
    mask_id.parentNode.removeChild(mask_id);
    document.body.style.overflow = document.body.old_overflow;
}

function msg_cancel_click(){
    close_talert();
    talert_func(false);
}

function msg_ok_click(){
    var info = true;
    if(document.getElementById("msg_input")){
        info = document.getElementById("msg_input").value;
    }
    close_talert();
    talert_func(info);
}

