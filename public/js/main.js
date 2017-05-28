var publicResponse="";
//var vktoken='{{  $userToken }}';
var API = 'https://api.vk.com/method/', apiV='&v=5.63';
var actualMskTzOffset;
var searchCount=10, searchResultCount=1;//количество сообществ, возвращаемых через поиск
var returnedIn=[], returnedOut=[];
var selectedIn=[], selectedOut=[];
var parseDate = new Date - 2000, lastChangeInPublicInput = new Date - 2000;
var lastChangeInPublicInputCounter=0;
var apiRequestBusy= 0, apiRequestBusyCounter=0;
var checkButtonBusy=false;//, checkButtonBusyTime;
var timerID= 0, checkButtonTimerId= 0, maxWaitTime=15000, checkButtonErrTry= 3, checkButtonErrTryCounter=0;
var pubMenuItemHeight=53;
var rv = -1;//версия ie, -1 если не ie


function apiRequest (method, params, token, completeFunction, place, freeParam, searchString, sourse) {
//        console.log('Мы в apiRequest, пришли из ' + place + ', поисковая строка - ' + searchString)
    if (apiRequestBusy==0 && (new Date () - parseDate)>333) {
        apiRequestBusyCounter=0;
        apiRequestGo(method, params, token, completeFunction, place, freeParam, searchString, sourse);
    }
    else {
//            console.log(apiRequestBusyCounter)
        if (apiRequestBusyCounter < 3) {
            setTimeout(function () {apiRequest(method, params, token, completeFunction, place, freeParam, searchString, sourse);}, 333)
            apiRequestBusyCounter++;
        }
    }
}

function apiRequestGo (method, params, token, completeFunction, place, freeParam, searchString, sourse) {
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
            completeFunction(err, resp, place, freeParam, searchString, sourse);
            parseDate = new Date();
            apiRequestBusy--;
        }
    });
}

//    let ifWeSucceed = function (mes) {
//        alert(mes.message + '!!!');
//    };
//
//    function fu() {
//        bla(ifWeSucceed, 'adsfa');
//    }
//
//    function bla (successFunction, errorFunction) {
//        $.ajax({
//            url: "/ajax",
//            dataType: "json",
//            success: successFunction,
//            error:successFunction,
//            complete: function () { }
//        });
//    }


function getPublicResponse (err, resp, place, ids, searchString, sourse) {
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
            fillReturned(resp, searchString, sourse);
        }
    }
    switch (place) {
        case 'checkButton':
            apiRequest('groups.search', 'q=' + ids + '&count=' + searchCount, true, groupSearch, 'getPublicResponse', '', searchString, sourse)
            break
        case 'manyIDs':
            checkButtonBusyFunc(false, sourse);
            var returned=selectReturned (sourse);
            if (returned.length > 0) {
                if (returned.length>searchResultCount+1) {
                    if (sourse=='Out') {returnedOut=[];}
                    else {returnedIn=[];}
                    checkButton(sourse);
                }
                else {
                    var publicListTable = '';
                    for (i=0;i<returned.length;i++) {
                        var currentComm;
                        if (sourse=='Out') {currentComm=convertComm(returnedOut[i], i, sourse);}
                        else {currentComm=convertComm(returnedIn[i], i, sourse);}
                        publicListTable = publicListTable +
                            '<tr class="trPubList">' +
                            //'<td class="pubAva"><div class="avatar" style="background-image: url(' + currentComm.photo + '); background-repeat: no-repeat; background-position: center center;"></div></td>' +
                            '<td class="pubAva"><img src="'+currentComm.photo+'" class="avatar"></td>' +
                            '<td class="pubContent">' +
                            '<div class="pubLink"><a href="' + currentComm.link + '" target="_blank" title="' + returned[i].name + '">' + currentComm.name + '</a></div>' +
                            '<div class="pubType">' + currentComm.type + '</div>' +
                            '<div class="pubStatus">' + currentComm.deactivated + '</div>' +
                            '<div class="pubCount">' + currentComm.members_count + '</div></td>' +
//                                    '<td class="pubCheck"><img src="/img/pubCheck.png" width="22" height="64"></td>' +
                            '<td class="pubCheck">' + currentComm.canSelect + '</td>' +
                            '</tr>\n';
                    }
                    document.getElementById('tbodyPl'+sourse).innerHTML = publicListTable;
                    changePublicMenuState (true, sourse)
                }
            }
            else {
                changePublicMenuState (false, sourse)
            }

            break
        default:
            console.log('Где это мы?');
            break
    }
}

