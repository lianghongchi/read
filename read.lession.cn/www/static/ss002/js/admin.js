
$(document).keydown(function(event) {
    // 回车搜索
    var search = $("#_search_form_table");
    if(event.keyCode == 13 && search.length > 0) {
        layer.load(0, {
            shade: [0.3, "#ccc"]
        });
        search.submit();
    }
    // 刷新键显示loading
    if(event.keyCode == 116 || (event.keyCode == 17 && event.keyCode == 82)) {
        showLoading("正在刷新...");
    }
});

// 显示loading
function showLoading(title) {
    if(!title || title == undefined) {
        title = '加载中...';
    }
    return layer.msg(title, {
        icon: 16,
        shade: 0.2,
        time: 0,
        area: '200px',
    });
}

// 隐藏loading
function hideLoading(index) {
//  return layer.close(index);
    return layer.closeAll('dialog');
}

// 关闭所有layer 弹层
function closeAllLayer() {
    layer.closeAll();
}

// 小提示
function tos(msg) {
    layer.msg(msg);
}

// 普通弹窗提示
function tos2(msg, callback) {
    return layer.alert(msg, {
        icon: 0
    }, function(index) {
        if(callback != undefined) {
            callback();
        }
        layer.close(index);
    });
}

// 错误弹窗提示
function errorMessage(msg, callback) {
    return layer.alert(msg, {icon: 0}, function(index) {
        if(callback != undefined) {
            callback();
        }
        layer.close(index);
    });
}

// 成功弹窗提示
function successMessage(msg, callback) {
    return layer.alert(msg, {icon: 1}, function(index) {
        if(callback != undefined) {
            callback();
        }
        layer.close(index);
    });
}

// 刷新当前页面
function reloadPage(){
    location.reload();
}

// 跳转页面
function redirect(url, isReplace = false) {
    if (isReplace) {
        window.location.replace(url);
    } else {
        location.href = url;
    }
}

// 返回页面并刷新
function backAndReload() {
    if (document.referrer) {
        self.location = document.referrer;
    } else {
        location.href = indexUrl;
    }
}

// 调试打印
function d(data) {
    console.log(data);
}

// 确认提示
function confirmMessage(info, okCallback, cancelCallback) {
    layer.confirm(info, {
        btn: ["确定", "取消"],
        icon: 0,
        title: '提示',
    }, function(){
        if (okCallback) {
            okCallback();
        }
    }, function(){
        if (cancelCallback) {
            cancelCallback();
        }
    });
}

/**
 * 表单快捷发起ajax请求并响应
 * @param  {string} url 请求地址
 * @param  {array} data 请求数据，json格式
 * @param  {callback} successCallback 请求并执行成功回调，接口code返回200 才会执行
 * @param  {string} method 请求方法，如post
 * @return
 */
function formRequest(url, method, data, successCallback) {
    if(method == undefined || !method) {
        method = 'POST';
    }
    showLoading();
    $.ajax({
        url: url,
        type: method,
        data: data,
        headers: {
            'DATATYPE': 'JSON',
        },
        async: true,
        dataType: "json",
        timeout: 8000,
        error: function(xmlHttpRequest, error) {
            var errorMsg = (error == "timeout") ? "网络连接繁忙，请稍后再试" : "服务器繁忙";
            hideLoading();
            errorMessage(errorMsg);
        },
        success: function(redata) {
            hideLoading();

            if(redata.code == undefined) {
                return errorMessage("数据解析失败，请稍候再试");
            }

            if (redata.code != 200) {
                return errorMessage(redata.msg);
            }

            if (successCallback == 'msg') {
                return tos(redata.msg);
            }

            successMessage(redata.msg, function(){
                if(successCallback) {
                    successCallback(redata.code, redata.msg, redata.data);
                }
            })
        }
    })
};

/**
 * 发起ajax请求
 * @param  {string} url 请求地址
 * @param  {array} data 请求数据，json格式
 * @param  {callback} successCallback 请求成功回调函数
 * @param  {string} method 请求方式，如 post
 * @return
 */
function simpleRequest(url, method, data, successCallback) {
    if(method == undefined || !method) {
        method = 'POST';
    }
    $.ajax({
        url: url,
        type: method,
        data: data,
        headers: {
            'DATATYPE': 'JSON',
        },
        async: true,
        dataType: "json",
        timeout: 8000,
        error: function(xmlHttpRequest, error) {
            var errorMsg = (error == "timeout") ? "网络连接繁忙，请稍后再试" : "服务器繁忙";
            hideLoading();
            errorMessage(errorMsg);
        },
        success: function(redata) {
            if(redata.code == undefined) {
                hideLoading();
                return errorMessage("数据解析失败，请稍候再试");
            }

            if (successCallback) {
                successCallback(redata.code, redata.msg, redata.data);
            }
        }
    })
}


