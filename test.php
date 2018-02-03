<!DOCTYPE html>
<html >
<head>
    <meta charset="UTF-8">
    <title>JQ</title>
    <script src="jquery-3.3.1.min.js"></script>
</head>
<body>
    <form>
        <input type="text" name="login">
        <input type="password" name="pass">
        <input type="submit" value="PRESS">
    </form>
    <form>
        <input type="text" name="login">
        <input type="password" name="pass">
        <input type="submit" value="PRESS">
    </form>
    <form>
        <input type="text" name="login">
        <input type="password" name="pass">
        <input type="submit" value="PRESS">
    </form>

    <script>

    $('form').submit(function(e){

        let fromData = $('form').serialize();

        console.log(fromData);

        $.post('/answer.php', {data: fromData}, function(answer){
            alert(answer);
        });

        e.preventDefault();
    });

    </script>
</body>
</html>