function convertComm (noConvertedPub, pubNum, sourse) {
    var convertedPub=[];
    //noConvertedPub.convName = convertedPub.name=noConvertedPub.name.replace('<', '&lt;');
    //if (noConvertedPub.verified==1) {
    //    noConvertedPub.convName=convertedPub.name='<img src="/img/verified.png" width="10" height="10" class="verified">' + convertedPub.name;
    //}
    noConvertedPub.name=noConvertedPub.name.replace('<', '&lt;')
    noConvertedPub.convName = convertedPub.name=noConvertedPub.name;
    if (noConvertedPub.verified==1) {
        noConvertedPub.convName=convertedPub.name='<img src="/img/verified.png" width="10" height="10" class="verified">' + convertedPub.name;
    }
    noConvertedPub.convPhoto=convertedPub.photo=noConvertedPub.photo;
    switch (noConvertedPub.type) {
        case 'page':
            noConvertedPub.convLink=convertedPub.link='https://vk.com/public' + noConvertedPub.gid;
            noConvertedPub.convType=convertedPub.type='Публичная страница';
            convertedPub.canSelect=true;
            break
        case 'group':
            noConvertedPub.convLink=convertedPub.link='https://vk.com/club' + noConvertedPub.gid;
            switch (noConvertedPub.is_closed) {
                case 0:noConvertedPub.convType=convertedPub.type='Открытая группа';convertedPub.canSelect=true;break
                case 1:noConvertedPub.convType=convertedPub.type='Закрытая группа';convertedPub.canSelect=true;break
                case 2:noConvertedPub.convType=convertedPub.type='Частная группа';convertedPub.canSelect=false;break
                default:noConvertedPub.convType=convertedPub.type='?? группа';convertedPub.canSelect=false;break
            }
            break
        case  'event':
            noConvertedPub.convLink=convertedPub.link='https://vk.com/event' + noConvertedPub.gid;
            switch (noConvertedPub.is_closed) {
                case 0:noConvertedPub.convType=convertedPub.type='Открытая встреча';convertedPub.canSelect=true;break
                case 1:noConvertedPub.convType=convertedPub.type='Закрытая встреча';convertedPub.canSelect=false;break
                case 2:noConvertedPub.convType=convertedPub.type='Частная встреча';convertedPub.canSelect=false;break
                default:noConvertedPub.convType=convertedPub.type='?? встреча';convertedPub.canSelect=false;break
            }
            break
        default:
            noConvertedPub.convLink=convertedPub.link='https://vk.com/public' + noConvertedPub.gid;
            noConvertedPub.convType=convertedPub.type='??';
            convertedPub.canSelect=false;
            break
    }

    switch (noConvertedPub.deactivated) {
        case 'deleted':convertedPub.deactivated='Del';convertedPub.canSelect=false;break
        case 'banned':convertedPub.deactivated='Бан';convertedPub.canSelect=false;break
        default:convertedPub.deactivated='';break
    }

    if (noConvertedPub.members_count===undefined) {
        if (convertedPub.deactivated=='' && (noConvertedPub.type=='page' || (noConvertedPub.type=='group' && (noConvertedPub.is_closed==0 || noConvertedPub.is_closed==1)) || (noConvertedPub.type=='event' && noConvertedPub.is_closed==0))) {
            convertedPub.deactivated='РКН';
            convertedPub.canSelect=false;
        }
        noConvertedPub.convMemberSel_count=noConvertedPub.convMember_count=convertedPub.members_count='?? чел.';
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
        noConvertedPub.convMember_count=convertedPub.members_count = noConvertedPub.members_count + subscribers;
        noConvertedPub.convMemberSel_count=noConvertedPub.convMember_count=convertedPub.members_count = convertedPub.members_count.replace(/(\d)(?=(\d\d\d)+([^\d]|$))/g, '$1 ')
        if (convertedPub.members_count.charAt(0)!='?') {
            noConvertedPub.convMember_count=convertedPub.members_count='<a href="https://vk.com/stats?gid='+noConvertedPub.gid+'" target="_blank" class="statLink">'+convertedPub.members_count+'</a>';
        }

    }
    if (convertedPub.canSelect) {convertedPub.canSelect='<img class="pubCheckButton" src="/img/pubCheck.png" onclick="pubSelect('+pubNum+', \''+sourse+'\')" width="22" height="50">';}
//        if (convertedPub.canSelect) {convertedPub.canSelect='<img class="pubCheckButton" src="/img/pubCheck.png" width="22" height="64" onclick="alert(1)">';}
    else {convertedPub.canSelect="";}


    return convertedPub;
}


