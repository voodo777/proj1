{{--//                        автолоадер пабликменю при прокрутке--}}
{{--//                        Подумать о кнопке подъёма скроллинга пабликменю вверх--}}
{{--попробовать добиться наличия результатов поиска "без энтера" через api (https://vk.com/dev/search.getHints ? https://vk.com/support?act=show&id=25383369)--}}
        {{--Не скрывать пабликменю, если фокус на пабликменю (эвентлистенер на пабменю -> сброс таймера таймаута онблюра инпута) --}}
        {{--показывать на первых местах паблики с подпиской--}}


<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

{{--<script src="https://vk.com/js/api/xd_connection.js?2"  type="text/javascript"></script>--}}
<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>

<script type="text/javascript">


    var publicResponse="";
    var vktoken='b6cfa04deb51b990556252284a6a69c59952e0f3b4d2846627d8f160df570b5c2a91b0001562aaef3f711';
    var API = 'https://api.vk.com/method/', apiV='&v=5.63';
    var searchCount=10, searchResultCount=1;//количество сообществ, возвращаемых через поиск
    var returned=[], tempReturned=[];
    var parseDate = new Date - 2000, lastChangeInPublicInput = new Date - 2000;
    var lastChangeInPublicInputCounter=0;
    var apiRequestBusy= 0, apiRequestBusyCounter=0;
    var checkButtonBusy=false;//, checkButtonBusyTime;
    var timerID= 0, checkButtonTimerId= 0, maxWaitTime=15000, checkButtonErrTry= 3, checkButtonErrTryCounter=0;
    var pubMenuItemHeight=67;
    var rv = -1;//версия ie, -1 если не ie



    function apiRequest (method, params, token, completeFunction, place, freeParam, searchString) {
//        console.log('Мы в apiRequest, пришли из ' + place + ', поисковая строка - ' + searchString)
        if (apiRequestBusy==0 && (new Date () - parseDate)>333) {
            apiRequestBusyCounter=0;
            apiRequestGo(method, params, token, completeFunction, place, freeParam, searchString);
        }
        else {
//            console.log(apiRequestBusyCounter)
            if (apiRequestBusyCounter < 3) {
                setTimeout(function () {apiRequest(method, params, token, completeFunction, place, freeParam, searchString);}, 333)
                apiRequestBusyCounter++;
            }
        }
    }

    function apiRequestGo (method, params, token, completeFunction, place, freeParam, searchString) {
        apiRequestBusy++;
        var tempToken="";
        if (token) {tempToken="&access_token=" + vktoken;}
//        console.log(API + method + "?" + params + apiV + tempToken)
        $.ajax({
            url: API + method + "?" + params + apiV + tempToken,
            dataType: "jsonp",
            success: function (e) {
                resp = e;
                err = false;
            },
            error: function () {
                resp = "Не удалось отправить запрос";
                err = true;
            },
            complete: function () {
                completeFunction(err, resp, place, freeParam, searchString);
                parseDate = new Date();
                apiRequestBusy--;
            }
        });
    }



    function getPublicResponse (err, resp, place, ids, searchString) {
//        console.log(place)
//        console.log('getPublicResponse: ' + searchString)
//        console.log(resp)
        if (err) {
//            console.log(resp);
        }
        else {
//            console.log('resp.response ' + resp.response)
            if (resp.response === undefined) {
                if (resp.error === undefined) {
                    console.log('Неизвестная ошибка');
                }
                else {
                    console.log(resp.error.error_code + ": " + resp.error.error_msg);
                }
            }
            else {
//                console.log(resp)
                fillReturned(resp, searchString, place);
            }
        }
        switch (place) {
            case 'checkButton':
                apiRequest('groups.search', 'q=' + ids + '&count=' + searchCount, true, groupSearch, 'getPublicResponse', '', searchString)
                break
            case 'manyIDs':
                checkButtonBusyFunc(false);
                if (returned.length > 0) {
                    if (returned.length>searchResultCount+1) {returned=[];checkButton();}
                    else {
//                        console.log(returned.mainString)
                        var publicListTable = '';
                        for (i=0;i<returned.length;i++) {
                            var currentComm=convertComm(returned[i]);
                            publicListTable = publicListTable +
                                    '<tr class="trPubList">' +
                                    '<td class="pubAva"><div class="avatar" style="background-image: url(' + currentComm.photo + '); background-repeat: no-repeat; background-position: center center;"></div></td>' +
                                    '<td class="pubContent">' +
                                        '<div class="pubLink"><a href="' + currentComm.link + '" target="_blank" title="' + returned[i].name + '">' + currentComm.name + '</a></div>' +
                                        '<div class="pubType">' + currentComm.type + '</div>' +
                                        '<div class="pubStatus">' + currentComm.deactivated + '</div>' +
                                        '<div class="pubCount">' + currentComm.members_count + '</div></td>' +
                                    '<td class="pubCheck"><img src="/img/pubCheck.png" width="22" height="64"></td>' +
                                    '</tr>\n';
                        }
                        document.getElementById('tbodyPL').innerHTML = publicListTable;
                        changePublicMenuState (true)
                    }
                }
                else {
                    changePublicMenuState (false)
                }

                break
            default:
                console.log('Где это мы?');
                break
        }
    }

    function convertComm (noConvertedPub) {
        var convertedPub=[];
        convertedPub.name=noConvertedPub.name.replace('<', '&lt;');
        if (noConvertedPub.verified==1) {
            convertedPub.name='<img src="/img/verified.png" width="14" height="14" class="verified">' + convertedPub.name;
        }
        convertedPub.photo=noConvertedPub.photo;
//        console.log(noConvertedPub.type)
        switch (noConvertedPub.type) {
            case 'page':
                convertedPub.link='https://vk.com/public' + noConvertedPub.gid;
                convertedPub.type='Публичная страница';
                convertedPub.canSelect=true;
                break
            case 'group':
                convertedPub.link='https://vk.com/club' + noConvertedPub.gid;
                switch (noConvertedPub.is_closed) {
                    case 0:convertedPub.type='Открытая группа';convertedPub.canSelect=true;break
                    case 1:convertedPub.type='Закрытая группа';convertedPub.canSelect=true;break
                    case 2:convertedPub.type='Частная группа';convertedPub.canSelect=false;break
                    default:convertedPub.type='Непонятная группа';convertedPub.canSelect=false;break;
                }
                break
            case  'event':
                convertedPub.link='https://vk.com/event' + noConvertedPub.gid;
                switch (noConvertedPub.is_closed) {
                    case 0:convertedPub.type='Открытая встреча';convertedPub.canSelect=true;break
                    case 1:convertedPub.type='Закрытая встреча';convertedPub.canSelect=false;break
                    case 2:convertedPub.type='Частная встреча';convertedPub.canSelect=false;break
                    default:convertedPub.type='Непонятная встреча';convertedPub.canSelect=false;break
                }
                break
            default:
                convertedPub.link='https://vk.com/public' + noConvertedPub.gid;
                convertedPub.type='??';
                convertedPub.canSelect=false;
                break
        }

        switch (noConvertedPub.deactivated) {
            case 'deleted':convertedPub.deactivated='Удалено';convertedPub.canSelect=false;break;
            case 'banned':convertedPub.deactivated='Забанено';convertedPub.canSelect=false;break;
            default:convertedPub.deactivated='';break
        }

        if (noConvertedPub.members_count===undefined) {
            if (convertedPub.deactivated=='' && (noConvertedPub.type=='page' || (noConvertedPub.type=='group' && (noConvertedPub.is_closed==0 || noConvertedPub.is_closed==1)) || (noConvertedPub.type=='event' && noConvertedPub.is_closed==0))) {
                convertedPub.deactivated='Блок РКН';
                convertedPub.canSelect=false;
            }
            convertedPub.members_count='?? подписчиков';
        }
        else {
            var subscribers='';
            switch (String(noConvertedPub.members_count).charAt(String(noConvertedPub.members_count).length-1)) {
                case '1': subscribers=' подписчик';break
                case '2': subscribers=' подписчика';break
                case '3': subscribers=' подписчика';break
                case '4': subscribers=' подписчика';break
                default : subscribers=' подписчиков';break
            }
            convertedPub.members_count = noConvertedPub.members_count + subscribers;
            convertedPub.members_count = convertedPub.members_count.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ')
        }

        return convertedPub;
    }

    function checkButtonBusyFunc (state) {
        if (state) {
//            console.log('Отменяем занятость: '+ new Date() + '  ' + returned.mainString)
            checkButtonTimerId=setTimeout(function () {breakCheckButton('timeout')}, maxWaitTime/checkButtonErrTry)
            checkButtonBusy=true;
//            checkButtonBusyTime=new Date();
            document.getElementById('preloaderInText').style.display='none';
            document.getElementById('preloaderInImg').style.display='block';
            document.getElementById('test').innerHTML='занято';
        }
        else {
            clearTimeout(checkButtonTimerId);
            checkButtonBusy=false;
//            console.log('Врубаем занятость: '+ new Date() + '  ' + returned.mainString)
            document.getElementById('preloaderInText').style.display='block';
            document.getElementById('preloaderInImg').style.display='none';
            document.getElementById('test').innerHTML='не занято';
        }
    }

    function groupSearch (err, resp, place, freeParam, searchString) {
//        console.log('groupSearch: ' + searchString)
        if (resp.response===undefined) {
//            console.log(resp)
            if (resp.error===undefined) {console.log('Неизвестная ошибка');breakCheckButton ('respError');}
            else {console.log(resp.error.error_code + ": " + resp.error.error_msg);}
        }
        else {
            //Обработка результатов поиска по пабликам, в случае, если количество найденных результатов отличается от нуля
            if (resp.response[0]!=0) {
                var groupIdString='';
                if (returned[0]===undefined) {
//                    console.log('Нетути такова')
                }
                else {
                    var findedPub=returned[0].gid;
                }
                for (i = 0; i < resp.response['items'].length; i++) {
                    if (resp.response['items'][i].id!=findedPub) {
                        groupIdString += resp.response['items'][i].id + ',';
                    }
                }
                groupIdString=groupIdString.substring(0, groupIdString.length-1)
            }
            apiRequest('groups.getById', 'group_ids=' + groupIdString + '&fields=members_count,verified', false, getPublicResponse, 'manyIDs', '', searchString);
        }
    }

    function fillReturned (resp, searchString, place) {
        searchResultCount=resp.response.length;
        for (i=0;i<resp.response.length;i++) {
            if (typeof returned[0]===undefined) {j=0;} else {j=returned.length;}
//            console.log(j + ' : ' + resp.response[i].photo_50)
            returned.mainString = searchString;
            returned[j] = [];
            returned[j].name = resp.response[i].name;
            returned[j].gid = resp.response[i].id;
            returned[j].type = resp.response[i].type;
            returned[j].is_closed = resp.response[i].is_closed;
            returned[j].deactivated = resp.response[i].deactivated;
            returned[j].photo = resp.response[i].photo_50;
            returned[j].members_count = resp.response[i].members_count;
            if (resp.response[i].verified==1) {returned[j].verified = resp.response[i].verified;}
            else {returned[j].verified = 0;}

        }
    }

    function checkButton() {
        var ids = document.getElementById("public_link").value.replace(/(^\s*)|(\s*)$/g, '');
//        console.log('Попытка использовать чекбаттон: ' + returned.mainString + '  ' + ids)
        if (!checkButtonBusy && ids.length!=0) {
//            console.log('Можно использовать checkButton: ' + returned.mainString)
            document.getElementById("public-menu").scrollTop=0
            clearInterval(timerID);
            timerID=0;
            checkButtonBusyFunc(true);
            var id = ids;
            returned = [], tempReturned = [];
            //Пытаемся получить голый айдишник/шотлинк, если есть ссылка прямо на вк
            if (ids.indexOf('vk.com/') != -1 || ids.indexOf('vkontakte.ru/') != -1) {
//                console.log('Ссылка')
                if (ids.indexOf('vk.com/') != -1) {
                    id = ids.slice(ids.indexOf('vk.com/') + 7);
                }
                else {
                    id = ids.slice(ids.indexOf('vkontakte.ru/') + 13);
                }
                if (id.indexOf('public') == 0 || id.indexOf('group') == 0 || id.indexOf('event') == 0) {
                    if (id.indexOf('public') == 0) {
                        if (!isNaN(id.slice(6))) {
                            id = id.slice(6)
                        }
                    }
                    else {
                        {
                            if (!isNaN(id.slice(5))) {
                                id = id.slice(5)
                            }
                        }
                    }
                }
                if (id.length > 0) {
                    apiRequest('groups.getById', 'group_id=' + id + '&fields=members_count,verified', false, getPublicResponse, 'checkButton', ids, ids);
                }
                else {093
                    apiRequest('groups.getById', 'group_id=' + ids + '&fields=members_count,verified', false, getPublicResponse, 'checkButton', ids, ids);
                }
            }
            //Если ссылки на ВК нет - есть два варианта: либо передали просто айдишник/шотлинк, либо это просто поисковая строка
            else {
//                console.log('Нет ссылки')
                apiRequest('groups.getById', 'group_id=' + id + '&fields=members_count,verified', false, getPublicResponse, 'checkButton', ids, ids);
            }
        }
        else {
//            console.log('Нельзя использовать checkButton: ' + returned.mainString)
//            if ((new Date() - checkButtonBusyTime) > 1000) {
//                checkButtonBusy=false;
//            }
        }
    }

    function breakCheckButton (errPlace) {
        if (checkButtonErrTryCounter<=checkButtonErrTry) {
//            console.log("checkButtonErrTryCounter " + checkButtonErrTryCounter)
            checkButtonErrTryCounter++;
            checkButtonBusyFunc(false);
            checkButton();
        }
        else {
            checkButtonErrTryCounter=0;
            clearTimeout(checkButtonTimerId);
            checkButtonBusyFunc(false);
            returned = [];
            returned[0] = 'error';
            var errMsg = '';
            if (errPlace == 'respError') {
                errMsg = 'Ошибка выполнения запроса. Проверьте подключение к интернету и работоспособность vk.com';
            }
            else {
                errMsg = 'Ошибка выполнения запроса. Попробуйте повторить поиск.';
            }
            document.getElementById('tbodyPL').innerHTML = '<tr><td id="searchError">' + errMsg + '</td></tr>';
            changePublicMenuState(true);
        }
    }


    function publicLinkChange () {
        var now = new Date()
        var tempString=document.getElementById('public_link').value
        if (rv==-1) {
            if (tempString.length >= 1) {
                document.getElementById('clearInput').style.display = '';
            }
            else {
                document.getElementById('clearInput').style.display = 'none';
            }
        }
        lastChangeInPublicInput=now;
        lastChangeInPublicInputCounter++;
        setTimeout(function (){
            var temp=lastChangeInPublicInputCounter;
            setTimeout(function () {checkLastChangeInPublicInput (temp)}, 500);
        }, 1)
    }

    function checkLastChangeInPublicInput (counter) {
//        console.log('Попытка использовать checkLastChangeInPublicInput: ' + returned.mainString)
        var publicLink=document.getElementById('public_link').value
        if (publicLink.length>=1) {
            if (counter == lastChangeInPublicInputCounter && timerID==0) {
//                console.log('Пора дёргать баттон: ' + document.getElementById("public_link").value)
                timerID = setInterval(function () {checkButton();}, 100);
                lastChangeInPublicInputCounter = 0;

            }
        }
        else {
            returned=[];
            changePublicMenuState (false)
        }
    }

    function changePublicMenuState (state) {
        if (state) {
            var pubMenuHeight;
            if (returned.length*pubMenuItemHeight<document.documentElement.clientHeight/3) pubMenuHeight=returned.length*pubMenuItemHeight+1;
            else pubMenuHeight=document.documentElement.clientHeight/3+1;
            if (pubMenuHeight<140) {
                switch (returned.length) {
                    case 0:
                        pubMenuHeight=0;
                        break
                    case 1:
                        pubMenuHeight=68;
                        break
                    case 2:
                        pubMenuHeight=135;
                        break
                    default:
                        pubMenuHeight=150;
                }
            }
            document.getElementById("public-menu").style.height=pubMenuHeight + 'px';
            document.getElementById("public-menu").style['border-bottom']='1px solid #cccccc';
            castShadow('public-check-btn', true, '2px 2px 5px 0px rgba(0,0,0,0.2)', '2px 2px 5px 0px rgba(0,0,0,0.2)', '2px 2px 5px 0px rgba(0,0,0,0.2)')
            castCorners('public_link', false, false, false, true, false)
            castCorners('public-check-btn', false, false, false, false, true)
        }
        else {
            document.getElementById("public-menu").style.height='0px';
            castShadow('public-check-btn', false)
            castCorners ('public_link', true, false, false, true, false, '4px')
            castCorners ('public-check-btn', true, false, false, false, true, '4px')
            document.getElementById("public-menu").style['border-bottom']='none';
        }
    }

    function castShadow (itemID, status, paramsWK, paramsMoz, params) {
        if (status) {
            document.getElementById(itemID).style['-webkit-box-shadow'] = paramsWK;
            document.getElementById(itemID).style['-moz-box-shadow'] = paramsMoz;
            document.getElementById(itemID).style['box-shadow'] = params;

        }
        else {
            document.getElementById(itemID).style['-webkit-box-shadow'] = 'none';
            document.getElementById(itemID).style['-moz-box-shadow'] = 'none';
            document.getElementById(itemID).style['box-shadow'] = 'none';
        }
    }

    function castCorners (itemID, status, topLeft, topRight, bottomLeft, bottomRight, raduis) {
        var rad;
        if (status) {rad=raduis;}
        else {rad='0px';}
        if (topLeft) {document.getElementById(itemID).style['border-top-left-radius']=rad;}
        if (topRight) {document.getElementById(itemID).style['border-top-right-radius']=rad;}
        if (bottomLeft) {document.getElementById(itemID).style['border-bottom-left-radius']=rad;}
        if (bottomRight) {document.getElementById(itemID).style['border-bottom-right-radius']=rad;}
    }


    document.addEventListener("DOMContentLoaded", ready)
    function ready () {
        document.getElementById('clearInput').style.display='none';
        if (getInternetExplorerVersion()!=-1) {
            document.getElementById('public-check-btn').style['margin-left']='-4px';
            document.getElementById('public_link').style['padding-right']='0px';
        }
//        document.getElementById('public_link').addEventListener('focus', function () {changePublicMenuState (true)},true);
//        document.getElementById('public_link').addEventListener('blur', function () {setTimeout( function () {changePublicMenuState (false)}, 1000)},true);
    }

    function getInternetExplorerVersion()    {
        if (navigator.appName == 'Microsoft Internet Explorer') {
            var ua = navigator.userAgent;
            var re  = new RegExp("MSIE ([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat( RegExp.$1 );
        }
        else if (navigator.appName == 'Netscape') {
            var ua = navigator.userAgent;
            var re  = new RegExp("Trident/.*rv:([0-9]{1,}[\.0-9]{0,})");
            if (re.exec(ua) != null)
                rv = parseFloat( RegExp.$1 );
        }
        return rv;
    }

    function clearPublicInput () {
        if (rv==-1) {
            document.getElementById('public_link').value = '';
            publicLinkChange();
        }
    }

</script>

<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>
    <link rel="stylesheet" type="text/css" href="<?=asset('css/style.css')?>" />
</head>


<body>
<div class="container">
    <div class="row">

        {{--<form action="/save-comm" class="form-inline">--}}
            {{--<div class="form-group">--}}
                {{--<input type="text" placeholder="public id" name="public_id" class="form-control">--}}
            {{--</div>--}}
            {{--<button type="submit" class="btn btn-success">Добавить</button>--}}
        {{--</form>--}}


        <form class="form-inline" id="inForm" onsubmit="return false;">
            <input type="text" placeholder="Ссылка, id или название" id="public_link" class="form-control public-in-form" oninput="publicLinkChange ();" autocomplete="off"">
            <button id="clearInput" onclick="clearPublicInput ();"><img src="/img/cross.png" width="28" height="28"></button>
            <button type="submit" id="public-check-btn" class="btn btn-success public-check-btn" onclick="checkButton()"><img id="preloaderInImg" src="/img/preloader.png" width="21" height="21"><p id="preloaderInText">Проверить</p></button>
            <div id="test">123</div>
        </form>
        <div id="public-menu">
            <table class="table tablePubList">
                <tbody class="tbodyPubList" id="tbodyPL">
                </tbody>
            </table>
        </div>
    </div>

    {{--<script type="text/javascript">--}}
        {{--$("#public-check-btn").click(function(){--}}
            {{--alert(jQuery.fn.jquery);--}}
        {{--});--}}
    {{--</script>--}}



    <div class="row">
        <table class="table publiclist">
            @foreach($comms as $community)
                <tr>
                    <td>{{$community->id}}</td>
                    <td>{{$community->vk}}</td>
                    <td>{{$community->name}}</td>
                    <td>{{$community->status}}</td>
                </tr>
            @endforeach
        </table>
    </div>
</div>

</body>
</html>