/**
 * 打开弹层新窗口
 * @param  {string} url 窗口URL地址
 * @param  {string} info 窗口顶部标题
 * @param  {string} area 窗口大小，(min 小窗口，med 中型窗口，max 大窗口，ra 固定比例的大窗口(页面的80%)，自定义窗口大小=>格式：["1000px", "500px"]，默认max)
 * @return
 */
openWindow = function(url, info, area) {
    if(!url || url == "" || url.length < 4) {
        return tos("新窗口url地址不能为空");
    }
    if(!area || area == '') {
        area = 'max';
    }

    switch(area) {
        case "min":
            area = ["800px", "400px"];
            break;
        case "med":
            area = ["900px", "600px"];
            break;
        case "max":
            area = ["1000px", "680px"];
            break;
        case "ra":
            area = ["80%", "80%"];
            break;
        default:
            if (typeof area != 'object') {
                area = eval("(" + area + ")");
            }
    }

    var index = layer.open({
        type: 2,
        title: info ? info : "窗口",
        closeBtn: 1,
        maxmin: true,
        moveOut: true,
        area: area,
        content: url,
        shade: [0.3, "#000000"],
        success: function() {
            var pdiv = $('#LAY_app_body', window.parent.document);
            var body = $('#layui-layer' + index);

            var box_w = pdiv.width();
            var box_h = pdiv.height();

            var w = body.width();
            var h = body.height();

            if (box_w>w && box_h>h) {
                layer.style(index, {
                    'max-width': '100%',
                    'max-height': '100%',
                });
            } else {
                layer.full(index);
            }
        },
        shadeClose: true,
        scrollbar: false
    })
};

$(function() {

    // 使用按钮打开弹层窗口，添加 class => _open_window 即可打开
    $("body").on('click', '._open_window', function() {
        var url = $(this).attr("w-url");
        var info = $(this).attr("w-info");
        var area = $(this).attr("w-area");
        openWindow(url, info, area);
    });

    // 使用按钮发起请求，添加 class => _open_request 即可发起post ajax请求
    $("body").on('click', '._open_request', function() {
        var url = $(this).attr("r-url");
        var id = $(this).attr("r-id");
        var info = $(this).attr("r-info");
        var title = id ? ("数据ID：" + id) : "信息";

        layer.confirm(info, {
            btn: ["确定", "取消"],
            icon: 0,
            title: title,
        }, function() {
            formRequest(url, {id: id}, function(){
                reloadPage();
            });
        }, function() {})
    });

    // 搜索回车键后显示loading
    $("._loading_page").click(function() {
        showLoading("加载中...");
    });

    // 顶部刷新按钮
    $("._page_reload").click(function() {
        showLoading("刷新中...");
        reloadPage();
    });

    var browser = function(){
        var u   = navigator.userAgent,
            app = navigator.appVersion,
            str = 'AppleWebKit',
            leng = u.indexOf(str);
            clean = u.substring(leng+str.length,leng+str.length+1);
        if(clean == '/'){
            var versions = u.substring(leng+str.length+1,leng+str.length+1+6);
        }else{
            var versions = u.substring(leng+str.length,leng+str.length+6);
        }
        return { //移动终端浏览器版本信息 
            mobile: u.indexOf('Mobile') > -1,
            ios: !!u.match(/\(i[^;]+;( U;)? CPU.+Mac OS X/), //ios终端 
            android: u.indexOf('Android') > -1 || u.indexOf('Linux') > -1, //android
            iPhone: u.indexOf('iPhone') > -1, //iPhone
            iPad: u.indexOf('iPad') > -1, //iPad 
            UC: u.indexOf('UCBrowser') > -1,
            version:versions
        }
    }();
    
    setTimeout(function(){
        var ua = navigator.userAgent.toLowerCase();
        var screenwidth = window.screen.width;
        if(!/iphone|ipad|ipod/.test(ua)){
            $("body").attr("scrolling","auto");
        }else{
            $('body').width(screenwidth + 'px');
            $("iframe").attr("scrolling","no");
            $('iframe').css({width:'1px', 'min-width':'100%'});
        }

        if(browser.mobile){
            $('.layui-table-cell').addClass('layui-table-cell_120');
        }


    },1000);

});