function pubSelect (pubNum, sourse) {
    var returned = selectReturned (sourse);
    fillSelected (true, sourse, pubNum)
    document.getElementById('avatar'+sourse).innerHTML='<img class = "avatarMedium" src="'+returned[pubNum].convPhoto+'">';
    console.log("Устанавливаем фотку " + returned[pubNum].convPhoto)
    document.getElementById('pubLink'+sourse).innerHTML='<a href="' + returned[pubNum].convLink + '" target="_blank" title="' + returned[pubNum].name + '">' + returned[pubNum].convName + '</a>'
    //document.getElementById('pubType'+sourse).innerHTML=returned[pubNum].convType;
    document.getElementById('pubCount'+sourse).innerHTML=returned[pubNum].convMember_count;
    document.getElementById('pubWrapper'+sourse).style.display='none';
    document.getElementById('publicMenu'+sourse).style.display='none';
    document.getElementById('publicContainer'+sourse).style.display='block';
}

function pubUnselect (sourse) {
    fillSelected (false, sourse)
    document.getElementById('pubWrapper'+sourse).style.display='block';
    document.getElementById('publicMenu'+sourse).style.display='block';
    document.getElementById('publicContainer'+sourse).style.display='none';
}

function fillSelected (fillType, sourse, pubNum) {
    if (fillType) {
        if (sourse=='Out') {
            selectedOut.gid=returnedOut[pubNum].gid;
            selectedOut.name=returnedOut[pubNum].name;
            selectedOut.count=returnedOut[pubNum].members_count;
            selectedOut.photo=returnedOut[pubNum].photo;
            //selectedOut.convLink=returnedOut[pubNum].convLink;
            selectedOut.type=returnedOut[pubNum].type;
            selectedOut.is_closed=returnedOut[pubNum].is_closed;
            selectedOut.verified=returnedOut[pubNum].verified;
        }
        else {
            selectedIn.gid=returnedIn[pubNum].gid;
            selectedIn.name=returnedIn[pubNum].name;
            selectedIn.count=returnedIn[pubNum].members_count;
            selectedIn.photo=returnedIn[pubNum].photo;
            //selectedIn.convLink=returnedIn[pubNum].convLink;
            selectedIn.type=returnedIn[pubNum].type;
            selectedIn.is_closed=returnedIn[pubNum].is_closed;
            selectedIn.verified=returnedIn[pubNum].verified;
        }
    }
    else {
        if (sourse=='Out') {
            selectedOut=[];
            document.getElementById('publicLinkOut').classList.remove('animateBorder')
        }
        else {
            selectedIn=[];
            document.getElementById('publicLinkIn').classList.remove('animateBorder')
        }
    }
}

