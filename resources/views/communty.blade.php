<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">

{{--<script src="https://vk.com/js/api/xd_connection.js?2"  type="text/javascript"></script>--}}
<script src="https://code.jquery.com/jquery-2.2.0.min.js"></script>

<script type="text/javascript">
    function checkButton() {
        var ids = document.getElementById("public_link").value;
        console.log(ids);
//        $publicInfo = $this->getInfoFromContactAboutPublic($community->vk)->response[0];
//        $community->name = $publicInfo->name;
//        $community->status = Community::STATUS_PARSED;
//        $community->save();
        //$result = file_get_contents('https://api.vk.com/method/groups.getById?group_ids=' . temp);


//        var token = "b6cfa04deb51b990556252284a6a69c59952e0f3b4d2846627d8f160df570b5c2a91b0001562aaef3f711"
//        var API = "https://api.vk.com/method/"
//        var options = {'method' : 'post','payload' : {code: 0,access_token:token}}
//        options.payload.code='return API.groups.getById({"group_id":"'+ids+'"});'
//        fs = UrlFetchApp.fetch(API+"execute", options)
//        res = JSON.parse(fs);
//        console.log(res);

//        var XHR = ("onload" in new XMLHttpRequest()) ? XMLHttpRequest : XDomainRequest;
//        console.log(XHR)
//        var x = new XHR();
//        console.log(x)
//        //x.open("GET", API + "groups.getById?group_id=" + ids, true);
//        x.open("POST", "https://api.vk.com/method/groups.getById?group_id=43503600", true);
//        console.log(x)
//        console.log('Ща будет результат, наверн')
//        x.onload = function (){
//            console.log('resp')
//            console.log( x.responseText);
//        }
//        x.onerror = function() {
//            console.log('err')
//            console.log( 'Ошибка ' + x.status );
//        }



//        VK.init(function() {
//            VK.api("groups.getById", {"group_id": "43503600"}, function (data) {
//                console.log(data.response);
//            });
//        }, function() {
//            console.log('Подрубиться к VK API не удалось')
//        }, '5.63');

        var ajaxErr=true;
        $.ajax({
            url: "https://api.vk.com/method/groups.getById?group_id=" + ids,
            dataType: "jsonp",
            success:function(e){
                ajaxErr=false;
                console.log(e.response[0])
                //console.log(e.error)
            }
//            error: function(e){
//                console.log(e)
//            }
        });
        console.log(ajaxErr)
        if (ajaxErr) {alert('Чёт пошло не так');}


    }
</script>

<head>
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap.min.css" integrity="sha384-BVYiiSIFeK1dGmJRAkycuHAHRg32OmUcww7on3RYdg4Va+PmSTsz/K68vbdEjh4u" crossorigin="anonymous">
    <!-- Optional theme -->
    <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/css/bootstrap-theme.min.css" integrity="sha384-rHyoN1iRsVXV4nD0JutlnGaslCJuC7uwjduW9SVrLvRYooPp2bWYgmgJQIXwl/Sp" crossorigin="anonymous">
    <!-- Latest compiled and minified JavaScript -->
    <script src="https://maxcdn.bootstrapcdn.com/bootstrap/3.3.7/js/bootstrap.min.js" integrity="sha384-Tc5IQib027qvyjSMfHjOMaLkfuWVxZxUPnCJA7l2mCWNIpG9mGCD8wGNIcPD7Txa" crossorigin="anonymous"></script>

</head>


<body>
<div class="container">

    <form action="/save-comm" class="form-inline">
        <div class="form-group">
            <input type="text" placeholder="public id" name="public_id" class="form-control">
        </div>
        <button type="submit" class="btn btn-success">Добавить</button>
    </form>


    <form action="" class="form-inline">
        <input type="text" placeholder="Ссылка на паблик" id="public_link" class="form-control">
        <button type="button" class="btn btn-success" onclick="checkButton()">Проверить</button>
    </form>

    <table class="table">
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

</body>
</html>
