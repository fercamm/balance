<?php
require "DBController.php";
require "Util.php";

$inserted = 0;
if (!empty($_POST)) {
    $db_handle = new DBController();
    
    $date = $_POST["date"];
    $type = intval($_POST["type"]);
    $amount = floatval($_POST["amount"]) * $type;
    $account = $_POST["account"];
    $description = $_POST["description"];
    $periodicity = $_POST["periodicity"];
    $duedate = $_POST["duedate"];

    if(empty($duedate)){
        $duedate = new DateTime($date);
    }else{
        $duedate = new DateTime($duedate);
    }
    $date = new DateTime($date);

    while($date <= $duedate){
        //inserta la transaccion
        $query = "INSERT INTO transactions (account, amount, date, description) values (?, ?, ?, ?)";
        $result = $db_handle->insert($query, 'idss', array($account, $amount, $date->format('Y-m-d'), $description));
        $inserted++;
        if($periodicity == 2){
            $date->modify('+1 month');
        }else{
            $date->modify('+1 week');
        }
    }
    
    // $util = new Util();
    // $util->redirect("balance.php");
}

?>

<html>
    <head>
        <link rel="stylesheet" href="./css/font-awesome.min.css">
        <link rel="stylesheet" href="/bootstrap-5.0.2-dist/css/bootstrap.css">
        <style>
            /* FONTS */
            @import url('https://fonts.googleapis.com/css2?family=Montserrat:wght@100;400;600;800&family=Roboto:wght@100;400;900&display=swap');
        </style>

        <script src="./jquery/jquery-3.6.0.min.js"></script>
        <script src="/bootstrap-5.0.2-dist/js/bootstrap.min.js"></script>
        <script src="./sweetalert2/sweetalert2.min.js"></script>
    </head>

    <?php
        if($inserted) echo "
            <script>
                $( document ).ready(function() {
                    Swal.fire(
                        'Transacción agregada',
                        '',
                        'success'
                    )
                });
            </script>"
    ?>

    <body class="container">
        <h3>Agregar movimiento</h3>
        
        <form class="row" method="POST">
            <div class="form-group">
                <label for="date">Fecha</label>
                <input type="date" class="form-control" id="date" name="date" aria-describedby="dateHelp" placeholder="dd/mm/yyyy" required value="<?php echo date('Y-m-d'); ?>">
                <small id="dateHelp" class="form-text text-muted">Fecha de la transacción</small>
            </div>

            <div class="form-group">
                <label for="type">Tipo de transacción</label>
                <select class="form-control custom-select" id="type" name="type" required>
                    <option value="">Seleccione</option>
                    <option value="1">Ingreso</option>
                    <option value="-1">Egreso</option>
                </select>
            </div>
            
            <div class="form-group">
                <label for="amount">Monto</label>
                <input type="number" min="1" class="form-control" id="amount" name="amount" aria-describedby="amountHelp" placeholder="1234.99" required>
                <small id="amountHelp" class="form-text text-muted">Negativo para un egreso. Positivo para un ingreso.</small>
            </div>
            
            <div class="form-group">
                <label for="account">Cuenta</label>
                <select class="form-control custom-select" id="account" name="account" required>
                    <option value="">Seleccione</option>
                    <option value="1">Banco pesos</option>
                    <option value="2">Efectivo pesos</option>
                    <option value="3">Banco dólares</option>
                    <option value="4">Efectivo dólares</option>
                    <option value="5">Criptomonedas</option>
                </select>
            </div>

            <div class="form-group">
                <label for="description">Descripción</label>
                <input type="text" class="form-control" id="description" name="description" placeholder="Ej: Compra de materiales." required>
            </div>

            <div class="form-group">
                <label for="periodicity">Periodicidad</label>
                <select class="form-control custom-select" id="periodicity" name="periodicity" required>
                    <option value="">Seleccione</option>
                    <option value="1">Unica vez</option>
                    <option value="2">Todos los meses, en la fecha ingresada</option>
                    <option value="3">Todas las semanas, en la fecha ingresada</option>
                </select>
            </div>

            <div class="form-group invisible" id="div_duedate">
                <label for="duedate">Hasta</label>
                <input type="date" class="form-control" id="duedate" name="duedate" aria-describedby="dateHelp" placeholder="dd/mm/yyyy">
                <small id="duedateHelp" class="form-text text-muted">Fecha hasta de la periodicidad de la transacción.</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 5px;">Registrar</button>
        </form>

        <p><a href="/balance.php">Balance</a></p>
        <p><a href="/transactions.php">Transacciones</a></p>
    </body>
</html>

<script language="JavaScript">
    $( "#periodicity" ).change(function() {
        if(this.value == 1){
            $('#div_duedate').removeClass('visible').addClass('invisible')
            $('#div_duedate').removeAttr('required')
        }else{
            $('#div_duedate').removeClass('invisible').addClass('visible')
            $('#div_duedate').attr('required')
        }
    });
</script>