function checkButtonBusyFunc (state, sourse) {
    if (state) {
//            console.log('Отменяем занятость: '+ new Date() + '  ' + returned.mainString)
        checkButtonTimerId=setTimeout(function () {breakCheckButton('timeout', sourse)}, maxWaitTime/checkButtonErrTry)
        checkButtonBusy=true;
        document.getElementById('preloaderText'+sourse).style.display='none';
        document.getElementById('preloaderImg'+sourse).style.display='block';
    }
    else {
        clearTimeout(checkButtonTimerId);
        checkButtonBusy=false;
//            console.log('Врубаем занятость: '+ new Date() + '  ' + returned.mainString)
        document.getElementById('preloaderText'+sourse).style.display='block';
        document.getElementById('preloaderImg'+sourse).style.display='none';
    }
}

function groupSearch (err, resp, place, freeParam, searchString, sourse) {
    if (resp.response===undefined) {
        console.log(resp)
        if (resp.error===undefined) {console.log('Неизвестная ошибка');breakCheckButton ('respError', sourse);}
        else {console.log(resp.error.error_code + ": " + resp.error.error_msg);}
    }
    else {
        //Обработка результатов поиска по пабликам, в случае, если количество найденных результатов отличается от нуля
//            console.log(resp.response.count)
        if (resp.response.count!=0) {
            var groupIdString='';
            var returned=selectReturned (sourse);
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
            apiRequest('groups.getById', 'group_ids=' + groupIdString + '&fields=members_count,verified', false, getPublicResponse, 'manyIDs', '', searchString, sourse);
        }
        else {
            getPublicResponse(false, [], 'manyIDs', '', searchString, sourse);
        }
    }
}

function fillReturned (resp, searchString, sourse) {
    searchResultCount=resp.response.length;
    var returned = selectReturned (sourse)
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
    fillSelectedReturned (returned, sourse)
}

function selectReturned (sourse) {
    if (sourse=='Out') {
        return returnedOut;
    }
    else {
        return returnedIn;
    }
}

function fillSelectedReturned (tempReturned, sourse) {
    if (sourse=='Out') {
        returnedOut=[];
        returnedOut=tempReturned;
    }
    else {
        returnedIn=[];
        returnedIn=tempReturned;
    }
}

function checkButton(sourse) {
    var ids = document.getElementById("publicLink"+sourse).value.replace(/(^\s*)|(\s*)$/g, '');
//        console.log('Попытка использовать чекбаттон: ' + returned.mainString + '  ' + ids)
    if (!checkButtonBusy && ids.length!=0) {
//            console.log('Можно использовать checkButton: ' + returned.mainString)
        document.getElementById("publicMenu"+sourse).scrollTop=0
        clearInterval(timerID);
        timerID=0;
        checkButtonBusyFunc(true, sourse);
        var id = ids;
        if (sourse=='Out') {returnedOut=[];}
        else {returnedIn=[];}
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
                apiRequest('groups.getById', 'group_id=' + id + '&fields=members_count,verified', false, getPublicResponse, 'checkButton', ids, ids, sourse);
            }
            else {
                apiRequest('groups.getById', 'group_id=' + ids + '&fields=members_count,verified', false, getPublicResponse, 'checkButton', ids, ids, sourse);
            }
        }
        //Если ссылки на ВК нет - есть два варианта: либо передали просто айдишник/шотлинк, либо это просто поисковая строка
        else {
//                console.log('Нет ссылки')
            apiRequest('groups.getById', 'group_id=' + id + '&fields=members_count,verified', false, getPublicResponse, 'checkButton', ids, ids, sourse);
        }
    }
    else {
//            console.log('Нельзя использовать checkButton: ' + returned.mainString)
//            if ((new Date() - checkButtonBusyTime) > 1000) {
//                checkButtonBusy=false;
//            }
    }
}

