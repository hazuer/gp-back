<!DOCTYPE html>
<html>
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>Barcode</title>

        <!-- Fonts -->
        <link href="https://fonts.googleapis.com/css2?family=Nunito:wght@200;600&display=swap" rel="stylesheet">

        <!-- Styles -->
        <link rel="stylesheet" href="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/css/bootstrap.min.css" integrity="sha384-Gn5384xqQ1aoWXA+058RXPxPg6fy4IWvTNh0E263XmFcJlSAwiGgFAW/dAiS6JXm" crossorigin="anonymous">
        <script src="https://code.jquery.com/jquery-3.2.1.slim.min.js" integrity="sha384-KJ3o2DKtIkvYIK3UENzmM7KCkRr/rE9/Qpg6aAZGJwFDMVNA/GpGFF93hXpG5KkN" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.12.9/umd/popper.min.js" integrity="sha384-ApNbgh9B+Y1QKtv3Rn7W3mgPxhU9K/ScQsAP7hUibX39j7fakFPskvXusvfa0b4Q" crossorigin="anonymous"></script>
        <script src="https://maxcdn.bootstrapcdn.com/bootstrap/4.0.0/js/bootstrap.min.js" integrity="sha384-JZR6Spejh4U02d8jOt6vLEHfe/JQGiRRSQQxSfFWpi1MquVdAyjUar5+76PVCmYl" crossorigin="anonymous"></script>
    </head>
    <style>
@media print {
    #printOnly {
       display : block;
    }
}
    </style>    
    <body>
        <div class="flex-center position-ref full-height">  
         <div class="row">
           <div class="col-6 d-flex justify-content-end">
                <img class="card p-2 m-2" width="350em" height="350em" src="{!!DNS2D::getBarcodePNGPath($dataQr->id_ot_detalle_tinta , 'QRCODE',50,50)!!}" alt="barcode"  />
           </div>
           <div class="col-6">
             <br><br>  
             <h1>{{$dataQr->nombre_tinta}}</h1>  
             <br><br>
             <h3>Folio: {{$dataQr->folio_entrega}}</h3>
             <h3>O.Fab: {{$dataQr->orden_trabajo_of}}</h3>
             <h3>Diseño: {{$dataQr->nombre_diseno}}</h3>
           </div>
          </div>     
        </div>

  <script>
$(document).ready(function () {
    window.print();
});
  </script>
    </body>
</html>
