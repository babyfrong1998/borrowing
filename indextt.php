<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Niramit:wght@200&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="style.css?<?php echo time() ?>">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/mdb-ui-kit/7.1.0/mdb.min.css" rel="stylesheet" />
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.0.0/dist/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-select@1.13.14/dist/css/bootstrap-select.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/1.13.7/css/jquery.dataTables.css" />
    <title>ระบบยืมคืนครุภัณฑ์ IT</title>

    
</head>

<style>
   body {
        background-image: url(img/bg.png);
        background-attachment: fixed;
        background-repeat: no-repeat;
        
    } 
</style>
<body>
<div class="container">
  <div class="row"> 
    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          
            <a href="login1.php">
              <button type="button" id="menu" class="btn btn-info col-12">พนักงาน</button>
            </a>
        </div>
      </div>  
        
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          
            <a href="login2.php">
              <button type="button" id="menu" class="btn btn-info col-12">ช่างไอที</button>
            </a>
        </div>
      </div>  
        
    </div>

    <div class="col-md-4">
      <div class="card">
        <div class="card-body">
          
            <a href="login3.php">
              <button type="button" id="menu" class="btn btn-info col-12">แอดมิน</button>
            </a>
        </div>
      </div>  
        
    </div>
  </div>
</div>

</body>
</html>