function breakCheckButton (errPlace, sourse) {
    if (checkButtonErrTryCounter<=checkButtonErrTry) {
//            console.log("checkButtonErrTryCounter " + checkButtonErrTryCounter)
        checkButtonErrTryCounter++;
        checkButtonBusyFunc(false, sourse);
        checkButton(sourse);
    }
    else {
        checkButtonErrTryCounter=0;
        clearTimeout(checkButtonTimerId);
        checkButtonBusyFunc(false, sourse);
        if (sourse=='Out') {returnedOut=[];returnedOut[0] = 'error';}
        else {returnedIn=[];returnedIn[0] = 'error';}
        var errMsg = '';
        if (errPlace == 'respError') {
            errMsg = 'Ошибка выполнения запроса. Проверьте подключение к интернету и работоспособность vk.com';
        }
        else {
            errMsg = 'Ошибка выполнения запроса. Попробуйте повторить поиск.';
        }
        document.getElementById('tbodyPl'+sourse).innerHTML = '<tr><td class="searchError">' + errMsg + '</td></tr>';
        changePublicMenuState(true, sourse);
    }
}


function publicLinkChange (sourse) {
    var now = new Date()
    var tempString=document.getElementById('publicLink'+sourse).value
    if (rv==-1) {
        if (tempString.length >= 1) {
            document.getElementById('clearInput'+sourse).style.display = '';
        }
        else {
            document.getElementById('clearInput'+sourse).style.display = 'none';
        }
    }
    lastChangeInPublicInput=now;
    lastChangeInPublicInputCounter++;
    setTimeout(function (){
        var temp=lastChangeInPublicInputCounter;
        setTimeout(function () {checkLastChangeInPublicInput (temp, sourse)}, 500);
    }, 1)
}

function checkLastChangeInPublicInput (counter, sourse) {
//        console.log('Попытка использовать checkLastChangeInPublicInput: ' + returned.mainString)
    var publicLink=document.getElementById('publicLink'+sourse).value
    if (publicLink.length>=1) {
        if (counter == lastChangeInPublicInputCounter && timerID==0) {
//                console.log('Пора дёргать баттон: ' + document.getElementById("publicLinkIn").value)
            timerID = setInterval(function () {checkButton(sourse);}, 100);
            lastChangeInPublicInputCounter = 0;

        }
    }
    else {
        if (sourse=='Out') {returnedOut=[];}
        else {returnedIn=[];}
        changePublicMenuState (false, sourse)
    }
}

