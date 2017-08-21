var urlLogin = '/auth/index';

var datagridOptions = {
  height : 'auto',
  nowrap : false,
  fitColumns : false,
  striped : true,
  border : true,
  collapsible :false, //是否可折叠的
  showFooter : true,
  fit : true, //自动大小
  remoteSort : false,
  frozenColumns : [[{field:'ck',checkbox:true}]],
  pagination: true, //分页控件
  pageSize : 50,
  pageList : [50,100,200,500],
  queryParams : {},
  onLoadSuccess : function(data){
    handleException(data);
  },
  onLoadError : function(){
    alert('数据加载错误,请与管理员联系或稍候再试!');
  }
}

//生成常用Datagrid
function simpleDatagrid(id, queryParams)
{
  if(typeof(queryParams) == 'undefined'){
    queryParams = {};
  }

  //保存到临时变量mainDatagridOptions，不改变全局datagridOptions的值
  var mainDatagridOptions = jQuery.extend(true, {}, datagridOptions);
  //生成Datagrid
  $(id).datagrid(mainDatagridOptions);

  mainDatagridOptions.queryParams = queryParams;
}

//保存/提交表单数据
function saveForm(datagrid, form, dlgform)
{
  $.post($(form).attr('action'), $(form).serialize(), function(responseText, textStatus, XMLHttpRequest)
  {
    if(!handleException(responseText))
    {
      $(dlgform).dialog('close');
      $(datagrid).datagrid('reload');
    }
  }, 'json');
}

//删除一条或多条数据
function deleteRows(datagrid, url, id)
{
  var postData = '';
  if(typeof(id)==undefined)
  {
    var ids = [];
    var rows = $(datagrid).datagrid('getSelections');

    if(rows.length<1)
    {
      alert('请至少选择一条要删除的记录。');
      return false;
    }

    for(var i=0;i<rows.length;i++){
      $.ajax({
        url: url+'/'+rows[i].id,
        type: 'DELETE',
        dataType: "json",
        success: function(data) {
          if(!handleException(data))
          {
            $(datagrid).datagrid('reload');
          }
        }
      });
    }
  } else {
    $.ajax({
      url: url,
      type: 'DELETE',
      dataType: "json",
      success: function(data) {
        if(!handleException(data))
        {
          $(datagrid).datagrid('reload');
        }
      }
    });
  }


}

//添加窗口
function dlgAdd(dlg, form)
{
  $(form).form('clear');
  $(dlg).dialog('open');
  return false;
}


//编辑窗口
function dlgEdit(dlg, form, url)
{
  $(form).form('clear');
  $(dlg).dialog('open');
  $(form).form('load', url);
  return false;
}

//生成随机字符串
function randomString(length) {
  var chars = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz'.split('');

  if (! length) {
    length = Math.floor(Math.random() * chars.length);
  }

  var str = '';
  for (var i = 0; i < length; i++) {
    str += chars[Math.floor(Math.random() * chars.length)];
  }
  return str;
}

//格式化状态
function formatterYesNo(value, row, index)
{
  return value==1 ? '<font style="color:red">是</font>' : '否';
}

//省市区联通公用方法
function area(url, selector, code, type, selected_value, option_html)
{
  var selected = '';
  $.ajax({
    url: url,
    dataType: 'json',
    data: {
      'type' : type,
      'code' : code
    },
    success: function(data){
      if(!handleException(data))
      {
        var items = $.map(data.rows, function(item, index){
          if (selected_value != '' && index == selected_value)
          {
            selected = 'selected';
          }
          option_html+= "<option value="+index+" "+selected+">"+item+"</option>";
          selected = '';
        });
        $(selector).html(option_html);
      }
    },
    error: function(){
      error.apply(this, arguments);
    }
  });
}

/**
 * 阻止事件继续，主要用于datagrid中点击链接选中行
 */
function stopEvent()
{
  if(typeof(event) != 'undefined')
  {
    (event && event.stopPropagation) ? event.stopPropagation() : window.event.cancelBubble=true;
  }
}

/**
 * 处理ajax返回的异常信息
 *
 */
function handleException(data)
{
  if(data.success) return false;

  if(data.code == '1007')    //登录过期，如果以后改code只改这里即可
    top.location.href = urlLogin;
  else
    alert(data.message);

  return true;
}

//生成google密钥
function generateGoolgeSecret()
{
  var url='/oa_ajax/generate_goolge_secret';
  $.post(url, '', function(data){
    $("#form-info :input[name='google_secret']").val(data);
  }, "json");
}
