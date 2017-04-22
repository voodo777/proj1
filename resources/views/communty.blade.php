<!DOCTYPE html>
<html lang="{{ config('app.locale') }}">
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