function changePublicMenuState (state, sourse) {
    if (state) {
        var pubMenuHeight;
        var returned=selectReturned (sourse);
        if (returned.length*pubMenuItemHeight<window.innerHeight/3) {pubMenuHeight=returned.length*pubMenuItemHeight+3;}
        else {pubMenuHeight=window.innerHeight/3+1;}
        if (pubMenuHeight<140) {
            switch (returned.length) {
                case 0:
                    pubMenuHeight=0;
                    break
                case 1:
                    pubMenuHeight=pubMenuItemHeight+2;
                    break
                case 2:
                    pubMenuHeight=pubMenuItemHeight*2+2;
                    break
                default:
                    pubMenuHeight=150;
            }
        }
//            console.log(pubMenuHeight)
        document.getElementById("publicMenu"+sourse).style.height=pubMenuHeight + 'px';
        document.getElementById("publicMenu"+sourse).style['border-bottom']='1px solid #cccccc';
        document.getElementById("publicMenu"+sourse).style['border-top']='1px solid #cccccc';
        castShadow('publicCheckBtn'+sourse, true, '2px 2px 5px 0px rgba(0,0,0,0.2)', '2px 2px 5px 0px rgba(0,0,0,0.2)', '2px 2px 5px 0px rgba(0,0,0,0.2)')
        castCorners('publicLink'+sourse, false, false, false, true, false)
        castCorners('publicCheckBtn'+sourse, false, false, false, false, true)
    }
    else {
        document.getElementById("publicMenu"+sourse).style.height='0px';
        castShadow('publicCheckBtn'+sourse, false)
        castCorners ('publicLink'+sourse, true, false, false, true, false, '4px')
        castCorners ('publicCheckBtn'+sourse, true, false, false, false, true, '4px')
        document.getElementById("publicMenu"+sourse).style['border-bottom']='none';
        document.getElementById("publicMenu"+sourse).style['border-top']='none';
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
    rad = status ? raduis : '0px' ;
    if (topLeft) {document.getElementById(itemID).style['border-top-left-radius']=rad;}
    if (topRight) {document.getElementById(itemID).style['border-top-right-radius']=rad;}
    if (bottomLeft) {document.getElementById(itemID).style['border-bottom-left-radius']=rad;}
    if (bottomRight) {document.getElementById(itemID).style['border-bottom-right-radius']=rad;}
}


document.addEventListener("DOMContentLoaded", ready)
function ready () {
    actualMskTzOffset=-moment.tz.zone('Europe/Moscow').parse(moment())/60;
    document.getElementById('clearInputIn').style.display='none';
    document.getElementById('clearInputOut').style.display='none';
    if (getInternetExplorerVersion()!=-1) {
        //document.getElementById('publicCheckBtnIn').style['margin-left']='-4px';
        //document.getElementById('publicCheckBtnOut').style['margin-left']='-4px';
        document.getElementById('publicLinkIn').style['padding-right']='0px';
        document.getElementById('publicLinkOut').style['padding-right']='0px';
        document.getElementById('publicMenuIn').style['width']='215px';
        document.getElementById('publicMenuOut').style['width']='215px';
    }
        //document.getElementById('publicLinkIn').addEventListener('focus', function () {changePublicMenuState (true)},true);
        //document.getElementById('publicLinkIn').addEventListener('blur', function () {setTimeout( function () {changePublicMenuState (false)}, 1000)},true);
        var focusInputIn=false, focusInputOut=false, focusMenuIn=false, focusMenuOut=false;
        document.getElementById('publicLinkIn').addEventListener('focus', function () {focusInputIn=true;checkSelectorFocus(focusInputIn, focusMenuIn, 'In');},true);
        document.getElementById('publicMenuIn').addEventListener('focus', function () {focusMenuIn=true;checkSelectorFocus(focusInputIn, focusMenuIn, 'In');},true);
        document.getElementById('publicLinkIn').addEventListener('blur', function () {focusInputIn=false; setTimeout(function () {checkSelectorFocus(focusInputIn, focusMenuIn, 'In');}, 100);},true);
        document.getElementById('publicMenuIn').addEventListener('blur', function () {focusMenuIn=false;setTimeout(function () {checkSelectorFocus(focusInputIn, focusMenuIn, 'In');}, 100);},true);
        document.getElementById('publicLinkOut').addEventListener('focus', function () {focusInputOut=true;checkSelectorFocus(focusInputOut, focusMenuIn, 'Out');},true);
        document.getElementById('publicMenuOut').addEventListener('focus', function () {focusMenuOut=true;checkSelectorFocus(focusInputOut, focusMenuIn, 'Out');},true);
        document.getElementById('publicLinkOut').addEventListener('blur', function () {focusInputOut=false; setTimeout(function () {checkSelectorFocus(focusInputOut, focusMenuOut, 'Out');}, 100);},true);
        document.getElementById('publicMenuOut').addEventListener('blur', function () {focusMenuOut=false;setTimeout(function () {checkSelectorFocus(focusInputOut, focusMenuOut, 'Out');}, 100);},true);
}

//Нужна мегафункция, которая дёргается из обоих эвентлистенеров, в зависимости от источника, проверяет, нет ли фокуса на втором источнике, если нет на обоих - скрывает
function checkSelectorFocus (focusInput, focusMenu, sourse) {
    var $tt = document.getElementById('publicMenu'+sourse).style.height;
    if (focusInput==false && focusMenu==false) {
        if ($tt!="0px") {
            console.log('Пора скрывать: ' + $tt)
            changePublicMenuState (false, sourse)
        }
    }
    else {
        if ($tt=="0px") {
            changePublicMenuState (true, sourse)
        }

    }
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

function clearPublicInput (sourse) {
    if (rv==-1) {
        document.getElementById('publicLink'+sourse).value = '';
        publicLinkChange(sourse);
    }
}

function scrollPubMenu () {
//        console.log(document.getElementById('publicMenuIn').scrollTop+document.getElementById('publicMenuIn').clientHeight + " : " + returned.length*67)
}

function sendRequest (){
    var err=false;
    if (selectedIn.gid===undefined) {
        document.getElementById('publicLinkIn').classList.remove('animateBorder');
        setTimeout(function () {document.getElementById('publicLinkIn').classList.add('animateBorder')}, 100);
        err=true;
    }
    if (selectedOut.gid===undefined) {
        document.getElementById('publicLinkOut').classList.remove('animateBorder');
        setTimeout(function () {document.getElementById('publicLinkOut').classList.add('animateBorder')}, 100);
        err=true;
    }
    //Обе даты приводим к GMT 0, считая, что в инпуте (timeInInput) время московское, а в браузере (timeNow) локальное
    var tempInpDataVal=document.getElementById('datetimepicker').value;
    var timeInInput = Date.parse(document.getElementById('datetimepicker').value.replace(/(\d+).(\d+).(\d+)/, '$2.$1.$3')) - actualMskTzOffset*1000*60*60;
    var timeNow = Date.parse(new Date())+new Date().getTimezoneOffset()*60*1000;
    if (tempInpDataVal==NaN || tempInpDataVal=='' || (timeInInput-timeNow)<=0) {
        document.getElementById('datetimepicker').classList.remove('animateBorder');
        setTimeout(function () {document.getElementById('datetimepicker').classList.add('animateBorder')}, 100);
        err=true;
    }
    console.log(selectedIn)
    console.log(selectedOut)
    console.log(timeInInput)
    console.log(new Date(timeInInput))



    if (!err) {
        timeInInput=(timeInInput-new Date().getTimezoneOffset()*60*1000)/1000;
        var tempReq="gidIn="+selectedIn.gid+"&nameIn="+selectedIn.name+"&countIn="+selectedIn.count+
            "&photoIn="+selectedIn.photo+"&typeIn="+selectedIn.type+"&isClosedIn="+selectedIn.is_closed+"&verifiedIn="+selectedIn.verified+
            "&gidOut="+selectedOut.gid+"&nameOut="+selectedOut.name+"&countOut="+selectedOut.count+
            "&photoOut="+selectedOut.photo+"&typeOut="+selectedOut.type+"&isClosedOut="+selectedOut.is_closed+"&verifiedOut="+selectedOut.verified+"&postTime="+timeInInput;
        //var tempReq="gidIn=1&nameIn=false&countIn=1&photoIn=http&typeIn=event&isClosedIn=1&verifiedIn=-0"+
        //    "&gidOut="+selectedOut.gid+"&nameOut=123&countOut=0&photoOut="+selectedOut.photo+"&typeOut="+selectedOut.type+"&isClosedOut="+selectedOut.is_closed+"&verifiedOut="+selectedOut.verified+"&postTime=11111111111111111111фыв";
        console.log(tempReq)
        console.log(tempReq.length)
        $.ajax({
            type: "GET", //Либо "GET"
            url: "/add_monitor", //Целевой скрипт
            data: tempReq,
            success: function(answer){ //Здесь call back виде функции с аргументом answer
                alert(answer);}, //answer это то что вернул скрипт
            error: function (err) {
                alert("Во время выполнения запроса произошла ошибка: " + err.status + ": " + err.statusText)
                console.log(err)
            }
        });
    }
}