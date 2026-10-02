<!DOCTYPE html>
<html>
<head>

<title>Access Denied</title>

<style>

body{
    font-family:Arial;
    background:#f5f7fc;
    display:flex;
    height:100vh;
    justify-content:center;
    align-items:center;
}


.box{

background:white;
padding:40px;
border-radius:15px;
text-align:center;
box-shadow:0 5px 20px #ccc;

}


h1{
color:#16246D;
}


a{
text-decoration:none;
color:white;
background:#16246D;
padding:10px 20px;
border-radius:8px;
}

</style>

</head>

<body>


<div class="box">

<h1>Access Denied</h1>

<p>You do not have permission to access this page.</p>

<p>Redirecting to POS in 3 seconds...</p>


<script>

setTimeout(function(){

    window.location.href="pos.php";

},3000);


</script>


</div>


</body>
</html>