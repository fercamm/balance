<?php
    require "DBController.php";

    $db_handle = new DBController();
    
    $numberFormatterUSD = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
    $numberFormatterARS = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
    
    $transactions = array();
    $dateFrom = new DateTime('first day of this month');
    $dateTo = new DateTime('last day of this month');

    if (!empty($_POST)) {
        if(!empty($_POST["transaction_id"])){
            if($_POST["method"] == 'UPDATE'){
                $transaction_id = $_POST["transaction_id"];
                $amount = $_POST["amount"];
                $description = $_POST["description"];
                $date = new DateTime($_POST["date"]);
    
                $query = "update transactions set amount = ?, date = ?, description = ? where id = ?";
                $result = $db_handle->update($query, 'dssi', array($amount, $date->format('Y-m-d'), $description, $transaction_id));
            }else{//DELETE
                $transaction_id = $_POST["transaction_id"];
    
                $query = "update transactions set deleted_at = now() where id = ?";
                $result = $db_handle->update($query, 'i', array($transaction_id));
            }
            die;
        }else{
            $dateFrom = new DateTime($_POST["date_from"]);
            $dateTo = new DateTime($_POST["date_to"]);
            
            $query = "select * from transactions where date >= ? and date <= ? and deleted_at is null order by date asc";
            $transactions = $db_handle->runQuery($query, 'ss', array($dateFrom->format('Y-m-d'), $dateTo->format('Y-m-d')));
        }
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

    <body class="container">
        <h3>
            Transacciones
            <?php
            if (!empty($_POST)) {
                echo 'del ';
                echo $dateFrom->format('d/m/Y');
                echo ' al ';
                echo $dateTo->format('d/m/Y');
            }
            ?>
        </h3>
        <div class="row" style="margin-top: 10px;">
            <?php foreach($transactions as $transaction){ ?>
                <span class="col-sm-2">
                    <span id="span_date_transaction_id_<?php echo $transaction['id'] ?>">
                        <?php echo (new DateTime($transaction['date']))->format('d/m/Y') ?>
                    </span>
                    <input type="date" class="form-control" style="display: none;" id="input_date_transaction_id_<?php echo $transaction['id'] ?>" name="input_date_transaction_id_<?php echo $transaction['id'] ?>" value="<?php echo (new DateTime($transaction['date']))->format('Y-m-d') ?>">
                </span>
                <span class="col-sm-3">
                    <?php
                        $amount;
                        switch($transaction['account']){
                            case 1:
                                $amount = $numberFormatterARS->formatCurrency($transaction['amount'], "ARS");
                                break;
                            case 2:
                                $amount = $numberFormatterARS->formatCurrency($transaction['amount'], "ARS");
                                break;
                            case 3:
                                $amount = $numberFormatterUSD->formatCurrency($transaction['amount'], "USD");
                                break;
                            case 4:
                                $amount = $numberFormatterUSD->formatCurrency($transaction['amount'], "USD");
                                break;
                            case 5:
                                $amount = $numberFormatterUSD->formatCurrency($transaction['amount'], "USD");
                                break;
                        }
                    ?>
                    <span id="span_amount_transaction_id_<?php echo $transaction['id'] ?>">
                        <?php echo $amount ?>
                    </span>
                    <input type="number" class="form-control" style="display: none;" id="input_amount_transaction_id_<?php echo $transaction['id'] ?>" name="input_amount_transaction_id_<?php echo $transaction['id'] ?>" value="<?php echo $transaction['amount'] ?>">
                </span>
                <span class="col-sm-5">
                    <span id="span_description_transaction_id_<?php echo $transaction['id'] ?>">
                        <?php echo $transaction['description'] ?>
                    </span>
                    <input type="text" class="form-control" style="display: none;" id="input_description_transaction_id_<?php echo $transaction['id'] ?>" name="input_description_transaction_id_<?php echo $transaction['id'] ?>" value="<?php echo $transaction['description'] ?>">
                </span>
                <span class="col-sm-2">
                    <p><a id="edit_transaction_id_<?php echo $transaction['id'] ?>" href="javascript:editTransaction(<?php echo $transaction['id'] ?>)">Editar</a></p>
                    <p><a id="save_transaction_id_<?php echo $transaction['id'] ?>" style="display: none;" href="javascript:saveTransaction(<?php echo $transaction['id'] ?>)">Guardar</a></p>
                    <p><a href="javascript:deleteTransaction(<?php echo $transaction['id'] ?>)">Eliminar</a></p>
                </span>
            <?php } ?>
        </div>

        <form class="row" method="POST" style="margin-top: 10px;">
            <div class="form-group">
                <label for="date_from">Fecha desde</label>
                <input type="date" class="form-control" id="date_from" name="date_from" placeholder="dd/mm/yyyy" required value="<?php echo $dateFrom->format('Y-m-d') ?>">
            </div>

            <div class="form-group">
                <label for="date_to">Fecha hasta</label>
                <input type="date" class="form-control" id="date_to" name="date_to" placeholder="dd/mm/yyyy" required value="<?php echo $dateTo->format('Y-m-d') ?>">
            </div>
            
            <button id="btn_submit" type="submit" class="btn btn-primary" style="margin-top: 5px;">Buscar transacciones</button>
        </form>

        <p><a href="/balance.php">Balance</a></p>
        <p><a href="/addTransaction.php">Agregar movimientos</a></p>
    </body>
    <script language="JavaScript">
        const deleteTransaction = (transactionId) => {
            Swal.fire({
				title: 'Confirma la eliminación?',
                html: 'Esta acción no se puede deshacer',
				icon: 'warning',
				showCancelButton: true,
				confirmButtonText: `Eliminar`,
				cancelButtonText: `Cancelar`,
			}).then((result) => {
				if (result.isConfirmed) {
                    $.ajax({
                        type: 'POST',
                        url: "/transactions.php",
                        cache: false,
                        data: 'transaction_id=' + transactionId + '&method=DELETE',
                        success: function (html) {
                            console.log(html)
                            if (html == '') {
                                swal.close()
                                Swal.fire(
                                    'Transacción eliminada',
                                    '',
                                    'success'
                                )

                                $('#btn_submit').click()
                            }
                        },
                        error: function (XMLHttpRequest, textStatus, errorThrown) {
                            swal.close()
                            Swal.fire(
                                'Error al eliminar',
                                'Revise los datos. Por favor, vuelva a intentarlo.',
                                'error'
                            )
                        }
                    });
                }
			})
        }

        const editTransaction = (transactionId) => {
            $('#span_description_transaction_id_'+transactionId).hide()
            $('#input_description_transaction_id_'+transactionId).show()

            $('#span_amount_transaction_id_'+transactionId).hide()
            $('#input_amount_transaction_id_'+transactionId).show()

            $('#span_date_transaction_id_'+transactionId).hide()
            $('#input_date_transaction_id_'+transactionId).show()

            $('#edit_transaction_id_'+transactionId).hide()
            $('#save_transaction_id_'+transactionId).show()
        }

        const saveTransaction = (transactionId) => {
            const date = $('#input_date_transaction_id_'+transactionId).val()
            const amount = $('#input_amount_transaction_id_'+transactionId).val()
            const description = $('#input_description_transaction_id_'+transactionId).val()

            let swal = Swal.fire({
                title: 'Guardando...',
                html: '.',
                didOpen: () => {
                    Swal.showLoading()
                }
            })

            $.ajax({
                type: 'POST',
                url: "/transactions.php",
                cache: false,
                data: 'transaction_id=' + transactionId + '&date=' + date + '&amount=' + amount + '&description=' + description + '&method=UPDATE',
                success: function (html) {
                    console.log(html)
                    if (html == '') {
                        swal.close()
                        Swal.fire(
                            'Datos guardados',
                            '',
                            'success'
                        )

                        $('#btn_submit').click()
                    }
                },
                error: function (XMLHttpRequest, textStatus, errorThrown) {
                    swal.close()
                    Swal.fire(
                        'Error al guardar',
                        'Revise los datos. Por favor, vuelva a intentarlo.',
                        'error'
                    )
                }
            });
        }
    </script>
</html>