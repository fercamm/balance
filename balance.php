<?php
    require "DBController.php";

    $db_handle = new DBController();

    $numberFormatterUSD = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);
    $numberFormatterARS = new \NumberFormatter("it-IT", \NumberFormatter::CURRENCY);

    $date = new DateTime();
    if (!empty($_POST)) {
        $date = new DateTime($_POST["date"]);
    }

    $query = "select account, sum(amount) as total from transactions where date <= ? and deleted_at is null group by account";
    $balance = $db_handle->runQuery($query, 's', array($date->format('Y-m-d')));

    $query = "select * from exchange_rate where date <= ? and deleted_at is null order by date desc limit 1";
    $rate = $db_handle->runQuery($query, 's', array($date->format('Y-m-d')));
$rate = $rate ? $rate[0]['rate'] : 1;
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
    </head>

    <body class="container">
        <h3>Balance al <?php echo $date->format('d/m/Y') ?></h3>

        <div class="row">
            <?php foreach($balance as $account){ ?>
                <label class="col-sm-4">
                    <?php
                    $total;
                    switch($account['account']){
                        case 1:
                            $accountName = 'Banco en pesos';
                            $total = $numberFormatterARS->formatCurrency($account['total'], "ARS");
                            break;
                        case 2:
                            $accountName = 'Efectivo en pesos';
                            $total = $numberFormatterARS->formatCurrency($account['total'], "ARS");
                            break;
                        case 3:
                            $accountName = 'Banco en dólares';
                            $total = $numberFormatterUSD->formatCurrency($account['total'], "USD");
                            break;
                        case 4:
                            $accountName = 'Efectivo en dólares';
                            $total = $numberFormatterUSD->formatCurrency($account['total'], "USD");
                            break;
                        case 5:
                            $accountName = 'Criptomonedas';
                            $total = $numberFormatterUSD->formatCurrency($account['total'], "USD");
                            break;
                    }
                    echo $accountName;
                    ?>
                </label>
                <span class="col-sm-8"><?php echo $total ?></span>
            <?php } ?>
        </div>
        
        <div class="row" style="margin-top: 10px;">
            <label class="col-sm-4">Total de pesos</label>
            <span class="col-sm-8">
                <?php
                    $total = 0;
                    foreach($balance as $account){
                        if(in_array($account['account'], array(1,2)))
                            $total += $account['total'];
                    }
                ?>
                <?php echo $numberFormatterARS->formatCurrency($total, "ARS") ?>
            </span>

            <label class="col-sm-4">Total de dólares</label>
            <span class="col-sm-8">
                <?php
                    $total = 0;
                    foreach($balance as $account){
                        if(!in_array($account['account'], array(1,2)))
                            $total += $account['total'];
                    }
                ?>
                <?php echo $numberFormatterUSD->formatCurrency($total, "USD") ?>
            </span>
        </div>

        <div class="row" style="margin-top: 10px;">
            <label class="col-sm-4">Total en pesos</label>
            <span class="col-sm-8">
                <?php
                    $total = 0;
                    foreach($balance as $account){
                        if(in_array($account['account'], array(1,2)))
                            $total += $account['total'];
                        if(!in_array($account['account'], array(1,2)))
                            $total += $account['total'] * $rate;
                    }
                ?>
                <?php echo $numberFormatterARS->formatCurrency($total, "ARS") ?>
            </span>

            <label class="col-sm-4">Total en dólares</label>
            <span class="col-sm-8">
                <?php
                    $total = 0;
                    foreach($balance as $account){
                        if(in_array($account['account'], array(1,2)))
                            $total += $account['total'] / $rate;
                        if(!in_array($account['account'], array(1,2)))
                            $total += $account['total'];
                    }
                ?>
                <?php echo $numberFormatterUSD->formatCurrency($total, "USD") ?>
            </span>
        </div>

        <div class="row" style="margin-top: 10px;">
            <label class="col-sm-4">Conversión 1 dólar</label>
            <span class="col-sm-8">
                <?php echo $numberFormatterARS->formatCurrency($rate, "ARS") ?>
            </span>
        </div>

        <form class="row" method="POST" style="margin-top: 10px;">
            <div class="form-group">
                <label for="date">Fecha del balance</label>
                <input type="date" class="form-control" id="date" name="date" aria-describedby="dateHelp" placeholder="dd/mm/yyyy" required value="<?php echo $date->format('Y-m-d'); ?>">
                <small id="dateHelp" class="form-text text-muted">Fecha para conocer el balance</small>
            </div>
            
            <button type="submit" class="btn btn-primary" style="margin-top: 5px;">Buscar balance a la fecha</button>
        </form>

        <p><a href="/transactions.php">Transacciones</a></p>
        <p><a href="/addTransaction.php">Agregar movimientos</a></p>
    </body>
</html>